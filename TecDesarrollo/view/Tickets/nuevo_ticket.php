<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once '../../config/conexion.php';
require_once '../../models/Ticket.php';

$ticket = new Ticket();
$departamentos = $ticket->obtenerDepartamentos();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_ticket'])) {
    $departamento_id = $_POST['departamento_id'];
    $asunto = trim($_POST['asunto']);
    $descripcion = trim($_POST['descripcion']);
    $prioridad = $_POST['prioridad'];
    
    if (empty($departamento_id) || empty($asunto) || empty($descripcion)) {
        $message = "Por favor complete todos los campos obligatorios";
        $message_type = "danger";
    } elseif (strlen($asunto) < 10) {
        $message = "El asunto debe tener al menos 10 caracteres";
        $message_type = "danger";
    } elseif (strlen($descripcion) < 20) {
        $message = "La descripción debe tener al menos 20 caracteres";
        $message_type = "danger";
    } else {
        $result = $ticket->crearTicket($_SESSION['user_id'], $departamento_id, $asunto, $descripcion, $prioridad);
        
        if ($result['success']) {
            $message = "Ticket creado exitosamente. Número: " . $result['numero_ticket'];
            $message_type = "success";
            
            $asunto = $descripcion = '';
        } else {
            $message = $result['message'];
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
    <title>Nuevo Ticket - Mesa de Ayuda</title>
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3 shadow">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-0">🎯 Mesa de Ayuda - Nuevo Ticket</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="../Home/cliente.php" class="btn btn-outline-light btn-sm me-2">🏠 Dashboard</a>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"> Crear Nuevo Ticket de Soporte</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <div class="mb-4 bg-light p-3 rounded">
                                <h6 class="text-primary mb-2">👤 Información del Solicitante</h6>
                                <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></p>
                                <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['correo_usuario']); ?></p>
                            </div>

                            <!-- Departamento -->
                            <div class="mb-3">
                                <label for="departamento_id" class="form-label">
                                     Departamento de Soporte <span class="text-danger">*</span>
                                </label>
                                <select name="departamento_id" id="departamento_id" class="form-select" required>
                                    <option value="">Seleccione un departamento...</option>
                                    <?php foreach ($departamentos as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" 
                                                <?php echo (isset($_POST['departamento_id']) && $_POST['departamento_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                            <?php
                                            $icons = [
                                                'Soporte Técnico' => '',
                                                'Sistemas y Desarrollo' => '',
                                                'Recursos Humanos' => '',
                                                'Administración' => '',
                                                'Logística' => '',
                                                'Atención al Cliente' => '',
                                                'Mantenimiento' => '🔧'
                                            ];
                                            $icon = $icons[$dept['nombre_departamento']] ?? '';
                                            echo $icon . ' ' . htmlspecialchars($dept['nombre_departamento']);
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Seleccione el departamento más apropiado para su consulta.</small>
                            </div>

                            <!-- Prioridad -->
                            <div class="mb-3">
                                <label for="prioridad" class="form-label">⚡ Prioridad</label>
                                <select name="prioridad" id="prioridad" class="form-select">
                                    <option value="Baja" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Baja') ? 'selected' : ''; ?>>
                                         Baja - No urgente
                                    </option>
                                    <option value="Media" selected <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Media') ? 'selected' : ''; ?>>
                                         Media - Normal
                                    </option>
                                    <option value="Alta" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Alta') ? 'selected' : ''; ?>>
                                         Alta - Urgente
                                    </option>
                                    <option value="Crítica" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Crítica') ? 'selected' : ''; ?>>
                                         Crítica - Sistema caído
                                    </option>
                                </select>
                            </div>

                            <!-- Asunto -->
                            <div class="mb-3">
                                <label for="asunto" class="form-label">
                                     Asunto <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="asunto" id="asunto" class="form-control" 
                                       placeholder="Resuma brevemente su problema o consulta..."
                                       value="<?php echo isset($asunto) ? htmlspecialchars($asunto) : ''; ?>"
                                       maxlength="200" required>
                                <small class="form-text text-muted">Mínimo 10 caracteres. Sea específico y claro.</small>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-4">
                                <label for="descripcion" class="form-label">
                                     Descripción Detallada <span class="text-danger">*</span>
                                </label>
                                <textarea name="descripcion" id="descripcion" class="form-control" rows="6" 
                                          placeholder="Describa detalladamente su problema o consulta:&#10;&#10;- ¿Qué estaba haciendo cuando ocurrió el problema?&#10;- ¿Qué error o mensaje apareció?&#10;- ¿Ha intentado alguna solución?&#10;- ¿Cuándo comenzó el problema?"
                                          required><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
                                <small class="form-text text-muted">Mínimo 20 caracteres. Incluya todos los detalles relevantes para una mejor asistencia.</small>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                <a href="../Home/cliente.php" class="btn btn-secondary">
                                     Cancelar
                                </a>
                                <button type="submit" name="crear_ticket" class="btn btn-primary">
                                     Crear Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="text-primary"> Consejos para un mejor soporte:</h6>
                        <ul class="small text-muted mb-0">
                            <li>Sea específico en la descripción del problema</li>
                            <li>Incluya capturas de pantalla si es posible (puede adjuntarlas después)</li>
                            <li>Mencione el sistema operativo y navegador que utiliza</li>
                            <li>Indique los pasos que siguió antes del problema</li>
                        </ul>
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

    <!-- Script para contador de caracteres -->
    <script>
        document.getElementById('asunto').addEventListener('input', function() {
            const maxLength = 200;
            const currentLength = this.value.length;
            const remaining = maxLength - currentLength;
            
            // Crear o actualizar contador
            let counter = document.getElementById('asunto-counter');
            if (!counter) {
                counter = document.createElement('small');
                counter.id = 'asunto-counter';
                counter.className = 'form-text';
                this.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${currentLength}/${maxLength} caracteres`;
            counter.className = remaining < 20 ? 'form-text text-warning' : 'form-text text-muted';
        });

        document.getElementById('descripcion').addEventListener('input', function() {
            const minLength = 20;
            const currentLength = this.value.length;
            
            // Crear o actualizar contador
            let counter = document.getElementById('desc-counter');
            if (!counter) {
                counter = document.createElement('small');
                counter.id = 'desc-counter';
                counter.className = 'form-text';
                this.parentNode.appendChild(counter);
            }
            
            if (currentLength < minLength) {
                counter.textContent = `Faltan ${minLength - currentLength} caracteres mínimo`;
                counter.className = 'form-text text-danger';
            } else {
                counter.textContent = `${currentLength} caracteres`;
                counter.className = 'form-text text-success';
            }
        });
    </script>
</body>
</html>