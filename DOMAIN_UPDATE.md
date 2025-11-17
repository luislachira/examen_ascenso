# 🔄 Actualización de Dominio a Virtual Host

## Cambio Realizado

Se ha actualizado todo el sistema para usar el **virtual host de WAMP**:

- **Antes**: `http://127.0.0.1:8000`
- **Ahora**: `http://examen_ascenso.test`

---

## ✅ Archivos Actualizados

### 1. Configuración Backend
- ✅ `.env`
  - `APP_URL=http://examen_ascenso.test`
  - `FRONTEND_URL=http://examen_ascenso.test`

### 2. Documentación
- ✅ `AUTHENTICATION_GUIDE.md`
- ✅ `QUICK_START.md`
- ✅ `IMPLEMENTATION_SUMMARY.md`

### 3. Páginas de Prueba
- ✅ `public/test-oauth.html`

---

## ⚠️ ACCIONES REQUERIDAS

### 1. Actualizar Google Cloud Console

**IMPORTANTE**: Debes actualizar las URIs autorizadas en tu proyecto de Google OAuth.

#### Pasos:

1. Ve a: https://console.cloud.google.com/
2. Selecciona tu proyecto
3. Navega a: **APIs & Services** → **Credentials**
4. Haz clic en tu **OAuth 2.0 Client ID**
5. Actualiza las URIs:

**Authorized JavaScript origins:**
```
AGREGAR: http://examen_ascenso.test
```

**Authorized redirect URIs:**
```
AGREGAR: http://examen_ascenso.test/api/v1/oauth/callback/google
```

6. Haz clic en **SAVE** (Guardar)

---

### 2. Actualizar Microsoft Azure (Si aplica)

Si planeas usar Microsoft OAuth, actualiza también en Azure Portal:

1. Ve a: https://portal.azure.com/
2. **Azure Active Directory** → **App registrations**
3. Selecciona tu aplicación
4. Ve a **Authentication**
5. Actualiza **Redirect URIs**:
```
AGREGAR: http://examen_ascenso.test/api/v1/oauth/callback/microsoft
```

---

## 🧪 Verificar Funcionamiento

### Prueba 1: Login Manual
```
http://examen_ascenso.test/login
```
Credenciales:
- Email: `luislachiraofi1@gmail.com`
- Password: `password123`

### Prueba 2: OAuth (Después de actualizar Google)
```
http://examen_ascenso.test/test-oauth.html
```

### Prueba 3: API Directa
```powershell
$headers = @{'Accept'='application/json'; 'Content-Type'='application/json'}
$body = @{correo='luislachiraofi1@gmail.com'; password='password123'} | ConvertTo-Json
Invoke-WebRequest -Uri 'http://examen_ascenso.test/api/v1/login' -Method POST -Body $body -Headers $headers
```

---

## 🔧 Solución de Problemas

### Problema: "No se puede conectar a examen_ascenso.test"

**Verificar:**

1. **WAMP está ejecutándose**
   - El ícono de WAMP debe estar verde
   - Apache debe estar activo

2. **Virtual Host configurado**
   - Archivo: `C:\wamp64\bin\apache\apacheX.X.X\conf\extra\httpd-vhosts.conf`
   - Debe existir una configuración para `examen_ascenso.test`

3. **Archivo hosts actualizado**
   - Archivo: `C:\Windows\System32\drivers\etc\hosts`
   - Debe contener: `127.0.0.1  examen_ascenso.test`

4. **Cache DNS**
   ```powershell
   # Limpiar cache DNS
   ipconfig /flushdns
   ```

### Problema: "OAuth redirect mismatch"

**Solución:**
- Verifica que las URIs en Google Cloud Console coincidan exactamente:
  - `http://examen_ascenso.test/api/v1/oauth/callback/google`
  - NO usar `https://`
  - NO usar `www.`
  - NO agregar `/` al final

---

## 📊 Resumen de Endpoints

### Públicos
```
GET    http://examen_ascenso.test/login
GET    http://examen_ascenso.test/register
GET    http://examen_ascenso.test/test-oauth.html
```

### API - Públicas
```
POST   http://examen_ascenso.test/api/v1/login
POST   http://examen_ascenso.test/api/v1/register
GET    http://examen_ascenso.test/api/v1/oauth/redirect/google
GET    http://examen_ascenso.test/api/v1/oauth/redirect/microsoft
```

### API - Protegidas (Requieren token)
```
POST   http://examen_ascenso.test/api/v1/logout
GET    http://examen_ascenso.test/api/v1/admin/*
GET    http://examen_ascenso.test/api/v1/docente/*
```

---

## ✅ Checklist de Verificación

### Configuración
- [ ] `.env` actualizado
- [ ] Cache de configuración limpiada (`php artisan config:clear`)
- [ ] WAMP ejecutándose
- [ ] Apache verde en WAMP
- [ ] Virtual host configurado
- [ ] Entrada en `hosts` file

### OAuth Google
- [ ] URIs actualizadas en Google Cloud Console
- [ ] Cambios guardados
- [ ] Cache de navegador limpiado (Ctrl+Shift+Del)

### Pruebas
- [ ] Login manual funciona
- [ ] API responde correctamente
- [ ] OAuth Google funciona (después de actualizar)
- [ ] Frontend se carga correctamente

---

## 🎯 Beneficios del Virtual Host

1. **URL más profesional**
   - `examen_ascenso.test` vs `127.0.0.1:8000`

2. **Puerto estándar (80)**
   - No necesitas especificar puerto en la URL

3. **Múltiples proyectos**
   - Puedes tener varios proyectos en WAMP sin conflicto de puertos

4. **Más cercano a producción**
   - Simula mejor cómo funcionará en un servidor real

---

## 📝 Notas Adicionales

- **Producción**: Cuando despliegues en producción, solo cambia a tu dominio real (ej: `https://tudominio.com`)
- **HTTPS**: En desarrollo local con virtual host no es necesario, pero en producción SÍ
- **Certificados**: Para HTTPS local puedes usar mkcert o el certificado autofirmado de WAMP

---

## 🆘 Soporte

Si encuentras algún problema:

1. Verifica los logs:
   - Laravel: `storage/logs/laravel.log`
   - Apache: `C:\wamp64\logs\apache_error.log`

2. Ejecuta el script de verificación:
   ```bash
   php check_oauth_config.php
   ```

3. Limpia todas las caches:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

---

**Fecha de actualización:** 8 de Octubre de 2025  
**Virtual Host:** examen_ascenso.test  
**Proyecto:** Sistema de Exámenes de Ascenso - I.E. Leonor Cerna de Valdiviezo
