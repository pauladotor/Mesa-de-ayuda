<?php
class Ticket {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }

    
    public function crearTicket($usuario_id, $departamento_id, $asunto, $descripcion, $prioridad = 'Media') {
        try {
            // Generar número de ticket único
            $numero_ticket = 'TK-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Verificar que el número no exista
            $check_query = "SELECT id FROM tickets WHERE numero_ticket = ?";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([$numero_ticket]);
            
            // Si existe, generar uno nuevo
            while ($check_stmt->fetch()) {
                $numero_ticket = 'TK-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $check_stmt->execute([$numero_ticket]);
            }
            
            $query = "INSERT INTO tickets (numero_ticket, usuario_id, departamento_id, asunto, descripcion, prioridad, estado, fecha_creacion, fecha_actualizacion) 
                     VALUES (?, ?, ?, ?, ?, ?, 'Abierto', NOW(), NOW())";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$numero_ticket, $usuario_id, $departamento_id, $asunto, $descripcion, $prioridad]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Ticket creado exitosamente',
                    'ticket_id' => $this->db->lastInsertId(),
                    'numero_ticket' => $numero_ticket
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el ticket'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerTodosLosTickets() {
        try {
            $query = "SELECT t.*, d.nombre_departamento, u.nombre_usuario, tec.nombre_usuario AS tecnico_asignado
            FROM tickets t
            INNER JOIN departamentos d ON t.departamento_id = d.id
            INNER JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN usuarios tec ON t.tecnico_asignado_id = tec.id
            ORDER BY t.fecha_creacion DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerTicketPorId($ticket_id, $usuario_id = null) {
        try {
            $query = "SELECT t.*, d.nombre_departamento, u.nombre_usuario 
                     FROM tickets t 
                     INNER JOIN departamentos d ON t.departamento_id = d.id 
                     INNER JOIN usuarios u ON t.usuario_id = u.id 
                     WHERE t.id = ?";
            
            $params = [$ticket_id];
            
            // Si se especifica usuario_id, agregar filtro
            if ($usuario_id) {
                $query .= " AND t.usuario_id = ?";
                $params[] = $usuario_id;
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function actualizarEstadoTicket($ticket_id, $estado) {
        try {
            $query = "UPDATE tickets SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$estado, $ticket_id]);
            
            if($result) {
                return [
                    'success' => true,
                    'message' => 'Estado del ticket actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el estado del ticket'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerDepartamentos() {
        try {
            $query = "SELECT * FROM departamentos ORDER BY nombre_departamento";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }

    public function asignarTecnico($ticket_id, $tecnico_id) {
        try {
            $query = "UPDATE tickets SET tecnico_asignado_id = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$tecnico_id, $ticket_id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Técnico asignado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al asignar el técnico'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }

    public function cerrarTicket($ticket_id) {
        try {
            $query = "UPDATE tickets SET estado = 'Cerrado', fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$ticket_id]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'El ticket ha sido cerrado exitosamente.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al cerrar el ticket.'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ];
        }
    }

    public function obtenerTicketsTecnico($tecnico_id) {
        try {
            $query = "SELECT t.*, d.nombre_departamento, u.nombre_usuario 
                     FROM tickets t 
                     INNER JOIN departamentos d ON t.departamento_id = d.id 
                     INNER JOIN usuarios u ON t.usuario_id = u.id 
                     WHERE t.tecnico_asignado_id = ? 
                     ORDER BY t.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$tecnico_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>