<?php

/** @var CodeIgniter\View\View $this*/ ?>
<?= $this->extend('templates/admin_template') ?>
<?= $this->section('content') ?>
<style>
    /* Premium Table & UI Styles */
    #table_detalle tbody tr:hover {
        background-color: #4a4a4a !important; /* Color oscuro para contraste */
        color: #ffffff !important;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    /* Asegurar que el texto de las celdas cambie a blanco al hacer hover */
    #table_detalle tbody tr:hover td {
        color: #ffffff !important;
    }

    /* Evitar que los inputs pierdan legibilidad */
    #table_detalle tbody tr:hover input {
        color: #495057 !important;
    }
    
    #table_detalle thead th {
        position: sticky;
        top: 0; /* Default if not in fixed layout */
        z-index: 1000;
        background-color: #fff;
        border-bottom: 2px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Ajuste para AdminLTE layout-fixed */
    .layout-fixed .main-header + .content-wrapper #table_detalle thead th {
        top: 57px;
    }

    /* Floating Action Bar */
    .floating-action-bar {
        position: fixed;
        bottom: -100px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        padding: 10px 20px;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .floating-action-bar.active {
        bottom: 25px;
    }

    .floating-action-bar .btn {
        border-radius: 8px;
        padding: 6px 15px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .selection-info {
        border-right: 1px solid #ddd;
        padding-right: 15px;
        margin-right: 10px;
        display: flex;
        flex-direction: column;
    }

    .selection-info .count {
        font-weight: 800;
        color: #007bff;
        font-size: 1.1rem;
    }

    .selection-info .text {
        font-size: 0.7rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Animación de entrada para los botones de la tabla */
    .btn-group-sm .btn {
        transition: all 0.2s ease;
    }
    .btn-group-sm .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    /* Balance Indicator */
    .balance-badge {
        font-weight: 700;
        border-radius: 50px;
        padding: 5px 15px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .balance-balanced {
        background-color: #28a745 !important;
        color: white;
    }
    .balance-unbalanced {
        background-color: #dc3545 !important;
        color: white;
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    /* Main Table Selection */
    #table_documentos tbody tr {
        cursor: pointer;
        transition: all 0.1s ease;
    }
    #table_documentos tbody tr.selected {
        background-color: rgba(0, 123, 255, 0.1) !important;
        box-shadow: inset 4px 0 0 #007bff;
    }

    /* Financial Typography */
    #table_detalle td {
        vertical-align: middle !important;
    }
    .col-money {
        font-family: 'Monaco', 'Consolas', monospace;
        font-weight: 600;
        text-align: right;
    }
    
    /* Relationship Column */
    .btn-xs {
        padding: 2px 6px;
        font-size: 0.7rem;
        line-height: 1.2;
    }
    td .small a.btn-go-to-ref:hover {
        text-decoration: underline !important;
    }

    /* Empty State */
    #empty_detail_state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 250px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        margin: 20px;
    }
</style>

<div class="container-fluid">
    <div class="content-header">
    </div>
    <div id="statusMessage" style="display: none;" class="alert alert-info"></div>

    <div class="row">
        <div class="col-12">
            <div id="caja_documentos" class="card shadow">
                <div class="card-header py-2">
                    <!-- Fila 1: Título y Filtros -->
                    <div class="d-flex flex-wrap align-items-center mb-2">
                        <h3 class="card-title mb-0 mr-3 font-weight-bold text-primary" style="white-space: nowrap;">
                            <i class="fas fa-list-ul"></i> FACTURAS
                        </h3>

                        <div class="d-flex flex-wrap align-items-center flex-grow-1" style="gap: 6px;">
                            <!-- Rango de Fechas -->
                            <div id="reportrange" class="btn btn-default btn-sm border shadow-sm" style="min-width: 200px;" aria-label="Rango de Fechas" role="button" title="Seleccionar Rango de Fechas">
                                <i class="fa fa-calendar text-primary"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>

                            <!-- Proveedor -->
                            <select id="b_cliente" name="b_cliente" class="form-control form-control-sm shadow-sm" style="width: 300px; height: 31px;" data-placeholder="Buscar Proveedor" data-allow-clear="1" title="Seleccionar Proveedor"></select>

                            <!-- Tipo Documento -->
                            <select id="f_tipo_doc" name="f_tipo_doc" class="form-control form-control-sm shadow-sm" style="width: 140px; height: 31px;" title="Tipo Documento">
                                <option value="">Todo Tipo</option>
                                <option value="01">Factura</option>
                                <option value="03">Boleta</option>
                                <option value="07">Nota Crédito</option>
                            </select>

                            <!-- Estado Documento -->
                            <select id="f_estado_doc" name="f_estado_doc" class="form-control form-control-sm shadow-sm" style="width: 140px; height: 31px;" title="Estado Documento">
                                <option value="">Todo Estado</option>
                                <option value="0">Pendiente</option>
                                <option value="1">Procesado</option>
                                <option value="10">Pend. Sire</option>
                            </select>

                            <!-- Botón Buscar -->
                            <button id="bListarDoc" class="btn btn-danger btn-sm shadow-sm px-3 font-weight-bold" type="button">
                                <i class="fas fa-search"></i> BUSCAR
                            </button>
                        </div>

                        <!-- Botones de Acción (derecha) -->
                        <div class="d-flex align-items-center ml-auto" style="gap: 4px;">
                            <div class="border-left mx-1 d-none d-lg-block" style="height: 24px;"></div>
                            <button class="btn btn-dark btn-sm shadow-sm" type="button" data-toggle="modal" data-target="#modal-transit-search" title="Buscar productos en facturas pendientes">
                                <i class="fas fa-truck-loading"></i> <span class="d-none d-xl-inline">Tránsito</span>
                            </button>
                            <button class="btn btn-primary btn-sm shadow-sm" id="bimport" type="button" data-toggle="modal" data-target="#uploadModal">
                                <i class="fas fa-file-code"></i> XML
                            </button>
                            <button class="btn btn-info btn-sm shadow-sm" id="bsire" type="button" data-toggle="modal" data-target="#importarSireModal">
                                <i class="fas fa-cloud-download-alt"></i> Sire
                            </button>
                            <button class="btn btn-success btn-sm shadow-sm" id="btn_sync_manual" type="button" title="Sincronizar facturas ya ingresadas manualmente">
                                <i class="fas fa-sync-alt"></i> <span class="d-none d-xl-inline">Sincronizar</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="table_documentos" class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>RUC</th>
                                <th>NOMBRE</th><th>TIPO</th>
                                <th>NRO_FACTURA</th>
                                <th>FECHA</th>
                                <th>TOTAL</th>
                                <th>RELACIÓN</th>
                                <th>GUIA</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div id="caja_detalle" class="card shadow" style="display: none;">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">DETALLE FACTURA</h3>
                    <div id="balance_indicator" class="balance-badge badge-secondary ml-3" style="display: none;">
                        <i class="fas fa-sync fa-spin"></i> <span>Cargando...</span>
                    </div>
                    <div class="card-tools ml-auto">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary shadow-sm" type="button" id="btn_promedio" title="Promediar"><i class="fas fa-percentage"></i> <span class="d-none d-lg-inline">Promedio</span></button>
                            <button class="btn btn-info shadow-sm" type="button" id="btn_descuento" title="Descuento B a A"><i class="fas fa-tags"></i> <span class="d-none d-lg-inline">Desc.B-A</span></button>
                            <button class="btn btn-warning shadow-sm" type="button" id="btn_excluir" title="Excluir"><i class="fas fa-ban"></i> <span class="d-none d-lg-inline">Excluir A</span></button>
                            <button class="btn btn-dark shadow-sm" type="button" id="btn_procesar_reglas" title="Extraer Lote y Vencimiento"><i class="fas fa-magic"></i> <span class="d-none d-lg-inline">Reglas</span></button>
                            <button class="btn btn-secondary shadow-sm" type="button" id="btn_flete" title="Flete"><i class="fas fa-truck"></i> <span class="d-none d-lg-inline">Flete</span></button>
                            <button class="btn btn-danger shadow-sm" type="button" id="btn_eliminar" title="Eliminar"><i class="fas fa-trash"></i> <span class="d-none d-lg-inline">Eliminar</span></button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <div class="row">
                        <div class="col-12">
                            <div class="input-group input-group-sm shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-file-invoice"></i> &nbsp; <span class="d-none d-md-inline">Datos Factura</span></span>
                                </div>
                                <input type="text" class="form-control" id="fac_cod" name="fac_cod" placeholder="RUC" title="RUC Proveedor">
                                <input type="text" class="form-control w-25" id="fac_cli" name="fac_cli" placeholder="Proveedor" title="Nombre Proveedor">
                                <input type="text" class="form-control" id="fac_ser" name="fac_ser" placeholder="Serie" title="Serie">
                                <input type="text" class="form-control" id="fac_num" name="fac_num" placeholder="Número" title="Número">
                                
                                <div class="input-group-prepend ml-2">
                                    <span class="input-group-text bg-primary text-white"><i class="fas fa-file-import"></i> &nbsp; <span class="d-none d-md-inline">Total Fact.</span></span>
                                </div>
                                <input type="text" class="form-control font-weight-bold" id="fac_tot" name="fac_tot" placeholder="Total Factura">
                                
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white"><i class="fas fa-calculator"></i> &nbsp; <span class="d-none d-md-inline">Total Tabla</span></span>
                                </div>
                                <input type="text" class="form-control font-weight-bold" id="table_total" name="table_total" placeholder="Total Tabla" readonly>

                                <div class="input-group-append ml-2">
                                    <button class="btn btn-success shadow font-weight-bold" type="button" id="btn_crear_compra">
                                        <i class="fas fa-check-double"></i> &nbsp; INGRESAR COMPRA
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">

                    <table id="table_detalle" class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>COD.PROV</th>
                                <th>DESCRIPCION FACTURA</th>
                                <th>LOTE</th>
                                <th>VENC</th>
                                <th>ACT</th>
                                <th>DESCRIPCION MEDINA</th>
                                <th>C.MED</th>
                                <th>EQUIV</th>
                                <th>CANT</th>
                                <th class="text-right">PU</th>
                                <th>CoAn</th>
                                <th class="text-right">TOTAL</th>
                                <th>A</th>
                                <th>B</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="card-footer"></div>
            </div>

            <!-- Empty State Container -->
            <div id="empty_detail_card" class="card shadow-none border-0">
                <div id="empty_detail_state">
                    <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted font-weight-light">No hay factura seleccionada</h4>
                    <p class="text-secondary">Haz clic en "Ver" en la lista superior para cargar el detalle y procesar el ingreso.</p>
                </div>
            </div>
        </div>
    </div>


</div>

<!-- Floating Action Bar -->
<div id="floating_actions" class="floating-action-bar">
    <div class="selection-info">
        <span class="count" id="selected_count">0</span>
        <span class="text">Seleccionados</span>
    </div>
    <button class="btn bg-primary btn-sm" type="button" onclick="$('#btn_promedio').click()"><i class="fas fa-percentage"></i> Promedio</button>
    <button class="btn bg-info btn-sm" type="button" onclick="$('#btn_descuento').click()"><i class="fas fa-tags"></i> Desc.B-A</button>
    <button class="btn btn-warning btn-sm" type="button" onclick="$('#btn_excluir').click()"><i class="fas fa-ban"></i> Excluir</button>
    <button class="btn btn-danger btn-sm" type="button" onclick="$('#btn_eliminar').click()"><i class="fas fa-trash"></i> Eliminar</button>
    <div style="width: 1px; height: 30px; background: #ddd; margin: 0 5px;"></div>
    <button class="btn btn-success btn-sm" type="button" onclick="$('#btn_crear_compra').click()"><i class="fas fa-check-double"></i> Ingresar Compra</button>
</div>


<div class="modal fade" id="importarSireModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="overlay" style="display: none;">
                <i class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <!-- Header con gradiente -->
            <div class="modal-header border-0 text-white py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-cloud-download-alt fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0">Importar desde SIRE</h5>
                        <small class="d-block" style="opacity: 0.85;">Descarga comprobantes de SUNAT</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Body -->
            <div class="modal-body px-4 py-4" style="background: #f8f9fa;">
                <!-- Campos ocultos -->
                <input type="hidden" id="codEstado" name="codEstado" value="0">
                <input type="hidden" id="codDocIde" name="codDocIde" value="6">

                <!-- Tipo de Documento -->
                <div class="form-group mb-3">
                    <label for="tipoDoc" class="small font-weight-bold text-uppercase text-muted mb-1">
                        <i class="fas fa-file-invoice mr-1"></i> Tipo de Comprobante
                    </label>
                    <select class="form-control shadow-sm" id="tipoDoc" name="tipoDoc" style="border-radius: 8px; height: 42px; border-color: #dee2e6;">
                        <option value="01">📄 Factura</option>
                        <option value="F7">📋 Nota de Crédito</option>
                    </select>
                </div>

                <!-- Período -->
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-uppercase text-muted mb-1">
                        <i class="fas fa-calendar-alt mr-1"></i> Período
                    </label>
                    <div id="reportrange3" class="form-control shadow-sm d-flex align-items-center justify-content-between" style="border-radius: 8px; height: 42px; cursor: pointer; border-color: #dee2e6;">
                        <div>
                            <i class="fa fa-calendar text-primary mr-1"></i>
                            <span class="font-weight-bold"></span>
                        </div>
                        <i class="fa fa-caret-down text-muted"></i>
                    </div>
                </div>

                <!-- Info -->
                <div class="small text-muted mb-0 p-2 rounded" style="background: #e9ecef;">
                    <i class="fas fa-info-circle text-info mr-1"></i>
                    Se importarán todos los comprobantes del período seleccionado desde el portal SIRE de SUNAT.
                </div>
            </div>
            <!-- Footer -->
            <div class="modal-footer border-0 px-4 pb-4 pt-0" style="background: #f8f9fa;">
                <button type="button" class="btn btn-light px-4" data-dismiss="modal" style="border-radius: 8px;">
                    Cancelar
                </button>
                <button class="btn text-white px-4 font-weight-bold" id="bimpsire" type="button" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; border: none;">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Importar Comprobantes
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="overlay" style="display: none;">
                <i class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div class="modal-header">
                <h4 class="modal-title">Importar Factura/Boleta de SUNAT</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="uploadForm" action="<?= site_url('importar/procesarFactura') ?>" method="post" enctype="multipart/form-data">
                    <label for="factura_html" class="sr-only">Archivo de Factura HTML</label>
                    <input type="file" id="factura_html" name="factura_html" required>
                    <button type="submit" class="btn btn-sm btn-light">Procesar Factura</button>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modal-producto" data-keyboard="false" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="input-group input-group-sm mb-12">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary">Producto </button>
                    </div>
                    <input type="text" class="form-control" id="busqueda" autocomplete="off" placeholder="Producto (Use '%' para caracter o palabra desconocida)">

                    <span class="input-group-append">
                        <button type="button" id="buscar" name="buscar" class="btn btn-primary"><i class="fa fa-search"> </i> Buscar</button>
                    </span>
                </div>
                <input type="hidden" id="imp_ruc_f" name="imp_ruc_f">
                <input type="hidden" id="imp_cod" name="imp_cod">

            </div>
            <div style="margin-left:5px;margin-right:5px;" class="card-body p-0">
                <table id="productos_centro" class="display compact nowrap dataTable no-footer dtr-inline collapsed table-bordered table-striped" style="width: 100%; background-color:white;">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Productos</th>
                            <th>Und</th>
                            <th>Pqt</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<!-- Modal Buscar en Tránsito -->
<div class="modal fade" id="modal-transit-search" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-search-location"></i> Localizador de Productos en Tránsito</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <div class="input-group mb-4 shadow-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-primary"></i></span>
                    </div>
                    <input type="text" id="transit_search_input" class="form-control form-control-lg" placeholder="Escribe el nombre del producto o código para buscar en facturas pendientes...">
                </div>
                
                <div id="transit_results_container" class="card shadow-none border" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover table-striped table-valign-middle mb-0" id="table_transit_results">
                        <thead class="bg-white" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>PRODUCTO</th>
                                <th class="text-center">CANT.</th>
                                <th class="text-right">PRECIO</th>
                                <th>PROVEEDOR</th>
                                <th>FACTURA</th>
                                <th>FECHA</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center text-muted p-5">
                                    <i class="fas fa-keyboard fa-3x mb-3 d-block opacity-50"></i>
                                    Inicia la búsqueda escribiendo el nombre de un producto arriba...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <p class="small text-muted mb-0"><i class="fas fa-info-circle"></i> Solo se muestran resultados de comprobantes que <b>aún no han sido ingresados</b> al sistema.</p>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-equivalencia" data-keyboard="false" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="input-group input-group-sm mb-12">
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary">Equivalencia </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="inputGroupEquiv">EQUIV.</label>
                            </div>
                            <select class="custom-select" id="inputGroupEquiv">
                                <option selected>Seleccione</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="txt_precio_ini">PRECIO</label>
                            </div>
                            <input type="text" class="form-control" id="txt_precio_ini" readonly>
                            <span class="input-group-text form-control text-center">÷</span>
                            <input type="text" class="form-control" id="txt_factor">
                            <span class="input-group-text form-control text-center">=</span>
                            <div class="input-group-append">
                                <input type="text" class="form-control" id="txt_precio_fin" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-light" id="bequiv">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- DataTables <link rel="stylesheet" href="../../plugins/datatables/jquery.dataTables.min.css">-->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<link rel="stylesheet" href="<?= site_url('../../plugins/datatables-keytable/css/keyTable.dataTables.min.css') ?>">
<link rel="stylesheet" href="../../plugins/jquery-confirm/css/jquery-confirm.css">

<link rel="stylesheet" href="../../plugins/sweetalert2/sweetalert2.min.css">

<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>
<script src="../../plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="<?= site_url('../../plugins/datatables-keytable/js/dataTables.keyTable.min.js') ?>"></script>
<script src="../../plugins/jquery-confirm/js/jquery-confirm.js"></script>
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<script>
    var idfact = 0;
    var idart = 0;
    var canti = 0;
    var equiv = 1;
    var idscmb = [];
    var idcmbb = 0;
    $(document).on('keydown', function(event) {
        if (event.key == "Escape" && $('#modal-producto').is(':visible')) {
            $("#busqueda").val('');
            $("#busqueda").focus();
        }
    });
    $(document).ready(function() {
        // Interceptar el envío del formulario
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault(); // Evitar el envío normal del formulario

            // Mostrar el overlay de carga
            $('.overlay').show();

            // Crear un FormData para enviar el archivo
            var formData = new FormData(this);

            // Enviar el formulario mediante AJAX
            $.ajax({
                url: $(this).attr('action'), // URL del formulario
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Ocultar el overlay de carga
                    $('.overlay').hide();

                    // Convertir la respuesta a JSON (si es necesario)
                    var data = JSON.parse(response);

                    // Verificar si la respuesta es exitosa
                    if (data.status === 200) {
                        // Recargar la tabla DataTable
                        if (typeof dtablefac !== 'undefined' && dtablefac.ajax.reload) {
                            dtablefac.ajax.reload(null, false); // Recargar sin resetear la paginación
                        }

                        // Cerrar el modal
                        $('#uploadModal').modal('hide');

                        // Mostrar un mensaje de éxito (opcional)
                        alert('Factura procesada con éxito.');
                    } else {
                        // Mostrar un mensaje de error
                        alert('Error: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Ocultar el overlay de carga
                    $('.overlay').hide();

                    // Mostrar un mensaje de error
                    alert('Error al procesar la factura: ' + error);
                }
            });
        });
    });
    $(document).ready(function() {
        // Función para inicializar un daterangepicker
        function initializeDateRangePicker(selector, startDate, endDate, callback) {
            $(selector).daterangepicker({
                startDate: startDate,
                endDate: endDate,
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
                    'Este Mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                locale: {
                    "format": "DD/MM/YYYY",
                    "separator": " - ",
                    "applyLabel": "Aplicar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "Desde",
                    "toLabel": "Hasta",
                    "customRangeLabel": "Personalizado",
                    "weekLabel": "W",
                    "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                    "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
                    "firstDay": 1
                }
            }, callback);

            // Ejecutar el callback inicial para mostrar las fechas seleccionadas
            callback(startDate, endDate);
        }

        // Callback para actualizar el texto del rango de fechas
        function updateDateRangeText(start, end, targetSelector) {
            $(targetSelector).html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        }

        // Fechas iniciales
        var start = moment().subtract(15, 'days');
        var end = moment();

        // Inicializar el primer calendario
        initializeDateRangePicker('#reportrange', start, end, function(start, end) {
            updateDateRangeText(start, end, '#reportrange span');
        });

        // Inicializar un segundo calendario (ejemplo)
        initializeDateRangePicker('#reportrange2', start, end, function(start, end) {
            updateDateRangeText(start, end, '#reportrange2 span');
        });
        // Inicializar el tercer calendario (Sire) con lógica de rango mensual obligatorio
        var startSire = moment().startOf('month');
        var endSire = moment().endOf('month');
        
        $('#reportrange3').daterangepicker({
            startDate: startSire,
            endDate: endSire,
            showDropdowns: true,
            linkedCalendars: false,
            ranges: {
                'Este Mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Hace 2 Meses': [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
                'Hace 3 Meses': [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')]
            },
            locale: {
                "format": "DD/MM/YYYY",
                "separator": " - ",
                "applyLabel": "Aplicar",
                "cancelLabel": "Cancelar",
                "fromLabel": "Desde",
                "toLabel": "Hasta",
                "customRangeLabel": "Otro Mes",
                "weekLabel": "W",
                "daysOfWeek": ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                "monthNames": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
                "firstDay": 1
            }
        }, function(start, end) {
            // Forzar que el rango sea del 1 al último día del mes seleccionado
            var s = start.clone().startOf('month');
            var e = end.clone().endOf('month');
            
            updateDateRangeText(s, e, '#reportrange3 span');
            
            // Sincronizar el picker internamente con el ajuste mensual
            setTimeout(function() {
                var drp = $('#reportrange3').data('daterangepicker');
                drp.setStartDate(s);
                drp.setEndDate(e);
            }, 100);
        });

        updateDateRangeText(startSire, endSire, '#reportrange3 span');

        // Evento click del botón #bimpsire
        $(document).ready(function() {
            // Monitorizar cambios en fac_tot para actualizar balance
            $('#fac_tot').on('input change', function() {
                updateBalance();
            });
            $('#bimpsire').on('click', function() {
                var $btn = $(this); // Guardar referencia al botón
                var codEstado = $('#codEstado').val().trim();
                var codDocIde = $('#codDocIde').val().trim();
                var tipoDoc = $('#tipoDoc').val().trim();

                // Obtener fechas del daterangepicker
                var dateRangePicker = $('#reportrange3').data('daterangepicker');
                if (!dateRangePicker) {
                    Swal.fire("Error", "No se pudo obtener el rango de fechas.", "error");
                    return;
                }

                var fechaInicio = moment(dateRangePicker.startDate).format('DD/MM/YYYY');
                var fechaFin = moment(dateRangePicker.endDate).format('DD/MM/YYYY');

                // Validar campos vacíos
                if (!codEstado || !codDocIde || !tipoDoc || !fechaInicio || !fechaFin) {
                    Swal.fire("Atención", "Por favor, complete todos los campos.", "warning");
                    return;
                }

                // Deshabilitar botón y mostrar mensaje de carga
                $btn.prop('disabled', true).text('Procesando...');

                // Enviar solicitud AJAX
                $.ajax({
                    url: 'importar/listaComprasSire',
                    type: 'POST',
                    data: {
                        codEstado: codEstado,
                        codDocIde: codDocIde,
                        tipoDoc: tipoDoc,
                        fecEmisionIni: fechaInicio,
                        fecEmisionFin: fechaFin
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: "Procesando...",
                            text: "Por favor, espere mientras se importan los datos.",
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        tablePrinFact.ajax.reload(null, false);
                        $('#importarSireModal').modal('hide');
                        Swal.fire("Éxito", "Datos procesados correctamente.", "success");
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire("Error", "Hubo un problema al procesar la solicitud. Inténtelo de nuevo.", "error");
                    },
                    complete: function() {
                        // Habilitar botón nuevamente
                        $btn.prop('disabled', false).text('Importar desde SUNAT');
                    }
                });
            });
        });
        $('#b_cliente').select2({
            theme: 'bootstrap4',
            width: $('#b_cliente').data('width') ? $('#b_cliente').data('width') : $('#b_cliente').hasClass('w-100') ? '100%' : 'style',
            placeholder: "Buscar Proveedor",
            allowClear: Boolean($('#b_cliente').data('allow-clear')),
            closeOnSelect: !$('#b_cliente').attr('multiple'),
            ajax: {
                url: "<?= site_url('personas/get_proveedores') ?>",
                dataType: "json",
                processResults: function(data) {
                    return {
                        results: data,
                    };
                },
            },

            escapeMarkup: function(markup) {
                return markup;
            },
        });
        $('#b_cliente').addClass('form-control col-2');
        $("#btn_uploadfactura").click(function() {
            $.confirm({
                title: 'Subir factura',
                icon: 'fa fa-warning',
                type: 'green',
                content: '' +
                    '<form action="" class="formName">' +
                    '<div class="form-group">' +
                    '<label for="facturaup">Subir una factura</label>' +
                    '<input type="file" id="facturaup" placeholder="Factura" class="facturaup form-control" required />' +
                    '</div>' +
                    '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function() {
                            var facturaup = this.$content.find('.facturaup').val();
                            if (!facturaup) {
                                $.alert('Agregar un archivo');
                                return false;
                            }
                            $.post('importar/agregar_factura_up', {
                                idfact: idfact,
                                vflete: flete
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload(null, false);
                            });
                        }
                    },
                    cancel: function() {
                        //close
                    },
                },
                onContentReady: function() {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function(e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        });
        var tablePrinFact = $('#table_documentos').DataTable({
            ajax: {
                url: "<?= site_url('importar/listarDocumentos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    cliente: function() {
                        return $("select#b_cliente option:checked").val();
                    },
                    startDate: function() {
                        return moment($('#reportrange').data('daterangepicker').startDate).format('DD/MM/YYYY');
                    },
                    endDate: function() {
                        return moment($('#reportrange').data('daterangepicker').endDate).format('DD/MM/YYYY');
                    },
                    tipoDoc: function() {
                        return $("select#f_tipo_doc option:checked").val();
                    },
                    estadoDoc: function() {
                        return $("select#f_estado_doc option:checked").val();
                    }
                }
            },
            columns: [{
                    data: 'ID'
                },
                {
                    data: 'RUC'
                },
                {
                    data: 'desRazonSocialEmis'
                },
                {
                    data: 'codCpe',
                    render: function(data, type, row) {
                        const tipos = {
                            '01': 'Factura',
                            '02': 'Boleta',
                            '03': 'Boleta de Venta',
                            '07': 'Nota de Crédito',
                            '08': 'Nota de Débito',
                            '09': 'Guía de Remisión',
                            '20': 'Comprobante de Retención'
                        };
                        return tipos[data] || 'Otro Comprobante';
                    }
                },                {
                    data: 'NRO_FACTURA'
                },
                {
                    data: 'FECHA'
                },
                {
                    data: 'TOTAL'
                },
                {
                    data: 'RELACION',
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted small">-</span>';

                        if (data.tipo === 'nc') {
                            // NC con referencia
                            if (data.ref_encontrada) {
                                let estRef = data.ref_estado;
                                let badgeColor = estRef === 1 ? 'success' : (estRef === 0 ? 'warning' : 'secondary');
                                return '<span class="small">' +
                                    '<i class="fas fa-link text-info" title="NC referencia"></i> ' +
                                    '<a href="javascript:void(0)" class="text-info font-weight-bold btn-go-to-ref" data-ref-id="' + (data.ref_id || '') + '" style="text-decoration:none;">' +
                                    data.ref_nro +
                                    '</a> ' +
                                    '<span class="badge badge-' + badgeColor + '" style="font-size:0.6rem;">E' + (estRef ?? '?') + '</span>' +
                                    '</span>';
                            }
                            // NC sin referencia encontrada en BD
                            if (data.numCpeRel) {
                                return '<span class="small text-muted"><i class="fas fa-link"></i> ' + data.numCpeRel + ' <span class="badge badge-secondary" style="font-size:0.6rem;">ext</span></span>';
                            }
                            // NC sin referencia
                            return '<button class="btn btn-outline-info btn-xs btn-vincular-nc" data-id="' + row.ID + '" title="Obtener referencia desde SUNAT"><i class="fas fa-link"></i> Vincular</button>';
                        }

                        if (data.tipo === 'factura' && data.tiene_nc) {
                            return '<span class="small">' +
                                '<i class="fas fa-exclamation-triangle text-warning"></i> ' +
                                '<span class="text-warning font-weight-bold">' + data.nc_count + ' NC</span>' +
                                '</span>';
                        }

                        if (data.tipo === 'nc_sin_ref') {
                            return '<button class="btn btn-outline-info btn-xs btn-vincular-nc" data-id="' + row.ID + '" title="Obtener referencia desde SUNAT"><i class="fas fa-link"></i> Vincular</button>';
                        }

                        return '<span class="text-muted small">-</span>';
                    }
                },
                {
                    data: 'NRO_GUIA'
                },

                {
                    data: 'NRO_GUIA',
                    render: function(data, type, row, meta) {
                        var rpt = '<div class="btn-group btn-group-sm" role="group" aria-label="Acciones">';
                        rpt = rpt + '<button type="button" class="btn ' + (row.ESTADO === 10 ? 'btn-primary btn-imp-comp' : 'btn-secondary') + '"><i class="nav-icon fas fa-arrow-circle-down"></i> Importar</button>';
                        rpt = rpt + '<button type="button" class="btn ' + (row.TOTAL === null ? 'btn-secondary' : 'btn-info btn-ver-comp') + '"><i class="nav-icon fas fa-eye"></i> Ver</button>';
                        rpt = rpt + '<button type="button" class="btn btn-warning btn-visor-comp"><i class="nav-icon fas fa-file-alt"></i> Visor</button>';
                        rpt = rpt + '<button type="button" class="btn ' + (row.ESTADO === 10 ? 'btn-secondary' : 'btn-danger btn-del-comp') + '"><i class="nav-icon fas fa-trash"></i> Eliminar</button>';
                        rpt = rpt + "</div>";
                        return rpt
                    }
                },

            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.ESTADO == 1) {
                    $(nRow).addClass('bg-success');
                }
                if (aData.ESTADO == 0) {
                    $(nRow).addClass('bg-warning');
                }
                // Resaltar NCs con fondo lavanda
                if (aData.codCpe == '07') {
                    $(nRow).css('background-color', '#e8daf5');
                }
                // Marcar facturas con NC asociada con fondo naranja claro
                if (aData.RELACION && aData.RELACION.tipo === 'factura' && aData.RELACION.tiene_nc) {
                    $(nRow).css('background-color', '#fff3cd');
                }
                return nRow;
            },
            order: [
                [0, 'desc']
            ],
            searching: false,
            paging: true,
            orderable: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            select: true
        });
        $('#table_documentos tbody').on('click', '.btn-imp-comp', function(event) {
            var data = tablePrinFact.row($(this).parents('tr')).data();

            $.confirm({
                title: 'Importar Detalle Comprobante',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a importar el detalle del comprobante desde la Sunat?</b>',
                type: 'blue',
                buttons: {
                    ok: {
                        text: "Si, Importar",
                        btnClass: 'btn-primary',
                        keys: ['enter'],
                        action: function() {
                            // Mostrar mensaje de "Procesando..."
                            $.alert({
                                title: 'Procesando...',
                                content: 'Por favor, espera mientras se importa el comprobante.',
                                type: 'orange',
                                icon: 'fa fa-spinner fa-spin',
                                closeIcon: false,
                                buttons: {}
                            });

                            // Enviar la petición al servidor
                            $.post('importar/importaComprobanteSunat', {
                                ruc: data['RUC'],
                                nro: data['NRO_FACTURA'],
                                cod: data['codCpe']
                            }, function(response) {
                                // Cerrar mensaje de "Procesando..."
                                $('.jconfirm').remove();
if (response.status === 200) {
    // Usar SweetAlert2 como toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: 'success',
        title: response.message
    }).then(() => {
        // Recargar la tabla
        tablePrinFact.ajax.reload(null, false);
    });
}
                               
								else {
                                    $.alert({
                                        title: 'Error',
                                        content: response.message || 'Ocurrió un error al importar el comprobante.',
                                        type: 'red',
                                        icon: 'fa fa-exclamation-triangle'
                                    });
                                }
                            }).fail(function() {
                                // Manejo de error si falla la petición AJAX
                                $('.jconfirm').remove();
                                $.alert({
                                    title: 'Error',
                                    content: 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
                                    type: 'red',
                                    icon: 'fa fa-exclamation-triangle'
                                });
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });

        $('#table_documentos tbody').on('click', '.btn-del-comp', function(event) {
            var data = tablePrinFact.row($(this).parents('tr')).data();
            var id = data['ID'];

            $.confirm({
                title: 'Eliminar Importación',
                icon: 'fa fa-trash',
                content: '¿Estás seguro de eliminar el detalle importado? El estado volverá a pendiente.',
                type: 'red',
                buttons: {
                    confirmar: {
                        text: 'Sí, eliminar',
                        btnClass: 'btn-danger',
                        action: function() {
                            $.post('importar/eliminar_compra', { id: id }, function(response) {
                                if (response.status === 200) {
                                    Swal.fire({
                                        title: "¡Eliminado!",
                                        text: response.message,
                                        icon: "success"
                                    });
                                    tablePrinFact.ajax.reload(null, false);
                                } else if (response.status === 300) {
                                    Swal.fire({
                                        title: "Comprobante Ingresado",
                                        text: response.message,
                                        icon: "warning",
                                        showCancelButton: true,
                                        confirmButtonText: "Ir a anular"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Lógica para ir al módulo de anulaciones o activarlo
                                        }
                                    });
                                } else {
                                    Swal.fire("Error", response.message, "error");
                                }
                            });
                        }
                    },
                    cancelar: function() {}
                }
            });
        });

        $('#table_documentos tbody').on('click', '.btn-ver-comp', function(event) {

            var data = tablePrinFact.row($(this).parents('tr')).data();
            idfact = data['ID'];
            idscmb = []; // Limpiar selección
            $('#floating_actions').removeClass('active');
            
            // Mostrar panel de detalle y ocultar empty state
            $('#caja_detalle').fadeIn();
            $('#empty_detail_card').hide();

            dtablefac.ajax.reload(null, false);
            $("#imp_ruc_f").val(data['CLI_CODCLIE']);
            $("#fac_cod").val(data['CLI_CODCLIE']);
            $("#fac_cli").val(data['CLI_NOMBRE']);
            $("#fac_ser").val(data['ALL_NUMSER']);
            $("#fac_num").val(data['ALL_NUMFACT']);
            $("#fac_tot").val(data['TOTAL']);
            updateBalance();
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                tablePrinFact.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            }
        });

        $('#table_documentos tbody').on('click', '.btn-visor-comp', function(event) {
            var data = tablePrinFact.row($(this).parents('tr')).data();
            data['codCpe']=data['codCpe']=='07'?'F7':data['codCpe'];
            var ruc = data['RUC'];
            var tipoDoc = data['codCpe'] || '01';
            var nroFactura = data['NRO_FACTURA'];
            
            var url = '<?= site_url('importar/visorComprobante') ?>?ruc=' + ruc + 
                      '&tipoDoc=' + tipoDoc + '&nroFactura=' + nroFactura;
            
            // Abrir en nueva ventana
            window.open(url, '_blank', 'width=1000,height=700,scrollbars=yes,resizable=yes');
        });

        var dtablefac = $('#table_detalle').DataTable({
            ajax: {
                url: "<?= site_url('importar/listarDetalleDocumentos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    id: function() {
                        return idfact;
                    }
                }
            },
            columns: [{
                    data: 'ID'
                },
                {
                    data: 'COD_PROD'
                },
                {
                    data: 'DES_PROD'
                },
                {
                    data: 'LOTE'
                },
                {
                    data: 'VENCIMIENTO'
                },
                {
                    data: 'TOTAL_SIST',
                    render: function(data, type, row, meta) {
                        return '<a class="openPopup" id="verProd"><i class="fas fa-eye"></i></a>';
                    }
                },
                {
                    data: 'ART_NOMBRE'
                },
                {
                    data: 'ART_KEY'
                },
                {
                    data: 'FAR_EQUIV',
                    render: function(data, type, row, meta) {
                        return '<a class="openPopup" id="verCant"><i class="fa fa-cubes"></i> ' + data + '</a>';
                    }
                },
                {
                    data: 'CANTIDAD',
                    render: function(data, type, row, meta) {
                        var cantidad = Math.round(data * 1000) / 1000;
                        return cantidad;
                    }
                },
                {
                    data: 'PRECIO',
                    className: 'col-money',
                    render: function(data, type, row, meta) {
                        return '<b>S/ ' + (Math.round(data * 1000) / 1000).toFixed(3) + '</b>';
                    }
                },
                {
                    data: 'ARM_COSPRO',
                    className: 'col-money text-muted',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * row.FAR_EQUIV * 1000) / 1000).toFixed(3);
                    }

                },
                {
                    data: 'TOTAL_SIST',
                    className: 'col-money',
                    render: function(data, type, row, meta) {
                        return "<input type='text' id='TOT_" + row.ID + "' class='form-control form-control-sm rowTotal text-right font-weight-bold' style='width:80px; border-color:#17a2b8;' value='" +
                            (Math.round(data * 1000) / 1000).toFixed(2) + "'>"
                    }
                },
                {
                    data: 'ID',
                    sortable: false,
                    render: function(data, type, row, meta) {
                        return '<input class="check_a" type="checkbox" name="group1" value="' + data + '"/>';
                    }
                },
                {
                    data: 'ID',
                    sortable: false,
                    render: function(data, type, row, meta) {
                        return '<input class="check_b" type="checkbox" name="group2" value="' + data + '"/>';
                    }
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                let api = this.api();
                let intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i :
                        0;
                };
                total = api
                    .column(12)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);
                $("#table_total").val(total.toFixed(2));
                updateBalance();
            },
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var min = aData.ARM_COSPRO * aData.FAR_EQUIV * 0.85;
                var max = aData.ARM_COSPRO * aData.FAR_EQUIV * 1.15;
                
                // Agregar alertas para producto inactivo o equivalencia inexistente
                var alertas = '';
                if (aData.ART_SITUACION == 1) {
                    alertas += '<i class="fas fa-exclamation-triangle text-danger" title="Producto Inactivo"></i> ';
                }
                if (aData.EQUIV_EXISTE == 0 && aData.ART_KEY) {
                    alertas += '<i class="fas fa-question-circle text-warning" title="Equivalencia no existe en PRECIOS"></i> ';
                }
                if (alertas) {
                    $(nRow).find('td:eq(2)').prepend(alertas);
                }
                
                if (aData.ESTADO == 0) {
                    $(nRow).addClass('bg-warning');
                } else if (max > aData.PRECIO && aData.PRECIO > min) {
                    $(nRow).addClass('bg-light');
                } else {
                    $(nRow).addClass('bg-danger');
                }

                return nRow;
            },
            searching: false,
            paging: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false

        });
        $('#table_detalle tbody').on('click', '.check_a', function() {
            var ids = [];
            var $checks = $('input[name="group1"]:checked');
            $checks.each(function() {
                ids.push(this.value);
            });
            idscmb = ids;

            // Actualizar barra flotante
            if (ids.length > 0) {
                $('#selected_count').text(ids.length);
                $('#floating_actions').addClass('active');
            } else {
                $('#floating_actions').removeClass('active');
            }
        });

        $('#table_detalle tbody').on('click', '.check_b', function() {
            $('input[name="group2"]').not(this).prop('checked', false);
            idcmbb = this.value;
            var rowData = dtablefac.row($(this).parents('tr')).data();
            canti = rowData.CANTIDAD;
        });

        var dtable = $('#productos_centro').DataTable({
            ajax: {
                url: "<?= site_url('productos/get_productos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    busqueda: function() {
                        return $("input#busqueda").val();
                    }
                }
            },
            columns: [{
                    data: 'ARM_CODART',
                    orderable: false
                },
                {
                    data: 'ART_NOMBRE'
                },
                {
                    data: 'ART_PQT',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * 1000) / 1000);
                    },
                    orderable: false
                },
                {
                    data: 'ART_UNID',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * 1000) / 1000);
                    },
                    orderable: false
                },
                {
                    data: 'ARM_CODART',
                    render: function(data, type, row, meta) {
                        return "<button id='selprod' class='btn btn-block bg-gradient-primary btn-xs'><i class='fas fa-pills'></i> Seleccionar</button>"
                    }
                }

            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.StockGen > 0) {
                    $(nRow).addClass('bg-success');
                }
                return nRow;
            },
            searching: false,
            paging: true,
            orderable: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            select: true,
            ordering: false,
            oLanguage: {
                "sInfo": " "
            },
            keys: true,
            //dom: 'Bfrt<"col-md-6 inline"i> <"col-md-6 inline"p>',

        });
        dtable.on('key', function(e, datatable, key, cell, originalEvent) {
            if (key == 13) { // Si se presiona Enter
                var rowData = datatable.row(cell.index().row).data();

                $.ajax({
                    url: "<?= site_url('importar/update_producto') ?>",
                    type: "POST",
                    dataType: "json", // Asegura que esperamos JSON
                    data: {
                        cli: $("#imp_ruc_f").val(),
                        cod: $.trim($("#imp_cod").val()),
                        art: rowData['ARM_CODART']
                    },
                    success: function(response) {
                        // Manejo basado en el JSON recibido
                        let color = response.status == 200 ? 'bg-success' : 'bg-danger';

                        $(document).Toasts('create', {
                            class: color,
                            title: 'Producto',
                            body: response.message,
                            position: 'bottomRight',
                            icon: 'far fa-check-circle fa-lg',
                            animation: true,
                            autohide: true,
                            delay: 2500
                        });

                        if (response.status == 200) {
                            dtablefac.ajax.reload(null, false);
                            $('#modal-producto').modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        $(document).Toasts('create', {
                            class: 'bg-danger',
                            title: 'Error',
                            body: 'Error en la conexión con el servidor',
                            position: 'bottomRight',
                            icon: 'fas fa-exclamation-circle',
                            autohide: true,
                            delay: 2500
                        });
                    }
                });
            }

            if (key == 27) { // Si se presiona Escape
                $("#busqueda").focus();
            }
        });
        $('#productos_centro tbody').on('click', '#selprod', function(event) {
            var rowData = dtable.row($(this).parents('tr')).data();
            $('#modal-producto').modal('hide');
            $.post(
                "<?= site_url('importar/update_producto') ?>", {
                    cli: $("#imp_ruc_f").val(),
                    cod: $.trim($("#imp_cod").val()),
                    art: rowData.ARM_CODART
                },
                function(rpta) {
                    const myArray = rpta.split("|");
                    color = myArray[0] == 0 ? 'bg-danger' : 'bg-success';
                    $(document).Toasts('create', {
                        class: color,
                        title: 'Producto',
                        body: myArray[1],
                        position: 'bottomRight',
                        icon: 'far fa-check-circle fa-lg',
                        animation: true,
                        autohide: true,
                        delay: 2500
                    });
                    dtablefac.ajax.reload(null, false);
                    $('#modal-producto').modal('hide');
                }
            );
        });
        $('#busqueda').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            console.log(keyCode);
            if (keyCode == 13) {
                $('#buscar').trigger('click');
            } else if (keyCode == 40) {
                dtable.cell().focus();
                $('#busqueda').blur();
            }
        });

        $('#fac_tot').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            console.log(this.value);
            if (keyCode == 13) {
                $.confirm({
                    title: 'Cambiar Monto',
                    icon: 'fa fa-warning',
                    content: '<b>¿Vas a Cambiar el Monto de la Factura.?</b>',
                    type: 'blue',
                    buttons: {
                        ok: {
                            text: "Si cambiar",
                            btnClass: 'btn-primary',
                            keys: ['enter'],
                            action: function() {
                                $.post('importar/cambiar_monto', {
                                    idfact: idfact,
                                    total: $('#fac_tot').val()
                                }, function(htmlexterno) {
                                    dtablefac.ajax.reload(null, false);
                                });
                            }
                        },
                        cancel: function() {}
                    }
                });

            }
        });

        $("#buscar").click(function() {
            dtable.ajax.reload();
        });

        $("#bListarDoc").click(function() {
            tablePrinFact.ajax.reload();
        });


        $("#btn_promedio").click(function() {
            $.confirm({
                title: 'Promediar Costos',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a promediar el precio de venta.?</b>',
                type: 'blue',
                buttons: {
                    ok: {
                        text: "Si promediar",
                        btnClass: 'btn-primary',
                        keys: ['enter'],
                        action: function() {
                            $.post('importar/promediar_costos', {
                                idfact: idfact,
                                ids: idscmb
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });
        $("#btn_flete").click(function() {

            $dtotal = $('#table_total').val();
            $stotal = $('#fac_tot').val();
            $fle_su = ($stotal - $dtotal);

            $.confirm({
                title: 'Agregar Flete',
                icon: 'fa fa-warning',
                type: 'red',
                content: '' +
                    '<form action="" class="formName">' +
                    '<div class="form-group">' +
                    '<label>Agregar el total del flete</label>' +
                    '<input type="number" placeholder="Flete" class="flete form-control" value="' + $fle_su + '" required />' +
                    '</div>' +
                    '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function() {
                            var flete = this.$content.find('.flete').val();
                            if (!flete) {
                                $.alert('Agregar un monto');
                                return false;
                            }
                            $.post('importar/agregar_flete', {
                                idfact: idfact,
                                vflete: flete
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {
                        //close
                    },
                },
                onContentReady: function() {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function(e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        });
        $("#btn_eliminar").click(function() {
            $.confirm({
                title: 'Eliminar Items',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a ELIMINAR items de la factura?</b>',
                type: 'red',
                buttons: {
                    ok: {
                        text: "Si Eliminar",
                        btnClass: 'btn-danger',
                        keys: ['enter'],
                        action: function() {
                            $.post('importar/eliminar_items', {
                                idfact: idfact,
                                ids: idscmb
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload(null, false);
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });
        $("#btn_excluir").click(function() {
            $.confirm({
                title: 'Excluir Productos',
                icon: 'fa fa-warning',
                content: '<b>¿Vas a excluir estos productos.?</b>',
                type: 'orange',
                buttons: {
                    ok: {
                        text: "Si excluir",
                        btnClass: 'btn-warning',
                        keys: ['enter'],
                        action: function() {
                            $.post('importar/excluir_productos', {
                                idfact: idfact,
                                ids: idscmb
                            }, function(htmlexterno) {
                                dtablefac.ajax.reload();
                            });
                        }
                    },
                    cancel: function() {}
                }
            });
        });

        $("#btn_descuento").click(function() {
            if (idcmbb > 0 && idscmb.length === 1) {
                $.confirm({
                    title: 'Descontar B a A',
                    icon: 'fa fa-warning',
                    content: '<b>Vas a asignar el último costo del sistema a "B" y le vas a descontar a "A".</b>',
                    type: 'purple',
                    buttons: {
                        ok: {
                            text: "Sí quiero descontar",
                            btnClass: 'btn-info',
                            keys: ['enter'],
                            action: function() {
                                $.post('importar/desc_promocion', {
                                    idfact,
                                    ids: idscmb[0],
                                    idcmbb,
                                    canti
                                }, function(response) {
                                    dtablefac.ajax.reload();
                                });
                            }
                        },
                        cancel: function() {}
                    }
                });
            } else {
                $.alert({
                    title: 'Error',
                    content: 'Debes seleccionar un solo elemento en "A" y "B" debe tener un valor mayor a 0.',
                    type: 'red',
                });
            }
        });
        $("#bequiv").click(function() {
            $.post('importar/actualizar_equiv', {
                idfact: idfact,
                codclie: $("#fac_cod").val(),
                artkey: idart,
                equiv: $("#inputGroupEquiv").find(":selected").val(),
                factr: $("#txt_factor").val()
            }, function(htmlexterno) {
                dtablefac.ajax.reload();
                $('#modal-equivalencia').modal('hide');
            });
        })

        $("#btn_procesar_reglas").click(function() {
            if (idfact > 0) {
                Swal.fire({
                    title: "Procesando Reglas...",
                    text: "Extrayendo Lote y Vencimiento.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post("<?= site_url('importar/procesarReglas') ?>", {
                    idfact: idfact
                }, function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        Swal.fire("Éxito", response.message, "success");
                        dtablefac.ajax.reload(null, false);
                    } else if (response.status === 'warning') {
                        Swal.fire("Atención", response.message, "warning");
                    } else {
                        Swal.fire("Error", response.message || "Error desconocido", "error");
                    }
                }).fail(function() {
                    Swal.close();
                    Swal.fire("Error", "Error al procesar las reglas", "error");
                });
            } else {
                Swal.fire("Error", "Seleccione una factura primero", "error");
            }
        });
        $("#btn_crear_compra").click(function() {
            if (idfact > 0) {
                $.confirm({
                    title: 'Hacer Compra',
                    icon: 'fa fa-warning',
                    content: '<b>¿Vas a ingresar una compra.?</b></br><blockquote class="quote-success"><h5>Atención! Revisa bien antes de hacerlo</h5><p></b></br>Gracias.</p></blockquote>',
                    type: 'green',
                    buttons: {
                        ok: {
                            text: "Si quiero hacer la compra",
                            btnClass: 'btn-primary',
                            keys: ['enter'],
                            action: function() {
                                $.post('importar/crea_compra', {
                                    idfact: idfact,
                                    codclie: $("#fac_cod").val()
                                }, function(response) {
                                    if (response.status === 200) {
                                        Swal.fire({
                                            title: "¡Éxito!",
                                            html: response.message,
                                            icon: "success"
                                        });
                                        tablePrinFact.ajax.reload(null, false);
                                        // También recargar detalle o limpiar si es necesario
                                        if (typeof dtablefac !== 'undefined') dtablefac.ajax.reload(null, false);
                                    } else {
                                        Swal.fire("Error", response.message, "error");
                                    }
                                });
                            }
                        },
                        cancel: function() {
                            $.alert({
                                title: 'Gracias',
                                content: 'Errar es humano, perdonar es divino, rectificar es de sabios.',
                                type: 'green',
                            });
                        }
                    }
                });
            } else {
                $.alert({
                    title: 'Error',
                    content: 'No es una factura Valida <i class="fa-regular fa-face-angry"></i>',
                    type: 'red',
                });
            }
        });
        $('#table_detalle tbody').on('click', '#verProd', function(event) {
            var data = dtablefac.row($(this).parents('tr')).data();
            //SI NO TIENE DATOS 
            $("#modal-producto").modal("show");
            $("input#imp_cod").val(data['COD_PROD']);
            $("input#busqueda").val(data['DES_PROD']);
            $('#busqueda').trigger('focus');
        });
        $('#table_detalle tbody').on('click', '#verCant', function(event) {
            var data = dtablefac.row($(this).parents('tr')).data();
            idart = data['ART_KEY'];
            equiv = data['FAR_EQUIV'];
            $.getJSON("<?= site_url('productos/get_equiv') ?>", {
                keyval: idart
            }, function(data) {
                $('#inputGroupEquiv option').remove();
                $.each(data, function(val, text) {
                    var equivo = text.PRE_EQUIV > 1 ? ' x ' + text.PRE_EQUIV : '';
                    var selected = text.PRE_EQUIV == equiv ? true : false;
                    console.log(selected);
                    $('#inputGroupEquiv').append(new Option(text.PRE_UNIDAD + equivo, text.PRE_EQUIV, false, selected));
                });
            });
            $("#modal-equivalencia").modal("show");
            $("input#txt_precio_ini").val(data['PRECIO']);
            $("input#txt_factor").val(1);
            $("input#txt_precio_fin").val(data['PRECIO']);
        });
        $('#table_detalle tbody').on('change', '.rowTotal', function() {
            /* input de cantidad */
            var data = dtablefac.row($(this).parents('tr')).data();
            vl = $(this).val();
            if (vl >= 0) {
                $.post("<?= site_url('importar/actualizaProd') ?>", {
                        id: data.ID,
                        idfact: data.IDFACT,
                        cantidad: data.CANTIDAD,
                        total: vl
                    }, /* und-val-idprod-local */
                    function(rpta) {
                        color = 'bg-success';
                        $(document).Toasts('create', {
                            class: color,
                            title: 'Producto',
                            body: 'Actualizado',
                            position: 'bottomRight',
                            icon: 'far fa-check-circle fa-lg',
                            animation: true,
                            autohide: true,
                            delay: 2500
                        });
                        dtablefac.ajax.reload();
                    }
                );
            } else {
                $.alert({
                    title: 'Error',
                    content: '<i class="fa-solid fa-face-sad-tear"></i> Monto mayor a cero.',
                    type: 'red',
                });
                $(this).val('0');
            }
        });
    });

    function updateBalance() {
        var totalFactura = parseFloat($('#fac_tot').val()) || 0;
        var totalTabla = parseFloat($('#table_total').val()) || 0;
        var diferencia = totalFactura - totalTabla;
        
        var $indicator = $('#balance_indicator');
        $indicator.show();
        
        if (Math.abs(diferencia) < 0.01) {
            $indicator.removeClass('balance-unbalanced badge-secondary').addClass('balance-balanced');
            $indicator.html('<i class="fas fa-check-circle"></i> ¡Cuadrado!');
            $('#fac_tot, #table_total').css('border-color', '#28a745');
        } else {
            $indicator.removeClass('balance-balanced badge-secondary').addClass('balance-unbalanced');
            $indicator.html('<i class="fas fa-exclamation-triangle"></i> Diferencia: S/ ' + diferencia.toFixed(2));
            $('#fac_tot, #table_total').css('border-color', '#dc3545');
        }
    }

    // Lógica para búsqueda en tránsito
    $('#transit_search_input').on('keyup', function() {
        var query = $(this).val();
        if (query.length < 3) return;

        $.post('importar/searchInTransit', { query: query }, function(data) {
            var html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="7" class="text-center p-4"><i class="fas fa-times-circle text-danger mb-2 d-block"></i> No se encontraron productos en tránsito con ese nombre.</td></tr>';
            } else {
                data.forEach(function(item) {
                    html += '<tr>';
                    html += '<td><div class="font-weight-bold">' + item.DES_PROD + '</div><small class="text-muted">' + item.RUC + '</small></td>';
                    html += '<td class="text-center"><span class="badge badge-info p-2" style="font-size:0.9rem;">' + item.CANTIDAD + '</span></td>';
                    html += '<td class="text-right font-weight-bold">S/ ' + parseFloat(item.PRECIO).toFixed(3) + '</td>';
                    html += '<td><small class="text-uppercase">' + (item.PROVEEDOR || 'Desconocido') + '</small></td>';
                    html += '<td><span class="badge badge-light border text-primary">' + item.NRO_FACTURA + '</span></td>';
                    html += '<td>' + moment(item.FECHA).format('DD/MM/YYYY') + '</td>';
                    html += '<td class="text-right"><button class="btn btn-sm btn-primary shadow-sm btn-go-to-fact" data-fact="' + item.NRO_FACTURA + '"><i class="fas fa-eye"></i></button></td>';
                    html += '</tr>';
                });
            }
            $('#table_transit_results tbody').html(html);
        });
    });

    $(document).on('click', '.btn-go-to-fact', function() {
            var nroFact = $(this).data('fact');
            $('#modal-transit-search').modal('hide');
            
            // Buscar en la tabla principal
            if (typeof dtablefac !== 'undefined') {
                dtablefac.search(nroFact).draw();
            }
            
            // Mostrar notificación
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                icon: 'info',
                title: 'Filtrado por factura: ' + nroFact
            });
        });

        // Evento para vincular NC (obtener referencia desde SUNAT)
        $(document).on('click', '.btn-vincular-nc', function() {
            var id = $(this).data('id');
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.post('importar/obtenerReferenciaNC', { id: id }, function(response) {
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Referencia obtenida',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    if (typeof tablePrinFact !== 'undefined' && tablePrinFact) {
                        tablePrinFact.ajax.reload(null, false);
                    }
                } else {
                    Swal.fire('Error', response.message, 'error');
                    $btn.prop('disabled', false).html('<i class="fas fa-link"></i> Vincular');
                }
            }).fail(function() {
                Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-link"></i> Vincular');
            });
        });

        // Evento para ir al comprobante referenciado (delegación global)
        $(document).on('click', '.btn-go-to-ref', function() {
            var refNro = $(this).text().trim();
            if (typeof tablePrinFact !== 'undefined' && tablePrinFact) {
                tablePrinFact.search(refNro).draw();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    icon: 'info',
                    title: 'Buscando: ' + refNro
                });
            }
        });

        // Evento para sincronizar facturas manuales (Delegación global)
        $(document).on('click', '#btn_sync_manual', function() {
            Swal.fire({
                title: '¿Sincronizar ingresos manuales?',
                text: "El sistema buscará facturas pendientes que ya hayan sido ingresadas manualmente al inventario y las marcará como procesadas.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, sincronizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sincronizando...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.post("<?= site_url('importar/verificar_ingresos_manuales') ?>", function(data) {
                        if (data.status === 200) {
                            if (typeof dtablefac !== 'undefined') {
                                dtablefac.ajax.reload(null, false);
                            }
                            Swal.fire({
                                icon: data.actualizados > 0 ? 'success' : 'info',
                                title: 'Sincronización terminada',
                                text: data.mensaje,
                                footer: data.detalles.length > 0 ? 'Documentos: ' + data.detalles.join(', ') : ''
                            });
                        } else {
                            Swal.fire("Error", "Hubo un problema al sincronizar.", "error");
                        }
                    });
                }
        });
    });
</script>



<?= $this->endSection(); ?>