<?php
require_once "Usuario.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_POST["id_usuario"];
    $opcion = $_POST["opcion"];

    $usuario = new Usuario();
    if ($usuario->actualizarOpcion($id_usuario, $opcion)) {
        echo "Opción actualizada correctamente";
    } else {
        echo "Error al actualizar";
    }
}
