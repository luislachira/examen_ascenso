#!/bin/bash

# Script para configurar permisos en servidor de producción
# Ejecutar como: sudo bash configurar-permisos.sh

echo "=== Configurando permisos para Laravel ==="

# Directorio base del proyecto
PROJECT_DIR=$(pwd)
WEB_USER="www-data"  # Cambiar según tu servidor (puede ser apache, nginx, etc.)

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto."
    exit 1
fi

echo "📁 Directorio del proyecto: $PROJECT_DIR"
echo "👤 Usuario web: $WEB_USER"

# Crear directorios si no existen
echo ""
echo "📂 Creando directorios necesarios..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Configurar permisos de directorios
echo ""
echo "🔐 Configurando permisos de directorios..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Configurar permisos de archivos
echo ""
echo "🔐 Configurando permisos de archivos..."
chmod 600 .env 2>/dev/null || echo "⚠️  .env no existe (normal si no está en el repo)"

# Cambiar propietario
echo ""
echo "👤 Cambiando propietario de archivos..."
chown -R $WEB_USER:$WEB_USER storage
chown -R $WEB_USER:$WEB_USER bootstrap/cache

# Verificar permisos
echo ""
echo "✅ Verificando permisos configurados..."
ls -la storage/ | head -5
ls -la bootstrap/cache/ | head -5

echo ""
echo "✅ Permisos configurados correctamente!"
echo ""
echo "📋 Resumen:"
echo "  - storage/: 775 (rwxrwxr-x)"
echo "  - bootstrap/cache/: 775 (rwxrwxr-x)"
echo "  - .env: 600 (rw-------)"
echo "  - Propietario: $WEB_USER:$WEB_USER"

