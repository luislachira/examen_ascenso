# ✅ Checklist de Producción - Sistema de Examen de Ascenso

## 🔒 Seguridad

### Variables de Entorno
- [x] `.env.example` existe y no contiene credenciales reales
- [ ] `.env` está en `.gitignore` (VERIFICADO ✅)
- [ ] `APP_DEBUG=false` en producción (VERIFICAR en servidor)
- [ ] `APP_ENV=production` en producción (VERIFICAR en servidor)
- [ ] `APP_KEY` está configurado (VERIFICAR en servidor)
- [ ] Todas las credenciales de base de datos configuradas
- [ ] Credenciales de OAuth (Google/Microsoft) configuradas
- [ ] Credenciales de Passport configuradas

### Archivos Sensibles
- [x] No hay credenciales hardcodeadas en el código
- [x] Archivos de prueba están en `.gitignore`
- [x] Certificados SSL están en `.gitignore`
- [x] Archivos de prueba eliminados (`check_phpinfo.php`, `test-oauth.html`)

### Configuración de Seguridad
- [x] CSRF habilitado para rutas web
- [x] CORS configurado correctamente
- [x] Middleware de autenticación configurado
- [x] Middleware de roles configurado
- [x] Sesiones configuradas correctamente
- [x] Cookies encriptadas (excepto las necesarias)

## ⚙️ Configuración

### Laravel
- [x] `APP_NAME` configurado correctamente
- [x] `APP_URL` debe ser la URL de producción
- [x] Cache de configuración: `php artisan config:cache`
- [x] Cache de rutas: `php artisan route:cache`
- [x] Cache de vistas: `php artisan view:cache`
- [x] Optimización: `php artisan optimize`

### Base de Datos
- [ ] Migraciones ejecutadas: `php artisan migrate --force`
- [ ] Seeders ejecutados (si es necesario)
- [ ] Backup de base de datos configurado

### Frontend
- [x] Build de producción ejecutado: `npm run build`
- [x] Code splitting configurado correctamente
- [x] No hay errores de JavaScript en consola
- [x] Sourcemaps deshabilitados en producción
- [x] Assets optimizados y minificados

## 📝 Logs y Debugging

### Logs
- [x] `LOG_CHANNEL` configurado (recomendado: `daily` o `stack`)
- [x] `LOG_LEVEL` configurado (recomendado: `error` o `warning` en producción)
- [x] Rotación de logs configurada
- [ ] Monitoreo de logs configurado (opcional)

### Debugging
- [x] `console.log` removidos del código de producción
- [x] Comentarios de debug removidos o deshabilitados
- [ ] Error tracking configurado (Sentry, Bugsnag, etc.) - OPCIONAL

## 🚀 Performance

### Optimizaciones
- [x] Code splitting implementado
- [x] Lazy loading de rutas implementado
- [x] Assets compilados y optimizados
- [ ] Cache de consultas configurado (Redis/Memcached) - OPCIONAL
- [ ] Queue workers configurados (si se usan) - OPCIONAL
- [ ] CDN configurado para assets estáticos - OPCIONAL

### Build
- [x] Build sin errores
- [x] Chunks optimizados
- [x] Tamaño de bundles aceptable

## 🌐 Servidor

### Requisitos
- [ ] PHP >= 8.1
- [ ] Extensiones PHP necesarias instaladas
- [ ] Composer instalado
- [ ] Node.js y npm instalados (solo para build)
- [ ] Servidor web configurado (Apache/Nginx)
- [ ] SSL/HTTPS configurado

### Permisos
- [ ] `storage/` y `bootstrap/cache/` con permisos de escritura (775)
- [ ] Propietario correcto de archivos
- [ ] `.env` con permisos 600

### Configuración del Servidor Web
- [ ] Document root apunta a `/public`
- [ ] URL rewriting configurado
- [ ] Headers de seguridad configurados (HSTS, CSP, etc.) - OPCIONAL

## 📦 Dependencias

### PHP
- [x] `composer install --optimize-autoloader --no-dev` ejecutado
- [x] Dependencias de desarrollo excluidas

### Node.js
- [x] `npm install --production` (solo si se necesita en servidor)
- [x] Build ejecutado localmente y subido

## 🔍 Verificaciones Finales

### Funcionalidad
- [ ] Login funciona correctamente
- [ ] OAuth (Google/Microsoft) funciona
- [ ] Roles y permisos funcionan
- [ ] Todas las funcionalidades principales probadas
- [ ] Auto-logout por inactividad funciona

### UI/UX
- [x] Logo de la institución aparece correctamente
- [x] Favicon configurado
- [x] Estilos cargados correctamente
- [ ] Responsive design verificado

### Errores Conocidos
- [x] Warnings en `ExamenController.php` corregidos (verificación de tipo agregada)

## 📋 Comandos de Despliegue

```bash
# 1. Actualizar código
git pull origin main

# 2. Instalar dependencias PHP
composer install --optimize-autoloader --no-dev

# 3. Ejecutar migraciones
php artisan migrate --force

# 4. Limpiar y optimizar cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 5. Limpiar cache de Passport (si aplica)
php artisan passport:keys

# 6. Verificar permisos
chmod -R 775 storage bootstrap/cache
chmod 600 .env

# 7. Reiniciar servicios (si es necesario)
# systemctl restart php-fpm
# systemctl restart nginx
```

## ⚠️ Acciones Requeridas ANTES de Producción

1. **ELIMINAR archivos de prueba del servidor:**
   - `public/check_phpinfo.php`
   - `public/test-oauth.html`

2. **VERIFICAR variables de entorno en `.env` del servidor:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://examen-ascenso.com
   ```

3. **✅ Warnings en `ExamenController.php` corregidos**

4. **EJECUTAR comandos de optimización** (ver sección arriba)

5. **VERIFICAR que SSL/HTTPS esté configurado correctamente**

6. **PROBAR todas las funcionalidades críticas** después del despliegue

