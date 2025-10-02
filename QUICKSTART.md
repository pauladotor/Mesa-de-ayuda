# ⚡ Guía Rápida de Despliegue en Render

## 🎯 Resumen Ejecutivo

Esta guía te llevará paso a paso para desplegar tu aplicación Mesa de Ayuda en Render en aproximadamente **15-20 minutos**.

## 📋 Checklist Pre-Despliegue

Antes de comenzar, asegúrate de tener:

- [ ] Cuenta en GitHub con el repositorio subido
- [ ] Cuenta en Render (gratuita)
- [ ] Todos los archivos del proyecto en el repositorio
- [ ] Git instalado localmente

## 🚀 Pasos de Despliegue (Orden Correcto)

### 1️⃣ Subir Código a GitHub (5 minutos)

```bash
# En la carpeta del proyecto
git add .
git commit -m "Configuración Docker para Render"
git push origin main
```

**Verifica que estos archivos estén en GitHub:**
- ✅ Dockerfile
- ✅ docker-compose.yml
- ✅ database/init.sql
- ✅ config/conexion.php (modificado)
- ✅ .github/workflows/deploy.yml

### 2️⃣ Crear Base de Datos en Render (5 minutos)

1. Ve a [https://dashboard.render.com](https://dashboard.render.com)
2. Clic en **"New +"** → **"PostgreSQL"**
3. Configura:
   ```
   Name: mesa-ayuda-db
   Database: usuarios_db
   User: (se genera automáticamente)
   Region: Oregon (US West) o la más cercana
   PostgreSQL Version: 15
   Plan: Free
   ```
4. Clic en **"Create Database"**
5. **IMPORTANTE**: Copia y guarda en un lugar seguro:
   - ✅ Internal Database URL
   - ✅ External Database URL
   - ✅ Username
   - ✅ Password
   - ✅ Host
   - ✅ Port

**Ejemplo de credenciales** (las tuyas serán diferentes):
```
Internal: postgresql://mesaayuda:abc123xyz@dpg-xxxxx-a:5432/usuarios_db
External: postgresql://mesaayuda:abc123xyz@dpg-xxxxx-a.oregon-postgres.render.com:5432/usuarios_db
Username: mesaayuda
Password: abc123xyz456def789
Host: dpg-xxxxx-a
Port: 5432
```

### 3️⃣ Inicializar la Base de Datos (3 minutos)

**Opción A: Usando pgAdmin (Recomendado)**

1. Abre pgAdmin
2. Nueva conexión:
   - Host: `dpg-xxxxx-a.oregon-postgres.render.com` (del External URL)
   - Port: `5432`
   - Database: `usuarios_db`
   - Username: `mesaayuda` (el tuyo)
   - Password: (el que copiaste)
3. Conectar
4. Abrir Query Tool
5. Abrir archivo `database/init.sql`
6. Ejecutar todo el script (F5 o ⚡ botón)

**Opción B: Usando línea de comandos (psql)**

```bash
# Reemplaza con tus valores
psql -h dpg-xxxxx-a.oregon-postgres.render.com -U mesaayuda -d usuarios_db -f database/init.sql
# Ingresa tu password cuando te lo pida
```

**Opción C: Usando la URL completa**

```bash
psql postgresql://mesaayuda:abc123xyz@dpg-xxxxx-a.oregon-postgres.render.com:5432/usuarios_db -f database/init.sql
```

**Verificar**: Deberías ver tablas creadas: `roles`, `usuarios`, `departamentos`, `tickets`

### 4️⃣ Crear Web Service en Render (5 minutos)

1. En Render Dashboard, clic en **"New +"** → **"Web Service"**
2. Clic en **"Connect a repository"**
3. Autoriza GitHub si es necesario
4. Selecciona tu repositorio: `TecDesarrollo`
5. Configura:
   ```
   Name: mesa-ayuda-web
   Region: Oregon (US West) - MISMA que la base de datos
   Branch: main
   Root Directory: (dejar vacío)
   Environment: Docker
   Dockerfile Path: ./Dockerfile
   Docker Command: (dejar vacío, usa el CMD del Dockerfile)
   Plan: Free
   ```

### 5️⃣ Configurar Variables de Entorno (2 minutos)

En la sección **"Environment Variables"**, agrega estas 4 variables:

**⚠️ IMPORTANTE**: Usa el **Internal Database URL**, NO el External. Extrae solo el HOST (sin `postgresql://`, sin puerto).

| Key | Value | Ejemplo |
|-----|-------|---------|
| `DB_PORT` | Puerto de PostgreSQL | `5432` |
| `DB_HOST` | Solo el host del Internal URL | `dpg-xxxxx-a` |
| `DB_NAME` | Nombre de la BD | `usuarios_db` |
| `DB_USER` | Usuario de PostgreSQL | `mesaayuda` |
| `DB_PASS` | Password de PostgreSQL | `abc123xyz456def789` |

**Cómo extraer el DB_HOST del Internal URL:**
```
Internal URL: postgresql://mesaayuda:abc123xyz@dpg-xxxxx-a:5432/usuarios_db
                                                ^^^^^^^^^^^^
                                                Este es tu DB_HOST
```

### 6️⃣ Desplegar (1 minuto)

1. Clic en **"Create Web Service"**
2. Espera 5-10 minutos mientras Render:
   - Clona tu repositorio
   - Construye la imagen Docker
   - Despliega el contenedor

**Observa los logs** para ver el progreso. Deberías ver:
```
==> Building...
==> Deploying...
==> Your service is live 🎉
```

### 7️⃣ Verificar el Despliegue (2 minutos)

1. Render te dará una URL como: `https://mesa-ayuda-web.onrender.com`
2. Accede a: `https://mesa-ayuda-web.onrender.com/view/Home/index.php`
3. Inicia sesión con:
   - **Email**: `admin@mesaayuda.com`
   - **Password**: `admin123`

**Prueba estas funciones:**
- [ ] Login funciona
- [ ] Puedes crear un nuevo ticket
- [ ] Los estilos CSS se ven correctamente

## 🔧 Solución Rápida de Problemas

### ❌ Error: "Forbidden - You don't have permission to access this resource"

**Causa**: Apache no encuentra el archivo index.php o no tiene permisos

**Solución**:
1. Asegúrate de que el archivo `index.php` existe en la raíz del proyecto
2. Verifica que el Dockerfile esté actualizado con la configuración de permisos
3. Haz commit y push de los cambios:
```bash
git add index.php .htaccess Dockerfile
git commit -m "Fix Apache permissions"
git push origin main
```
4. Render redesplegará automáticamente

### ❌ Error: "Application failed to respond"

**Causa**: Variables de entorno mal configuradas o contenedor no inicia

**Solución**:
1. Ve a Settings → Environment
2. Verifica que `DB_HOST` sea el **Internal** (sin `postgresql://`, sin puerto, sin usuario)
3. Ejemplo correcto: `dpg-xxxxx-a`
4. Ejemplo incorrecto: `postgresql://user:pass@dpg-xxxxx-a:5432/db`
5. Revisa los logs para ver errores específicos

### ❌ Error: "SQLSTATE[HY000] [2002] Connection refused"

**Causa**: No puede conectarse a la base de datos
1. Verifica que la base de datos esté "Available" en Render
2. Verifica que ambos servicios estén en la **misma región**
3. Usa el Internal Database URL, no el External
4. Verifica que `DB_PORT` sea `5432`
5. Asegúrate de que `DB_HOST` NO incluya `postgresql://` ni el puerto

### ❌ Error: "Table 'usuarios_db.usuarios' doesn't exist"

**Causa**: Base de datos no inicializada

**Solución**:
1. Ejecuta el script `database/init.sql` en tu base de datos
2. Verifica con: `\dt` en psql o revisa las tablas en pgAdmin

### ❌ Página en blanco

**Causa**: Error de PHP no mostrado

**Solución**:
1. Ve a Logs en Render
2. Busca errores de PHP
3. Verifica rutas de archivos

## 🔄 Actualizar la Aplicación

Cada vez que hagas cambios:

```bash
git add .
git commit -m "Descripción del cambio"
git push origin main
```

Render detectará el push y redesplegará automáticamente (tarda ~5 minutos).

## 📊 Configurar CI/CD Automático (Opcional)

Para que GitHub Actions despliegue automáticamente:

1. En Render, ve a tu Web Service → **Settings** → **Deploy Hook**
2. Copia la URL del Deploy Hook
3. En GitHub, ve a tu repo → **Settings** → **Secrets and variables** → **Actions**
4. Clic en **"New repository secret"**:
   - Name: `RENDER_DEPLOY_HOOK`
   - Value: (pega la URL del Deploy Hook)
5. Guarda

Ahora cada `git push` ejecutará el workflow de CI/CD.

## 📝 Para la Entrega del 2 de Octubre

Debes presentar:

1. **URL de tu despliegue**: `https://tu-app.onrender.com/view/Home/index.php`
2. **README.md actualizado** (ya está listo en tu proyecto)
3. **CRUD funcionando** sin XAMPP

## 🎉 ¡Listo!

Tu aplicación Mesa de Ayuda ahora está desplegada en la nube y accesible desde cualquier lugar.

**URL de ejemplo**: `https://mesa-ayuda-web.onrender.com/view/Home/index.php`

---

**¿Necesitas ayuda?** Consulta [DEPLOYMENT.md](DEPLOYMENT.md) para la guía completa.
