# Script para verificar y configurar permisos en Windows
# Ejecutar como: .\configurar-permisos.ps1

Write-Host "=== Configurando permisos para Laravel (Windows) ===" -ForegroundColor Cyan

$PROJECT_DIR = Get-Location
Write-Host "📁 Directorio del proyecto: $PROJECT_DIR" -ForegroundColor White

# Verificar que estamos en el directorio correcto
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto." -ForegroundColor Red
    exit 1
}

# Crear directorios si no existen
Write-Host ""
Write-Host "📂 Verificando/Creando directorios necesarios..." -ForegroundColor Yellow
$directories = @(
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "storage\app\public",
    "bootstrap\cache"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "  ✓ Creado: $dir" -ForegroundColor Green
    }
    else {
        Write-Host "  ✓ Existe: $dir" -ForegroundColor Green
    }
}

# Verificar permisos de escritura
Write-Host ""
Write-Host "🔐 Verificando permisos de escritura..." -ForegroundColor Yellow
$testDirs = @("storage", "bootstrap\cache")
$allOk = $true

foreach ($dir in $testDirs) {
    $testFile = Join-Path $dir "test_write_permission.tmp"
    "test" | Out-File -FilePath $testFile -ErrorAction SilentlyContinue
    if (Test-Path $testFile) {
        Remove-Item $testFile -ErrorAction SilentlyContinue
        Write-Host "  ✓ $dir - Permisos de escritura OK" -ForegroundColor Green
    }
    else {
        Write-Host "  ✗ $dir - SIN permisos de escritura" -ForegroundColor Red
        $allOk = $false
    }
}

# Verificar .env
Write-Host ""
Write-Host "🔐 Verificando archivo .env..." -ForegroundColor Yellow
if (Test-Path ".env") {
    $content = Get-Content ".env" -ErrorAction SilentlyContinue
    if ($null -ne $content) {
        Write-Host "  ✓ .env existe y es legible" -ForegroundColor Green
    }
    else {
        Write-Host "  ✗ .env existe pero NO es legible" -ForegroundColor Red
        $allOk = $false
    }
}
else {
    Write-Host "  ⚠ .env no existe (normal si no está en el repo)" -ForegroundColor Yellow
}

Write-Host ""
if ($allOk) {
    Write-Host "✅ Todos los permisos están correctos!" -ForegroundColor Green
}
else {
    Write-Host "⚠️  Algunos permisos necesitan atención" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📋 NOTA PARA PRODUCCIÓN (Linux):" -ForegroundColor Cyan
Write-Host "  Ejecutar: sudo bash configurar-permisos.sh" -ForegroundColor White
Write-Host "  O manualmente:" -ForegroundColor White
Write-Host "    chmod -R 775 storage bootstrap/cache" -ForegroundColor Gray
Write-Host "    chmod 600 .env" -ForegroundColor Gray
Write-Host "    chown -R www-data:www-data storage bootstrap/cache" -ForegroundColor Gray
