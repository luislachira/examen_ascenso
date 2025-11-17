# Resumen: Mensaje de Cuenta Pendiente OAuth

## ¿Qué se implementó?

Se agregó un mensaje elegante y consistente que se muestra cuando un usuario se registra con Google o Microsoft OAuth, indicándole que debe esperar la aprobación del administrador.

## Cambios realizados

### 1. **Backend** (`OAuthController.php`)
- Detecta cuando un usuario registrado por OAuth tiene estado `PENDIENTE`
- Redirige a `/oauth/callback?pending=true` en lugar de bloquear con error
- Mantiene seguridad: usuarios pendientes NO pueden iniciar sesión

### 2. **Frontend** (`oauth-success.tsx`)
- Detecta el parámetro `?pending=true` en la URL
- Muestra ventana elegante con mensaje de espera
- Mismo diseño que el resto del sistema de login

### 3. **Mejora** (`register.tsx`)
- Actualizado para mostrar el MISMO mensaje que OAuth
- Eliminado el `alert()` básico de JavaScript
- UI consistente y profesional

## Resultado Visual

```
┌─────────────────────────────────────────┐
│                                         │
│         [Logo Institución]              │
│    I.E. LEONOR CERNA DE VALDIVIEZO     │
│                                         │
│              ⏳ (3rem)                  │
│                                         │
│         Registro Exitoso                │
│                                         │
│  Tu cuenta ha sido creada correctamente.│
│                                         │
│  Por favor, espera hasta que el         │
│  administrador acepte tu solicitud.     │
│                                         │
│      [Botón: Volver al Login]          │
│                                         │
└─────────────────────────────────────────┘
```

## Flujo Completo

1. Usuario hace clic en "Sign in with Google/Microsoft"
2. Se autentica con su cuenta OAuth
3. Si es la primera vez:
   - Se crea cuenta con estado `PENDIENTE`
   - **SE MUESTRA MENSAJE DE ESPERA** ← NUEVO
4. Si ya existe pero está pendiente:
   - **SE MUESTRA MENSAJE DE ESPERA** ← NUEVO
5. Si ya existe y está activo:
   - Login normal (sin mensaje)

## Archivos Modificados

- ✅ `app/Http/Controllers/Api/V1/OAuthController.php`
- ✅ `resources/js/pages/auth/oauth-success.tsx`
- ✅ `resources/js/pages/auth/register.tsx`

## Estados de Usuario

| Estado | Puede Login | Mensaje |
|--------|-------------|---------|
| `PENDIENTE` (2) | ❌ | ✅ Mostrar ventana de espera |
| `ACTIVO` (1) | ✅ | ❌ Login normal |
| `SUSPENDIDO` (0) | ❌ | ⚠️ Error "Cuenta suspendida" |

## Código Clave

### Backend - Detectar pendiente
```php
if ($usuario->estado === Usuario::ESTADO_PENDIENTE) {
    return redirect(config('app.frontend_url') 
        . '/oauth/callback?pending=true&email=' . urlencode($usuario->correo));
}
```

### Frontend - Mostrar mensaje
```typescript
const pending = searchParams.get('pending');

if (pending === 'true') {
    setIsPending(true);
    return; // Muestra la ventana de espera
}
```

## Pruebas Rápidas

1. **Test OAuth nuevo usuario:**
   - Registrarse con Google
   - Debe mostrar mensaje de espera
   - Verificar BD: estado = '2'

2. **Test registro manual:**
   - Registrarse con formulario
   - Debe mostrar MISMO mensaje
   - Verificar BD: estado = '2'

3. **Test usuario activo:**
   - Login con cuenta aprobada
   - Debe entrar sin mensaje

## Notas Importantes

- ⚠️ Los usuarios con estado `PENDIENTE` NO pueden iniciar sesión
- ✅ El mensaje es el MISMO para OAuth y registro manual
- ✅ Elimina el uso de `alert()` básico de JavaScript
- ✅ UI profesional y consistente
- 🔒 Seguridad mantenida: validación en backend

## Documentación Completa

Ver: `OAUTH_PENDING_MESSAGE.md` para detalles técnicos completos.

---

**Estado:** ✅ Completado y funcional
**Fecha:** 2025-10-10
