<?php
/**
 * Script de Backup Automático
 * Genera backups diarios de archivos y BD
 * Mantiene últimos 5 backups
 * Sube a servidor FTP
 * 
 * Uso: php backup.php
 * Configurar en cron: 0 2 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php
 */

// ==================== CONFIGURACIÓN ====================
// RUTAS
$BASE_DIR = dirname(dirname(__FILE__));
$BACKUP_DIR = $BASE_DIR . '/backups';
$DB_CONFIG = $BASE_DIR . '/config/db.php';
$LOG_FILE = $BACKUP_DIR . '/backup.log';

// CONFIGURACIÓN FTP
$FTP_ENABLED = true;                    // Cambiar a true cuando esté configurado FTP
$FTP_HOST = '127.0.0.1';       // Cambiar con tu servidor FTP
$FTP_USER = 'ftp_admin';          // Cambiar con tu usuario
$FTP_PASS = 'Fito2025!';         // Cambiar con tu password
$FTP_REMOTE_DIR = '/backups';          // Directorio en el servidor FTP
$FTP_PORT = 21;

// CONFIGURACIÓN DE ROTACIÓN
$MAX_BACKUPS = 5;                       // Mantener últimos 5 backups

// BASE DE DATOS
require $DB_CONFIG;

// ==================== FUNCIONES ====================

/**
 * Escribe en el archivo de log
 */
function escribir_log($mensaje, $tipo = 'INFO') {
    global $LOG_FILE;
    // Asegurar que el directorio del log existe antes de escribir
    $logDir = dirname($LOG_FILE);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $linea = "[$timestamp] [$tipo] $mensaje\n";
    @file_put_contents($LOG_FILE, $linea, FILE_APPEND);
    echo $linea;
}

/**
 * Crea directorio de backups si no existe
 */
function crear_directorio_backup() {
    global $BACKUP_DIR;
    if (!is_dir($BACKUP_DIR)) {
        if (!mkdir($BACKUP_DIR, 0755, true)) {
            escribir_log("Error al crear directorio de backups", 'ERROR');
            return false;
        }
        escribir_log("Directorio de backups creado: $BACKUP_DIR", 'INFO');
    }
    return true;
}

/**
 * Genera nombre único para el backup con fecha
 */
function generar_nombre_backup() {
    return 'backup_' . date('Y-m-d_H-i-s');
}

/**
 * Genera backup de archivos (ZIP)
 */
function backup_archivos($nombre_base) {
    global $BASE_DIR, $BACKUP_DIR;
    
    $archivo_zip = $BACKUP_DIR . '/' . $nombre_base . '_archivos.zip';
    
    escribir_log("Iniciando backup de archivos...", 'INFO');
    
    // Verificar que la extensión Zip esté disponible
    if (!class_exists('ZipArchive')) {
        escribir_log("Extensión Zip no disponible en PHP. Habilita 'extension=zip' en php.ini y reinicia Apache.", 'ERROR');
        return false;
    }

    // Crear archivo ZIP
    $zip = new ZipArchive();
    if ($zip->open($archivo_zip, ZipArchive::CREATE) !== true) {
        escribir_log("No se pudo crear archivo ZIP", 'ERROR');
        return false;
    }
    
    // Agregar archivos (excluir directorio de backups)
    $archivos_excluir = ['backups', '.git', '.gitignore'];
    agregar_archivos_zip($zip, $BASE_DIR, '', $archivos_excluir);
    
    if (!$zip->close()) {
        escribir_log("Error al cerrar archivo ZIP", 'ERROR');
        return false;
    }
    
    $tamaño = filesize($archivo_zip);
    escribir_log("Backup de archivos completado: $archivo_zip (Tamaño: " . formatear_tamaño($tamaño) . ")", 'INFO');
    
    return $archivo_zip;
}

/**
 * Agrega archivos recursivamente a ZIP
 */
function agregar_archivos_zip(&$zip, $ruta, $ruta_interna = '', $excluir = []) {
    $gestor = opendir($ruta);
    
    while (false !== ($archivo = readdir($gestor))) {
        if ($archivo === '.' || $archivo === '..' || in_array($archivo, $excluir)) {
            continue;
        }
        
        $ruta_completa = $ruta . '/' . $archivo;
        $ruta_zip = $ruta_interna . '/' . $archivo;
        
        if (is_dir($ruta_completa)) {
            $zip->addEmptyDir($ruta_zip);
            agregar_archivos_zip($zip, $ruta_completa, $ruta_zip, $excluir);
        } else {
            $zip->addFile($ruta_completa, $ruta_zip);
        }
    }
    closedir($gestor);
}

/**
 * Genera backup de base de datos (SQL dump)
 */
function backup_base_datos($nombre_base) {
    global $BACKUP_DIR, $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    
    $archivo_sql = $BACKUP_DIR . '/' . $nombre_base . '_database.sql';
    
    escribir_log("Iniciando backup de base de datos...", 'INFO');
    
    // Usar mysqldump si está disponible
    $comando = sprintf(
        'mysqldump -h %s -u %s -p%s %s > %s 2>&1',
        escapeshellarg($DB_HOST),
        escapeshellarg($DB_USER),
        escapeshellarg($DB_PASS),
        escapeshellarg($DB_NAME),
        escapeshellarg($archivo_sql)
    );
    
    if (stripos(PHP_OS, 'WIN') === 0) {
        // Windows
        $comando = str_replace('mysqldump', 'mysqldump.exe', $comando);
    }
    
    $salida = [];
    $retorno = 0;
    exec($comando, $salida, $retorno);
    
    if ($retorno !== 0) {
        escribir_log("Error al ejecutar mysqldump (código: $retorno)", 'ERROR');
        // Intentar método alternativo con PHP
        return backup_base_datos_php($archivo_sql);
    }
    
    if (!file_exists($archivo_sql) || filesize($archivo_sql) === 0) {
        escribir_log("Error: Backup SQL vacío o no creado", 'ERROR');
        return false;
    }
    
    $tamaño = filesize($archivo_sql);
    escribir_log("Backup de base de datos completado: $archivo_sql (Tamaño: " . formatear_tamaño($tamaño) . ")", 'INFO');
    
    return $archivo_sql;
}

/**
 * Backup de BD usando PDO (alternativo si mysqldump no está disponible)
 */
function backup_base_datos_php($archivo_sql) {
    global $pdo, $DB_NAME, $BACKUP_DIR;
    
    escribir_log("Usando método PHP para backup de BD", 'INFO');
    
    try {
        // Obtener todas las tablas
        $stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$DB_NAME'");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $sql = "-- Backup de $DB_NAME\n";
        $sql .= "-- Generado: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tablas as $tabla) {
            // Structure
            $stmt = $pdo->query("SHOW CREATE TABLE $tabla");
            $row = $stmt->fetch();
            $sql .= "\n\n" . $row['Create Table'] . ";\n\n";
            
            // Data
            $stmt = $pdo->query("SELECT * FROM $tabla");
            $datos = $stmt->fetchAll();
            
            foreach ($datos as $fila) {
                $columnas = array_keys($fila);
                $valores = array_map(function($v) use ($pdo) {
                    return is_null($v) ? 'NULL' : $pdo->quote($v);
                }, array_values($fila));
                
                $sql .= "INSERT INTO $tabla (" . implode(', ', $columnas) . ") VALUES (" . implode(', ', $valores) . ");\n";
            }
        }
        
        if (file_put_contents($archivo_sql, $sql) === false) {
            escribir_log("Error al escribir archivo SQL", 'ERROR');
            return false;
        }
        
        $tamaño = filesize($archivo_sql);
        escribir_log("Backup de base de datos completado (PHP): $archivo_sql (Tamaño: " . formatear_tamaño($tamaño) . ")", 'INFO');
        
        return $archivo_sql;
        
    } catch (Exception $e) {
        escribir_log("Error en backup PHP: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Crea archivo ZIP con ambos backups
 */
function crear_zip_completo($nombre_base, $archivo_zip_archivos, $archivo_sql) {
    global $BACKUP_DIR;
    
    $archivo_zip_final = $BACKUP_DIR . '/' . $nombre_base . '_COMPLETO.zip';
    
    escribir_log("Creando backup completo ZIP...", 'INFO');
    
    $zip = new ZipArchive();
    if ($zip->open($archivo_zip_final, ZipArchive::CREATE) !== true) {
        escribir_log("Error al crear ZIP completo", 'ERROR');
        return false;
    }
    
    $zip->addFile($archivo_zip_archivos, basename($archivo_zip_archivos));
    $zip->addFile($archivo_sql, basename($archivo_sql));
    
    if (!$zip->close()) {
        escribir_log("Error al cerrar ZIP completo", 'ERROR');
        return false;
    }
    
    $tamaño = filesize($archivo_zip_final);
    escribir_log("Backup completo creado: $archivo_zip_final (Tamaño: " . formatear_tamaño($tamaño) . ")", 'INFO');
    
    // Eliminar archivos intermedios
    @unlink($archivo_zip_archivos);
    @unlink($archivo_sql);
    
    return $archivo_zip_final;
}

/**
 * Mantiene rotación de backups (últimos 5)
 */
function rotar_backups() {
    global $BACKUP_DIR, $MAX_BACKUPS;
    
    escribir_log("Iniciando rotación de backups...", 'INFO');
    
    // Obtener todos los backups ZIP completos
    $archivos = glob($BACKUP_DIR . '/backup_*_COMPLETO.zip');
    
    if (count($archivos) > $MAX_BACKUPS) {
        // Ordenar por fecha (antiguo a nuevo)
        usort($archivos, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Eliminar los más antiguos
        $a_eliminar = array_splice($archivos, 0, count($archivos) - $MAX_BACKUPS);
        
        foreach ($a_eliminar as $archivo) {
            if (@unlink($archivo)) {
                escribir_log("Backup antiguo eliminado: " . basename($archivo), 'INFO');
            } else {
                escribir_log("Error al eliminar: " . basename($archivo), 'WARN');
            }
        }
    }
    
    $backups_restantes = glob($BACKUP_DIR . '/backup_*_COMPLETO.zip');
    escribir_log("Backups actuales: " . count($backups_restantes) . "/$MAX_BACKUPS", 'INFO');
}

/**
 * Sube archivo a servidor FTP
 */
function subir_ftp($archivo) {
    global $FTP_ENABLED, $FTP_HOST, $FTP_USER, $FTP_PASS, $FTP_REMOTE_DIR, $FTP_PORT;
    
    if (!$FTP_ENABLED) {
        escribir_log("FTP deshabilitado en configuración", 'WARN');
        return false;
    }
    
    if (!function_exists('ftp_connect')) {
        escribir_log("Extensión FTP no disponible en PHP", 'ERROR');
        return false;
    }
    
    escribir_log("Conectando a servidor FTP: $FTP_HOST", 'INFO');
    
    $conexion_ftp = @ftp_connect($FTP_HOST, $FTP_PORT, 10);
    
    if (!$conexion_ftp) {
        escribir_log("No se pudo conectar al servidor FTP: $FTP_HOST:$FTP_PORT", 'ERROR');
        return false;
    }
    
    if (!@ftp_login($conexion_ftp, $FTP_USER, $FTP_PASS)) {
        escribir_log("Error de autenticación FTP", 'ERROR');
        ftp_close($conexion_ftp);
        return false;
    }
    
    escribir_log("Autenticación FTP exitosa", 'INFO');
    
    // Cambiar a modo pasivo
    ftp_pasv($conexion_ftp, true);
    
    // Crear directorio remoto si no existe
    if (!@ftp_chdir($conexion_ftp, $FTP_REMOTE_DIR)) {
        escribir_log("Creando directorio remoto: $FTP_REMOTE_DIR", 'INFO');
        ftp_mkdir($conexion_ftp, $FTP_REMOTE_DIR);
        ftp_chdir($conexion_ftp, $FTP_REMOTE_DIR);
    }
    
    $nombre_archivo = basename($archivo);
    
    escribir_log("Subiendo archivo a FTP: $nombre_archivo", 'INFO');
    
    if (!@ftp_put($conexion_ftp, $nombre_archivo, $archivo, FTP_BINARY)) {
        escribir_log("Error al subir archivo FTP", 'ERROR');
        ftp_close($conexion_ftp);
        return false;
    }
    
    escribir_log("Archivo subido exitosamente a FTP", 'INFO');
    
    // Cerrar conexión FTP
    if (!@ftp_quit($conexion_ftp)) {
        @ftp_close($conexion_ftp);
    }
    
    escribir_log("Conexión FTP cerrada", 'INFO');
    
    return true;
}

/**
 * Formatea tamaño de archivo en bytes a formato legible
 */
function formatear_tamaño($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($unidades) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $unidades[$pow];
}

// ==================== EJECUCIÓN PRINCIPAL ====================

escribir_log("============================================", 'INFO');
escribir_log("INICIANDO BACKUP AUTOMÁTICO", 'INFO');
escribir_log("============================================", 'INFO');

try {
    // Crear directorio de backups
    if (!crear_directorio_backup()) {
        exit(1);
    }
    
    // Generar nombre base con fecha
    $nombre_base = generar_nombre_backup();
    
    // Backup de archivos
    $zip_archivos = backup_archivos($nombre_base);
    if (!$zip_archivos) {
        escribir_log("Backup de archivos falló, abortando...", 'ERROR');
        exit(1);
    }
    
    // Backup de base de datos
    $sql = backup_base_datos($nombre_base);
    if (!$sql) {
        escribir_log("Backup de BD falló, abortando...", 'ERROR');
        exit(1);
    }
    
    // Crear ZIP con ambos backups
    $zip_completo = crear_zip_completo($nombre_base, $zip_archivos, $sql);
    if (!$zip_completo) {
        escribir_log("Creación de backup completo falló, abortando...", 'ERROR');
        exit(1);
    }
    
    // Rotar backups antiguos
    rotar_backups();
    
    // Subir a FTP
    if ($FTP_ENABLED) {
        if (subir_ftp($zip_completo)) {
            escribir_log("Backup subido a FTP exitosamente", 'INFO');
        } else {
            escribir_log("Fallo al subir a FTP, pero backup local se conserva", 'WARN');
        }
    }
    
    escribir_log("============================================", 'INFO');
    escribir_log("BACKUP COMPLETADO EXITOSAMENTE", 'INFO');
    escribir_log("============================================", 'INFO');
    
} catch (Exception $e) {
    escribir_log("Error no manejado: " . $e->getMessage(), 'ERROR');
    exit(1);
}
?>
