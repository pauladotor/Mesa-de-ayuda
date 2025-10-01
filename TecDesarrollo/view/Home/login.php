<<<<<<< HEAD
<?php
// login.php - Procesador de login (si necesitas mantener este archivo separado)
session_start();
require_once '../config/conexion.php';
require_once '../modelos/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo_usuario']);
    $password = trim($_POST['contra1_usuario']);
    
    if (!empty($correo) && !empty($password)) {
        $usuario = new Usuario();
        $result = $usuario->login($correo, $password);
        
        if ($result['success']) {
            $user_data = $result['user'];
            
            // Establecer sesión
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['nombre_usuario'] = $user_data['nombre_usuario'];
            $_SESSION['correo_usuario'] = $user_data['correo_usuario'];
            $_SESSION['rol'] = $user_data['nombre_rol'];
            
            // Redirigir según el rol
            if ($user_data['rol_id'] == 1) {
                header("Location: ./public/view/admin.php");
            } else if ($user_data['rol_id'] == 2) {
                header("Location: ./public/view/cliente.php");
            } else if ($user_data['rol_id'] == 3) {
                header("Location: ./public/view/tecnico.php");
            }
            exit();
        } else {
            // Redirigir con error
            header("Location: index.php?error=" . urlencode($result['message']));
            exit();
        }
    } else {
        header("Location: index.php?error=" . urlencode("Complete todos los campos"));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
=======
<?php
// login.php - Procesador de login (si necesitas mantener este archivo separado)
session_start();
require_once '../config/conexion.php';
require_once '../modelos/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo_usuario']);
    $password = trim($_POST['contra1_usuario']);
    
    if (!empty($correo) && !empty($password)) {
        $usuario = new Usuario();
        $result = $usuario->login($correo, $password);
        
        if ($result['success']) {
            $user_data = $result['user'];
            
            // Establecer sesión
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['nombre_usuario'] = $user_data['nombre_usuario'];
            $_SESSION['correo_usuario'] = $user_data['correo_usuario'];
            $_SESSION['rol'] = $user_data['nombre_rol'];
            
            // Redirigir según el rol
            if ($user_data['rol_id'] == 1) {
                header("Location: ./public/view/admin.php");
            } else if ($user_data['rol_id'] == 2) {
                header("Location: ./public/view/cliente.php");
            } else if ($user_data['rol_id'] == 3) {
                header("Location: ./public/view/tecnico.php");
            }
            exit();
        } else {
            // Redirigir con error
            header("Location: index.php?error=" . urlencode($result['message']));
            exit();
        }
    } else {
        header("Location: index.php?error=" . urlencode("Complete todos los campos"));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
>>>>>>> 19b4cc9b5eb857dcd6df0e85b8a44f66b1b55ff0
}