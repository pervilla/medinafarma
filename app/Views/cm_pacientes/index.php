<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<style>
    .search-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .search-input-group {
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border-radius: 50px;
        overflow: hidden;
        background: white;
        display: flex;
        align-items: center;
        padding: 5px 15px;
    }
    .search-input-group input {
        border: none !important;
        box-shadow: none !important;
        font-size: 1.2rem;
        padding: 10px 15px;
    }
    .search-input-group .btn-search {
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 600;
        text-transform: uppercase;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-users text-success mr-2"></i> <?= esc($titulo) ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Buscador -->
        <div class="search-container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="search-input-group">
                        <i class="fas fa-search text-muted ml-2"></i>
                        <input type="text" class="form-control" id="busqueda" placeholder="Buscar por Nombre o DNI del paciente..." autocomplete="off">
                        <button type="button" id="buscar" class="btn btn-success btn-search shadow-sm">
                            <i class="fa fa-search mr-1"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Directorio Clínico</h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalNuevoPaciente">
                                <i class="fas fa-user-plus"></i> Registrar Paciente
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tabla_pacientes" class="table table-hover table-striped mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>DNI/Doc</th>
                                        <th>Nombre del Paciente</th>
                                        <th>Tipo Sangre</th>
                                        <th>Tel. Emergencia</th>
                                        <th>Alergias</th>
                                        <th width="80">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevoPaciente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i> Registrar Nuevo Paciente Clínico</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formPaciente">
                    <input type="hidden" name="id" id="paciente_id">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> <strong>Importante:</strong> El paciente debe existir previamente en el directorio general de Clientes/Farmacia para emitir comprobantes.
                    </div>
                    
                    <div class="form-group border-bottom pb-3">
                        <label>1. Seleccionar Titular/Paciente (Buscador)</label>
                        <select class="form-control" id="cliente_id" name="cliente_id" style="width: 100%;" required></select>
                        <small class="text-muted">Busca por DNI o Nombre. Si no existe, créalo primero en el módulo de Clientes.</small>
                    </div>

                    <h6 class="text-success font-weight-bold mt-3 border-bottom pb-2">2. Ficha Clínica Básica</h6>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo de Sangre</label>
                                <select class="form-control" name="tipo_sangre">
                                    <option value="">Desconocido</option>
                                    <option value="O+">O+</option><option value="O-">O-</option>
                                    <option value="A+">A+</option><option value="A-">A-</option>
                                    <option value="B+">B+</option><option value="B-">B-</option>
                                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Contacto Emergencia</label>
                                <input type="text" class="form-control" name="contacto_emergencia">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tel. Emergencia</label>
                                <input type="text" class="form-control" name="telefono_emergencia">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alergias Conocidas</label>
                        <input type="text" class="form-control" name="alergias" placeholder="Ej. Penicilina, Ibuprofeno, Ninguna...">
                    </div>
                    
                    <div class="form-group">
                        <label>Enfermedades Crónicas</label>
                        <input type="text" class="form-control" name="enfermedades_cronicas" placeholder="Ej. Hipertensión, Diabetes...">
                    </div>

                    <div class="form-group">
                        <label>Observaciones Médicas</label>
                        <textarea class="form-control" name="observaciones_medicas" rows="2"></textarea>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="consentimiento_datos" name="consentimiento_datos" value="1" checked>
                        <label class="custom-control-label" for="consentimiento_datos">El paciente acepta la Ley de Protección de Datos Personales (Ley 29733)</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarPaciente"><i class="fas fa-save mr-1"></i> Guardar Paciente</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="<?= base_url('plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<script src="<?= base_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        // Inicializar Datatable
        var dtable = $('#tabla_pacientes').DataTable({
            ajax: {
                url: "<?= site_url('CmPacientes/get_pacientes') ?>",
                type: "POST",
                dataSrc: '',
                data: function(d) {
                    d.busqueda = $("#busqueda").val();
                }
            },
            columns: [
                { data: 'id' },
                { data: 'DNI', render: data => data ? data : '<span class="text-muted">N/A</span>' },
                { data: 'CLI_NOMBRE', render: data => `<strong>${data}</strong>` },
                { data: 'tipo_sangre', render: data => data ? `<span class="badge badge-danger">${data}</span>` : '' },
                { data: 'telefono_emergencia' },
                { data: 'alergias', render: data => data ? data : '<span class="text-muted">Ninguna</span>' },
                { 
                    data: 'id',
                    render: function(data, type, row) {
                        return '<button class="btn btn-xs btn-warning mr-1 editar-paciente" data-id="'+data+'" title="Editar"><i class="fa fa-edit"></i></button>' +
                               '<button class="btn btn-xs btn-outline-info" title="Ver Ficha"><i class="fa fa-notes-medical"></i></button>';
                    }
                }
            ],
            language: {
                emptyTable: "No se encontraron pacientes registrados",
                loadingRecords: "Cargando...",
                processing: "Procesando..."
            },
            searching: false,
            paging: true,
            responsive: true,
            order: [[0, 'desc']]
        });

        // Búsqueda
        $("#buscar").click(function() {
            dtable.ajax.reload();
        });

        $('#busqueda').on('keypress', function(e) {
            if (e.which == 13) $("#buscar").trigger('click');
        });

        // Inicializar Select2 para el buscador de Titulares/Clientes
        $('#cliente_id').select2({
            theme: 'bootstrap4',
            placeholder: 'Escribe el nombre o DNI del cliente...',
            dropdownParent: $('#modalNuevoPaciente'),
            ajax: {
                url: "<?= site_url('CmPacientes/buscar_titular') ?>",
                type: "post",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { busqueda: params.term };
                },
                processResults: function (response) {
                    // Mapear los resultados de CodeIgniter al formato de Select2
                    let results = [];
                    if(response && response.length > 0) {
                        response.forEach(function(item) {
                            results.push({
                                id: item.id,
                                text: item.text // El modelo ya retorna 'id' y 'text' por el formato 'simple'
                            });
                        });
                    }
                    return { results: results };
                },
                cache: true
            }
        });

        // Editar paciente
        $(document).on('click', '.editar-paciente', function() {
            let id = $(this).data('id');
            $.post("<?= site_url('CmPacientes/get_one') ?>", { id: id }, function(p) {
                $('#paciente_id').val(p.id);
                // cliente_id is Select2, set value and trigger
                let option = new Option(p.CLI_NOMBRE, p.cliente_id, true, true);
                $('#cliente_id').empty().append(option).trigger('change');
                $('[name="tipo_sangre"]').val(p.tipo_sangre);
                $('[name="alergias"]').val(p.alergias);
                $('[name="enfermedades_cronicas"]').val(p.enfermedades_cronicas);
                $('[name="contacto_emergencia"]').val(p.contacto_emergencia);
                $('[name="telefono_emergencia"]').val(p.telefono_emergencia);
                $('[name="observaciones_medicas"]').val(p.observaciones_medicas);
                $('[name="consentimiento_datos"]').prop('checked', p.consentimiento_datos == 1);
                $('.modal-title').html('<i class="fas fa-edit mr-2"></i> Editar Paciente');
                $('#modalNuevoPaciente').modal('show');
            });
        });

        // Resetear formulario al abrir modal nuevo
        $('#modalNuevoPaciente').on('hidden.bs.modal', function() {
            $('#paciente_id').val('');
            $("#formPaciente")[0].reset();
            $('#cliente_id').val(null).trigger('change');
            $('.modal-title').html('<i class="fas fa-user-plus mr-2"></i> Registrar Nuevo Paciente Clínico');
        });

        // Guardar Paciente
        $("#btnGuardarPaciente").click(function() {
            if(!$("#cliente_id").val()) {
                alert("Debes seleccionar un cliente/titular.");
                return;
            }

            let btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.post("<?= site_url('CmPacientes/guardar') ?>", $("#formPaciente").serialize(), function(res) {
                if(res.status == 'success') {
                    $('#modalNuevoPaciente').modal('hide');
                    dtable.ajax.reload();
                    $("#formPaciente")[0].reset();
                    $('#cliente_id').val(null).trigger('change');
                } else {
                    alert(res.msg);
                }
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar Paciente');
            });
        });
    });
</script>
<?= $this->endSection(); ?>
