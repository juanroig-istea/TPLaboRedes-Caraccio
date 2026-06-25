#!/bin/bash

##############################################################################
# Script de Backup Automático (Bash)
# Alternativa más eficiente para sistemas Linux/Unix
# 
# Uso: ./backup.sh
# En cron: 0 2 * * * /var/www/html/club_jugadores_app/tools/backup.sh
##############################################################################

# ==================== CONFIGURACIÓN ====================

# Rutas
BASE_DIR="/var/www/html/club_jugadores_app"
BACKUP_DIR="$BASE_DIR/backups"
DB_CONFIG="$BASE_DIR/config/db.php"
LOG_FILE="$BACKUP_DIR/backup.log"

# Base de datos (cambiar con valores reales)
DB_HOST="localhost"
DB_USER="tu_usuario"
DB_PASS="tu_password"
DB_NAME="tu_base_de_datos"

# FTP Configuration
FTP_ENABLED=false                    # Cambiar a true si usas FTP
FTP_HOST="your-ftp-host.com"        # Servidor FTP
FTP_USER="tu_usuario_ftp"           # Usuario FTP
FTP_PASS="tu_password_ftp"          # Contraseña FTP
FTP_REMOTE_DIR="/backups"           # Directorio remoto en FTP
FTP_PORT=21

# Configuración de rotación
MAX_BACKUPS=5
FECHA=$(date +%Y-%m-%d_%H-%M-%S)
NOMBRE_BACKUP="backup_$FECHA"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ==================== FUNCIONES ====================

# Escribir en log
log() {
    local TIPO="$1"
    local MENSAJE="$2"
    local TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    
    echo "[$TIMESTAMP] [$TIPO] $MENSAJE" | tee -a "$LOG_FILE"
    
    case $TIPO in
        ERROR)
            echo -e "${RED}[$TIPO] $MENSAJE${NC}" >&2
            ;;
        WARN)
            echo -e "${YELLOW}[$TIPO] $MENSAJE${NC}" >&2
            ;;
        INFO)
            echo -e "${GREEN}[$TIPO] $MENSAJE${NC}"
            ;;
    esac
}

# Crear directorio de backups
crear_directorio() {
    if [ ! -d "$BACKUP_DIR" ]; then
        mkdir -p "$BACKUP_DIR"
        chmod 755 "$BACKUP_DIR"
        log "INFO" "Directorio de backups creado: $BACKUP_DIR"
    fi
}

# Verificar dependencias
verificar_dependencias() {
    log "INFO" "Verificando dependencias..."
    
    if ! command -v zip &> /dev/null; then
        log "ERROR" "zip no está instalado. Instala: sudo apt-get install zip"
        exit 1
    fi
    
    if ! command -v mysqldump &> /dev/null; then
        log "WARN" "mysqldump no disponible, usaremos copia de archivos SQL"
    fi
    
    log "INFO" "Dependencias verificadas"
}

# Formatear tamaño de archivo
formatear_tamaño() {
    local bytes=$1
    if (( bytes < 1024 )); then
        echo "${bytes}B"
    elif (( bytes < 1024*1024 )); then
        echo "$(( bytes / 1024 ))KB"
    elif (( bytes < 1024*1024*1024 )); then
        echo "$(( bytes / 1024 / 1024 ))MB"
    else
        echo "$(( bytes / 1024 / 1024 / 1024 ))GB"
    fi
}

# Backup de archivos
backup_archivos() {
    log "INFO" "Iniciando backup de archivos..."
    
    local ARCHIVO_ZIP="$BACKUP_DIR/${NOMBRE_BACKUP}_archivos.zip"
    
    # Excluir: backups, .git, node_modules, etc
    zip -r -q "$ARCHIVO_ZIP" "$BASE_DIR" \
        -x "$BACKUP_DIR/*" \
        "$BASE_DIR/.git/*" \
        "$BASE_DIR/.gitignore" \
        "$BASE_DIR/node_modules/*" \
        "$BASE_DIR/vendor/*" \
        "$BASE_DIR/.DS_Store"
    
    if [ $? -ne 0 ]; then
        log "ERROR" "Fallo al crear backup de archivos"
        return 1
    fi
    
    local TAMAÑO=$(stat -f%z "$ARCHIVO_ZIP" 2>/dev/null || stat -c%s "$ARCHIVO_ZIP" 2>/dev/null)
    log "INFO" "Backup de archivos completado: $(basename $ARCHIVO_ZIP) (Tamaño: $(formatear_tamaño $TAMAÑO))"
    
    echo "$ARCHIVO_ZIP"
}

# Backup de base de datos con mysqldump
backup_bd_mysqldump() {
    log "INFO" "Iniciando backup de base de datos (mysqldump)..."
    
    local ARCHIVO_SQL="$BACKUP_DIR/${NOMBRE_BACKUP}_database.sql"
    
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" \
        --single-transaction \
        --routines \
        --triggers \
        "$DB_NAME" > "$ARCHIVO_SQL" 2>/dev/null
    
    if [ $? -ne 0 ]; then
        log "ERROR" "Fallo al ejecutar mysqldump"
        return 1
    fi
    
    if [ ! -s "$ARCHIVO_SQL" ]; then
        log "ERROR" "Archivo SQL vacío"
        return 1
    fi
    
    local TAMAÑO=$(stat -f%z "$ARCHIVO_SQL" 2>/dev/null || stat -c%s "$ARCHIVO_SQL" 2>/dev/null)
    log "INFO" "Backup de BD completado: $(basename $ARCHIVO_SQL) (Tamaño: $(formatear_tamaño $TAMAÑO))"
    
    echo "$ARCHIVO_SQL"
}

# Crear ZIP completo con ambos backups
crear_zip_completo() {
    local ZIP_ARCHIVOS="$1"
    local SQL="$2"
    
    log "INFO" "Creando backup completo ZIP..."
    
    local ARCHIVO_FINAL="$BACKUP_DIR/${NOMBRE_BACKUP}_COMPLETO.zip"
    
    # Crear ZIP con ambos archivos
    zip -q "$ARCHIVO_FINAL" "$ZIP_ARCHIVOS" "$SQL"
    
    if [ $? -ne 0 ]; then
        log "ERROR" "Error al crear ZIP completo"
        return 1
    fi
    
    local TAMAÑO=$(stat -f%z "$ARCHIVO_FINAL" 2>/dev/null || stat -c%s "$ARCHIVO_FINAL" 2>/dev/null)
    log "INFO" "Backup completo creado: $(basename $ARCHIVO_FINAL) (Tamaño: $(formatear_tamaño $TAMAÑO))"
    
    # Eliminar archivos intermedios
    rm -f "$ZIP_ARCHIVOS" "$SQL"
    
    echo "$ARCHIVO_FINAL"
}

# Rotar backups
rotar_backups() {
    log "INFO" "Iniciando rotación de backups..."
    
    local BACKUPS=($(ls -t "$BACKUP_DIR"/backup_*_COMPLETO.zip 2>/dev/null))
    
    if [ ${#BACKUPS[@]} -gt $MAX_BACKUPS ]; then
        log "INFO" "Se encontraron ${#BACKUPS[@]} backups, eliminando antiguos..."
        
        # Eliminar los más antiguos
        for ((i=MAX_BACKUPS; i<${#BACKUPS[@]}; i++)); do
            rm -f "${BACKUPS[$i]}"
            log "INFO" "Backup antiguo eliminado: $(basename ${BACKUPS[$i]})"
        done
    fi
    
    local TOTAL=${#BACKUPS[@]}
    if [ $TOTAL -gt $MAX_BACKUPS ]; then
        TOTAL=$MAX_BACKUPS
    fi
    
    log "INFO" "Backups actuales: $TOTAL/$MAX_BACKUPS"
}

# Subir a FTP
subir_ftp() {
    local ARCHIVO="$1"
    
    if [ "$FTP_ENABLED" != "true" ]; then
        log "WARN" "FTP deshabilitado en configuración"
        return 0
    fi
    
    if ! command -v ftp &> /dev/null; then
        log "WARN" "Cliente FTP no disponible"
        return 1
    fi
    
    log "INFO" "Subiendo archivo a FTP..."
    
    # Crear script FTP temporal
    local FTP_SCRIPT=$(mktemp)
    cat > "$FTP_SCRIPT" << EOF
open $FTP_HOST $FTP_PORT
$FTP_USER
$FTP_PASS
cd $FTP_REMOTE_DIR
mkdir $FTP_REMOTE_DIR
cd $FTP_REMOTE_DIR
binary
put "$ARCHIVO" $(basename $ARCHIVO)
quit
EOF
    
    # Ejecutar FTP
    ftp -n -i < "$FTP_SCRIPT" > /dev/null 2>&1
    local RESULTADO=$?
    
    rm -f "$FTP_SCRIPT"
    
    if [ $RESULTADO -eq 0 ]; then
        log "INFO" "Archivo subido a FTP: $(basename $ARCHIVO)"
        return 0
    else
        log "WARN" "Fallo al subir a FTP, pero backup local se conserva"
        return 1
    fi
}

# ==================== EJECUCIÓN PRINCIPAL ====================

main() {
    echo ""
    echo "============================================"
    log "INFO" "INICIANDO BACKUP AUTOMÁTICO"
    echo "============================================"
    echo ""
    
    # Crear directorio
    crear_directorio
    
    # Verificar dependencias
    verificar_dependencias
    
    # Backup de archivos
    ZIP_ARCHIVOS=$(backup_archivos)
    if [ -z "$ZIP_ARCHIVOS" ]; then
        log "ERROR" "No se pudo crear backup de archivos, abortando"
        exit 1
    fi
    
    # Backup de BD
    ARCHIVO_SQL=$(backup_bd_mysqldump)
    if [ -z "$ARCHIVO_SQL" ]; then
        log "ERROR" "No se pudo crear backup de BD, abortando"
        exit 1
    fi
    
    # Crear ZIP final
    ARCHIVO_FINAL=$(crear_zip_completo "$ZIP_ARCHIVOS" "$ARCHIVO_SQL")
    if [ -z "$ARCHIVO_FINAL" ]; then
        log "ERROR" "No se pudo crear backup completo, abortando"
        exit 1
    fi
    
    # Rotar backups
    rotar_backups
    
    # Subir a FTP
    if [ "$FTP_ENABLED" = "true" ]; then
        subir_ftp "$ARCHIVO_FINAL"
    fi
    
    echo ""
    echo "============================================"
    log "INFO" "BACKUP COMPLETADO EXITOSAMENTE"
    echo "============================================"
    echo ""
}

# Ejecutar si se llamó directamente
if [ "$0" = "${BASH_SOURCE[0]}" ]; then
    main
fi
