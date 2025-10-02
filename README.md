# 🎫 Mesa de Ayuda - Sistema de Gestión de Tickets

Sistema de gestión de tickets de soporte técnico desarrollado en PHP con MySQL. Permite a los usuarios crear, gestionar y dar seguimiento a tickets de soporte de manera eficiente.

## 📋 Características

- **Gestión de Usuarios**: Sistema de autenticación con roles (Administrador, Cliente, Técnico)
- **Gestión de Tickets**: Crear, ver y actualizar tickets de soporte
- **Departamentos**: Organización por departamentos (Soporte Técnico, Desarrollo, Redes, etc.)
- **Prioridades**: Clasificación de tickets por prioridad (Baja, Media, Alta, Urgente)
- **Estados**: Seguimiento del estado de tickets (Abierto, En Progreso, Resuelto, Cerrado)
- **Panel de Administración**: Vista completa para administradores
- **Panel de Cliente**: Vista personalizada para usuarios finales

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 8.1
- **Base de Datos**: PostgreSQL 15
- **Frontend**: HTML5, CSS3, Bootstrap 5.3.3
- **Servidor Web**: Apache
- **Contenedorización**: Docker
- **Despliegue**: Render

## 📁 Estructura del Proyecto

```
TecDesarrollo/
├── config/
│   └── conexion.php          # Configuración de base de datos
├── models/
│   ├── Usuario.php           # Modelo de usuarios
│   └── Ticket.php            # Modelo de tickets
├── view/
│   ├── Home/
│   │   ├── index.php         # Página de login
│   │   ├── Registro.php      # Registro de usuarios
│   │   ├── admin.php         # Panel de administración
│   │   ├── cliente.php       # Panel de cliente
│   │   ├── css/              # Estilos CSS
│   │   └── img/              # Imágenes
│   └── Tickets/
│       ├── nuevo_ticket.php  # Crear nuevo ticket
│       ├── mis_tickets.php   # Ver tickets del usuario
│       └── ver_ticket.php    # Detalle de ticket
├── database/
│   └── init.sql              # Script de inicialización de BD
├── Dockerfile                # Configuración Docker para PHP
├── docker-compose.yml        # Orquestación de contenedores
├── .env.example              # Plantilla de variables de entorno
└── logout.php                # Cerrar sesión
```

## 🚀 Despliegue

Este proyecto está configurado para desplegarse en **Render** usando contenedores Docker.

### Servicio Utilizado

- **Plataforma**: Render (https://render.com)
- **Tipo**: Web Service + MySQL Database
- **Contenedorización**: Docker
- **Plan**: Free Tier

### Variables de Entorno Configuradas

Las siguientes variables de entorno son necesarias para el funcionamiento del sistema:

| Variable | Descripción | Valor Ejemplo |
|----------|-------------|---------------|
| `DB_HOST` | Host de la base de datos PostgreSQL | `dpg-xxxxx-a` |
| `DB_PORT` | Puerto de PostgreSQL | `5432` |
| `DB_NAME` | Nombre de la base de datos | `usuarios_db` |
| `DB_USER` | Usuario de PostgreSQL | `mesaayuda` |
| `DB_PASS` | Contraseña de PostgreSQL | `password_generado_por_render` |

**Configuración en Render**:
1. Las variables se configuran en la sección "Environment" del Web Service
2. Se utilizan los valores proporcionados por el servicio PostgreSQL de Render
3. El sistema usa `getenv()` para leer estas variables en producción
4. En desarrollo local, usa valores por defecto de `localhost` y puerto `5432`

### Dificultades Encontradas y Soluciones

#### 1. Migración de MySQL a PostgreSQL
**Problema**: Render no soporta MySQL en el plan gratuito, solo PostgreSQL.

**Solución**: 
- Migrar toda la aplicación de MySQL a PostgreSQL
- Actualizar `config/conexion.php` para usar PDO con PostgreSQL (`pgsql`)
- Convertir el script SQL a sintaxis de PostgreSQL (SERIAL, BOOLEAN, tipos ENUM personalizados)
- Actualizar Dockerfile para instalar extensiones `pdo_pgsql` y `pgsql`

#### 2. Conexión entre contenedores Docker
**Problema**: La aplicación PHP no podía conectarse a la base de datos cuando ambos estaban en contenedores separados.

**Solución**: 
- Modificar `config/conexion.php` para usar variables de entorno con `getenv()`
- Configurar correctamente el `DB_HOST` y `DB_PORT` con los valores del Internal Database URL de Render
- Usar el nombre del servicio (`db`) en docker-compose para pruebas locales

#### 3. Inicialización de la base de datos
**Problema**: Al desplegar en Render, la base de datos estaba vacía sin tablas ni datos iniciales.

**Solución**:
- Crear script `database/init.sql` con toda la estructura de la base de datos en sintaxis PostgreSQL
- Ejecutar manualmente el script en la base de datos de Render usando `psql` o pgAdmin
- Incluir datos iniciales: roles, departamentos y usuarios de prueba
- Crear triggers para actualizar automáticamente `fecha_actualizacion`

### Proceso de Despliegue

1. **Preparación**:
   - Crear cuenta en Render
   - Subir código a GitHub

2. **Base de Datos**:
   - Crear servicio PostgreSQL en Render
   - Ejecutar script `init.sql` usando psql
   - Guardar credenciales (Internal Database URL)

3. **Aplicación Web**:
   - Crear Web Service conectado a GitHub
   - Configurar variables de entorno (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS)
   - Desplegar desde Dockerfile

4. **Verificación**:
   - Probar login
   - Crear tickets de prueba
   - Verificar funcionalidad completa

### URL de Despliegue

🔗 **[Acceder a la aplicación](https://tu-app.onrender.com/view/Home/index.php)**

> **Nota**: Reemplaza con tu URL real de Render una vez desplegado

### Credenciales de Prueba

Para probar el sistema, puedes usar estas credenciales:

- **Administrador**:
  - Email: `admin@mesaayuda.com`
  - Contraseña: `admin123`

- **Cliente**:
  - Email: `cliente@mesaayuda.com`
  - Contraseña: `cliente123`

> ⚠️ **Importante**: Cambia estas contraseñas en producción

## 💻 Instalación Local

### Prerrequisitos

- Docker Desktop
- Git

### Pasos

1. **Clonar el repositorio**:
```bash
git clone <tu-repositorio>
cd TecDesarrollo
```

2. **Crear archivo .env** (opcional):
```bash
cp .env.example .env
```

3. **Levantar los contenedores**:
```bash
docker-compose up -d --build
```

4. **Acceder a la aplicación**:
```
http://localhost:8080/view/Home/index.php
```

5. **Detener los contenedores**:
```bash
docker-compose down
```

## 📚 Documentación Adicional

Para una guía detallada de despliegue, consulta [DEPLOYMENT.md](DEPLOYMENT.md)

## 🔒 Seguridad

- Las contraseñas se almacenan encriptadas con `password_hash()` de PHP
- Uso de PDO con prepared statements para prevenir SQL injection
- Validación de sesiones y roles para control de acceso
- Variables de entorno para credenciales sensibles

## 🤝 Contribuciones

Este proyecto fue desarrollado como parte del curso de Desarrollo de Software.

## 📄 Licencia

Este proyecto es de uso académico.

---

**Desarrollado con ❤️ para el curso de Desarrollo de Software**
