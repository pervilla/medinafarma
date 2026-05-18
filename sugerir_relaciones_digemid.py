import pyodbc
import pandas as pd
import re
import time
from rapidfuzz import fuzz

# Configuración de conexión (Ajustar si es necesario)
DB_CONFIG = {
    'driver': 'SQL Server',
    'server': 'server',
    'database': 'BDATOS',
    'user': 'sa',
    'password': '159357852456'
}


# =========================================================
# CONEXION
# =========================================================

def get_connection():
    conn_str = (
        f"DRIVER={DB_CONFIG['driver']};"
        f"SERVER={DB_CONFIG['server']};"
        f"DATABASE={DB_CONFIG['database']};"
        f"UID={DB_CONFIG['user']};"
        f"PWD={DB_CONFIG['password']}"
    )
    return pyodbc.connect(conn_str)

# =========================================================
# NORMALIZACION
# =========================================================

REEMPLAZOS = {
    "TABLETAS": "TAB",
    "TABLETA": "TAB",
    "TABS": "TAB",
    "TABL": "TAB",
    "CAPSULAS": "CAP",
    "CAPSULA": "CAP",
    "CAPS": "CAP",
    "AMPOLLAS": "AMP",
    "AMPOLLA": "AMP",
    "JARABE": "JBE",
    "SUSPENSION": "SUSP",
    "SOLUCION": "SOL",
    "INYECTABLE": "INY",
    "MILIGRAMOS": "MG",
    "GRAMOS": "GR",
    "MICROGRAMOS": "MCG",
    "UNIDADES": "UI"
}

SINONIMOS = {
    "ACIDO ACETILSALICILICO": "ASPIRINA",
    "CLORURO DE SODIO": "SUERO"
}

def normalizar(texto):

    if pd.isna(texto):
        return ""

    texto = str(texto).upper().strip()

    for k, v in SINONIMOS.items():
        texto = texto.replace(k, v)

    for k, v in REEMPLAZOS.items():
        texto = texto.replace(k, v)

    texto = re.sub(r'[^A-Z0-9\s]', ' ', texto)

    texto = re.sub(r'\s+', ' ', texto).strip()

    return texto

# =========================================================
# EXTRAER CONCENTRACION
# =========================================================

def extraer_concentracion(texto):

    if not texto:
        return ""

    texto = str(texto).upper()

    matches = re.findall(
        r'(\d+(?:\.\d+)?\s?(?:MG|GR|G|MCG|ML|UI|%))',
        texto
    )

    return " ".join(matches)

# =========================================================
# SIMILITUD
# =========================================================

def similarity(a, b):

    if not a or not b:
        return 0

    return fuzz.token_set_ratio(a, b) / 100

# =========================================================
# COMPARACION FRACCION
# =========================================================

def comparar_fraccion(a, b):

    try:
        return float(a) == float(b)
    except:
        return False

# =========================================================
# BUSCAR CANDIDATOS
# =========================================================

def filtrar_candidatos(df_digemid, nombre):

    tokens = nombre.split()

    if len(tokens) == 0:
        return df_digemid

    tokens_importantes = tokens[:3]

    candidatos = df_digemid[
        df_digemid['norm_name'].apply(
            lambda x: any(t in x for t in tokens_importantes)
        )
    ]

    if candidatos.empty:
        return df_digemid

    return candidatos

# =========================================================
# MATCHING
# =========================================================

def matching_proceso():

    print("\n======================================")
    print(" INICIANDO MATCHING INTELIGENTE ")
    print("======================================\n")

    conn = get_connection()

    # =====================================================
    # CONSULTA MEDINAFARMA
    # =====================================================

    query_medina = """
    SELECT 
        RTRIM(LTRIM(T2.ART_NOMBRE)) AS ART_NOMBRE,
        RTRIM(LTRIM(T4.TAB_NOMLARGO)) AS LABORATORIO,
        T1.ARM_CODART,
        RTRIM(LTRIM(ISNULL(TABLAS2.TAB_NOMLARGO, ''))) AS PRINCIPIO_ACTIVO,
        tpr.PRE_EQUIV as Fraccion           
    FROM dbo.ARTICULO AS T1
    INNER JOIN DBO.ARTI AS T2 
        ON T1.ARM_CODART = T2.ART_KEY 
        AND T1.ARM_CODCIA = T2.ART_CODCIA

    INNER JOIN TABLAS AS T4 
        ON T2.ART_FAMILIA = T4.TAB_NUMTAB 
        AND T4.TAB_CODCIA = 25 
        AND T4.TAB_TIPREG = 122

    LEFT JOIN dbo.TABLAS TABLAS2 
        ON T2.ART_CODCIA = TABLAS2.TAB_CODCIA 
        AND TABLAS2.TAB_TIPREG = 129 
        AND T2.ART_SUBGRU = TABLAS2.TAB_NUMTAB

    INNER JOIN dbo.precios as tpr 
        ON tpr.PRE_CODART = T1.ARM_CODART 
        AND tpr.PRE_FLAG_UNIDAD = 'A'

    WHERE T2.ART_SITUACION = 0 
    AND T4.TAB_NUMTAB NOT IN (442)
    """

    print("Consultando productos internos...")

    df_medina = pd.read_sql(query_medina, conn)

    print(f"Total productos internos: {len(df_medina)}")

    # =====================================================
    # CONSULTA DIGEMID
    # =====================================================

    query_digemid = """
    SELECT 
        Cod_Prod, 
        Nom_Prod, 
        Concent,
        Fracciones,
        Nom_Form_Farm, 
        Nom_Titular, 
        ISNULL(Nom_IFA, '') as Nom_IFA
    FROM PRECIOS_DIGEMID
    WHERE Situacion = 'ACT'
    """

    print("Consultando DIGEMID...")

    df_digemid = pd.read_sql(query_digemid, conn)

    print(f"Total DIGEMID: {len(df_digemid)}")

    # =====================================================
    # NORMALIZAR
    # =====================================================

    print("\nNormalizando datos...")

    df_medina['norm_name'] = df_medina['ART_NOMBRE'].apply(normalizar)

    df_medina['norm_ifa'] = (
        df_medina['PRINCIPIO_ACTIVO']
        .fillna('')
        .apply(normalizar)
    )

    df_medina['norm_concent'] = (
        df_medina['ART_NOMBRE']
        .apply(extraer_concentracion)
        .apply(normalizar)
    )

    df_digemid['norm_name'] = (
        df_digemid['Nom_Prod']
        .fillna('')
        .apply(normalizar)
    )

    df_digemid['norm_ifa'] = (
        df_digemid['Nom_IFA']
        .fillna('')
        .apply(normalizar)
    )

    df_digemid['norm_concent'] = (
        df_digemid['Concent']
        .fillna('')
        .apply(normalizar)
    )

    # =====================================================
    # MATCHING
    # =====================================================

    sugerencias = []

    total = len(df_medina)

    print("\nProcesando matching...\n")

    inicio = time.time()

    for idx, row_m in df_medina.iterrows():

        name_m = row_m['norm_name']
        ifa_m = row_m['norm_ifa']
        concent_m = row_m['norm_concent']

        candidatos = filtrar_candidatos(df_digemid, name_m)

        resultados_producto = []

        for _, row_d in candidatos.iterrows():

            score_nombre = similarity(
                name_m,
                row_d['norm_name']
            )

            score_ifa = similarity(
                ifa_m,
                row_d['norm_ifa']
            )

            score_concent = similarity(
                concent_m,
                row_d['norm_concent']
            )

            # BONUS SI EL IFA ESTA CONTENIDO

            if ifa_m and row_d['norm_ifa']:

                if (
                    row_d['norm_ifa'] in ifa_m
                    or
                    ifa_m in row_d['norm_ifa']
                ):
                    score_ifa = max(score_ifa, 1)

            # BONUS LABORATORIO

            bonus_lab = 0

            try:

                lab_med = str(row_m['LABORATORIO']).upper()

                lab_dig = str(row_d['Nom_Titular']).upper()

                if (
                    lab_med
                    and
                    lab_dig
                    and
                    lab_med.split()[0] in lab_dig
                ):
                    bonus_lab = 0.10

            except:
                pass

            # BONUS FRACCION

            bonus_fraccion = 0

            if comparar_fraccion(
                row_m['Fraccion'],
                row_d['Fracciones']
            ):
                bonus_fraccion = 0.05

            # SCORE FINAL

            final_score = (
                (score_nombre * 0.50)
                +
                (score_ifa * 0.25)
                +
                (score_concent * 0.20)
                +
                bonus_lab
                +
                bonus_fraccion
            )

            resultados_producto.append({
                'score': final_score,
                'row': row_d
            })

        # =================================================
        # TOP 3
        # =================================================

        resultados_producto = sorted(
            resultados_producto,
            key=lambda x: x['score'],
            reverse=True
        )

        top3 = resultados_producto[:3]

        for pos, item in enumerate(top3, start=1):

            score = item['score']
            row_d = item['row']

            if score >= 0.80:

                sugerencias.append({

                    'TOP': pos,

                    'SCORE': round(score, 4),

                    'ARM_CODART': row_m['ARM_CODART'],

                    'ART_NOMBRE': row_m['ART_NOMBRE'],

                    'PRINCIPIO_ACTIVO': row_m['PRINCIPIO_ACTIVO'],

                    'FRACCION_MEDINA': row_m['Fraccion'],

                    'COD_PROD_DIGEMID': row_d['Cod_Prod'],

                    'NOMBRE_DIGEMID': row_d['Nom_Prod'],

                    'CONCENT_DIGEMID': row_d['Concent'],

                    'IFA_DIGEMID': row_d['Nom_IFA'],

                    'FRACCION_DIGEMID': row_d['Fracciones']
                })

        if idx % 100 == 0:

            porcentaje = (idx / total) * 100

            print(
                f"Procesados: {idx}/{total} "
                f"({porcentaje:.2f}%)"
            )

    # =====================================================
    # RESULTADOS
    # =====================================================

    tiempo = time.time() - inicio

    print("\n======================================")
    print(" MATCHING FINALIZADO ")
    print("======================================")

    print(f"\nTiempo: {tiempo:.2f} segundos")

    print(f"Total sugerencias: {len(sugerencias)}")

    if sugerencias:

        df_resultado = pd.DataFrame(sugerencias)

        archivo = "sugerencias_matching_digemid_2.csv"

        df_resultado.to_csv(
            archivo,
            index=False,
            encoding='utf-8-sig'
        )

        print(f"\nArchivo generado: {archivo}")

    conn.close()

# =========================================================
# MAIN
# =========================================================

if __name__ == "__main__":
    matching_proceso()