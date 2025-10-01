<?php

// Configuración de la base de datos usando variables de entorno
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'mesa_ayuda');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $connection;

    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }
    }

    /**
     * Obtener instancia única de la base de datos
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Prevenir clonación del objeto
     */
    private function __clone() {}

    /**
     * Prevenir deserialización del objeto
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton.");
    }

    /**
     * Cerrar la conexión
     */
    public function closeConnection() {
        $this->connection = null;
    }

    /**
     * Verificar si la conexión está activa
     * 
     * @return bool
     */
    public function isConnected() {
        return $this->connection !== null;
    }

    /**
     * Ejecutar una consulta preparada de forma segura
     * 
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta.");
        }
    }
}

// Función helper para obtener la conexión rápidamente
function getDB() {
    return Database::getInstance()->getConnection();
}

// Verificar conexión al cargar el archivo (solo en desarrollo)
if (getenv('APP_ENV') === 'development') {
    try {
        $db = Database::getInstance();
        if ($db->isConnected()) {
            error_log("✅ Conexión a la base de datos establecida correctamente");
        }
    } catch (Exception $e) {
        error_log("❌ Error al conectar con la base de datos: " . $e->getMessage());
    }
}