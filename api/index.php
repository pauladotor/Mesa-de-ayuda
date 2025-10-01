<?php
// Redirecciona al index.php dentro de la carpeta Home, usando la nueva raíz virtual /TecDesarrollo/public/
header('Location: /view/Home/index.php', true, 302);
exit();
?>