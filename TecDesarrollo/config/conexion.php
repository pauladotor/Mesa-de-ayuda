<<<<<<< HEAD
<?php

// --- 1. Definición de Credenciales Dinámicas ---
// Usamos getenv() para leer las variables que Render inyecta en el servidor
// Si la variable no existe (ej: en local), asignamos un valor por defecto (para desarrollo local)

// Usamos el operador ternario ?: para asignar 'localhost' si la variable de Render no existe
define('DB_HOST', getenv('DB_HOST') ?: 'localhost'); 
define('DB_PORT', getenv('DB_PORT') ?: '3306'); // Puerto 3306 para MySQL local, 5432 para Render
define('DB_NAME', getenv('DB_NAME') ?: 'usuarios_db'); 
define('DB_USER', getenv('DB_USER') ?: 'root'); 
define('DB_PASS', getenv('DB_PASS') ?: ''); 

class Database {
    // Las propiedades de la clase ahora toman los valores dinámicos
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        // --- 2. Lógica de Conexión y Detección de Entorno ---
        // Verificamos si estamos usando las credenciales de Render (PostgreSQL) o locales (MySQL)
        
        $is_render = ($this->port == '5432');
        
        try {
            if ($is_render) {
                // Conexión para Render (PostgreSQL)
                $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            } else {
                // Conexión para entorno local (MySQL)
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            }

            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $exception) {
            // Usa error_log para depuración en Render.
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            // Muestra un error genérico al usuario
            die("Error interno del servidor. Por favor, revisa los logs de conexión.");
        }

        return $this->conn;
    }
}

function getDB() {
    $database = new Database();
    return $database->getConnection();
}
=======
<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'usuarios_db');
define('DB_USER', 'root');  
define('DB_PASS', '');     

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

function getDB() {
    $database = new Database();
    return $database->getConnection();
}
>>>>>>> 19b4cc9b5eb857dcd6df0e85b8a44f66b1b55ff0
?>