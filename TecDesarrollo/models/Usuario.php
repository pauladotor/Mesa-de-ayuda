<<<<<<< HEAD
<?php
class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function registrar($nombre, $correo, $celular, $password, $rol_id) {
        try {
            // Verificar si el correo ya existe
            $check_query = "SELECT id FROM usuarios WHERE correo_usuario = ?";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([$correo]);
            
            if ($check_stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado'
                ];
            }
            
            // Encriptar contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $query = "INSERT INTO usuarios (nombre_usuario, correo_usuario, celular_usuario, contrasena_usuario, rol_id, activo, fecha_registro) 
                     VALUES (?, ?, ?, ?, ?, 1, NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$nombre, $correo, $celular, $hashed_password, $rol_id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente',
                    'user_id' => $this->db->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar el usuario'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }
    
    public function login($correo, $password) {
        try {
            // Primero intentamos una consulta más simple
            $query = "SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     INNER JOIN roles r ON u.rol_id = r.id 
                     WHERE u.correo_usuario = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$correo]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verificar si la columna activo existe
                $activo = isset($user['activo']) ? $user['activo'] : 1;
                
                if ($activo && password_verify($password, $user['contrasena_usuario'])) {
                    return [
                        'success' => true,
                        'user' => $user,
                        'message' => 'Login exitoso'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Credenciales incorrectas'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }
            
        } catch (PDOException $e) {
            // Error específico de base de datos
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerUsuarios() {
        try {
            $query = "SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     INNER JOIN roles r ON u.rol_id = r.id 
                     ORDER BY u.fecha_registro DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerUsuariosPorRol($rol_id) {
        try {
            $query = "SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     INNER JOIN roles r ON u.rol_id = r.id 
                     WHERE u.rol_id = ?
                     ORDER BY u.nombre_usuario";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$rol_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    public function cambiarEstado($user_id, $activo) {
        try {
            $query = "UPDATE usuarios SET activo = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$activo, $user_id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function cambiarRol($user_id, $nuevo_rol) {
    try {
        $query = "UPDATE usuarios SET rol_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$nuevo_rol, $user_id]);
    } catch (Exception $e) {
        return false;
    }
}

=======
<?php
class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function registrar($nombre, $correo, $celular, $password, $rol_id) {
        try {
            // Verificar si el correo ya existe
            $check_query = "SELECT id FROM usuarios WHERE correo_usuario = ?";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([$correo]);
            
            if ($check_stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado'
                ];
            }
            
            // Encriptar contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $query = "INSERT INTO usuarios (nombre_usuario, correo_usuario, celular_usuario, contrasena_usuario, rol_id, activo, fecha_registro) 
                     VALUES (?, ?, ?, ?, ?, 1, NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$nombre, $correo, $celular, $hashed_password, $rol_id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente',
                    'user_id' => $this->db->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar el usuario'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }
    
    public function login($correo, $password) {
        try {
            // Primero intentamos una consulta más simple
            $query = "SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     INNER JOIN roles r ON u.rol_id = r.id 
                     WHERE u.correo_usuario = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$correo]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verificar si la columna activo existe
                $activo = isset($user['activo']) ? $user['activo'] : 1;
                
                if ($activo && password_verify($password, $user['contrasena_usuario'])) {
                    return [
                        'success' => true,
                        'user' => $user,
                        'message' => 'Login exitoso'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Credenciales incorrectas'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }
            
        } catch (PDOException $e) {
            // Error específico de base de datos
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerUsuarios() {
        try {
            $query = "SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     INNER JOIN roles r ON u.rol_id = r.id 
                     ORDER BY u.fecha_registro DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerUsuariosPorRol($rol_id) {
        try {
            $query = "SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     INNER JOIN roles r ON u.rol_id = r.id 
                     WHERE u.rol_id = ?
                     ORDER BY u.nombre_usuario";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$rol_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    public function cambiarEstado($user_id, $activo) {
        try {
            $query = "UPDATE usuarios SET activo = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$activo, $user_id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function cambiarRol($user_id, $nuevo_rol) {
    try {
        $query = "UPDATE usuarios SET rol_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$nuevo_rol, $user_id]);
    } catch (Exception $e) {
        return false;
    }
}

>>>>>>> 19b4cc9b5eb857dcd6df0e85b8a44f66b1b55ff0
}