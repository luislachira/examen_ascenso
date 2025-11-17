# Mensaje de Cuenta Pendiente para OAuth

## 📋 Resumen

Se ha implementado un sistema consistente de notificación para usuarios que se registran mediante OAuth (Google/Microsoft), mostrando el mismo mensaje de "espera de aprobación del administrador" que se muestra en el registro manual.

## 🎯 Objetivo

Cuando un usuario se registra usando Google o Microsoft OAuth, el sistema debe:
1. Crear la cuenta con estado `PENDIENTE`
2. Mostrar una ventana informativa con el mensaje de espera
3. Permitir al usuario volver al login fácilmente

## ✅ Cambios Implementados

### 1. Backend - OAuthController.php

**Archivo:** `app/Http/Controllers/Api/V1/OAuthController.php`

**Modificación en el método `callback()`:**

```php
// Verificar el estado de la cuenta
if ($usuario->estado !== Usuario::ESTADO_ACTIVO) {
    $status = $usuario->estado === Usuario::ESTADO_PENDIENTE 
        ? 'pendiente de aprobación' 
        : 'suspendida';
    
    // Si es una cuenta pendiente, redirigir con parámetro especial
    if ($usuario->estado === Usuario::ESTADO_PENDIENTE) {
        return redirect(config('app.frontend_url', config('app.url')) 
            . '/oauth/callback?pending=true&email=' . urlencode($usuario->correo));
    }
    
    // Para otros estados (suspendida), redirigir con error
    return redirect(config('app.frontend_url', config('app.url')) 
        . '/login?error=' . urlencode("Su cuenta está {$status}."));
}
```

**Cambios:**
- ✅ Detecta cuando una cuenta está en estado `PENDIENTE`
- ✅ Redirige al callback con parámetro `pending=true`
- ✅ Incluye el email del usuario para referencia
- ✅ Mantiene el comportamiento para cuentas suspendidas

---

### 2. Frontend - oauth-success.tsx

**Archivo:** `resources/js/pages/auth/oauth-success.tsx`

**Nuevas importaciones:**
```typescript
import '@css/Login.css';
import logo from '@/assets/logo_leonor_cerna 2.png';
```

**Nuevo estado:**
```typescript
const [isPending, setIsPending] = useState(false);
```

**Nuevos parámetros de URL:**
```typescript
const pending = searchParams.get('pending');
const email = searchParams.get('email');
```

**Nueva lógica de verificación:**
```typescript
// Verificar si la cuenta está pendiente de aprobación
if (pending === 'true') {
    console.log('⏳ Cuenta pendiente de aprobación');
    setIsPending(true);
    return;
}
```

**Nueva UI para cuenta pendiente:**
```typescript
if (isPending) {
    return (
        <div className="login-container">
            <div className="login-box" style={{ textAlign: 'center' }}>
                <div className="login-header">
                    <img src={logo} alt="Logo I.E. Leonor Cerna de Valdiviezo" />
                    <h2>I.E. LEONOR CERNA DE VALDIVIEZO</h2>
                </div>
                <div style={{ 
                    padding: '2rem 1rem',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '1.5rem',
                    alignItems: 'center'
                }}>
                    <div style={{ fontSize: '3rem', color: '#f7b731' }}>⏳</div>
                    <h3>Registro Exitoso</h3>
                    <p>Tu cuenta ha sido creada correctamente.</p>
                    <p style={{ fontWeight: '500' }}>
                        Por favor, espera hasta que el administrador acepte tu solicitud.
                    </p>
                    <button 
                        onClick={() => navigate('/login')}
                        className="btn btn-primary"
                        style={{ marginTop: '1rem', maxWidth: '250px' }}
                    >
                        Volver al Login
                    </button>
                </div>
            </div>
        </div>
    );
}
```

---

### 3. Frontend - register.tsx (Mejora de consistencia)

**Archivo:** `resources/js/pages/auth/register.tsx`

**Cambios realizados:**
- ✅ Eliminado el `alert()` básico de JavaScript
- ✅ Implementada la misma UI elegante que OAuth
- ✅ Mismo diseño, mismos colores, misma experiencia
- ✅ Estado `showSuccessMessage` para controlar la visualización
- ✅ Navegación con React Router en lugar de `window.location.href`

**Nuevo flujo:**
```typescript
try {
    await register(formData);
    setShowSuccessMessage(true); // Muestra el mensaje elegante
} catch (err) {
    // Manejo de errores
}
```

---

## 🎨 Diseño de la Interfaz

### Características del mensaje:

1. **Icono visual:** ⏳ (emoji de reloj de arena)
   - Color: `#f7b731` (amarillo institucional)
   - Tamaño: `3rem`

2. **Estructura:**
   - Logo de la institución en la parte superior
   - Nombre de la institución
   - Icono de reloj
   - Título: "Registro Exitoso"
   - Mensaje 1: "Tu cuenta ha sido creada correctamente."
   - Mensaje 2: "Por favor, espera hasta que el administrador acepte tu solicitud."
   - Botón: "Volver al Login"

3. **Estilo:**
   - Fondo: Usa la clase `login-container` (con imagen de fondo)
   - Caja: Usa la clase `login-box` (fondo blanco, centrado)
   - Tipografía clara y legible
   - Espaciado generoso (`gap: 1.5rem`)
   - Botón con estilo institucional

---

## 🔄 Flujo de Usuario

### Registro con OAuth (Google/Microsoft)

1. Usuario hace clic en "Sign in with Google/Microsoft"
2. Es redirigido al proveedor OAuth
3. Se autentica con su cuenta
4. OAuth redirige de vuelta al backend (`/api/v1/oauth/callback/{provider}`)
5. Backend crea/busca el usuario:
   - Si es nuevo: estado = `PENDIENTE`
   - Si existe: mantiene su estado actual
6. Si estado = `PENDIENTE`:
   - Backend redirige a: `/oauth/callback?pending=true&email=...`
   - Frontend muestra mensaje de espera
7. Usuario hace clic en "Volver al Login"
8. Es redirigido a `/login`

### Registro Manual

1. Usuario completa el formulario de registro
2. Hace clic en "Registrar"
3. Backend crea usuario con estado `PENDIENTE`
4. Frontend muestra el mismo mensaje de espera
5. Usuario hace clic en "Volver al Login"
6. Es redirigido a `/login`

---

## 🔐 Seguridad y Estados

### Estados de Usuario

El modelo `Usuario` tiene tres estados posibles:

```php
const ESTADO_PENDIENTE = '2';  // Esperando aprobación
const ESTADO_ACTIVO = '1';     // Puede iniciar sesión
const ESTADO_SUSPENDIDO = '0'; // Cuenta bloqueada
```

### Comportamiento por Estado

| Estado | OAuth Login | Manual Login | Mostrar Mensaje |
|--------|-------------|--------------|-----------------|
| `PENDIENTE` | ❌ Bloqueado | ❌ Bloqueado | ✅ "Espera aprobación" |
| `ACTIVO` | ✅ Permitido | ✅ Permitido | ❌ Login normal |
| `SUSPENDIDO` | ❌ Bloqueado | ❌ Bloqueado | ⚠️ "Cuenta suspendida" |

---

## 🧪 Pruebas Recomendadas

### Escenario 1: Nuevo usuario con Google
1. Registrarse con una cuenta de Google nueva
2. Verificar que se muestra el mensaje de espera
3. Verificar que el usuario se crea con estado `PENDIENTE` en la BD
4. Intentar iniciar sesión → debe mostrar error
5. Administrador aprueba la cuenta
6. Iniciar sesión → debe funcionar correctamente

### Escenario 2: Nuevo usuario con Microsoft
1. Mismo flujo que Escenario 1 pero con Microsoft

### Escenario 3: Usuario existente pendiente
1. Crear usuario manualmente (queda pendiente)
2. Intentar registrar con OAuth usando el mismo correo
3. Debe reconocer al usuario existente
4. Debe mostrar mensaje de espera

### Escenario 4: Usuario activo existente
1. Tener un usuario ya aprobado (estado ACTIVO)
2. Iniciar sesión con OAuth usando ese correo
3. Debe iniciar sesión normalmente sin mensaje de espera

---

## 📝 Notas Técnicas

### Parámetros de URL

El sistema utiliza query parameters para comunicar estados:

- `?pending=true` - Indica cuenta pendiente de aprobación
- `?email=xxx` - Email del usuario (para logging/debug)
- `?error=xxx` - Mensaje de error a mostrar
- `?token=xxx` - Token de autenticación (login exitoso)
- `?user=xxx` - Datos del usuario (login exitoso)

### Logging

El componente `oauth-success.tsx` incluye logs detallados:

```typescript
console.log('🔵 OAuth Callback recibido');
console.log('Token:', token ? 'Presente' : 'Ausente');
console.log('User:', userJson ? 'Presente' : 'Ausente');
console.log('Error:', errorMsg);
console.log('Pending:', pending);
```

Esto facilita el debugging en desarrollo.

---

## 🎯 Beneficios

1. **Consistencia:** Misma experiencia para OAuth y registro manual
2. **Claridad:** Usuario sabe exactamente qué esperar
3. **Profesionalismo:** UI elegante y pulida
4. **UX mejorada:** No más alerts nativos del navegador
5. **Mantenibilidad:** Código limpio y bien estructurado
6. **Seguridad:** Estados claramente definidos y validados

---

## 📚 Archivos Modificados

1. ✅ `app/Http/Controllers/Api/V1/OAuthController.php`
2. ✅ `resources/js/pages/auth/oauth-success.tsx`
3. ✅ `resources/js/pages/auth/register.tsx`
4. ✅ `resources/css/Login.css` (imagen de fondo actualizada)

---

## 🚀 Próximos Pasos

- [ ] Agregar credenciales OAuth de Microsoft en `.env`
- [ ] Probar flujo completo con ambos proveedores
- [ ] Verificar que emails de notificación al admin funcionan (si aplica)
- [ ] Documentar proceso de aprobación de usuarios para administradores
- [ ] Considerar agregar notificaciones push o emails al usuario cuando sea aprobado

---

**Fecha de implementación:** 2025-10-10
**Versión:** 1.0
**Estado:** ✅ Completado y listo para pruebas
