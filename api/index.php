<?php
// Redirección simple a la raíz de la aplicación, dejando que vercel.json maneje el resto.
header('Location: /', true, 302);
exit();
?>