<?php
// Este archivo redirige a la página principal (index.php) dentro de la carpeta Home
// Usamos el código de estado 302 para la redirección
header('Location: /TecDesarrollo/public/view/Home/index.php', true, 302);
exit();
?>