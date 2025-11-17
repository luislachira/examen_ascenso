# 🔄 Eliminación de la Columna DNI - Resumen de Cambios

**Fecha**: 10 de Octubre de 2025

---

## 📋 Resumen

Se ha eliminado la columna `dni` de la tabla `usuarios` y todas sus referencias en el código.

---

## 🗄️ Cambios en Base de Datos

### Nueva Migración Creada

**Archivo**: `database/migrations/2025_10_10_232233_remove_dni_from_usuarios_table.php`

```php
public function up(): void
{
    Schema::table('usuarios', function (Blueprint $table) {
        // Eliminar el índice único antes de eliminar la columna
        $table->dropUnique(['dni']);
        // Eliminar la columna dni
        $table->dropColumn('dni');
    });
}

public function down(): void
{
    Schema::table('usuarios', function (Blueprint $table) {
        // Restaurar la columna dni si se hace rollback
        $table->string('dni', 8)->unique()->after('idUsuario');
    });
}
```

### Para Aplicar los Cambios

```bash
# Ejecutar la migración
php artisan migrate

# Si necesitas revertir (restaurar DNI)
php artisan migrate:rollback --step=1
```

---

## 📁 Archivos Modificados

### 1. **Modelo Usuario** ✅
**Archivo**: `app/Models/Usuario.php`

**Cambios**:
- Eliminado `'dni'` del array `$fillable`

**Antes**:
```php
protected $fillable = [
    'dni',
    'nombre',
    'apellidos',
    // ...
];
```

**Después**:
```php
protected $fillable = [
    'nombre',
    'apellidos',
    // ...
];
```

---

### 2. **AuthController** ✅
**Archivo**: `app/Http/Controllers/Api/V1/AuthController.php`

**Cambios**:
- Eliminado `'dni' => $usuario->dni` de la respuesta del login

**Antes**:
```php
'usuario' => [
    'idUsuario' => $usuario->idUsuario,
    'dni' => $usuario->dni,
    'nombre' => $usuario->nombre,
    // ...
]
```

**Después**:
```php
'usuario' => [
    'idUsuario' => $usuario->idUsuario,
    'nombre' => $usuario->nombre,
    // ...
]
```

---

### 3. **OAuthController** ✅
**Archivo**: `app/Http/Controllers/Api/V1/OAuthController.php`

**Cambios**:
- Eliminado `'dni' => $usuario->dni` de la respuesta OAuth
- Eliminado `'dni' => $this->generateTemporaryDni()` al crear usuario
- Eliminado método completo `generateTemporaryDni()`

**Ya no se genera DNI temporal para usuarios OAuth**

---

### 4. **RegisterRequest** ✅
**Archivo**: `app/Http/Requests/Auth/RegisterRequest.php`

**Cambios**:
- Eliminada validación de `dni`
- Eliminados mensajes de error de `dni`

**Antes**:
```php
return [
    'dni' => 'required|digits:8|unique:usuarios,dni',
    'nombre' => 'required|string|max:200',
    // ...
];
```

**Después**:
```php
return [
    'nombre' => 'required|string|max:200',
    // ...
];
```

---

### 5. **Seeder de Admin** ✅
**Archivo**: `database/migrations/2025_10_01_180000_seed_default_admin_user.php`

**Cambios**:
- Eliminado `'dni' => '54433321'` del insert

---

### 6. **Frontend - authStore.ts** ✅
**Archivo**: `resources/js/store/authStore.ts`

**Cambios**:
- Eliminado `dni: string` de interface `UsuarioDTO`
- Eliminado `dni: string` de interface `RegisterData`

**Antes**:
```typescript
export interface UsuarioDTO {
    idUsuario: number;
    dni: string;
    nombre: string;
    // ...
}
```

**Después**:
```typescript
export interface UsuarioDTO {
    idUsuario: number;
    nombre: string;
    // ...
}
```

---

## ⚠️ IMPORTANTE - Antes de Ejecutar la Migración

### Verificar Datos Existentes

Si ya tienes usuarios en la base de datos, verifica si necesitas respaldar la información del DNI:

```bash
# Ver usuarios con DNI
php artisan tinker
>>> \App\Models\Usuario::select('idUsuario', 'dni', 'correo')->get()
```

### Backup Recomendado

```bash
# Hacer backup de la base de datos antes de migrar
mysqldump -u root -p examen_ascenso > backup_antes_de_eliminar_dni.sql
```

---

## 🧪 Pruebas Después de la Migración

### 1. Verificar Modelo
```bash
php artisan tinker
>>> $user = \App\Models\Usuario::first()
>>> $user->toArray()
# No debe incluir 'dni'
```

### 2. Probar Login
```bash
# Login manual debe funcionar sin dni
POST http://examen_ascenso.test/api/v1/login
{
    "correo": "luislachiraofi1@gmail.com",
    "password": "password123"
}

# Respuesta debe ser sin dni:
{
    "access_token": "...",
    "usuario": {
        "idUsuario": 1,
        "nombre": "Luis",
        "apellidos": "Lachira Nima",
        "correo": "luislachiraofi1@gmail.com",
        "rol": "0"
    }
}
```

### 3. Probar Registro
```bash
POST http://examen_ascenso.test/api/v1/register
{
    "nombre": "Juan",
    "apellidos": "Pérez",
    "correo": "juan@ejemplo.com",
    "password": "password123",
    "password_confirmation": "password123"
}
# Debe funcionar sin enviar dni
```

### 4. Probar OAuth
```bash
# Login con Google debe funcionar sin generar DNI temporal
GET http://examen_ascenso.test/api/v1/oauth/redirect/google
```

---

## 📊 Impacto de los Cambios

### ✅ Beneficios
1. **Simplificación**: Ya no se requiere DNI para registrarse
2. **OAuth mejorado**: No se genera DNI temporal para usuarios OAuth
3. **Menos validaciones**: Formularios más simples
4. **Base de datos**: Columna innecesaria eliminada

### ⚠️ Consideraciones
1. **Datos históricos**: Si tenías DNI en producción, se perderán al migrar
2. **Frontend**: Asegúrate de actualizar formularios que soliciten DNI
3. **Reportes**: Si generabas reportes con DNI, necesitas actualizarlos

---

## 🔄 Rollback (Restaurar DNI)

Si necesitas restaurar la columna DNI:

```bash
# Revertir la migración
php artisan migrate:rollback --step=1

# Esto restaurará la columna dni en la tabla usuarios
```

**NOTA**: Los datos de DNI se perderán, tendrás que volver a ingresarlos manualmente.

---

## 📝 Archivos Afectados - Checklist

- [x] `database/migrations/2025_10_10_232233_remove_dni_from_usuarios_table.php` - NUEVA
- [x] `app/Models/Usuario.php` - MODIFICADO
- [x] `app/Http/Controllers/Api/V1/AuthController.php` - MODIFICADO
- [x] `app/Http/Controllers/Api/V1/OAuthController.php` - MODIFICADO
- [x] `app/Http/Requests/Auth/RegisterRequest.php` - MODIFICADO
- [x] `database/migrations/2025_10_01_180000_seed_default_admin_user.php` - MODIFICADO
- [x] `resources/js/store/authStore.ts` - MODIFICADO

---

## 🚀 Próximos Pasos

### 1. Ejecutar la Migración
```bash
php artisan migrate
```

### 2. Limpiar Caches
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 3. Compilar Frontend (si es necesario)
```bash
npm run build
```

### 4. Probar Todo el Flujo
- [ ] Login manual
- [ ] Registro de nuevos usuarios
- [ ] OAuth con Google
- [ ] OAuth con Microsoft
- [ ] Respuestas de API

---

## ❓ Preguntas Frecuentes

### ¿Por qué eliminar el DNI?
El DNI es un dato sensible y personal que puede no ser necesario para todos los casos de uso. Además, complica el registro y OAuth.

### ¿Qué pasa con los usuarios existentes?
Los usuarios existentes mantendrán todos sus datos excepto el DNI, que se eliminará permanentemente.

### ¿Puedo agregar DNI opcional más adelante?
Sí, puedes crear una nueva migración para agregar DNI como columna nullable:
```php
$table->string('dni', 8)->nullable();
```

---

**Desarrollado para**: I.E. Leonor Cerna de Valdiviezo  
**Fecha**: 10 de Octubre de 2025
