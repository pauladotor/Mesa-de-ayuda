<?php
session_start();

$_SESSION = array();

session_destroy();

header("Location: Home/index.php?mensaje=sesion_cerrada");
exit();
?>
