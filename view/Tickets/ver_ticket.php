<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mis_tickets.php");
    exit();
}

require_once '../../config/conexion.php';
require_once '../../models/Ticket.php';

$ticket = new Ticket();
$ticket_id = (int)$_GET['id'];

// Permitir ver el ticket si es el propietario o si es admin/técnico
if(isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
    $usuario_id = (int)$_GET['usuario_id'];
} else {
    $usuario_id = $_SESSION['user_id'];
}

if($_SESSION['rol_id'] == 1 && !isset($_GET['usuario_id'])) {
    $usuario_id = $ticket->obtenerTicketPorId($ticket_id)['usuario_id'];
    header("Location: ver_ticket.php?id=$ticket_id&usuario_id=$usuario_id");
    exit();
}

$ticket_data = $ticket->obtenerTicketPorId($ticket_id, $usuario_id);

if (!$ticket_data) {
    $_SESSION['error'] = "Ticket no encontrado o no tienes permisos para verlo.";
    header("Location: mis_tickets.php");
    exit();
}

// Manejar cierre de ticket
if (isset($_GET['cerrarTicket']) && $_GET['cerrarTicket'] == 1) {
    if ($ticket_data['estado'] == 'Resuelto') {
        $cerrar_resultado = $ticket->cerrarTicket($ticket_id);
        if ($cerrar_resultado['success']) {
            $_SESSION['success'] = "El ticket ha sido cerrado exitosamente.";
        } else {
            $_SESSION['error'] = $cerrar_resultado['message'];
        }
    } else {
        $_SESSION['error'] = "Solo se pueden cerrar tickets que estén en estado 'Resuelto'.";
    }
    header("Location: ver_ticket.php?id=" . $ticket_id);
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
    <title>Ticket <?php echo htmlspecialchars($ticket_data['numero_ticket']); ?> - Mesa de Ayuda</title>
</head>
<body>
    <header class="bg-dark text-white py-3 shadow">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-0"> Ticket <?php echo htmlspecialchars($ticket_data['numero_ticket']); ?></h1>
                </div>
                <div class="col-md-6 text-end">
                    <?php if ($_SESSION['rol_id'] == 1 || $_SESSION['rol_id'] == 3) {?>
                        <a href="<?php echo $_SESSION['rol_id'] == 1 ? '../Home/admin.php' : '../Home/tecnico.php'; ?>" class="btn btn-outline-light btn-sm me-2"> Dashboard</a>
                    <?php } else { ?>
                        <a href="mis_tickets.php" class="btn btn-outline-light btn-sm me-2"> Mis Tickets</a>
                        <a href="../Home/cliente.php" class="btn btn-outline-light btn-sm me-2"> Dashboard</a>
                    <?php } ?>
                    <a href="../../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <div class="row">
            <!-- Información principal del ticket -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
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
                            <?php if ($ticket_data['estado'] == 'Resuelto'): ?>
                                <button class="btn btn-success" onclick="confirmarCerrar()">
                                     Cerrar Ticket
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                 Imprimir
                            </button>
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
                    <div class="card-header bg-info text-white">
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

                <!-- Acciones rápidas -->
                <div class="card mt-4 shadow">
                    <div class="card-header">
                        <h6 class="mb-0"> Acciones Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="nuevo_ticket.php" class="btn btn-success btn-sm">
                                 Crear Nuevo Ticket
                            </a>
                            <a href="mis_tickets.php" class="btn btn-outline-primary btn-sm">
                                 Ver Todos mis Tickets
                            </a>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copiarEnlace()">
                                 Copiar Enlace
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Mesa de Ayuda Empresarial</h5>
                    <p class="mb-0">Soporte técnico profesional 24/7</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">📞 Contacto: (57) 123-456-7890</p>
                    <p class="mb-0">📧 Email: soporte@empresa.com</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal de confirmación para cerrar ticket -->
    <div class="modal fade" id="cerrarTicketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🔒 Cerrar Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea cerrar este ticket?</p>
                    <p class="text-muted small">Una vez cerrado, no podrá reabrirlo y se considerará como resuelto definitivamente.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="cerrarTicket()">Sí, Cerrar Ticket</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmarCerrar() {
            const modal = new bootstrap.Modal(document.getElementById('cerrarTicketModal'));
            modal.show();
        }
        
        function cerrarTicket() {
            bootstrap.Modal.getInstance(document.getElementById('cerrarTicketModal')).hide();
            window.location.href = 'ver_ticket.php?id=<?php echo $ticket_data['id']; ?>&cerrarTicket=1';
        }
        
        function copiarEnlace() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                // Crear notificación temporal
                const alert = document.createElement('div');
                alert.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
                alert.style.zIndex = '9999';
                alert.innerHTML = '✅ Enlace copiado al portapapeles';
                document.body.appendChild(alert);
                
                setTimeout(() => {
                    alert.remove();
                }, 3000);
            }).catch(function(err) {
                alert('Error al copiar el enlace: ' + err);
            });
        }
    </script>
</body>
</html>