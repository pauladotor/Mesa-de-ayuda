# 🚀 Guía de Despliegue - Mesa de Ayuda

## 📋 Descripción del Proyecto

**Mesa de Ayuda** es un sistema de gestión de tickets de soporte desarrollado en PHP con PostgreSQL. Este proyecto permite a los usuarios crear, gestionar y dar seguimiento a tickets de soporte técnico.

## 🏗️ Arquitectura del Despliegue

El proyecto está configurado para desplegarse en **Render** usando contenedores Docker:

- **Contenedor Web (PHP 8.1 + Apache)**: Ejecuta la aplicación PHP
- **Base de datos PostgreSQL 15**: Base de datos para almacenar usuarios, tickets y departamentos
- **Puerto de salida**: 80 (HTTP)

## 📦 Archivos de Configuración Creados

### 1. `Dockerfile`
Configura el contenedor PHP con Apache y las extensiones necesarias (PDO, pdo_pgsql, pgsql).

### 2. `docker-compose.yml`
Orquesta los contenedores para pruebas locales antes del despliegue.

### 3. `database/init.sql`
Script SQL que inicializa la base de datos con:
- Tablas: roles, usuarios, departamentos, tickets
- Datos iniciales: roles, departamentos, usuario administrador
- Usuario admin: `admin@mesaayuda.com` / `admin123`
- Usuario cliente: `cliente@mesaayuda.com` / `cliente123`

### 4. `config/conexion.php` (Modificado)
Actualizado para usar variables de entorno, permitiendo configuración flexible entre desarrollo y producción.

### 5. `.env.example`
Plantilla de variables de entorno necesarias.

### 6. `.dockerignore`
Excluye archivos innecesarios del contenedor Docker.

## 🔧 Variables de Entorno Configuradas

Las siguientes variables de entorno son necesarias para el funcionamiento:

| Variable | Descripción | Valor por Defecto |
|----------|-------------|-------------------|
| `DB_HOST` | Host de la base de datos | `db` (Docker) / `localhost` |
| `DB_PORT` | Puerto de PostgreSQL | `5432` |
| `DB_NAME` | Nombre de la base de datos | `usuarios_db` |
| `DB_USER` | Usuario de PostgreSQL | `mesaayuda` |
| `DB_PASS` | Contraseña de PostgreSQL | `mesaayuda123` |

## 🧪 Pruebas Locales con Docker

Antes de desplegar en Render, puedes probar localmente:

### Prerrequisitos
- Docker Desktop instalado
- Git instalado

### Pasos para prueba local:

1. **Clonar el repositorio** (si aún no lo has hecho):
```bash
git clone <tu-repositorio>
cd TecDesarrollo
```

2. **Crear archivo .env** (opcional, usa valores por defecto):
```bash
cp .env.example .env
```

3. **Construir y levantar los contenedores**:
```bash
docker-compose up -d --build
```

4. **Verificar que los contenedores estén corriendo**:
```bash
docker-compose ps
```

5. **Acceder a la aplicación**:
- Abrir navegador en: `http://localhost:8080/view/Home/index.php`

6. **Credenciales de prueba**:
   - **Admin**: `admin@mesaayuda.com` / `admin123`
   - **Cliente**: `cliente@mesaayuda.com` / `cliente123`

7. **Ver logs** (si hay problemas):
```bash
docker-compose logs -f
```

8. **Detener los contenedores**:
```bash
docker-compose down
```

## 🌐 Despliegue en Render

### Opción 1: Despliegue Manual (Recomendado para este proyecto)

#### Paso 1: Crear cuenta en Render
1. Ve a [https://render.com](https://render.com)
2. Crea una cuenta gratuita
3. Verifica tu correo electrónico

#### Paso 2: Crear Base de Datos PostgreSQL
1. En el dashboard de Render, haz clic en **"New +"** → **"PostgreSQL"**
2. Configura:
   - **Name**: `mesa-ayuda-db`
   - **Database**: `usuarios_db`
   - **User**: `mesaayuda` (se genera automáticamente)
   - **Region**: Selecciona la más cercana (ej: Oregon)
   - **PostgreSQL Version**: 15
   - **Plan**: Free
3. Haz clic en **"Create Database"**
4. **IMPORTANTE**: Guarda las credenciales que Render te proporciona:
   - Internal Database URL
   - External Database URL
   - Username
   - Password
   - Host
   - Port

#### Paso 3: Inicializar la Base de Datos
1. En Render, ve a tu base de datos PostgreSQL
2. Haz clic en **"Connect"** → **"External Connection"**
3. Usa un cliente PostgreSQL (pgAdmin, DBeaver, o línea de comandos) para conectarte
4. Ejecuta el script `database/init.sql` completo en tu base de datos

**Usando línea de comandos (psql)**:
```bash
psql -h <EXTERNAL_HOST> -U <USERNAME> -d usuarios_db -f database/init.sql
# Ingresa tu password cuando te lo pida
```

**Usando la conexión completa**:
```bash
psql postgresql://<USERNAME>:<PASSWORD>@<HOST>:<PORT>/usuarios_db -f database/init.sql
```

#### Paso 4: Conectar GitHub a Render
1. En tu repositorio de GitHub, asegúrate de que todos los archivos estén subidos:
```bash
git add .
git commit -m "Configuración Docker para despliegue en Render"
git push origin main
```

#### Paso 5: Crear Web Service en Render
1. En Render, haz clic en **"New +"** → **"Web Service"**
2. Conecta tu repositorio de GitHub
3. Selecciona el repositorio `TecDesarrollo`
4. Configura:
   - **Name**: `mesa-ayuda-web`
   - **Region**: La misma que la base de datos
   - **Branch**: `main`
   - **Root Directory**: (dejar vacío)
   - **Environment**: `Docker`
   - **Dockerfile Path**: `./Dockerfile`
   - **Plan**: Free

#### Paso 6: Configurar Variables de Entorno
En la sección **"Environment Variables"**, agrega:

| Key | Value |
|-----|-------|
| `DB_HOST` | (Host del Internal Database URL de PostgreSQL) |
| `DB_PORT` | `5432` |
| `DB_NAME` | `usuarios_db` |
| `DB_USER` | (Usuario de tu PostgreSQL en Render) |
| `DB_PASS` | (Contraseña de tu PostgreSQL en Render) |

**Ejemplo de Internal Database URL**:
```
postgresql://mesaayuda:abc123xyz@dpg-xxxxx-a:5432/usuarios_db
```

**Variables de entorno extraídas**:
```
DB_HOST=dpg-xxxxx-a
DB_PORT=5432
DB_NAME=usuarios_db
DB_USER=mesaayuda
DB_PASS=abc123xyz
```

#### Paso 7: Desplegar
1. Haz clic en **"Create Web Service"**
2. Render comenzará a construir tu aplicación (puede tardar 5-10 minutos)
3. Observa los logs para verificar que no haya errores

#### Paso 8: Verificar el Despliegue
1. Una vez completado, Render te dará una URL como: `https://mesa-ayuda-web.onrender.com`
2. Accede a: `https://mesa-ayuda-web.onrender.com/view/Home/index.php`
3. Inicia sesión con las credenciales de prueba

### Opción 2: Despliegue con GitHub Actions (Opcional)

Si deseas automatizar el despliegue, puedes crear un workflow de GitHub Actions.

#### Crear archivo `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Render

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Trigger Render Deploy
      run: |
        curl -X POST ${{ secrets.RENDER_DEPLOY_HOOK }}
```

#### Configurar Deploy Hook:
1. En Render, ve a tu Web Service
2. Ve a **Settings** → **Deploy Hook**
3. Copia la URL del Deploy Hook
4. En GitHub, ve a tu repositorio → **Settings** → **Secrets and variables** → **Actions**
5. Crea un nuevo secret:
   - Name: `RENDER_DEPLOY_HOOK`
   - Value: (pega la URL del Deploy Hook)

Ahora, cada vez que hagas `git push`, se desplegará automáticamente.

## 🔍 Verificación del Despliegue

### Checklist de Verificación:
- [ ] La URL de Render carga correctamente
- [ ] Puedes acceder a `/view/Home/index.php`
- [ ] Puedes iniciar sesión con el usuario admin
- [ ] Puedes crear un nuevo ticket
- [ ] Los estilos CSS se cargan correctamente
- [ ] No hay errores en la consola del navegador

### Solución de Problemas Comunes:

#### 1. Error de conexión a la base de datos
**Síntoma**: "Error de conexión: SQLSTATE[HY000]"
**Solución**: 
- Verifica que las variables de entorno estén correctamente configuradas
- Asegúrate de usar el **Internal Database URL** (no el External)
- Verifica que `DB_PORT` esté configurado como `5432`
- Verifica que la base de datos esté inicializada con el script SQL
- Asegúrate de que el host NO incluya `postgresql://` ni el puerto

#### 2. Página en blanco o error 500
**Síntoma**: La página no carga o muestra error 500
**Solución**:
- Revisa los logs en Render: **Logs** tab
- Verifica que el Dockerfile se haya construido correctamente
- Asegúrate de que todos los archivos PHP estén en el repositorio

#### 3. CSS o imágenes no cargan
**Síntoma**: La página se ve sin estilos
**Solución**:
- Verifica las rutas en los archivos PHP
- Asegúrate de que las carpetas `css/` e `img/` estén en el repositorio
- Revisa los permisos de archivos en el contenedor

#### 4. "Forbidden - You don't have permission to access this resource"
**Síntoma**: Error 403 al acceder a la URL
**Solución**:
- Asegúrate de que existe un archivo `index.php` en la raíz del proyecto
- Verifica que el Dockerfile configure correctamente los permisos de Apache
- El archivo `index.php` en la raíz redirige automáticamente a `/view/Home/index.php`

#### 5. "Application failed to respond"
**Síntoma**: Render muestra este mensaje
**Solución**:
- El contenedor puede estar tardando en iniciar
- Verifica los logs para ver si hay errores de PHP
- Asegúrate de que Apache esté corriendo correctamente

## 📊 Monitoreo y Mantenimiento

### Ver logs en tiempo real:
1. Ve a tu Web Service en Render
2. Haz clic en la pestaña **"Logs"**
3. Los logs se actualizan en tiempo real

### Reiniciar el servicio:
1. Ve a **Settings**
2. Haz clic en **"Manual Deploy"** → **"Deploy latest commit"**

### Actualizar la aplicación:
1. Haz cambios en tu código local
2. Commit y push a GitHub:
```bash
git add .
git commit -m "Descripción de cambios"
git push origin main
```
3. Render detectará los cambios y redesplegará automáticamente

## 🔒 Seguridad en Producción

### Recomendaciones importantes:

1. **Cambiar contraseñas por defecto**:
   - Cambia las contraseñas de los usuarios admin y cliente
   - Usa contraseñas fuertes generadas

2. **Variables de entorno**:
   - Nunca subas archivos `.env` al repositorio
   - Usa las variables de entorno de Render

3. **HTTPS**:
   - Render proporciona HTTPS automáticamente
   - Asegúrate de usar siempre HTTPS en producción

4. **Backups**:
   - Render Free tier no incluye backups automáticos
   - Considera exportar la base de datos periódicamente

## 📝 Actualización del README.md

Agrega esta sección a tu `README.md`:

```markdown
## 🚀 Despliegue

Este proyecto está desplegado en Render usando Docker.

### Servicio Utilizado
- **Plataforma**: Render
- **Tipo**: Web Service + MySQL Database
- **Contenedorización**: Docker

### Variables de Entorno Configuradas
- `DB_HOST`: Host de la base de datos MySQL
- `DB_NAME`: Nombre de la base de datos (usuarios_db)
- `DB_USER`: Usuario de MySQL
- `DB_PASS`: Contraseña de MySQL

### Dificultades Encontradas y Soluciones

1. **Conexión entre contenedores**: 
   - Problema: La aplicación PHP no podía conectarse a MySQL
   - Solución: Configurar variables de entorno y usar el Internal Database URL de Render

2. **Inicialización de la base de datos**:
   - Problema: Las tablas no existían al desplegar
   - Solución: Crear script `init.sql` y ejecutarlo manualmente en la base de datos de Render

3. **Rutas de archivos**:
   - Problema: CSS e imágenes no cargaban
   - Solución: Verificar rutas relativas y estructura de directorios en el contenedor

### URL de Despliegue
🔗 [https://mesa-ayuda-web.onrender.com/view/Home/index.php](https://mesa-ayuda-web.onrender.com/view/Home/index.php)

### Credenciales de Prueba
- **Admin**: admin@mesaayuda.com / admin123
- **Cliente**: cliente@mesaayuda.com / cliente123
```

## 🎯 Resumen de Entregables

Para la entrega del 2 de octubre, debes presentar:

1. ✅ **URL del despliegue funcionando**
2. ✅ **README.md actualizado** con:
   - Servicio elegido (Render)
   - Configuración de variables de entorno
   - Dificultades encontradas y soluciones
3. ✅ **CRUD funcionando** sin necesidad de XAMPP

## 📞 Soporte

Si encuentras problemas durante el despliegue:
1. Revisa los logs en Render
2. Verifica la sección de "Solución de Problemas" en esta guía
3. Consulta la documentación oficial de Render: https://render.com/docs

---

**¡Éxito con tu despliegue! 🚀**
