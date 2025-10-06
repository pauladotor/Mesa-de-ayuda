<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: index.php");
    exit();
}

require_once '../../config/conexion.php'; 

$using_mysqli = false;
$using_pdo = false;
$pdo = null;

if (isset($conexion) && ($conexion instanceof mysqli)) {
    $using_mysqli = true;
}
elseif (isset($pdo) && ($pdo instanceof PDO)) {
    $using_pdo = true;
}
elseif (function_exists('getDB')) {
    $db = getDB();
    if ($db instanceof PDO) {
        $using_pdo = true;
        $pdo = $db;
    }
}

if (!$using_mysqli && !$using_pdo) {
    if (isset($db_host, $db_user, $db_pass, $db_name)) {
        $conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conexion->connect_errno) {
            die("Error de conexión MySQL: " . $conexion->connect_error);
        }
        $using_mysqli = true;
    } else {
        die("Error: no se detectó una conexión válida. Revisa 'config/conexion.php' — debe definir \$conexion (mysqli) o \$pdo (PDO) o una función getDB().");
    }
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'], $_POST['rol_id'])) {
    $usuario_id = (int) $_POST['usuario_id'];
    $nuevo_rol = (int) $_POST['rol_id'];

    if ($usuario_id === (int)$_SESSION['user_id']) {
        $mensaje = "No puedes cambiar tu propio rol.";
    } else {
        $ok = false;
        if ($using_mysqli) {
            $stmt = $conexion->prepare("UPDATE usuarios SET rol_id = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $nuevo_rol, $usuario_id);
                $ok = $stmt->execute();
                $stmt->close();
            }
        } elseif ($using_pdo) {
            $stmt = $pdo->prepare("UPDATE usuarios SET rol_id = :rol WHERE id = :id");
            $ok = $stmt->execute([':rol' => $nuevo_rol, ':id' => $usuario_id]);
        }

        $mensaje = $ok ? "✅ Rol actualizado correctamente." : "❌ Error al actualizar el rol.";
    }
}

// --- obtener lista de usuarios ---
$usuarios = [];
if ($using_mysqli) {
    $sql = "SELECT u.id, u.nombre_usuario, u.correo_usuario, u.rol_id, r.nombre_rol 
            FROM usuarios u 
            INNER JOIN roles r ON u.rol_id = r.id
            ORDER BY u.fecha_registro DESC";
    $res = $conexion->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $usuarios[] = $row;
        }
        $res->free();
    }
} elseif ($using_pdo) {
    $sql = "SELECT u.id, u.nombre_usuario, u.correo_usuario, u.rol_id, r.nombre_rol 
            FROM usuarios u 
            INNER JOIN roles r ON u.rol_id = r.id
            ORDER BY u.fecha_registro DESC";
    $stmt = $pdo->query($sql);
    if ($stmt) {
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de Usuarios - Mesa de Ayuda</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3 shadow">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <a href="admin.php" class="btn btn-primary btn-sm">Inicio</a>
                </div>
                <div class="col-md-4">
                    <h1 class="h3 mb-0">Gestión de Usuarios</h1>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">👋 Admin: <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>
    <br><br>

    <div class="card m-4 shadow">
        <div class="card-header">
            <h4 class="mb-0">Lista de Usuarios (<?php echo count($usuarios); ?>)</h4>
        </div>
        <div class="card-body">
            <?php if ($mensaje): ?>
                <div class="alert alert-info"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <?php if (empty($usuarios)): ?>
                <div class="alert alert-warning text-center">No hay usuarios registrados.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol Actual</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['nombre_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($u['correo_usuario']); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($u['nombre_rol']); ?></span></td>
                                    <td>
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">
                                            <select name="rol_id" class="form-select form-select-sm" style="max-width:140px;">
                                                <option value="1" <?php echo ($u['rol_id'] == 1) ? 'selected' : ''; ?>>Admin</option>
                                                <option value="3" <?php echo ($u['rol_id'] == 3) ? 'selected' : ''; ?>>Técnico</option>
                                                <option value="2" <?php echo ($u['rol_id'] == 2) ? 'selected' : ''; ?>>Usuario</option>
                                            </select>
                                            <button type="submit" class="btn btn-success btn-sm">Actualizar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <br> <br><br><br><br>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Mesa de Ayuda Empresarial</h5>
                    <p class="mb-0">Soporte técnico profesional 24/7</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Contacto: (57) 123-456-7890</p>
                    <p class="mb-0">Email: soporte@empresa.com</p>
                </div>
            </div>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
