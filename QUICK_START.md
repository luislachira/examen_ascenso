# 🚀 Guía Rápida de Inicio

## ✅ Login Manual Funcionando

### Credenciales actuales:
- **Email**: `luislachiraofi1@gmail.com`
- **Contraseña**: `password123`

### Cambiar contraseña (Recomendado):
```bash
php artisan admin:change-password
```

---

## 🔵 Para habilitar Login con Google:

### 1. Obtén credenciales OAuth de Google:
- Ve a: https://console.cloud.google.com/
- Crea un proyecto
- Habilita "Google+ API"
- Crea credenciales OAuth 2.0
- Redirect URI: `http://examen_ascenso.test/api/v1/oauth/callback/google`

### 2. Agrega al `.env`:
```env
GOOGLE_CLIENT_ID=tu_client_id_aqui
GOOGLE_CLIENT_SECRET=tu_client_secret_aqui
```

### 3. Limpia la cache:
```bash
php artisan config:clear
```

---

## 🟦 Para habilitar Login con Microsoft:

### 1. Registra la app en Azure:
- Ve a: https://portal.azure.com/
- Azure Active Directory → App registrations → New registration
- Redirect URI: `http://examen_ascenso.test/api/v1/oauth/callback/microsoft`

### 2. Agrega al `.env`:
```env
MICROSOFT_CLIENT_ID=tu_application_id_aqui
MICROSOFT_CLIENT_SECRET=tu_client_secret_aqui
MICROSOFT_TENANT=common
```

### 3. Limpia la cache:
```bash
php artisan config:clear
```

---

## 🧪 Probar el Login

### Desde el navegador:
1. Ve a: http://examen_ascenso.test/login
2. Usa: `luislachiraofi1@gmail.com` / `password123`
3. Deberías ser redirigido al dashboard

### Desde la API (PowerShell):
```powershell
$headers = @{'Accept'='application/json'; 'Content-Type'='application/json'}
$body = @{correo='luislachiraofi1@gmail.com'; password='password123'} | ConvertTo-Json
Invoke-WebRequest -Uri 'http://examen_ascenso.test/api/v1/login' -Method POST -Body $body -Headers $headers
```

---

## 📚 Documentación Completa

Para más detalles, consulta: **[AUTHENTICATION_GUIDE.md](./AUTHENTICATION_GUIDE.md)**

---

## 🔧 Comandos Útiles

```bash
# Cambiar contraseña de admin
php artisan admin:change-password

# Limpiar caches
php artisan config:clear
php artisan route:clear

# Ver todas las rutas
php artisan route:list

# Iniciar servidor
php artisan serve
```

---

**¿Necesitas ayuda?** Consulta la documentación completa o revisa los logs en `storage/logs/laravel.log`
