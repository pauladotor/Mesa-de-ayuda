<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Usuario.php';

$message = '';
$message_type = '';
$usuario = new Usuario();

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro'])) {
    $nombre = trim($_POST['nombre_usuario']);
    $correo = trim($_POST['correo_usuario']);
    $celular = trim($_POST['celular_usuario']);
    $password1 = trim($_POST['contrasena1_usuario']);
    $password2 = trim($_POST['contrasena2_usuario']);
    $rol = $_POST['rol_usuario'];
    
    // Validaciones
    if (empty($nombre) || empty($correo) || empty($celular) || empty($password1) || empty($password2)) {
        $message = "Por favor complete todos los campos";
        $message_type = "danger";
    } elseif ($password1 !== $password2) {
        $message = "Las contraseñas no coinciden";
        $message_type = "danger";
    } elseif (strlen($password1) < 6) {
        $message = "La contraseña debe tener al menos 6 caracteres";
        $message_type = "danger";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $message = "Por favor ingrese un correo válido";
        $message_type = "danger";
    } elseif (!preg_match('/^[0-9]{10,}$/', $celular)) {
        $message = "El número de celular debe tener al menos 10 dígitos";
        $message_type = "danger";
    } else {
        $result = $usuario->registrar($nombre, $correo, $celular, $password1, $rol);
        
        if ($result['success']) {
            // Redirigir al login con mensaje de éxito
            header("Location: index.php?mensaje=registro_exitoso");
            exit();
        } else {
            $message = $result['message'];
            $message_type = "danger";
        }
    }
}

// Obtener roles para el select
try {
    $db = getDB();
    $roles_query = "SELECT * FROM roles WHERE id != 1"; // Excluir rol de administrador
    $roles_stmt = $db->prepare($roles_query);
    $roles_stmt->execute();
    $roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $roles = [['id' => 2, 'nombre_rol' => 'Cliente']];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style2.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Registro - Mesa de Ayuda</title>
</head>
<body>

<main class="d-flex justify-content-center align-items-center vh-100">
    <div class="contenedor bg-light border border-info shadow-lg rounded-4 bg-white bg-opacity-50 p-4">
        
        <h3 class="text-center mb-1">Registro de Usuario</h3>
        <p class="text-center text-muted mb-4">Mesa de Ayuda Empresarial</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> text-center alert-dismissible fade show" role="alert">
                <?php if ($message_type == 'success'): ?>✅<?php else: ?><?php endif; ?> 
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="d-flex w-100">
            <div class="formulario">
                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label">Nombre completo:</label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" 
                           class="form-control bg-white bg-opacity-50 rounded-pill" 
                           placeholder="Ingrese su nombre completo" 
                           value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="correo_usuario" class="form-label"> Correo electrónico:</label>
                    <input type="email" name="correo_usuario" id="correo_usuario" 
                           class="form-control bg-white bg-opacity-50 rounded-pill" 
                           placeholder="ejemplo@correo.com"
                           value="<?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="celular_usuario" class="form-label"> Número de celular:</label>
                    <input type="tel" name="celular_usuario" id="celular_usuario" 
                           class="form-control bg-white bg-opacity-50 rounded-pill" 
                           placeholder="3001234567"
                           value="<?php echo isset($celular) ? htmlspecialchars($celular) : ''; ?>" 
                           pattern="[0-9]{10,}" 
                           required>
                    <small class="form-text text-muted">Ingrese al menos 10 dígitos</small>
                </div>
                
                <div class="mb-3">
                    <label for="rol_usuario" class="form-label"> Tipo de usuario:</label>
                    <select name="rol_usuario" id="rol_usuario" class="form-control bg-white bg-opacity-50 rounded-pill" required>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id']; ?>" 
                                    <?php echo ($rol['id'] == 2) ? 'selected' : ''; ?>>
                                <?php 
                                $icon = $rol['id'] == 2 ? '👤' : '🛠️';
                                echo $icon . ' ' . htmlspecialchars($rol['nombre_rol']); 
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="contrasena1_usuario" class="form-label"> Contraseña:</label>
                    <input type="password" name="contrasena1_usuario" id="contrasena1_usuario" 
                           class="form-control bg-white bg-opacity-50 rounded-pill" 
                           placeholder="Mínimo 6 caracteres" 
                           minlength="6" required>
                </div>
                
                <div class="mb-3">
                    <label for="contrasena2_usuario" class="form-label"> Confirmar contraseña:</label>
                    <input type="password" name="contrasena2_usuario" id="contrasena2_usuario" 
                           class="form-control bg-white bg-opacity-50 rounded-pill" 
                           placeholder="Repita su contraseña" 
                           minlength="6" required>
                </div>
                
                <div class="mb-3 d-flex justify-content-center">
                    <button type="button" onclick="window.location.href='index.php'" 
                            class="btn btn-secondary rounded-pill ms-2 me-2">
                         Regresar
                    </button>
                    <button type="submit" name="registro" class="btn btn-success rounded-pill">
                         Registrarse
                    </button>
                </div>
            </div>
            
            <div class="d-flex align-items-center justify-content-center">
                <img class="lobby2" src="img/manos.jpg" alt="Registro">
            </div>
        </form>

        <!-- Información adicional -->
        <div class="mt-3 text-center">
            <small class="text-muted">
                 Sus datos están protegidos y serán utilizados únicamente para el sistema de mesa de ayuda
            </small>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para validar contraseñas -->
<script>
    document.getElementById('contrasena2_usuario').addEventListener('input', function() {
        const password1 = document.getElementById('contrasena1_usuario').value;
        const password2 = this.value;
        
        if (password2.length > 0) {
            if (password1 === password2) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });

    // Validar formato de celular
    document.getElementById('celular_usuario').addEventListener('input', function() {
        const value = this.value;
        if (value.length >= 10 && /^[0-9]+$/.test(value)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else if (value.length > 0) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });
</script>
</body>
</html>