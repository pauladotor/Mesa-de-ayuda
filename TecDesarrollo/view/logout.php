<<<<<<< HEAD
<?php
session_start();

$_SESSION = array();

session_destroy();

header("Location: Home/index.php?mensaje=sesion_cerrada");
exit();
?>
=======
<?php
session_start();

$_SESSION = array();

session_destroy();

header("Location: Home/index.php?mensaje=sesion_cerrada");
exit();
?>
>>>>>>> 19b4cc9b5eb857dcd6df0e85b8a44f66b1b55ff0
