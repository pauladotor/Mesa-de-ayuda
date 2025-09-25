<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$db = getDB();
$user_id = $_SESSION['user_id'];

$query = "SELECT id, nombre_usuario, correo_usuario, celular_usuario FROM usuarios WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit();
}

$modo_editar = isset($_GET['editar']);
$success = isset($_GET['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $celular = $_POST['celular'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        $update = "UPDATE usuarios SET nombre_usuario = ?, correo_usuario = ?, celular_usuario = ?, contrasena_usuario = ? WHERE id = ?";
        $stmt = $db->prepare($update);
        $stmt->execute([$nombre, $correo, $celular, $password, $user_id]);
    } else {
        $update = "UPDATE usuarios SET nombre_usuario = ?, correo_usuario = ?, celular_usuario = ? WHERE id = ?";
        $stmt = $db->prepare($update);
        $stmt->execute([$nombre, $correo, $celular, $user_id]);
    }

    $_SESSION['nombre_usuario'] = $nombre;
    header("Location: perfil.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Mesa de Ayuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3 shadow">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-2">
                <?php
                $url_inicio = '#'; 
                if ($_SESSION['rol_id'] == 1) {
                    $url_inicio = 'admin.php';
                } elseif ($_SESSION['rol_id'] == 3) {
                    $url_inicio = 'tecnico.php';
                } elseif ($_SESSION['rol_id'] == 2) {
                    $url_inicio = 'cliente.php';
                }
                ?>
                <a href="<?php echo $url_inicio; ?>" class="btn btn-primary btn-sm">Inicio</a>
            </div>
            <div class="col-md-4">
                <h1 class="h3 mb-0">Perfil</h1>
            </div>
            <div class="col-md-6 text-end">
                <span class="me-3">
                    👋 
                    <?php 
                    if ($_SESSION['rol_id'] == 1) {
                        echo "Admin: ";
                    } elseif ($_SESSION['rol_id'] == 3) {
                        echo "Técnico: ";
                    } elseif ($_SESSION['rol_id'] == 2) {
                        echo "Cliente: ";
                    } else {
                        echo "Usuario: ";
                    }
                    echo htmlspecialchars($_SESSION['nombre_usuario']); 
                    ?>
                </span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</header>


    <!-- Contenido -->
    <div class="container my-5">
        <?php if ($success): ?>
            <div class="alert alert-success text-center"> Perfil actualizado correctamente.</div>
        <?php endif; ?>

        <div class="card shadow-lg p-4">
            <?php if (!$modo_editar): ?>
                <div class="text-center mb-4">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                         alt="Avatar" class="rounded-circle" width="120">
                </div>
                <h4 class="text-center mb-4">Información Personal</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre_usuario']) ?></li>
                    <li class="list-group-item"><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo_usuario']) ?></li>
                    <li class="list-group-item"><strong>Celular:</strong> <?= htmlspecialchars($usuario['celular_usuario']) ?></li>
                </ul>
                <div class="d-flex justify-content-center mt-4 gap-2">
                    <a href="perfil.php?editar=1" class="btn btn-primary">Editar Perfil</a>
                    <a href="<?php echo ($_SESSION['rol_id'] == 1) ? 'admin.php' : 'tecnico.php'; ?>" class="btn btn-secondary">Volver</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <h4 class="mb-4 text-center">Editar Perfil</h4>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo_usuario']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Celular</label>
                        <input type="text" name="celular" class="form-control" value="<?= htmlspecialchars($usuario['celular_usuario']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña (opcional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Dejar en blanco si no desea cambiarla">
                    </div>
                    <div class="d-flex justify-content-center mt-4 gap-2">
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                        <a href="perfil.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <br><br><br><br><br>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">Mesa de Ayuda Empresarial - Soporte técnico profesional 24/7</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
