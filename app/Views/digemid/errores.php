<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión Errores DIGEMID</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Procesar CSV de Errores</h3>
                        </div>
                        <div class="card-body">
                            <form id="formCsv" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Archivo CSV</label>
                                    <input type="file" name="archivo_csv" class="form-control" accept=".csv" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Procesar
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Acciones</h3>
                        </div>
                        <div class="card-body">
                            <button id="btnErrores" class="btn btn-warning btn-block">
                                <i class="fas fa-exclamation-triangle"></i> Ver Productos con Errores
                            </button>
                            <button id="btnSinRelacionar" class="btn btn-info btn-block">
                                <i class="fas fa-unlink"></i> Ver Sin Relacionar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title" id="tituloLista">Productos</h3>
                        </div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            <div id="listaProductos"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalEditar">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Producto</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editCodProd">
                    <div class="form-group">
                        <label>Estado</label>
                        <select id="editEstado" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <textarea id="editObservacion" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" id="btnGuardar" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<style>
.producto-error { border-left: 4px solid #dc3545; }
.producto-activo { border-left: 4px solid #28a745; }
.producto-inactivo { border-left: 4px solid #6c757d; }
.producto-item {
    border: 1px solid #ddd;
    padding: 10px;
    margin: 5px 0;
    border-radius: 4px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<script>
$(document).ready(function() {
    $('#formCsv').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        
        $.ajax({
            url: '<?= site_url('digemiderrores/procesarCsv') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('CSV procesado: ' + response.procesados + ' registros');
                    $('#formCsv')[0].reset();
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error al procesar el archivo');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Procesar');
            }
        });
    });
    
    $('#btnErrores').click(function() {
        cargarErrores();
    });
    
    $('#btnSinRelacionar').click(function() {
        cargarSinRelacionar();
    });
    
    $('#btnGuardar').click(function() {
        guardarCambios();
    });
});

function cargarErrores() {
    $('#tituloLista').text('Productos con Errores');
    
    $.get('<?= site_url('digemiderrores/listarErrores') ?>', function(data) {
        let html = '';
        data.forEach(function(producto) {
            const clase = producto.ESTADO == 1 ? 'producto-activo' : 'producto-error';
            html += `<div class="producto-item ${clase}">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>${producto.Nom_Prod}</strong><br>
                        <small>${producto.Concent} - ${producto.Nom_Form_Farm}</small><br>
                        ${producto.ART_NOMBRE ? '<strong>Artículo:</strong> ' + producto.ART_NOMBRE + '<br>' : ''}
                        <span class="badge badge-${producto.ESTADO == 1 ? 'success' : 'danger'}">
                            ${producto.ESTADO == 1 ? 'Activo' : 'Inactivo'}
                        </span>
                        ${producto.OBSERVACION ? '<br><small class="text-danger">' + producto.OBSERVACION + '</small>' : ''}
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="editarProducto(${producto.Cod_Prod}, ${producto.ESTADO}, '${producto.OBSERVACION || ''}')">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>`;
        });
        $('#listaProductos').html(html);
    }, 'json');
}

function cargarSinRelacionar() {
    $('#tituloLista').text('Productos Sin Relacionar');
    
    $.get('<?= site_url('digemiderrores/sinRelacionar') ?>', function(data) {
        let html = '';
        data.forEach(function(producto) {
            html += `<div class="producto-item">
                <strong>${producto.Nom_Prod}</strong><br>
                <small>${producto.Concent} - ${producto.Nom_Form_Farm} - ${producto.Presentac}</small><br>
                <small class="text-muted">${producto.Nom_Titular}</small><br>
                <span class="badge badge-warning">Sin Relacionar</span>
            </div>`;
        });
        $('#listaProductos').html(html);
    }, 'json');
}

function editarProducto(codProd, estado, observacion) {
    $('#editCodProd').val(codProd);
    $('#editEstado').val(estado);
    $('#editObservacion').val(observacion);
    $('#modalEditar').modal('show');
}

function guardarCambios() {
    const codProd = $('#editCodProd').val();
    const estado = $('#editEstado').val();
    const observacion = $('#editObservacion').val();
    
    $.post('<?= site_url('digemiderrores/actualizarEstado') ?>', {
        cod_prod: codProd,
        estado: estado,
        observacion: observacion
    }, function(response) {
        if (response.success) {
            $('#modalEditar').modal('hide');
            cargarErrores();
        } else {
            alert('Error al guardar');
        }
    }, 'json');
}
</script>
<?= $this->endSection() ?>