<?php
/**
 * Script de Pruebas - Mesa de Ayuda
 * 
 * Este script ejecuta pruebas básicas del sistema
 * para verificar que todo funciona correctamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=================================\n";
echo "INICIANDO TESTS - MESA DE AYUDA\n";
echo "=================================\n\n";

$tests_passed = 0;
$tests_failed = 0;

/**
 * Helper para ejecutar tests
 */
function test($description, $callback) {
    global $tests_passed, $tests_failed;
    
    echo "TEST: $description... ";
    
    try {
        $result = $callback();
        if ($result) {
            echo " PASS\n";
            $tests_passed++;
        } else {
            echo " FAIL\n";
            $tests_failed++;
        }
    } catch (Exception $e) {
        echo " ERROR: " . $e->getMessage() . "\n";
        $tests_failed++;
    }
}

// TEST 1: Verificar versión de PHP
test("Verificar versión de PHP >= 8.0", function() {
    return version_compare(PHP_VERSION, '8.0.0', '>=');
});

// TEST 2: Verificar extensiones requeridas
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
foreach ($required_extensions as $ext) {
    test("Verificar extensión PHP: $ext", function() use ($ext) {
        return extension_loaded($ext);
    });
}

// TEST 3: Verificar que los directorios existen
$required_dirs = [
    'config',
    'controllers',
    'models',
    'view',
    'public/Scripts'
];

foreach ($required_dirs as $dir) {
    test("Verificar directorio existe: $dir", function() use ($dir) {
        return is_dir(__DIR__ . '/' . $dir);
    });
}

// TEST 4: Verificar archivos críticos
$required_files = [
    'config/database.php'
];

foreach ($required_files as $file) {
    test("Verificar archivo existe: $file", function() use ($file) {
        return file_exists(__DIR__ . '/' . $file);
    });
}

// TEST 5: Probar conexión a base de datos (si existe config)
if (file_exists(__DIR__ . '/config/database.php')) {
    test("Probar conexión a base de datos", function() {
        try {
            require_once __DIR__ . '/config/database.php';
            
            $db = Database::getInstance();
            return $db->isConnected();
        } catch (Exception $e) {
            // En CI/CD no tenemos BD, esto es esperado
            if (getenv('GITHUB_ACTIONS') === 'true') {
                return true; // Skip en CI/CD
            }
            throw $e;
        }
    });
}

// TEST 6: Verificar sintaxis de archivos PHP
test("Verificar sintaxis de archivos PHP", function() {
    $php_files = glob(__DIR__ . '/*.php');
    $php_files = array_merge($php_files, glob(__DIR__ . '/models/*.php'));
    $php_files = array_merge($php_files, glob(__DIR__ . '/controllers/*.php'));
    $php_files = array_merge($php_files, glob(__DIR__ . '/view/*.php'));
    
    foreach ($php_files as $file) {
        $output = [];
        $return_var = 0;
        exec("php -l " . escapeshellarg($file), $output, $return_var);
        
        if ($return_var !== 0) {
            return false;
        }
    }
    
    return true;
});

// TEST 7: Verificar variables de entorno críticas (en producción)
if (getenv('APP_ENV') === 'production') {
    $env_vars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    
    foreach ($env_vars as $var) {
        test("Verificar variable de entorno: $var", function() use ($var) {
            return !empty(getenv($var));
        });
    }
}

// TEST 8: Verificar funciones básicas de PHP
test("Verificar función password_hash disponible", function() {
    return function_exists('password_hash');
});

test("Verificar función json_encode disponible", function() {
    return function_exists('json_encode');
});

// TEST 9: Probar operaciones básicas
test("Probar serialización JSON", function() {
    $data = ['test' => 'value', 'number' => 123];
    $json = json_encode($data);
    $decoded = json_decode($json, true);
    return $decoded['test'] === 'value' && $decoded['number'] === 123;
});

test("Probar encriptación de contraseñas", function() {
    $password = 'test123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    return password_verify($password, $hash);
});

// TEST 10: Verificar permisos de escritura (solo en local)
if (getenv('GITHUB_ACTIONS') !== 'true') {
    test("Verificar permisos de escritura en directorio temporal", function() {
        $test_file = sys_get_temp_dir() . '/mesa_ayuda_test.txt';
        $result = file_put_contents($test_file, 'test');
        if ($result !== false) {
            unlink($test_file);
            return true;
        }
        return false;
    });
}

// Resumen de tests
echo "\n=================================\n";
echo " RESUMEN DE TESTS\n";
echo "=================================\n";
echo " Tests exitosos: $tests_passed\n";
echo "Tests fallidos: $tests_failed\n";
echo "Total: " . ($tests_passed + $tests_failed) . "\n";
echo " Tasa de éxito: " . round(($tests_passed / ($tests_passed + $tests_failed)) * 100, 2) . "%\n";
echo "=================================\n\n";

// Determinar código de salida
if ($tests_failed > 0) {
    echo " ALGUNOS TESTS FALLARON\n";
    exit(1);
} else {
    echo " TODOS LOS TESTS PASARON\n";
    exit(0);
}