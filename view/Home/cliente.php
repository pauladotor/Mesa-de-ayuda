<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Validar que solo clientes (rol 2) puedan acceder
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header("Location: index.php");
    exit();
}

require_once '../../config/conexion.php';
require_once '../../models/Ticket.php';

$ticket = new Ticket();
$departamentos = $ticket->obtenerDepartamentos();
$mis_tickets = $ticket->obtenerTicketsPorUsuario($_SESSION['user_id']);

// Contar tickets por estado
$abiertos = count(array_filter($mis_tickets, function($t) { return $t['estado'] == 'Abierto'; }));
$en_progreso = count(array_filter($mis_tickets, function($t) { return $t['estado'] == 'En Progreso'; }));
$resueltos = count(array_filter($mis_tickets, function($t) { return $t['estado'] == 'Resuelto' || $t['estado'] == 'Cerrado'; }));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
    <title>Mesa de Ayuda - Dashboard</title>
</head>
<body>
    <header class="bg-dark text-white py-3 shadow">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-0">🎯 Mesa de Ayuda Empresarial</h1>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">👋 Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
                    <a href="../../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <!-- Sección de bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body text-center">
                        <h2>💼 Bienvenido a tu Mesa de Ayuda</h2>
                        <p class="mb-3">Estamos aquí para resolver todas tus consultas y problemas técnicos de manera rápida y eficiente.</p>
                        <a href="../Tickets/nuevo_ticket.php" class="btn btn-light btn-lg">
                            ➕ Generar Nuevo Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de mis tickets -->
        <div class="row mb-4">
            <div class="col-12">
                <h3>📊 Resumen de tus Tickets</h3>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-warning text-dark">
                    <div class="card-body">
                        <h4><?php echo $abiertos; ?></h4>
                        <p class="mb-0">🔓 Tickets Abiertos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h4><?php echo $en_progreso; ?></h4>
                        <p class="mb-0">⚙️ En Progreso</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h4><?php echo $resueltos; ?></h4>
                        <p class="mb-0">✅ Resueltos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departamentos disponibles -->
        <div class="row mb-4">
            <div class="col-12">
                <h3>🏢 Departamentos de Soporte Disponibles</h3>
                <div class="row">
                    <?php foreach ($departamentos as $dept): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php
                                        $icons = [
                                            'Soporte Técnico' => '💻',
                                            'Sistemas y Desarrollo' => '⚡',
                                            'Recursos Humanos' => '👥',
                                            'Administración' => '📋',
                                            'Logística' => '📦',
                                            'Atención al Cliente' => '🤝',
                                            'Mantenimiento' => '🔧'
                                        ];
                                        $icon = $icons[$dept['nombre_departamento']] ?? '📁';
                                        echo $icon . ' ' . htmlspecialchars($dept['nombre_departamento']);
                                        ?>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?php echo htmlspecialchars($dept['descripcion']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Últimos tickets -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">🎫 Mis Tickets Recientes</h4>
                        <a href="../Tickets/mis_tickets.php" class="btn btn-primary btn-sm">Ver Todos</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($mis_tickets)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-3">📝 Aún no has creado ningún ticket</p>
                                <a href="../Tickets/nuevo_ticket.php" class="btn btn-primary">Crear mi primer ticket</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Asunto</th>
                                            <th>Departamento</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $tickets_recientes = array_slice($mis_tickets, 0, 5);
                                        foreach ($tickets_recientes as $t): 
                                        ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($t['numero_ticket']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($t['asunto']); ?></td>
                                                <td><?php echo htmlspecialchars($t['nombre_departamento']); ?></td>
                                                <td>
                                                    <?php
                                                    $estado_color = [
                                                        'Abierto' => 'warning',
                                                        'En Progreso' => 'info',
                                                        'Resuelto' => 'success',
                                                        'Cerrado' => 'secondary'
                                                    ];
                                                    $color = $estado_color[$t['estado']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo htmlspecialchars($t['estado']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($t['fecha_creacion'])); ?></td>
                                                <td>
                                                    <a href="../Tickets/ver_ticket.php?php echo $t['id']; ?>" class="btn btn-outline-primary btn-sm">Ver</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>