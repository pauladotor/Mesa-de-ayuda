<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Usuario.php';

$error_message = '';
$success_message = '';
$usuario = new Usuario();

// Verificar mensajes de URL
if (isset($_GET['mensaje'])) {
    switch ($_GET['mensaje']) {
        case 'sesion_cerrada':
            $success_message = 'Sesión cerrada correctamente. ¡Hasta pronto!';
            break;
        case 'registro_exitoso':
            $success_message = 'Registro completado exitosamente. Ya puedes iniciar sesión.';
            break;
    }
}

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $correo = trim($_POST['correo_usuario']);
    $password = trim($_POST['contra1_usuario']);
    
    if (!empty($correo) && !empty($password)) {
        $result = $usuario->login($correo, $password);
        
        if ($result['success']) {
            $user_data = $result['user'];
            
            // Login exitoso
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['nombre_usuario'] = $user_data['nombre_usuario'];
            $_SESSION['correo_usuario'] = $user_data['correo_usuario'];
            $_SESSION['rol'] = $user_data['nombre_rol'];
            $_SESSION['rol_id'] = $user_data['rol_id'];
            
            // Redirigir según el rol
            if ($user_data['rol_id'] == 1) {
                header("Location: admin.php");
            } else {
                header("Location: cliente.php");
            }
            exit();
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = "Por favor complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style2.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Mesa de Ayuda - Iniciar Sesión</title>
</head>
<body>

<main class="d-flex justify-content-center align-items-center vh-100">
    <div class="contenedor bg-light border border-info shadow-lg rounded-4 bg-white bg-opacity-50 p-4">

        <h1 class="d-flex justify-content-center"> Mesa de Ayuda</h1>
        <p class="text-center text-muted mb-4">Sistema de Gestión de Tickets</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center alert-dismissible fade show" role="alert">
                 <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success text-center alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="d-flex w-100">

            <div class="formulario align-items-center"> 

                <div class="d-flex align-items-center justify-content-center p-3">
                    <img class="imagenUs rounded-circle mr-2" src="img/usuario.png" alt="Usuario" width="110">
                </div>

                <div class="mb-3">
                    <label for="correo_usuario" class="form-label"> Correo electrónico:</label>
                    <input type="email" name="correo_usuario" id="correo_usuario" 
                           class="form-control bg-white bg-opacity-50 rounded-pill" 
                           placeholder="ejemplo@correo.com" 
                           value="<?php echo isset($_POST['correo_usuario']) ? htmlspecialchars($_POST['correo_usuario']) : ''; ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label for="contra1_usuario" class="form-label"> Contraseña:</label>
                    <input type="password" name="contra1_usuario" id="contra1_usuario" 
                           class="form-control bg-white bg-opacity-50 rounded-pill" 
                           placeholder="Ingrese su contraseña" required>
                </div>

                <div class="d-flex justify-content-center">
                    <button type="submit" name="login" class="btn btn-primary rounded-pill me-2">
                         Ingresar
                    </button>
                    <button type="button" onclick="window.location.href='Registro.php'" 
                            class="btn btn-success rounded-pill ms-2">
                         Registrarse
                    </button>
                </div>

            </div>

            <div class="d-flex align-items-center justify-content-center">
                <img class="lobby shadow" src="img/mesaAyuda.png" alt="Mesa de Ayuda" width="80" height="20">
            </div>
        </form>

        <!-- Información adicional -->
        <div class="mt-4 text-center">
            <small class="text-muted">
                 Sistema de Mesa de Ayuda Empresarial<br>
                Gestiona tus tickets de soporte de manera eficiente
            </small>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>