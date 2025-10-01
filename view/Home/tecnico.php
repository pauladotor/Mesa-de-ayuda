<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if($_SESSION['rol_id'] != 3 && $_SESSION['rol_id'] != 1) {
    header("Location: index.php");
    exit();
}

require_once '../../config/conexion.php';
require_once '../../models/Ticket.php';

$ticket = new Ticket();
$usuario_id = $_SESSION['user_id'];

$mis_tickets = $ticket->obtenerTicketsTecnico($usuario_id);

if($_SESSION['rol_id'] == 1) {
    $mis_tickets = $ticket->obtenerTodosLosTickets();
}

// Condicion para actualizar el estado del ticket
if(isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['estado']) && !empty($_GET['estado'])) {
    $ticket_id = (int)$_GET['id'];
    $nuevo_estado = $_GET['estado'];

    $resultado = $ticket->actualizarEstadoTicket($ticket_id, $nuevo_estado);
    if ($resultado['success']) {
        header("Location: tecnico.php?id=$ticket_id");
        exit();
    } else {
        $error_message = $resultado['message'];
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
    <title>Mis Tickets - Mesa de Ayuda</title>
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3 shadow">
    <div class="container">
        <div class="row align-items-center">
            
            <!-- Botón Inicio dinámico según el rol -->
            <div class="col-md-2">
                <a href="<?php echo ($_SESSION['rol_id'] == 1) ? 'admin.php' : 'tecnico.php'; ?>" 
                   class="btn btn-primary btn-sm">
                   Inicio
                </a>
            </div>

            <!-- Título -->
            <div class="col-md-4">
                <h1 class="h3 mb-0">Tickets</h1>
            </div>

            <!-- Usuario y cerrar sesión -->
            <div class="col-md-6 text-end">
                <span class="me-3">
                    👋 
                    <?php
                    if ($_SESSION['rol_id'] == 1) {
                        echo "Admin: ";
                    } elseif ($_SESSION['rol_id'] == 3) {
                        echo "Técnico: ";
                    } else {
                        echo "Usuario: ";
                    }
                    echo htmlspecialchars($_SESSION['nombre_usuario']);
                    ?>
                </span>
                <a href="perfil.php" class="btn btn-outline-warning btn-sm">Perfil</a>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</header>


    <!-- Lista de tickets -->
        <div class="card m-4 shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"> Lista de Tickets (<?php echo count($mis_tickets); ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (empty($mis_tickets)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-ticket-alt fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">No se encontraron tickets</h5>
                        <p class="text-muted"> Aun no se ha creado un ticket por solucionar. </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Número</th>
                                    <th>Asunto</th>
                                    <th>Departamento</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Última Actualización</th>
                                    <th>ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mis_tickets as $t): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary">
                                                <?php echo htmlspecialchars($t['numero_ticket']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($t['asunto']); ?>">
                                                <?php echo htmlspecialchars($t['asunto']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($t['nombre_departamento']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php
                                            $prioridad_colors = [
                                                'Baja' => 'success',
                                                'Media' => 'warning',
                                                'Alta' => 'orange',
                                                'Crítica' => 'danger'
                                            ];
                                            $prioridad_icons = [
                                                'Baja' => '',
                                                'Media' => '',
                                                'Alta' => '',
                                                'Crítica' => ''
                                            ];
                                            $color = $prioridad_colors[$t['prioridad']] ?? 'secondary';
                                            $icon = $prioridad_icons[$t['prioridad']] ?? '';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo $icon . ' ' . htmlspecialchars($t['prioridad']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $estado_colors = [
                                                'Abierto' => 'warning',
                                                'En Progreso' => 'info',
                                                'Resuelto' => 'success',
                                                'Cerrado' => 'secondary'
                                            ];
                                            $estado_icons = [
                                                'Abierto' => '',
                                                'En Progreso' => '',
                                                'Resuelto' => '',
                                                'Cerrado' => ''
                                            ];
                                            $color = $estado_colors[$t['estado']] ?? 'secondary';
                                            $icon = $estado_icons[$t['estado']] ?? '';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo $icon . ' ' . htmlspecialchars($t['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y H:i', strtotime($t['fecha_creacion'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($t['fecha_actualizacion'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="tecnico.php?id=<?php echo $t['id']; ?>" 
                                                class="btn btn-outline-primary btn-sm" 
                                                title="Ver detalles">
                                                    Ver Detalles
                                                </a>
                                            </div>
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

    <!-- Detalles del ticket seleccionado -->

    <!-- Verifica si se ha seleccionado un ticket -->
    <?php if (isset($_GET['id'])): 
        $ticket_id = (int)$_GET['id'];
        $ticket_data = $ticket->obtenerTicketPorId($ticket_id);
        if ($ticket_data['tecnico_asignado_id'] == $usuario_id || $_SESSION['rol_id'] == 1) { ?>

        <!-- Información principal del ticket -->
            <div class="container my-5">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header bg-dark text-black">
                                <h4 class="mb-0"><?php echo htmlspecialchars($ticket_data['asunto']); ?></h4>
                            </div>
                            <div class="card-body">
                                <!-- Estado y prioridad -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <?php
                                        $estado_colors = [
                                            'Abierto' => 'warning',
                                            'En Progreso' => 'info',
                                            'Resuelto' => 'success',
                                            'Cerrado' => 'secondary'
                                        ];
                                        $estado_icons = [
                                            'Abierto' => '',
                                            'En Progreso' => '',
                                            'Resuelto' => '',
                                            'Cerrado' => ''
                                        ];
                                        $color = $estado_colors[$ticket_data['estado']] ?? 'secondary';
                                        $icon = $estado_icons[$ticket_data['estado']] ?? '';
                                        ?>
                                        <h5>
                                            Estado: <span class="badge bg-<?php echo $color; ?> fs-6">
                                                <?php echo $icon . ' ' . htmlspecialchars($ticket_data['estado']); ?>
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
                                        $prioridad_colors = [
                                            'Baja' => 'success',
                                            'Media' => 'warning',
                                            'Alta' => 'orange',
                                            'Crítica' => 'danger'
                                        ];
                                        $prioridad_icons = [
                                            'Baja' => '',
                                            'Media' => '',
                                            'Alta' => '',
                                            'Crítica' => ''
                                        ];
                                        $color = $prioridad_colors[$ticket_data['prioridad']] ?? 'secondary';
                                        $icon = $prioridad_icons[$ticket_data['prioridad']] ?? '⚪';
                                        ?>
                                        <h5>
                                            Prioridad: <span class="badge bg-<?php echo $color; ?> fs-6">
                                                <?php echo $icon . ' ' . htmlspecialchars($ticket_data['prioridad']); ?>
                                            </span>
                                        </h5>
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="mb-4">
                                    <h6 class="text-primary">Descripción:</h6>
                                    <div class="bg-light p-3 rounded">
                                        <?php echo nl2br(htmlspecialchars($ticket_data['descripcion'])); ?>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="d-flex flex-wrap gap-2">
                                    <form action="tecnico.php" method="get" class="d-inline container">
                                        <div class="info-estado d-flex flex-row gap-2">
                                            <label> Cambiar estado del Ticket: </label>
                                            <select class="form-select" name="estado" aria-label="Estado del Ticket" <?php echo ($ticket_data['estado'] === 'Cerrado' ? 'disabled' : '') ?>>
                                                <?php
                                                $estado_options = [
                                                    'Abierto' => 'Abierto',
                                                    'En Progreso' => 'En Progreso',
                                                    'Resuelto' => 'Resuelto',
                                                    'Cerrado' => 'Cerrado'
                                                ];
                                                foreach ($estado_options as $estado => $label) {
                                                    echo '<option value="' . $estado . '" ' . ($estado === $ticket_data['estado'] ? 'selected' : '') . ($estado === 'Cerrado' ? 'disabled' : '') . '>' . $label . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="boton-estado d-flex justify-content-end">
                                            <button type="submit" class="btn btn-success mt-2" <?php echo ($ticket_data['estado'] === 'Cerrado' ? 'disabled' : '') ?>> Actualizar Estado </button>
                                            <input type="hidden" name="id" value="<?php echo $ticket_data['id']; ?>">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de comentarios (preparada para futuras funcionalidades) -->
                        <div class="card mt-4 shadow">
                            <div class="card-header">
                                <h5 class="mb-0"> Seguimiento y Comentarios</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center py-3 text-muted">
                                    <p>Los comentarios y seguimiento del ticket aparecerán aquí.</p>
                                    <small>Funcionalidad en desarrollo</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel lateral con información adicional -->
                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header bg-info text-black">
                                <h5 class="mb-0">ℹ Información del Ticket</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong> Número de Ticket:</strong><br>
                                    <code><?php echo htmlspecialchars($ticket_data['numero_ticket']); ?></code>
                                </div>

                                <div class="mb-3">
                                    <strong> Departamento:</strong><br>
                                    <?php echo htmlspecialchars($ticket_data['nombre_departamento']); ?>
                                </div>

                                <div class="mb-3">
                                    <strong> Solicitante:</strong><br>
                                    <?php echo htmlspecialchars($ticket_data['nombre_usuario']); ?>
                                </div>

                                <div class="mb-3">
                                    <strong> Fecha de Creación:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($ticket_data['fecha_creacion'])); ?>
                                </div>

                                <div class="mb-3">
                                    <strong> Última Actualización:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($ticket_data['fecha_actualizacion'])); ?>
                                </div>

                                <?php if ($ticket_data['fecha_resolucion']): ?>
                                    <div class="mb-3">
                                        <strong> Fecha de Resolución:</strong><br>
                                        <?php echo date('d/m/Y H:i', strtotime($ticket_data['fecha_resolucion'])); ?>
                                    </div>
                                <?php endif; ?>

                                
                                <!-- Tiempo transcurrido -->
                                <div class="mb-3">
                                    <strong> Tiempo Transcurrido:</strong><br>
                                    <?php
                                    $fecha_creacion = new DateTime($ticket_data['fecha_creacion']);
                                    $fecha_actual = new DateTime();
                                    $diferencia = $fecha_creacion->diff($fecha_actual);
                                    
                                    if ($diferencia->days > 0) {
                                        echo $diferencia->days . " días, " . $diferencia->h . " horas";
                                    } elseif ($diferencia->h > 0) {
                                        echo $diferencia->h . " horas, " . $diferencia->i . " minutos";
                                    } else {
                                        echo $diferencia->i . " minutos";
                                    }
                                    ?>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        

    <?php } else { ?>
        <div class="container my-5">
            <div class="alert alert-warning text-center">
                Ticket no encontrado.
            </div>
        </div>
    <?php } endif; ?>
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Mesa de Ayuda Empresarial</h5>
                    <p class="mb-0">Soporte técnico profesional 24/7</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0"> Contacto: (57) 123-456-7890</p>
                    <p class="mb-0"> Email: soporte@empresa.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>