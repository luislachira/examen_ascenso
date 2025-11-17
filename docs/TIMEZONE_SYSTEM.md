# Sistema de Zona Horaria - Documentación

## 📋 Resumen

Este sistema maneja todas las fechas y horas usando **una sola zona horaria**: `America/Lima` (configurada en `config/app.php`).

**IMPORTANTE**: Todas las comparaciones de fechas se hacen en el **servidor** usando la zona horaria del servidor, **NO** la del navegador del usuario. Esto garantiza consistencia incluso con 50+ usuarios simultáneos desde diferentes ubicaciones.

## 🎯 Principios Fundamentales

### 1. **Una sola fuente de verdad: el servidor**
- Todas las fechas se comparan en el servidor usando `America/Lima`
- El frontend NO debe hacer comparaciones de fechas críticas
- El servidor siempre tiene la última palabra sobre si un examen está disponible

### 2. **Almacenamiento en Base de Datos**
- Las fechas se almacenan como strings en formato `'Y-m-d H:i:s'`
- Estas fechas representan la hora local de `America/Lima`
- **NO se usa UTC** para evitar confusiones y mantener simplicidad

### 3. **Comparaciones de Fechas**
- Todas las comparaciones se hacen como **strings** usando `strcmp()`
- Esto evita problemas de conversión de zona horaria
- Formato: `'Y-m-d H:i:s'` (ejemplo: `'2025-11-11 15:30:00'`)

## 🔧 Cómo Funciona

### Backend (PHP/Laravel)

#### 1. Obtener la hora actual
```php
use App\Services\FechaService;

// ✅ CORRECTO: Usar el servicio centralizado
$ahora = FechaService::ahora(); // Carbon en America/Lima
$ahoraStr = FechaService::ahoraString(); // '2025-11-11 15:30:00'
```

#### 2. Comparar fechas
```php
// ✅ CORRECTO: Comparar como strings
$fechaBD = '2025-11-11 15:30:00'; // De la base de datos
$ahora = FechaService::ahoraString(); // '2025-11-11 15:30:00'

if (strcmp($ahora, $fechaBD) >= 0) {
    // La fecha ya pasó
}
```

#### 3. Guardar fechas
```php
// ✅ CORRECTO: Formatear antes de guardar
$fecha = Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s');
$examen->fecha_inicio_vigencia = $fecha;
```

### Frontend (React/TypeScript)

#### ⚠️ IMPORTANTE: El frontend NO debe hacer comparaciones críticas

El frontend puede mostrar fechas formateadas, pero **NO debe decidir** si un examen está disponible basándose en la hora del navegador del usuario.

```typescript
// ❌ INCORRECTO: Usar la hora del navegador para validar
const ahora = new Date();
if (ahora > fechaExamen) { ... }

// ✅ CORRECTO: El servidor decide si está disponible
// El frontend solo muestra información, el servidor valida
```

## 📍 Flujo de Validación

### Cuando un usuario intenta iniciar un examen:

1. **Frontend**: Envía request al servidor
2. **Backend**: 
   - Obtiene la hora actual del servidor (`America/Lima`)
   - Compara con `fecha_inicio_vigencia` y `fecha_fin_vigencia`
   - Decide si el examen está disponible
3. **Backend**: Responde con el resultado
4. **Frontend**: Muestra el resultado al usuario

### Cuando se actualizan estados automáticamente:

1. **Middleware**: Se ejecuta en cada request
2. **Middleware**: Verifica si ya se ejecutó en el último minuto (usando cache)
3. **Modelo Examen**: Ejecuta `actualizarEstadosAutomaticamente()`
4. **Modelo Examen**: Compara fechas usando la hora del servidor
5. **Modelo Examen**: Actualiza estados si es necesario

## 🛡️ Garantías del Sistema

### Para 50+ usuarios simultáneos:

1. **Consistencia**: Todos los usuarios ven el mismo estado del examen
2. **Precisión**: La hora del servidor es la única fuente de verdad
3. **Sin conflictos**: No importa la zona horaria del navegador del usuario
4. **Actualización automática**: Los estados se actualizan cada minuto automáticamente

## 🔍 Debugging

### Verificar la configuración actual:

```php
use App\Services\FechaService;

$info = FechaService::getInfoTimezone();
/*
Array:
[
    'timezone' => 'America/Lima',
    'hora_actual' => '2025-11-11 15:30:00',
    'timestamp' => 1733935800,
    'offset' => '-05:00',
    'timezone_abbr' => 'PET'
]
*/
```

### Logs importantes:

El sistema registra información de zona horaria en los logs cuando:
- Se actualizan estados de exámenes
- Un usuario intenta iniciar un examen
- Hay comparaciones de fechas críticas

## ⚙️ Configuración

### Archivo: `config/app.php`

```php
'timezone' => 'America/Lima',
```

**NO cambiar** esta configuración sin actualizar:
1. Todas las fechas existentes en la base de datos
2. El código que compara fechas
3. La documentación

## 📝 Ejemplos de Uso

### Ejemplo 1: Verificar si un examen está disponible

```php
use App\Services\FechaService;

$examen = Examen::find($id);
$fechaInicio = $examen->getRawOriginal('fecha_inicio_vigencia');
$fechaFin = $examen->getRawOriginal('fecha_fin_vigencia');

// Verificar si está en rango
if (FechaService::estaEnRango($fechaInicio, $fechaFin)) {
    // El examen está disponible
}
```

### Ejemplo 2: Actualizar estado de un examen

```php
use App\Services\FechaService;

$examen = Examen::find($id);
$fechaFin = $examen->getRawOriginal('fecha_fin_vigencia');

// Verificar si ya pasó la fecha de fin
if (FechaService::yaPaso($fechaFin)) {
    $examen->estado = '2'; // Finalizado
    $examen->save();
}
```

## 🚨 Problemas Comunes y Soluciones

### Problema 1: "El examen aparece disponible pero el servidor dice que no"

**Causa**: El frontend está usando la hora del navegador para validar.

**Solución**: El frontend NO debe validar. Solo el servidor decide.

### Problema 2: "Los estados no se actualizan automáticamente"

**Causa**: El middleware no se está ejecutando o hay un problema con el cache.

**Solución**: 
1. Verificar que el middleware esté registrado en `bootstrap/app.php`
2. Verificar los logs de Laravel
3. Ejecutar manualmente: `php artisan examenes:actualizar-estados`

### Problema 3: "Las fechas se guardan incorrectamente"

**Causa**: No se está usando `config('app.timezone')` al crear fechas.

**Solución**: Siempre usar `FechaService` o `Carbon::now(config('app.timezone'))`.

## 📚 Referencias

- [Carbon Documentation](https://carbon.nesbot.com/docs/)
- [PHP DateTimeZone](https://www.php.net/manual/en/class.datetimezone.php)
- [Laravel Configuration](https://laravel.com/docs/configuration)

