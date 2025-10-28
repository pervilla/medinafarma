<?= $this->extend('templates/admin_template') ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Relación Productos DIGEMID</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Crear Nueva Relación</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Buscar Producto DIGEMID</label>
                                <input type="text" id="buscarDigemid" class="form-control" placeholder="Escriba nombre del producto...">
                                <div id="resultadosDigemid" class="mt-2"></div>
                            </div>
                            
                            <div class="form-group">
                                <label>Buscar Artículo Interno</label>
                                <input type="text" id="buscarArticulo" class="form-control" placeholder="Escriba nombre del artículo...">
                                <div id="resultadosArticulo" class="mt-2"></div>
                            </div>
                            
                            <div class="form-group">
                                <button id="btnRelacionar" class="btn btn-success" disabled>
                                    <i class="fas fa-link"></i> Crear Relación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title" id="tituloPanel">Relaciones Existentes</h3>
                            <div class="card-tools">
                                <div class="btn-group">
                                    <button id="btnRelaciones" class="btn btn-sm btn-success">
                                        <i class="fas fa-link"></i> Relacionados
                                    </button>
                                    <button id="btnErrores" class="btn btn-sm btn-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Errores
                                    </button>
                                    <button id="btnSinRelacionar" class="btn btn-sm btn-info">
                                        <i class="fas fa-unlink"></i> Sin Relacionar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <div id="contenidoPanel"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.producto-item, .articulo-item {
    border: 1px solid #ddd;
    padding: 8px;
    margin: 2px 0;
    cursor: pointer;
    border-radius: 4px;
}
.producto-item:hover, .articulo-item:hover {
    background-color: #f8f9fa;
}
.producto-item.selected, .articulo-item.selected {
    background-color: #007bff;
    color: white;
}
.relacion-item {
    border: 1px solid #ddd;
    padding: 10px;
    margin: 5px 0;
    border-radius: 4px;
}
.producto-error { border-left: 4px solid #dc3545; }
.producto-activo { border-left: 4px solid #28a745; }
.producto-inactivo { border-left: 4px solid #6c757d; }
</style>

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
                <button type="button" id="btnGuardarEdit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<script>
let productoSeleccionado = null;
let articuloSeleccionado = null;

$(document).ready(function() {
    cargarRelaciones();
    
    // Búsqueda de productos DIGEMID
    $('#buscarDigemid').on('input', function() {
        const termino = $(this).val();
        if (termino.length >= 3) {
            buscarProductosDigemid(termino);
        } else {
            $('#resultadosDigemid').empty();
        }
    });
    
    // Búsqueda de artículos
    $('#buscarArticulo').on('input', function() {
        const termino = $(this).val();
        if (termino.length >= 3) {
            buscarArticulos(termino);
        } else {
            $('#resultadosArticulo').empty();
        }
    });
    
    // Crear relación
    $('#btnRelacionar').click(function() {
        if (productoSeleccionado && articuloSeleccionado) {
            crearRelacion();
        }
    });
    
    // Botones del panel
    $('#btnRelaciones').click(function() {
        cargarRelaciones();
    });
    
    $('#btnErrores').click(function() {
        cargarErrores();
    });
    
    $('#btnSinRelacionar').click(function() {
        cargarSinRelacionar();
    });
    
    $('#btnGuardarEdit').click(function() {
        guardarCambios();
    });
});

function buscarProductosDigemid(termino) {
    $.post('<?= site_url('digemidrelacion/buscar') ?>', {termino: termino}, function(data) {
        let html = '';
        data.forEach(function(producto) {
            html += `<div class="producto-item" data-cod="${producto.Cod_Prod}">
                <strong>${producto.Nom_Prod}</strong><br>
                <small>${producto.Concent} - ${producto.Nom_Form_Farm} - ${producto.Presentac}</small><br>
                <small class="text-muted">${producto.Nom_Titular}</small>
            </div>`;
        });
        $('#resultadosDigemid').html(html);
        
        $('.producto-item').click(function() {
            $('.producto-item').removeClass('selected');
            $(this).addClass('selected');
            productoSeleccionado = $(this).data('cod');
            verificarSeleccion();
        });
    }, 'json');
}

function buscarArticulos(termino) {
    $.post('<?= site_url('digemidrelacion/buscarArticulos') ?>', {termino: termino}, function(data) {
        let html = '';
        data.forEach(function(articulo) {
            html += `<div class="articulo-item" data-key="${articulo.ART_KEY}">
                <strong>${articulo.ART_NOMBRE}</strong>
            </div>`;
        });
        $('#resultadosArticulo').html(html);
        
        $('.articulo-item').click(function() {
            $('.articulo-item').removeClass('selected');
            $(this).addClass('selected');
            articuloSeleccionado = $(this).data('key');
            verificarSeleccion();
        });
    }, 'json');
}

function verificarSeleccion() {
    if (productoSeleccionado && articuloSeleccionado) {
        $('#btnRelacionar').prop('disabled', false);
    } else {
        $('#btnRelacionar').prop('disabled', true);
    }
}

function crearRelacion() {
    $.post('<?= site_url('digemidrelacion/relacionar') ?>', {
        cod_prod: productoSeleccionado,
        pre_codeart: articuloSeleccionado
    }, function(response) {
        if (response.success) {
            alert('Relación creada exitosamente');
            limpiarSeleccion();
            cargarRelaciones();
        } else {
            alert('Error al crear la relación');
        }
    }, 'json');
}

function limpiarSeleccion() {
    productoSeleccionado = null;
    articuloSeleccionado = null;
    $('#buscarDigemid').val('');
    $('#buscarArticulo').val('');
    $('#resultadosDigemid').empty();
    $('#resultadosArticulo').empty();
    $('#btnRelacionar').prop('disabled', true);
}

function cargarRelaciones() {
    $('#tituloPanel').text('Relaciones Existentes');
    $.get('<?= site_url('digemidrelacion/relacionados') ?>', function(data) {
        let html = '';
        data.forEach(function(relacion) {
            html += `<div class="relacion-item producto-activo">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>DIGEMID:</strong> ${relacion.Nom_Prod}<br>
                        <small>${relacion.Concent} - ${relacion.Nom_Form_Farm}</small><br>
                        <strong>Artículo:</strong> ${relacion.ART_NOMBRE}
                    </div>
                    <button class="btn btn-sm btn-danger" onclick="eliminarRelacion(${relacion.Cod_Prod})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>`;
        });
        $('#contenidoPanel').html(html);
    }, 'json');
}

function cargarErrores() {
    $('#tituloPanel').text('Productos con Errores');
    $.get('<?= site_url('digemiderrores/listarErrores') ?>', function(data) {
        let html = '';
        data.forEach(function(producto) {
            const clase = producto.ESTADO == 1 ? 'producto-activo' : 'producto-error';
            html += `<div class="relacion-item ${clase}">
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
        $('#contenidoPanel').html(html);
    }, 'json');
}

function cargarSinRelacionar() {
    $('#tituloPanel').text('Productos Sin Relacionar (Primeros 50)');
    $.get('<?= site_url('digemidrelacion/sinRelacionarLimitado') ?>', function(data) {
        let html = '';
        data.forEach(function(producto) {
            html += `<div class="relacion-item">
                <strong>${producto.Nom_Prod}</strong><br>
                <small>${producto.Concent} - ${producto.Nom_Form_Farm} - ${producto.Presentac}</small><br>
                <small class="text-muted">${producto.Nom_Titular}</small><br>
                <span class="badge badge-warning">Sin Relacionar</span>
            </div>`;
        });
        $('#contenidoPanel').html(html);
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

function eliminarRelacion(codProd) {
    if (confirm('¿Está seguro de eliminar esta relación?')) {
        $.post('<?= site_url('digemidrelacion/eliminar') ?>', {cod_prod: codProd}, function(response) {
            if (response.success) {
                cargarRelaciones();
            } else {
                alert('Error al eliminar la relación');
            }
        }, 'json');
    }
}
</script>
<?= $this->endSection() ?>