#!/bin/bash

##############################################################################
# Script de Verificación de Configuración de Backup
# Verifica que todos los requisitos están en su lugar
##############################################################################

echo ""
echo "=========================================="
echo "VERIFICADOR DE CONFIGURACIÓN DE BACKUP"
echo "=========================================="
echo ""

BASE_DIR="${1:-.}"
BACKUP_DIR="$BASE_DIR/backups"

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

ERRORES=0
ADVERTENCIAS=0

# Funciones de verificación
check_ok() {
    echo -e "${GREEN}✓${NC} $1"
}

check_error() {
    echo -e "${RED}✗${NC} $1"
    ((ERRORES++))
}

check_warn() {
    echo -e "${YELLOW}!${NC} $1"
    ((ADVERTENCIAS++))
}

check_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# ==================== VERIFICACIONES ====================

echo -e "${BLUE}1. Verificando archivos del proyecto...${NC}"
echo ""

# Verificar estructura de directorios
if [ -f "$BASE_DIR/config/db.php" ]; then
    check_ok "config/db.php existe"
else
    check_error "config/db.php NO existe"
fi

if [ -f "$BASE_DIR/tools/backup.php" ]; then
    check_ok "tools/backup.php existe"
else
    check_error "tools/backup.php NO existe"
fi

if [ -f "$BASE_DIR/tools/backup.sh" ]; then
    check_ok "tools/backup.sh existe"
else
    check_error "tools/backup.sh NO existe"
fi

if [ -f "$BASE_DIR/schema.sql" ]; then
    check_ok "schema.sql existe"
else
    check_error "schema.sql NO existe"
fi

echo ""
echo -e "${BLUE}2. Verificando directorio de backups...${NC}"
echo ""

if [ -d "$BACKUP_DIR" ]; then
    check_ok "Directorio $BACKUP_DIR existe"
    
    # Verificar permisos
    if [ -w "$BACKUP_DIR" ]; then
        check_ok "Directorio tiene permisos de escritura"
    else
        check_error "Directorio NO tiene permisos de escritura"
    fi
else
    check_warn "Directorio $BACKUP_DIR NO existe (se creará automáticamente)"
fi

echo ""
echo -e "${BLUE}3. Verificando dependencias del sistema...${NC}"
echo ""

# Verificar PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -1)
    check_ok "PHP está instalado: $PHP_VERSION"
else
    check_error "PHP NO está instalado"
fi

# Verificar MySQL
if command -v mysql &> /dev/null; then
    MYSQL_VERSION=$(mysql --version)
    check_ok "MySQL está instalado: $MYSQL_VERSION"
else
    check_error "MySQL client NO está instalado"
fi

# Verificar mysqldump
if command -v mysqldump &> /dev/null; then
    check_ok "mysqldump está disponible"
else
    check_warn "mysqldump NO disponible (se usará método PHP)"
fi

# Verificar zip
if command -v zip &> /dev/null; then
    check_ok "zip está instalado"
else
    check_error "zip NO está instalado - instala: sudo apt-get install zip"
fi

# Verificar extensiones PHP
echo ""
echo -e "${BLUE}4. Verificando extensiones PHP...${NC}"
echo ""

# ZipArchive
if php -m | grep -q Zip; then
    check_ok "Extensión Zip habilitada en PHP"
else
    check_warn "Extensión Zip NO habilitada en PHP"
fi

# PDO
if php -m | grep -q PDO; then
    check_ok "Extensión PDO habilitada en PHP"
else
    check_error "Extensión PDO NO habilitada - es necesaria"
fi

# FTP (opcional)
if php -m | grep -q ftp; then
    check_ok "Extensión FTP habilitada en PHP (opcional)"
else
    check_warn "Extensión FTP NO habilitada en PHP (solo si usarás FTP)"
fi

echo ""
echo -e "${BLUE}5. Verificando configuración de base de datos...${NC}"
echo ""

# Leer DB config
if [ -f "$BASE_DIR/config/db.php" ]; then
    DB_HOST=$(grep "\$DB_HOST" "$BASE_DIR/config/db.php" | grep -o "'[^']*'" | head -1 | tr -d "'")
    DB_NAME=$(grep "\$DB_NAME" "$BASE_DIR/config/db.php" | grep -o "'[^']*'" | head -1 | tr -d "'")
    DB_USER=$(grep "\$DB_USER" "$BASE_DIR/config/db.php" | grep -o "'[^']*'" | head -1 | tr -d "'")
    
    if [ -z "$DB_HOST" ]; then
        check_warn "DB_HOST no configurado (default: localhost)"
    else
        check_info "DB_HOST: $DB_HOST"
    fi
    
    if [ -z "$DB_NAME" ]; then
        check_error "DB_NAME es 'tu_base_de_datos' - debes editarla"
    else
        if [[ "$DB_NAME" == *"tu_base_de_datos"* ]]; then
            check_error "DB_NAME es 'tu_base_de_datos' - debes cambiarla por el nombre real"
        else
            check_ok "DB_NAME configurado: $DB_NAME"
        fi
    fi
    
    if [ -z "$DB_USER" ]; then
        check_error "DB_USER es 'tu_usuario' - debes editarlo"
    else
        if [[ "$DB_USER" == *"tu_usuario"* ]]; then
            check_error "DB_USER es 'tu_usuario' - debes cambiarla por el usuario real"
        else
            check_ok "DB_USER configurado: $DB_USER"
        fi
    fi
fi

echo ""
echo -e "${BLUE}6. Verificando configuración FTP en backup.php...${NC}"
echo ""

if [ -f "$BASE_DIR/tools/backup.php" ]; then
    FTP_ENABLED=$(grep "\$FTP_ENABLED" "$BASE_DIR/tools/backup.php" | grep -o "true\|false" | head -1)
    FTP_HOST=$(grep "\$FTP_HOST" "$BASE_DIR/tools/backup.php" | grep -o "'[^']*'" | head -1 | tr -d "'")
    
    if [ "$FTP_ENABLED" = "true" ]; then
        check_ok "FTP está HABILITADO"
        
        if [[ "$FTP_HOST" == *"your-ftp-host"* ]]; then
            check_error "FTP_HOST NO configurado - debes cambiar 'your-ftp-host.com'"
        else
            check_info "FTP_HOST: $FTP_HOST"
        fi
    else
        check_info "FTP está DESHABILITADO (está bien si no usas FTP)"
    fi
fi

echo ""
echo -e "${BLUE}7. Prueba de ejecución...${NC}"
echo ""

echo "Ejecutando: php $BASE_DIR/tools/backup.php"
echo ""

if [ -f "$BASE_DIR/tools/backup.php" ]; then
    php "$BASE_DIR/tools/backup.php" 2>&1 | head -20
    
    if [ ${PIPESTATUS[0]} -eq 0 ]; then
        check_ok "Script PHP ejecutado exitosamente"
    else
        check_error "Script PHP falló"
    fi
    
    # Verificar si se crearon los backups
    if [ -d "$BACKUP_DIR" ] && [ -f "$BACKUP_DIR/backup.log" ]; then
        check_ok "Log de backup creado"
        check_info "Últimas líneas del log:"
        tail -5 "$BACKUP_DIR/backup.log" | sed 's/^/    /'
    fi
else
    check_error "No se puede ejecutar backup.php"
fi

echo ""
echo "=========================================="
echo "RESUMEN"
echo "=========================================="
echo -e "Errores: ${RED}$ERRORES${NC}"
echo -e "Advertencias: ${YELLOW}$ADVERTENCIAS${NC}"
echo ""

if [ $ERRORES -eq 0 ]; then
    if [ $ADVERTENCIAS -eq 0 ]; then
        echo -e "${GREEN}✓ Todo está correctamente configurado${NC}"
        echo ""
        echo "Próximos pasos:"
        echo "1. Agregar al crontab:"
        echo "   crontab -e"
        echo "   0 2 * * * /usr/bin/php $BASE_DIR/tools/backup.php"
        echo ""
        echo "2. Verificar con:"
        echo "   crontab -l"
        exit 0
    else
        echo -e "${YELLOW}⚠ Hay advertencias pero el sistema puede funcionar${NC}"
        exit 0
    fi
else
    echo -e "${RED}✗ Hay errores que deben corregirse${NC}"
    echo ""
    echo "Por favor:"
    echo "1. Lee el archivo CONFIGURAR_BACKUP_CRON.md"
    echo "2. Corrige los errores indicados arriba"
    echo "3. Vuelve a ejecutar este script"
    exit 1
fi
