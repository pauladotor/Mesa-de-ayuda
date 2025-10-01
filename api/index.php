<?php
// Redirección simple a la página principal, usando la raíz virtual public
header('Location: /view/Home/index.php', true, 302);
exit();
?>