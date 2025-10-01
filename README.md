Mesa de ayuda

Sistema CRUD para gestión de tickets de soporte técnico con CI/CD automatizado.

##  Tabla de Contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [CI/CD Pipeline](#cicd-pipeline)
- [Instalación Local](#instalación-local)
- [Despliegue en Render](#despliegue-en-render)
- [Variables de Entorno](#variables-de-entorno)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Uso](#uso)

## Características

- ✅ CRUD completo de tickets
- ✅ Gestión de usuarios
- ✅ Sistema de autenticación
- ✅ CI/CD automatizado con GitHub Actions
- ✅ Despliegue automático a Render
- ✅ Tests automatizados
- ✅ Validación de sintaxis PHP

##  Tecnologías

- **Backend:** PHP 8.1
- **Base de Datos:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **CI/CD:** GitHub Actions
- **Hosting:** Render (servicio cloud gratuito)

## CI/CD Pipeline

Este proyecto incluye un pipeline completo de CI/CD que se ejecuta automáticamente:

###  Continuous Integration (CI)

Cada vez que haces `push` o `pull request` a la rama `master`:

1.  **Validación de sintaxis PHP** - Verifica que no haya errores de sintaxis
2.  **Ejecución de tests** - Corre pruebas automáticas
3.  **Generación de reportes** - Crea reportes de cada build

###  Continuous Deployment (CD)

Cuando el código pasa todos los tests en la rama `master`:

1.  **Despliegue automático a Render** - Despliega la aplicación
2.  **Verificación post-despliegue** - Confirma que el sitio responde
3. **Notificación de estado** - Informa el resultado del despliegue

## Instalación Local

### Requisitos previos

- PHP 8.1 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx) o PHP built-in server

### Pasos

1. **Clonar el repositorio**

```bash
git clone https://github.com/pauladotor/Mesa-de-ayuda.git
cd Mesa-de-ayuda
```

2. **Configurar la base de datos**

```sql
CREATE DATABASE mesa_ayuda;
USE mesa_ayuda;

-- Importar el schema (si tienes un archivo SQL)
SOURCE database/schema.sql;
```

3. **Configurar variables de entorno**

Crea un archivo `.env` en la raíz del proyecto:

```env
DB_HOST=localhost
DB_NAME=mesa_ayuda
DB_USER=root
DB_PASS=tu_contraseña
DB_PORT=3306
APP_ENV=development
```

4. **Iniciar el servidor**

```bash
# Opción 1: Servidor PHP built-in
php -S localhost:8000 -t TecDesarrollo/view

# Opción 2: Configurar Apache/Nginx apuntando a la carpeta del proyecto
```

5. **Acceder a la aplicación**

Abre tu navegador en: `http://localhost:8000`

## ☁️ Despliegue en Render

### Por qué elegimos Render

- ✅ **Gratuito** para proyectos pequeños
- ✅ **Rápido** y fácil de configurar
- ✅ **Despliegue automático** desde GitHub
- ✅ **SSL gratuito** incluido
- ✅ **Buena documentación** y soporte

### Pasos para desplegar

#### 1. Crear cuenta en Render

1. Ve a [render.com](https://render.com)
2. Regístrate con tu cuenta de GitHub
3. Autoriza el acceso a tus repositorios

#### 2. Crear un nuevo Web Service

1. En el dashboard de Render, haz clic en **"New +"**
2. Selecciona **"Web Service"**
3. Conecta tu repositorio `Mesa-de-ayuda`
4. Configura:
   - **Name:** `mesa-de-ayuda`
   - **Environment:** `PHP`
   - **Branch:** `master`
   - **Build Command:** (dejar vacío si no usas Composer)
   - **Start Command:** `php -S 0.0.0.0:$PORT -t TecDesarrollo/view`

#### 3. Configurar Variables de Entorno en Render

En la sección "Environment" de tu servicio:

```
DB_HOST = tu-host-mysql.render.com
DB_NAME = mesa_ayuda
DB_USER = tu_usuario
DB_PASS = tu_contraseña_segura
DB_PORT = 3306
APP_ENV = production
```

#### 4. Crear Base de Datos en Render (Opcional)

1. En Render, crea un nuevo **PostgreSQL** o usa un servicio MySQL externo
2. Copia las credenciales a las variables de entorno

#### 5. Obtener el Deploy Hook

1. En tu servicio de Render, ve a **Settings**
2. Busca la sección **"Deploy Hook"**
3. Copia la URL del Deploy Hook

#### 6. Configurar Secrets en GitHub

1. Ve a tu repositorio en GitHub
2. Settings → Secrets and variables → Actions
3. Agrega estos secrets:

```
RENDER_DEPLOY_HOOK = https://api.render.com/deploy/srv-XXXXXXXX?key=YYYYYYYY
RENDER_URL = https://mesa-de-ayuda.onrender.com
```

#### 7. Probar el despliegue

```bash
# Hacer cualquier cambio y pushearlo
git add .
git commit -m "Probar despliegue automático"
git push origin master

# El pipeline se ejecutará automáticamente
# Ve a la pestaña "Actions" en GitHub para ver el progreso
```

## 🔐 Variables de Entorno

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `DB_HOST` | Host de la base de datos | `localhost` o `mysql.render.com` |
| `DB_NAME` | Nombre de la base de datos | `mesa_ayuda` |
| `DB_USER` | Usuario de la base de datos | `root` |
| `DB_PASS` | Contraseña de la base de datos | `tu_password_seguro` |
| `DB_PORT` | Puerto de MySQL | `3306` |
| `APP_ENV` | Entorno de la aplicación | `development` o `production` |
| `RENDER_DEPLOY_HOOK` | URL del webhook de Render | URL de deploy hook |
| `RENDER_URL` | URL de tu aplicación | `https://tu-app.onrender.com` |

## 📁 Estructura del Proyecto

```
Mesa-de-ayuda/
├── .github/
│   └── workflows/
│       ├── ci-cd-pipeline.yml    # Pipeline completo CI/CD
│       ├── php-tests.yml         # Tests antiguos
│       └── php-workflow.yml      # Workflow antiguo
├── TecDesarrollo/
│   ├── config/
│   │   └── database.php          # Configuración de BD
│   ├── controllers/              # Controladores
│   ├── models/                   # Modelos
│   │   ├── Ticket.php
│   │   └── Usuario.php
│   ├── public/
│   │   └── Scripts/              # JavaScript
│   ├── view/                     # Vistas HTML
│   │   ├── Home/
│   │   ├── Tickets/
│   │   └── index.php
│   └── test.php                  # Archivo de pruebas
├── .htaccess                     # Configuración Apache
├── render.yaml                   # Configuración de Render
├── README.md                     # Este archivo
└── .gitignore

```

## Uso

### Crear un ticket

```php
// Ejemplo de uso del CRUD
require_once 'config/database.php';
require_once 'models/Ticket.php';

$ticket = new Ticket();
$ticket->crear([
    'titulo' => 'Problema con el login',
    'descripcion' => 'No puedo iniciar sesión',
    'prioridad' => 'alta',
    'usuario_id' => 1
]);
```

## Ejecutar Tests

```bash
# Ejecutar tests manualmente
cd TecDesarrollo
php test.php

# O ver los tests en GitHub Actions
# Ve a la pestaña "Actions" en tu repositorio
```

## 🐛 Resolución de Problemas

### El despliegue falla

1. Verifica que `RENDER_DEPLOY_HOOK` esté configurado en GitHub Secrets
2. Revisa los logs en la pestaña "Actions" de GitHub
3. Verifica los logs en el dashboard de Render

### Error de conexión a la base de datos

1. Verifica que las variables de entorno estén configuradas correctamente
2. Asegúrate de que la base de datos esté creada
3. Revisa las credenciales en Render

### El sitio no carga

1. Verifica que el `Start Command` en Render sea correcto
2. Revisa los logs de Render para ver errores
3. Asegúrate de que el puerto esté configurado correctamente

## 📝 Entregables de la Actividad

- [x] Pipeline de CI configurado y funcionando
- [x] Pipeline de CD configurado y funcionando
- [x] Despliegue automático a servicio cloud (Render)
- [x] README con documentación completa
- [x] Variables de entorno configuradas
- [x] URL del servicio desplegado funcionando

## 📧 Contacto

- **Autor:** Paula Dotor
- **Repositorio:** [github.com/pauladotor/Mesa-de-ayuda](https://github.com/pauladotor/Mesa-de-ayuda)
- **URL Desplegada:** [mesa-de-ayuda.onrender.com](https://mesa-de-ayuda.onrender.com) *(actualizar con tu URL)*

## 📄 Licencia

Este proyecto es parte de una actividad académica.

---

**Nota:** Este proyecto incluye CI/CD automatizado. Cada push a la rama `master` ejecutará automáticamente los tests y desplegará a producción si todo está correcto. 🚀
