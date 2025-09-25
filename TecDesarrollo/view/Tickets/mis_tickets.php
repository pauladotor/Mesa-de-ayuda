<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once '../../config/conexion.php';
require_once '../../models/Ticket.php';

$ticket = new Ticket();
$mis_tickets = $ticket->obtenerTicketsPorUsuario($_SESSION['user_id']);

// Filtros
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
$departamento_filtro = isset($_GET['departamento']) ? $_GET['departamento'] : '';

// Aplicar filtros
if ($estado_filtro || $departamento_filtro) {
    $mis_tickets = array_filter($mis_tickets, function($t) use ($estado_filtro, $departamento_filtro) {
        $estado_match = empty($estado_filtro) || $t['estado'] == $estado_filtro;
        $dept_match = empty($departamento_filtro) || $t['departamento_id'] == $departamento_filtro;
        return $estado_match && $dept_match;
    });
}

$departamentos = $ticket->obtenerDepartamentos();

if($_SESSION['rol_id'] == 1) {
    $mis_tickets = $ticket->obtenerTodosLosTickets();
}
?>

<!DOCTYPE html>
<html lang="es">
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
                <div class="col-md-6">
                    <h1 class="h3 mb-0"><?php echo ($_SESSION['rol_id'] == 1 ? ' Todos los Tickets' : ' Mis Tickets'); ?></h1>
                </div>
                <div class="col-md-6 text-end">
                    <?php if($_SESSION['rol_id'] == 1): ?>
                        <a href="../Home/admin.php" class="btn btn-outline-light btn-sm me-2"> Menu Admintrador </a>
                    <?php else: ?>
                    <a href="../Home/cliente.php" class="btn btn-outline-light btn-sm me-2"> Inicio</a>
                    <a href="nuevo_ticket.php" class="btn btn-success btn-sm me-2"> Nuevo Ticket</a>
                    <?php endif; ?>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="estado" class="form-label">Estado:</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="Abierto" <?php echo $estado_filtro == 'Abierto' ? 'selected' : ''; ?>>🔓 Abierto</option>
                            <option value="En Progreso" <?php echo $estado_filtro == 'En Progreso' ? 'selected' : ''; ?>>⚙️ En Progreso</option>
                            <option value="Resuelto" <?php echo $estado_filtro == 'Resuelto' ? 'selected' : ''; ?>>✅ Resuelto</option>
                            <option value="Cerrado" <?php echo $estado_filtro == 'Cerrado' ? 'selected' : ''; ?>>🔒 Cerrado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="departamento" class="form-label">Departamento:</label>
                        <select name="departamento" id="departamento" class="form-select">
                            <option value="">Todos los departamentos</option>
                            <?php foreach ($departamentos as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                        <?php echo $departamento_filtro == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['nombre_departamento']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2"> Filtrar</button>
                        <a href="mis_tickets.php" class="btn btn-outline-secondary"> Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de tickets -->
        <div class="card">
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
                        <p class="text-muted">
                            <?php if ($estado_filtro || $departamento_filtro): ?>
                                No hay tickets que coincidan con los filtros seleccionados.
                            <?php else: ?>
                                Aún no has creado ningún ticket de soporte.
                            <?php endif; ?>
                        </p>
                        <a href="nuevo_ticket.php" class="btn btn-primary">Crear mi primer ticket</a>
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
                                    <th>Acciones</th>
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
                                                'Alta' => 'danger',
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
                                            $icon = $estado_icons[$t['estado']] ?? '❓';
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
                                                <a href="ver_ticket.php?id=<?php echo $t['id']; ?>" 
                                                class="btn btn-outline-primary btn-sm" 
                                                title="Ver detalles">
                                                    ver detalles
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <a href="../Home/admin.php" class="btn btn-primary">volver</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <br><br><br><br><br><br><br>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>