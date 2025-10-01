<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: index.php");
    exit();
}

require_once '../../config/conexion.php';
require_once '../../models/Usuario.php';
require_once '../../models/Ticket.php';

$usuario = new Usuario();
$ticket = new Ticket();

$usuarios = $usuario->obtenerUsuarios();
$tecnicos = $usuario->obtenerUsuariosPorRol(3);
$todos_tickets = $ticket->obtenerTodosLosTickets();

$admin_count = count(array_filter($usuarios, function($u) { return $u['rol_id'] == 1; }));
$cliente_count = count(array_filter($usuarios, function($u) { return $u['rol_id'] == 2; }));
$tecnico_count = count(array_filter($usuarios, function($u) { return $u['rol_id'] == 3; }));

$tickets_abiertos = count(array_filter($todos_tickets, function($t) { return $t['estado'] == 'Abierto'; }));
$tickets_progreso = count(array_filter($todos_tickets, function($t) { return $t['estado'] == 'En Progreso'; }));
$tickets_resueltos = count(array_filter($todos_tickets, function($t) { return $t['estado'] == 'Resuelto' || $t['estado'] == 'Cerrado'; }));
$total_tickets = count($todos_tickets);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tecnicoId'], $_POST['id_ticket'])) {
    $tecnicoId = intval($_POST['tecnicoId']);
    $ticketId = intval($_POST['id_ticket']);
    
    if ($ticket->asignarTecnico($ticketId, $tecnicoId)) {
        header("Location: admin.php?mensaje=tecnico_asignado");
        exit();
    } else {
        $error_message = "Error al asignar el técnico. Por favor, inténtelo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css">
    <title>Panel de Administración - Mesa de Ayuda</title>
</head>
<body>
    <header class="bg-dark text-white py-3 shadow">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-0"> Panel de Administración</h1>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">👋 Admin: <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
                    <a href="perfil.php" class="btn btn-outline-warning btn-sm">Perfil</a>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-5">
        <!-- Resumen ejecutivo -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center mb-4">Resumen Ejecutivo - Mesa de Ayuda</h2>
            </div>
        </div>

        <!-- Estadísticas de Tickets -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Estadísticas de Tickets</h4>
                    <a href="tecnico.php" class="btn btn-sm btn-primary shadow-sm">
                        Ver Tickets
                    </a>
                </div>

            </div>
            <div class="col-md-3">
                <div class="card text-center stat-card bg-primary text-white">
                    <div class="card-body p-3">
                        <h4><?php echo $total_tickets; ?></h4>
                        <h6 class="card-title">
                                Total Tickets
                        </h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center stat-card bg-warning text-dark">
                    <div class="card-body p-3">
                        <h4><?php echo $tickets_abiertos; ?></h4>
                        <h6 class="card-title"> Abiertos</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center stat-card bg-info text-white">
                    <div class="card-body p-3">
                        <h4><?php echo $tickets_progreso; ?></h4>
                        <h6 class="card-title"> En Progreso</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center stat-card bg-success text-white">
                    <div class="card-body p-3">
                        <h4><?php echo $tickets_resueltos; ?></h4>
                        <h6 class="card-title"> Resueltos</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Usuarios -->
        <div class="row mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Estadísticas de Usuarios</h4>
                <a href="gestionar_usuario.php" class="btn btn-sm btn-primary shadow-sm">
                    Ver Usuarios
                </a>
            </div>

            <div class="col-md-4">
                <div class="card text-center stat-card bg-purple-1 text-white">
                    <div class="card-body p-3">
                        <h4><?php echo $admin_count; ?></h4>
                        <h6 class="card-title"> Administradores</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center stat-card bg-purple-2 text-white">
                    <div class="card-body p-3">
                        <h4><?php echo $cliente_count; ?></h4>
                        <h6 class="card-title"> Clientes</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center stat-card bg-purple-3 text-white">
                    <div class="card-body p-3">
                        <h4><?php echo $tecnico_count; ?></h4>
                        <h6 class="card-title"> Técnicos</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Recientes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"> Tickets Recientes</h5>
                        <a href="../Tickets/mis_tickets.php" class="btn btn-primary btn-sm">Ver Todos los Tickets</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($todos_tickets)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-3"> No hay tickets registrados</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Número</th>
                                            <th>Usuario</th>
                                            <th>Asunto</th>
                                            <th>Departamento</th>
                                            <th>Prioridad</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Tecnico</th>
                                            <th>Ver</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $tickets_recientes = array_slice($todos_tickets, 0, 10);
                                        foreach ($tickets_recientes as $t): 
                                        ?>
                                            <tr>
                                                <td><strong class="text-primary"><?php echo htmlspecialchars($t['numero_ticket']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($t['nombre_usuario']); ?></td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;">
                                                        <?php echo htmlspecialchars($t['asunto']); ?>
                                                    </div>
                                                </td>
                                                <td><small><?php echo htmlspecialchars($t['nombre_departamento']); ?></small></td>
                                                <td>
                                                    <?php
                                                    $prioridad_colors = [
                                                        'Baja' => 'success', 'Media' => 'warning', 'Alta' => 'orange', 'Crítica' => 'danger'
                                                    ];
                                                    $color = $prioridad_colors[$t['prioridad']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo $t['prioridad']; ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $estado_colors = [
                                                        'Abierto' => 'warning', 'En Progreso' => 'info', 'Resuelto' => 'success', 'Cerrado' => 'secondary'
                                                    ];
                                                    $color = $estado_colors[$t['estado']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo $t['estado']; ?></span>
                                                </td>
                                                <td><small><?php echo date('d/m/Y', strtotime($t['fecha_creacion'])); ?></small></td>
                                                <td class="d-flex justify-content-around">
                                                    <?php 
                                                    if ($t['tecnico_asignado']) {
                                                        echo htmlspecialchars($t['tecnico_asignado']);
                                                        echo '<button type="button" class="btn btn-primary asignarTecnicoModal" data-id="' . $t['id'] . '" data-bs-toggle="modal"
                                                        data-bs-target="#addsuppmodal"><i class="fa-solid fa-retweet"></i></button>';
                                                    } else {
                                                        echo '<button type="button" class="btn btn-primary asignarTecnicoModal" data-id="' . $t['id'] . '" data-bs-toggle="modal" 
                                                        data-bs-target="#addsuppmodal"> Asignar Técnico </button>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="../Tickets/ver_ticket.php?id=<?php echo $t['id']; ?>&usuario_id=<?php echo $t['usuario_id']; ?>" class="btn btn-info btn-sm text-white">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Asignar Técnico -->
        <div class="modal fade" id="addsuppmodal" tabindex="-1" aria-labelledby="addsuppmodalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="asignarTecnicoForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="addsuppmodalLabel">Asignar Técnico</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="id_ticket" name="id_ticket" value="">
                            <div class="mb-3">
                                <label for="tecnicoId" class="form-label">Seleccionar Técnico</label>
                                <select class="form-select" id="tecnicoId" name="tecnicoId" required>
                                    <option value="" disabled selected>Seleccione un técnico</option>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?php echo $tecnico['id']; ?>"><?php echo htmlspecialchars($tecnico['nombre_usuario']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cerrar </button>
                            <button type="submit" class="btn btn-primary"> Guardar cambios </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Usuarios Registrados -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">👥 Usuarios Registrados</h5>
            </div>
            <div class="card-body">
                <?php if (empty($usuarios)): ?>
                    <div class="alert alert-info text-center">
                        <strong>ℹ No hay usuarios registrados</strong><br>
                        <a href="Registro.php" class="btn btn-primary mt-2">Registrar primer usuario</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Celular</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $user): ?>
                                    <tr>
                                        <td><strong><?php echo $user['id']; ?></strong></td>
                                        <td>
                                            <?php 
                                            $icon = '';
                                            switch($user['rol_id']) {
                                                case 1: $icon = ''; break;
                                                case 2: $icon = ''; break;
                                                case 3: $icon = ''; break;
                                            }
                                            echo $icon . ' ' . htmlspecialchars($user['nombre_usuario']); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['correo_usuario']); ?></td>
                                        <td><?php echo htmlspecialchars($user['celular_usuario']); ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = '';
                                            switch($user['rol_id']) {
                                                case 1: $badge_class = 'bg-danger'; break;
                                                case 2: $badge_class = 'bg-success'; break;
                                                case 3: $badge_class = 'bg-info'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($user['nombre_rol']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $fecha = new DateTime($user['fecha_registro']);
                                            echo $fecha->format('d/m/Y H:i');
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (isset($user['activo']) && $user['activo'] == 1): ?>
                                                <span class="badge bg-success"> Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"> Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="mt-4 text-center">
            <a href="Registro.php" class="btn bg-purple-3 me-2 text-white"> Registrar Nuevo Usuario</a>
            <a href="tecnico.php" class="btn btn-primary me-2"> Gestionar Tickets</a>
            <a href="gestionar_usuario.php" class="btn btn-success me-2 text-white"> Ver Usuarios</a>
            <a href="cliente.php" class="btn btn-info me-2 text-white"> Vista de Cliente</a>
            <a href="../logout.php" class="btn btn-outline-secondary"> Cerrar Sesión</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Mesa de Ayuda Empresarial - Admin</h5>
                    <p class="mb-0">Panel de administración y gestión</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">📞 Contacto: (57) 123-456-7890</p>
                    <p class="mb-0">📧 Email: admin@empresa.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../public/Scripts/script.js"></script>
</body>
</html>