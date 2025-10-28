<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Medinafarma</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Google Font: Source Sans Pro 
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">-->
        <!-- Font Awesome -->
        <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">
        <!-- Ionicons 
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">-->
        <!-- Tempusdominus Bbootstrap 4 -->
        <link rel="stylesheet" href="<?= site_url('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') ?>">
        <!-- iCheck -->
        <link rel="stylesheet" href="<?= site_url('plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
        <!-- JQVMap -->
        <link rel="stylesheet" href="<?= site_url('plugins/jqvmap/jqvmap.min.css') ?>">
        <!-- Theme style -->
        <link rel="stylesheet" href="<?= site_url('dist/css/adminlte.min.css') ?>">
        <!-- overlayScrollbars -->
        <link rel="stylesheet" href="<?= site_url('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
        <!-- Daterange picker -->
        <link rel="stylesheet" href="<?= site_url('plugins/daterangepicker/daterangepicker.css') ?>">
        <!-- summernote -->
        <link rel="stylesheet" href="<?= site_url('plugins/summernote/summernote-bs4.css') ?>">
    </head>
    <body class="hold-transition sidebar-mini layout-fixed text-sm">
        <div class="wrapper">
            <!-- Preloader -->
            <div class="preloader flex-column justify-content-center align-items-center">
                <img class="animation__shake" src="<?= site_url('dist/img/AdminLTELogo.png') ?>" alt="AdminLTELogo" height="60" width="60">
            </div>
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?= site_url('productos') ?>" class="nav-link">Productos</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?= site_url('consultorio') ?>" class="nav-link">Citas Médicas</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?= site_url('caja/dia') ?>" class="nav-link">Caja</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= site_url('requerimiento') ?>" class="nav-link"><i class="fas fa-store nav-icon"></i> Requerimientos</a>
                    </li>
                    

                </ul>

                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">
                    <!-- Navbar Search -->
                    <li class="nav-item">
                        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                            <i class="fas fa-search"></i>
                        </a>
                        <div class="navbar-search-block">
                            <form class="form-inline">
                                <div class="input-group input-group-sm">
                                    <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                    <div class="input-group-append">
                                        <button class="btn btn-navbar" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>

                    <!-- Messages Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-comments"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <a href="#" class="dropdown-item">
                                <!-- Message Start -->
                                <div class="media">
                                    <img src="<?= site_url('dist/img/user1-128x128.jpg') ?>" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                    <div class="media-body">
                                        <h3 class="dropdown-item-title">
                                            Brad Diesel
                                            <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                        </h3>
                                        <p class="text-sm">Call me whenever you can...</p>
                                        <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                    </div>
                                </div>
                                <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <!-- Message Start -->
                                <div class="media">
                                    <img src="<?= site_url('dist/img/user8-128x128.jpg') ?>" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                    <div class="media-body">
                                        <h3 class="dropdown-item-title">
                                            John Pierce
                                            <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                        </h3>
                                        <p class="text-sm">I got your message bro</p>
                                        <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                    </div>
                                </div>
                                <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <!-- Message Start -->
                                <div class="media">
                                    <img src="<?= site_url('dist/img/user3-128x128.jpg') ?>" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                    <div class="media-body">
                                        <h3 class="dropdown-item-title">
                                            Nora Silvester
                                            <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                        </h3>
                                        <p class="text-sm">The subject goes here</p>
                                        <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                    </div>
                                </div>
                                <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                        </div>
                    </li>
                    <!-- Notifications Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-envelope mr-2"></i> 4 new messages
                                <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-users mr-2"></i> 8 friend requests
                                <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-file mr-2"></i> 3 new reports
                                <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="modal" data-target="#modal-login" href="#" role="button">
                            <i class="fas fa-user"></i>
                        </a>
                    </li>

                    
                    <li class="nav-item">
                        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>                            
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <!-- Brand Logo -->
                <a href="<?= site_url('dashboard') ?>" class="brand-link">
                    <img src="<?= site_url('dist/img/AdminLTELogo.png') ?>" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                         style="opacity: .8">
                    <span class="brand-text font-weight-light">Medinafarma</span>
                </a>

                <?php 
                $session = session(); 
                $url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
                if (!isset($menu)){
                    $menu['p']=00;
                    $menu['i']=00;
                }
               
                ?>
                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Sidebar user panel (optional) -->
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="image">
                            <img src="<?= site_url('dist/img/user2-160x160.jpg') ?>" class="img-circle elevation-2" alt="User Image">
                        </div>
                        <div class="info">
                            <a href="#" class="d-block"><?= $session->get('user_id')?$session->get('user_name'):"Invitado"; ?></a>
                        </div>
                    </div>

                    <!-- Sidebar Menu -->
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <!-- Add icons to the links using the .nav-icon class
                                 with font-awesome or any other icon font library -->
                            <li class="nav-item <?=$menu['p']==10?'menu-open':''?> has-treeview">
                                <a href="#" class="nav-link <?=$menu['p']==10?'active':''?>">
                                    <i class="nav-icon fas fa-box-open"></i>
                                    <p>Caja
                                        <i class="fas fa-angle-left right"></i>
                                        <span class="badge badge-info right">Diario</span>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('caja/diario') ?>" class="nav-link <?=$menu['i']==12?'active':''?>">
                                            <i class="fas fa-money-check-alt nav-icon"></i>
                                            <p>Caja Diario</p>
                                        </a>
                                    </li>
                                    <?php if($session->get('user_id')=='ADMIN'){?>  
                                    <li class="nav-item">
                                        <a href="<?= site_url('caja') ?>" class="nav-link <?=$menu['i']==11?'active':''?>">
                                            <i class="fas fa-chart-line nav-icon"></i>
                                            <p>Movimiento Caja</p>
                                        </a>
                                    </li> 
                                    <?php } ?>  
                                    <li class="nav-item">
                                        <a href="<?= site_url('operaciones/ventas') ?>" class="nav-link  <?=$menu['i']==13?'active':''?>">
                                            <i class="fas fa-file-invoice nav-icon text-primary"></i>                                            
                                            <p>Boletas y Facturas</p>
                                        </a>
                                    </li>                                   
                                </ul>
                            </li>
                            <?php if($session->get('user_id')=='ADMIN'){?>  
                            <li class="nav-item <?=$menu['p']==20?'menu-open':''?> has-treeview">
                                <a href="#" class="nav-link <?=$menu['p']==20?'active':''?>">
                                    <i class="nav-icon fas fa-chart-pie"></i>
                                    <p>
                                        Empleados
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('comisiones') ?>" class="nav-link <?=$menu['i']==21?'active':''?>">
                                        <i class="fa fa-male nav-icon"></i><i class="fa fa-bar-chart nav-icon"></i>
                                            <p>Comisiones</p>
                                        </a>
                                    </li>                                    
                                    <li class="nav-item">
                                        <a href="<?= site_url('empleado/rentabilidad') ?>" class="nav-link <?=$menu['i']==23?'active':''?>">
                                        <i class="fa fa-male nav-icon"></i><i class="fa fa-line-chart nav-icon"></i>
                                            <p>Rentabilidad</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= site_url('empleado/creditos_adelantos') ?>" class="nav-link <?=$menu['i']==22?'active':''?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Creditos y Adelantos</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <?php } ?>

                            <?php if($session->get('user_id')=='ADMIN'){?>  
                            <li class="nav-item <?=$menu['p']==30?'menu-open':''?> has-treeview">
                                <a href="#" class="nav-link <?=$menu['p']==30?'active':''?>">
                                    <i class="nav-icon fas fa-tree"></i>
                                    <p>
                                        Operaciones
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('operaciones') ?>" class="nav-link <?=$menu['i']==31?'active':''?>">
                                            <i class="fa fa-asl-interpreting nav-icon text-info"></i>
                                            <p>Costeo Facturas</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= site_url('operaciones/exportacion_guias') ?>" class="nav-link <?=$menu['i']==32?'active':''?>">
                                            <i class="fa fa-cloud-upload nav-icon text-warning"></i>
                                            <p>Exportación Guias</p>
                                        </a>
                                    </li>  
                                    <li class="nav-item">
                                        <a href="<?= site_url('operaciones/ventas') ?>" class="nav-link <?=$menu['i']==33?'active':''?>">
                                            <i class="fa fa-shopping-basket	nav-icon text-primary"></i>
                                            <p>Ventas</p>
                                        </a>
                                    </li>  
                                    <li class="nav-item">
                                        <a href="<?= site_url('importar') ?>" class="nav-link <?=$menu['i']==35?'active':''?>">
                                            <i class="fa fa-cart-arrow-down	 nav-icon text-success"></i>
                                            <p>Compras</p>
                                        </a>
                                    </li>     
                                    <li class="nav-item">
                                        <a href="<?= site_url('cartera') ?>" class="nav-link <?=$menu['i']==34?'active':''?>">
                                            <i class="fa fa-hourglass-2	nav-icon"></i>
                                            <p>Cuentas por Pagar</p>
                                        </a>
                                    </li>                              
                                </ul>
                            </li>
                            <?php } ?>
                            <?php if($session->get('user_id')=='ADMIN'){?>  
                            <li class="nav-item <?=$menu['p']==40?'menu-open':''?> has-treeview">
                                <a href="#" class="nav-link <?=$menu['p']==40?'active':''?>">
                                    <i class="nav-icon fas fa-medkit"></i>
                                    <p>
                                        Consultorio
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('consultorio') ?>" class="nav-link <?=$menu['i']==41?'active':''?>">
                                            <i class="far fa-calendar-alt nav-icon"></i>
                                            <p>Citas Médicas</p>
                                        </a>
                                    </li>                                                              
                                </ul>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('consultorio/campania') ?>" class="nav-link <?=$menu['i']==42?'active':''?>">
                                            <i class="	far fa-calendar-plus nav-icon"></i>
                                            <p>Campañas Médicas</p>
                                        </a>
                                    </li>                                                              
                                </ul>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('consultorio/confirmados') ?>" class="nav-link <?=$menu['i']==43?'active':''?>">
                                            <i class="far fa-calendar-check nav-icon"></i>
                                            <p>Confirmados</p>
                                        </a>
                                    </li>                                                              
                                </ul>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('consultorio/historial') ?>" class="nav-link <?=$menu['i']==44?'active':''?>">
                                            <i class="fas fa-history nav-icon"></i>
                                            <p>Historial Atenciones</p>
                                        </a>
                                    </li>                                                              
                                </ul>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('consultorio/pagos') ?>" class="nav-link <?=$menu['i']==45?'active':''?>">
                                            <i class="fas fa-solid fa-money-bill"></i>
                                            <p>Pagos por Servicios</p>
                                        </a>
                                    </li>                                                              
                                </ul>
                                
                            </li>
                            <?php } ?>                            
                            <li class="nav-item <?=$menu['p']==50?'menu-open':''?> has-treeview">
                                <a href="#" class="nav-link <?=$menu['p']==50?'active':''?>">
                                    <i class="nav-icon fas fa-pills"></i>
                                    <p>
                                        Productos
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('productos') ?>" class="nav-link <?=$menu['i']==51?'active':''?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Listado</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= site_url('requerimiento') ?>" class="nav-link <?=$menu['i']==52?'active':''?>">
                                            <i class="fas fa-store nav-icon"></i>
                                            <p>Requerimientos</p>
                                        </a>
                                    </li>
                                    <?php if($session->get('user_id')=='ADMIN'){?>  
                                    <li class="nav-item">
                                        <a href="<?= site_url('productos/enfermedades') ?>" class="nav-link <?=$menu['i']==53?'active':''?>">
                                            <i class="fa fa-pencil-alt nav-icon"></i>
                                            <p>Enfermedades</p>
                                        </a>
                                    </li>  
                                    <li class="nav-item">
                                        <a href="<?= site_url('productos/get_inventario') ?>" class="nav-link <?=$menu['i']==54?'active':''?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Listado Inventario</p>
                                        </a>
                                    </li> 
                                    <li class="nav-item">
                                        <a href="<?= site_url('productos/get_controlados') ?>" class="nav-link <?=$menu['i']==55?'active':''?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Listado Controlados</p>
                                        </a>
                                    </li>
                                    
                                    <li class="nav-item">
                                        <a href="<?= site_url('productos/actualizar_productos') ?>" class="nav-link <?=$menu['i']==56?'active':''?>">
                                            <i class="fa fa-cloud-upload-alt nav-icon"></i>
                                            <p>Exportar Product/Precios</p>
                                        </a>
                                    </li> 
                                    <li class="nav-item">
                                        <a href="<?= site_url('inventario') ?>" class="nav-link <?=$menu['i']==57?'active':''?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Inventario</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= site_url('digemid') ?>" class="nav-link <?=$menu['i']==58?'active':''?>">
                                            <i class="fas fa-file-archive nav-icon"></i>
                                            <p>Archivo DIGEMID</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= site_url('digemidrelacion') ?>" class="nav-link <?=$menu['i']==59?'active':''?>">
                                            <i class="fas fa-link nav-icon"></i>
                                            <p>Relación DIGEMID</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= site_url('digemiderrores') ?>" class="nav-link <?=$menu['i']==60?'active':''?>">
                                            <i class="fas fa-exclamation-triangle nav-icon"></i>
                                            <p>Errores DIGEMID</p>
                                        </a>
                                    </li> 
                                    <?php } ?>                                  
                                </ul>
                            </li>

                            <?php if($session->get('user_id')=='ADMIN'){?>  
                            <li class="nav-item <?=$menu['p']==70?'menu-open':''?> has-treeview">
                                <a href="#" class="nav-link <?=$menu['p']==70?'active':''?>">
                                    <i class="nav-icon fas fa-diagnoses"></i>
                                    <p>
                                        Gerencia 
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('analisis/compras') ?>" class="nav-link <?=$menu['i']==71?'active':''?>">
                                            <i class="far fa-calendar-alt nav-icon"></i>
                                            <p>Análisis de Compras</p>
                                        </a>
                                    </li>                                                              
                                </ul>
                            </li>
                            <?php } ?>  

                            <li class="nav-item <?=$menu['p']==60?'menu-open':''?> has-treeview">
                                <a href="#" class="nav-link <?=$menu['p']==60?'active':''?>">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Personas <i class="fas fa-angle-left right"></i> </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= site_url('personas') ?>" class="nav-link <?=$menu['i']==61?'active':''?>">
                                            <i class="fa fa-building nav-icon"></i>
                                            <p>Clientes</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="" class="nav-link <?=$menu['i']==62?'active':''?>">
                                            <i class="far fa-user nav-icon"></i>
                                            <p>Empleados</p>
                                        </a>
                                    </li>   
                                    <li class="nav-item">
                                        <a href="<?= site_url('personas/unir') ?>" class="nav-link <?=$menu['i']==63?'active':''?>">
                                        <i class="fas fa-code-merge nav-icon"></i>
                                            <p>Unir Clientes</p>
                                        </a>
                                    </li>                                 
                                </ul>
                            </li>
                            
                        </ul>
                    </nav>
                    <!-- /.sidebar-menu -->
                </div>
                <!-- /.sidebar -->
            </aside>




            <!-- Modal -->
            <div class="modal fade" id="modal-login">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="login-box2">
                            <!-- /.login-logo -->
                            <div class="card card-outline card-primary">
                                <div class="card-header text-center">
                                    <a href="../../index2.html" class="h1"><b>MEDINA</b>FARMA</a>
                                </div>
                                <?php if(empty($session->get('user_id'))){?> 
                                <div class="card-body">
                                    <p class="login-box-msg">Identifíquese, para iniciar sesión</p>                                    
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="usuario" placeholder="Usuario">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-users"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="desc_usuario" placeholder="Descripción">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-user"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="password" class="form-control" id="password" placeholder="Password">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-lock"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-8">
                                                <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                            <!-- /.col -->
                                            <div class="col-4">
                                                <button type="button" class="btn btn-primary btn-block">Iniciar</button>
                                            </div>
                                            <!-- /.col -->
                                        </div>                                    
                                </div>
                                <?php } else {?>
                                    <div class="card-body">
                                    <p class="login-box-msg">Cerrar sesión</p>  
                                    <p><?= $session->get('user_id')?$session->get('user_name'):""; ?></p>
                                        <div class="row">
                                        <div class="col-4">
                                        <button id="salir_session" type="button" class="btn btn-primary btn-block">Salir</button>
                                        </div>
                                        </div>                                    
                                </div>
                                <?php } ?>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>

                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <?= $this->renderSection('content'); ?>
        </div>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="<?= site_url('plugins/jquery/jquery.min.js') ?>"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?= site_url('plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 4 -->
    <script src="<?= site_url('plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <!-- ChartJS -->
    <script src="<?= site_url('plugins/chart.js/Chart.min.js') ?>"></script>
    <!-- Sparkline -->
    <script src="<?= site_url('plugins/sparklines/sparkline.js') ?>"></script>
    <!-- JQVMap -->
    <script src="<?= site_url('plugins/jqvmap/jquery.vmap.min.js') ?>"></script>
    <script src="<?= site_url('plugins/jqvmap/maps/jquery.vmap.usa.js') ?>"></script>
    <!-- jQuery Knob Chart -->
    <script src="<?= site_url('plugins/jquery-knob/jquery.knob.min.js') ?>"></script>
    <!-- daterangepicker -->
    <script src="<?= site_url('plugins/moment/moment.min.js') ?>"></script>
    <script src="<?= site_url('plugins/moment/moment-with-locales.js') ?>"></script>
    <script src="<?= site_url('plugins/daterangepicker/daterangepicker.js') ?>"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="<?= site_url('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') ?>"></script>
    <!-- Summernote -->
    <script src="<?= site_url('plugins/summernote/summernote-bs4.min.js') ?>"></script>
    <!-- overlayScrollbars -->
    <script src="<?= site_url('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?= site_url('dist/js/adminlte.js') ?>"></script>
<script>
    $('#usuario').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        var usuario = $("#usuario").val();
        $.post("<?= site_url('login/user') ?>", {user: usuario}, function (result) {
                if(result){
                    $("#desc_usuario").val(result);
                    $("#password").focus();
                }else{
                    $("#usuario").focus();
                }                
            });
    }
});
$('#password').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        var password = $("#password").val(); var usuario = $("#usuario").val();
        $.post("<?= site_url('login/auth') ?>", {pwd: password,user: usuario}, function (result) {
                if(result){
                    $('#modal-login').modal('hide');
                    location.reload();
                }else{
                    $("#password").focus();
                }                
            });
    }
});
$("#salir_session").click(function(){
  $.post("<?= site_url('login/close') ?>", function(data, status){
    alert("Data: " + data + "\nStatus: " + status);
    location.reload();
  });
});


</script>

    <?= $this->renderSection('footer'); ?>

</body>
</html>
