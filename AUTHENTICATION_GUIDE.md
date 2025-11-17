# 🔐 Guía de Autenticación - Sistema de Exámenes de Ascenso

## 📋 Tabla de Contenidos
1. [Credenciales por Defecto](#credenciales-por-defecto)
2. [Cambiar Contraseña del Administrador](#cambiar-contraseña-del-administrador)
3. [Login Manual (Email/Contraseña)](#login-manual-emailcontraseña)
4. [Login con Google OAuth](#login-con-google-oauth)
5. [Login con Microsoft OAuth](#login-con-microsoft-oauth)
6. [Rutas API Protegidas](#rutas-api-protegidas)
7. [Tokens de Acceso](#tokens-de-acceso)

---

## 🔑 Credenciales por Defecto

Después de la instalación, existe un usuario administrador con las siguientes credenciales:

- **Email**: `luislachiraofi1@gmail.com`
- **Contraseña**: `password123`
- **Rol**: Administrador

⚠️ **IMPORTANTE**: Se recomienda cambiar esta contraseña inmediatamente después del primer inicio de sesión.

---

## 🔄 Cambiar Contraseña del Administrador

Ejecuta el siguiente comando Artisan para cambiar la contraseña de manera segura:

```bash
php artisan admin:change-password
```

O puedes especificar el email directamente:

```bash
php artisan admin:change-password luislachiraofi1@gmail.com
```

El comando te solicitará:
1. La nueva contraseña (mínimo 8 caracteres)
2. Confirmación de la contraseña

---

## 📧 Login Manual (Email/Contraseña)

### Endpoint
```
POST /api/v1/login
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Body
```json
{
  "correo": "usuario@ejemplo.com",
  "password": "tu_contraseña"
}
```

### Respuesta Exitosa (200)
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "usuario": {
    "idUsuario": 1,
    "dni": "12345678",
    "nombre": "Luis",
    "apellidos": "Lachira Nima",
    "correo": "luislachiraofi1@gmail.com",
    "rol": "0"
  }
}
```

### Ejemplo con cURL
```bash
curl -X POST http://examen_ascenso.test/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "correo": "luislachiraofi1@gmail.com",
    "password": "password123"
  }'
```

---

## 🔵 Login con Google OAuth

### 1. Configurar Credenciales de Google

#### a. Crear proyecto en Google Cloud Console
1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la API de Google+ (Google People API)

#### b. Crear credenciales OAuth 2.0
1. Ve a **APIs & Services** → **Credentials**
2. Click en **Create Credentials** → **OAuth 2.0 Client ID**
3. Selecciona **Web application**
4. Configura:
   - **Authorized JavaScript origins**: `http://examen_ascenso.test`
   - **Authorized redirect URIs**: `http://examen_ascenso.test/api/v1/oauth/callback/google`
5. Copia el **Client ID** y **Client Secret**

#### c. Agregar al archivo `.env`
```env
GOOGLE_CLIENT_ID=tu_client_id_aqui
GOOGLE_CLIENT_SECRET=tu_client_secret_aqui
GOOGLE_REDIRECT_URI=/api/v1/oauth/callback/google
```

### 2. Flujo de Autenticación

#### Paso 1: Usuario hace clic en el botón "Sign in with Google"
```javascript
// El frontend redirige al usuario a:
window.location.href = '/api/v1/oauth/redirect/google';
```

#### Paso 2: El backend redirige a Google
El usuario es redirigido a la pantalla de login de Google.

#### Paso 3: Google redirige de vuelta
Después de autenticarse, Google redirige a:
```
/api/v1/oauth/callback/google
```

#### Paso 4: El backend procesa y redirige al frontend
El backend crea/busca al usuario y redirige al frontend con:
```
/oauth/callback?token=JWT_TOKEN&user=USER_JSON
```

#### Paso 5: El frontend procesa el token
La página `/oauth/callback` guarda el token y redirige al dashboard.

### 3. Notas Importantes
- Si es un usuario nuevo, se crea con **estado "Pendiente"** (debe ser aprobado por un admin)
- Si el usuario ya existe, se valida que esté **activo** antes de permitir el acceso
- Se genera un **DNI temporal** (`TEMP######`) que el usuario debe actualizar más tarde

---

## 🟦 Login con Microsoft OAuth

### 1. Configurar Credenciales de Microsoft

#### a. Registrar aplicación en Azure AD
1. Ve a [Azure Portal](https://portal.azure.com/)
2. Ve a **Azure Active Directory** → **App registrations**
3. Click en **New registration**
4. Configura:
   - **Name**: Sistema de Exámenes de Ascenso
   - **Supported account types**: Accounts in any organizational directory and personal Microsoft accounts
   - **Redirect URI**: `http://examen_ascenso.test/api/v1/oauth/callback/microsoft`

#### b. Crear Client Secret
1. En tu aplicación registrada, ve a **Certificates & secrets**
2. Click en **New client secret**
3. Copia el **Value** (solo se muestra una vez)

#### c. Configurar permisos
1. Ve a **API permissions**
2. Agrega los siguientes permisos de **Microsoft Graph**:
   - `User.Read` (Delegated)
   - `email` (Delegated)
   - `profile` (Delegated)

#### d. Agregar al archivo `.env`
```env
MICROSOFT_CLIENT_ID=tu_application_id_aqui
MICROSOFT_CLIENT_SECRET=tu_client_secret_aqui
MICROSOFT_REDIRECT_URI=/api/v1/oauth/callback/microsoft
MICROSOFT_TENANT=common
```

### 2. Flujo de Autenticación

Similar al flujo de Google, pero con Microsoft:

1. Usuario hace clic en "Sign in with Microsoft"
2. Redirige a `/api/v1/oauth/redirect/microsoft`
3. Microsoft autentica al usuario
4. Redirige a `/api/v1/oauth/callback/microsoft`
5. Backend procesa y redirige al frontend con el token

---

## 🔒 Rutas API Protegidas

Todas las rutas bajo `/api/v1` que requieren autenticación están protegidas con el middleware `auth:api`.

### Usar el Token de Acceso

Agrega el token en el header de tus peticiones:

```
Authorization: Bearer {tu_token_aqui}
```

### Ejemplo con cURL
```bash
curl -X GET http://examen_ascenso.test/api/v1/admin/usuarios \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

### Rutas por Rol

#### 🔴 Administrador (rol='0')
```
GET    /api/v1/admin/usuarios          # Listar usuarios
GET    /api/v1/admin/usuarios/{id}     # Ver usuario
PATCH  /api/v1/admin/usuarios/{id}     # Actualizar usuario
DELETE /api/v1/admin/usuarios/{id}     # Eliminar usuario
PATCH  /api/v1/admin/usuarios/{id}/approve  # Aprobar usuario
PATCH  /api/v1/admin/usuarios/{id}/suspend  # Suspender usuario

# Categorías, Preguntas y Exámenes (CRUD completo)
/api/v1/admin/categorias
/api/v1/admin/preguntas
/api/v1/admin/examenes
```

#### 🟢 Docente (rol='1')
```
GET    /api/v1/docente/examenes         # Listar exámenes disponibles
GET    /api/v1/docente/examenes/{id}    # Ver detalles del examen
```

### Middleware de Roles

El sistema usa el middleware `role:{rol}` para proteger rutas:

```php
// Solo administradores
Route::middleware('role:0')->group(function () {
    // rutas de admin
});

// Solo docentes
Route::middleware('role:1')->group(function () {
    // rutas de docentes
});
```

---

## 🎫 Tokens de Acceso

### Características
- **Tipo**: JWT (JSON Web Token) con Passport
- **Método**: Personal Access Token
- **Duración**: 8 horas (configurable en `AuthServiceProvider`)
- **Refresh Token**: 30 días

### Logout
```
POST /api/v1/logout
```

Headers:
```
Authorization: Bearer {tu_token}
```

Esto revoca el token actual.

---

## 🧪 Pruebas

### Probar Login Manual
```bash
# PowerShell
$headers = @{'Accept'='application/json'; 'Content-Type'='application/json'}
$body = @{correo='luislachiraofi1@gmail.com'; password='password123'} | ConvertTo-Json
$response = Invoke-WebRequest -Uri 'http://examen_ascenso.test/api/v1/login' -Method POST -Body $body -Headers $headers
$response.Content
```

### Probar Ruta Protegida
```bash
# Reemplaza {TOKEN} con tu token
curl -X GET http://examen_ascenso.test/api/v1/admin/usuarios \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## ❓ Preguntas Frecuentes

### ¿Por qué un usuario OAuth tiene estado "Pendiente"?
Los usuarios que se registran vía OAuth (Google/Microsoft) se crean automáticamente pero deben ser **aprobados por un administrador** antes de poder acceder al sistema completo. Esto es una medida de seguridad.

### ¿Cómo aprobar un usuario?
Como administrador:
```bash
PATCH /api/v1/admin/usuarios/{id}/approve
```

### ¿Qué es el DNI temporal?
Cuando un usuario se registra via OAuth, el sistema genera un DNI temporal con formato `TEMP######`. El usuario debe actualizar este DNI desde su perfil.

### ¿Puedo usar ambos métodos de login?
Sí, un usuario puede:
1. Registrarse manualmente y usar email/contraseña
2. Luego usar OAuth para iniciar sesión más rápidamente

Siempre que el correo electrónico coincida, el sistema usará la misma cuenta.

---

## 📝 Notas de Seguridad

1. **Nunca expongas los Client Secrets** en el código frontend
2. **Usa HTTPS en producción** para proteger los tokens
3. **Cambia las contraseñas por defecto** inmediatamente
4. **Configura CORS** correctamente para tu dominio en producción
5. **Revisa los permisos OAuth** periódicamente

---

## 🐛 Solución de Problemas

### Error: "Proveedor OAuth no soportado"
Verifica que estás usando `google` o `microsoft` como provider en la URL.

### Error: "Error al iniciar autenticación OAuth"
Verifica que las credenciales en `.env` sean correctas y que hayas ejecutado:
```bash
php artisan config:clear
```

### Error: "Su cuenta está pendiente de aprobación"
El usuario necesita ser aprobado por un administrador.

---

## 📚 Recursos Adicionales

- [Laravel Passport Documentation](https://laravel.com/docs/passport)
- [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
- [Google OAuth 2.0 Setup](https://developers.google.com/identity/protocols/oauth2)
- [Microsoft Identity Platform](https://docs.microsoft.com/en-us/azure/active-directory/develop/)

---

**Desarrollado para I.E. Leonor Cerna de Valdiviezo** 🎓
