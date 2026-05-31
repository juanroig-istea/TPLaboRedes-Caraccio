# CONFIGURACIÓN DE BACKUP AUTOMÁTICO CON CRON

## 📋 Descripción

Este sistema genera backups automáticos diarios de la aplicación y la base de datos:
- ✅ Backup de archivos (ZIP)
- ✅ Backup de base de datos (SQL)
- ✅ Almacenamiento con fecha en el nombre
- ✅ Mantiene últimos 5 backups (rotación automática)
- ✅ Sube a servidor FTP (opcional)
- ✅ Logging detallado de todas las operaciones

---

## 🔧 CONFIGURACIÓN INICIAL

### 1. Editar Configuración del Script

Abre el archivo `tools/backup.php` y edita la sección "CONFIGURACIÓN":

```php
// CONFIGURACIÓN FTP
$FTP_ENABLED = true;                    // Cambiar a true para habilitar FTP
$FTP_HOST = 'your-ftp-host.com';       // Tu servidor FTP (ej: ftp.ejemplo.com)
$FTP_USER = 'tu_usuario_ftp';          // Tu usuario FTP
$FTP_PASS = 'tu_password_ftp';         // Tu contraseña FTP
$FTP_REMOTE_DIR = '/backups';          // Ruta en servidor FTP
$FTP_PORT = 21;                        // Puerto FTP (21 por defecto)

// ROTACIÓN DE BACKUPS
$MAX_BACKUPS = 5;                      // Cambiar si deseas más/menos backups
```

**Notas importantes:**
- Si NO usas FTP, déja `$FTP_ENABLED = false`
- Las credenciales deben ser exactas
- El directorio FTP remoto se crea automáticamente si no existe

### 2. Crear Directorio de Backups

```bash
# En el servidor (via SSH o terminal)
mkdir -p /var/www/html/club_jugadores_app/backups
chmod 755 /var/www/html/club_jugadores_app/backups
```

### 3. Dar Permisos al Script

```bash
chmod +x /var/www/html/club_jugadores_app/tools/backup.php
```

---

## ⏰ CONFIGURAR CRON JOB

### En Linux/Ubuntu:

**1. Abrir el editor de cron:**
```bash
crontab -e
```

**2. Agregar una línea como esta (ejecutar diariamente a las 2:00 AM):**
```
0 2 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php
```

**Ejemplos de horarios diferentes:**
```
# Cada día a las 2:00 AM
0 2 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php

# Cada día a las 3:00 AM
0 3 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php

# Cada día a las 12:00 PM (mediodía)
0 12 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php

# Dos veces al día: 2 AM y 2 PM
0 2 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php
0 14 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php

# Cada 6 horas
0 */6 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php
```

**3. Guardar y salir:**
- Si usas `nano`: Ctrl+X → Y → Enter
- Si usas `vi`: :wq → Enter

**4. Verificar que se agregó correctamente:**
```bash
crontab -l
```

### En Windows:

Si usas Windows con XAMPP/WAMP, usa el Programador de tareas:

**1. Abrir Programador de tareas**
   - Buscar "Programador de tareas" en el menú Inicio

**2. Crear tarea básica:**
   - Clic derecho → "Crear tarea básica"
   - Nombre: "Backup Club Jugadores"
   - En "Desencadenadores": Seleccionar "Diario" y la hora deseada
   - En "Acciones": Agregar acción
     - Programa: `C:\ruta\a\php.exe`
     - Argumentos: `"C:\ruta\a\club_jugadores_app\tools\backup.php"`

---

## 📊 MONITOREAR BACKUPS

### Ver el registro de logs:

```bash
# Ver últimas 50 líneas del log
tail -50 /var/www/html/club_jugadores_app/backups/backup.log

# Ver log en tiempo real (actualiza cada segundo)
tail -f /var/www/html/club_jugadores_app/backups/backup.log

# Ver solo errores
grep ERROR /var/www/html/club_jugadores_app/backups/backup.log

# Ver los archivos de backup disponibles
ls -lh /var/www/html/club_jugadores_app/backups/
```

### Interpretar el log:

```
[2025-01-15 02:00:01] [INFO] INICIANDO BACKUP AUTOMÁTICO
[2025-01-15 02:00:02] [INFO] Iniciando backup de archivos...
[2025-01-15 02:00:05] [INFO] Backup de archivos completado: backup_2025-01-15_02-00-01_archivos.zip (Tamaño: 15.34 MB)
[2025-01-15 02:00:06] [INFO] Iniciando backup de base de datos...
[2025-01-15 02:00:08] [INFO] Backup de base de datos completado: backup_2025-01-15_02-00-01_database.sql (Tamaño: 0.45 MB)
[2025-01-15 02:00:10] [INFO] Creando backup completo ZIP...
[2025-01-15 02:00:12] [INFO] Backup completo creado: backup_2025-01-15_02-00-01_COMPLETO.zip (Tamaño: 15.78 MB)
[2025-01-15 02:00:12] [INFO] Iniciando rotación de backups...
[2025-01-15 02:00:13] [INFO] Backups actuales: 5/5
[2025-01-15 02:00:14] [INFO] Conectando a servidor FTP: ftp.ejemplo.com
[2025-01-15 02:00:15] [INFO] Autenticación FTP exitosa
[2025-01-15 02:00:25] [INFO] Archivo subido exitosamente a FTP
[2025-01-15 02:00:25] [INFO] Conexión FTP cerrada
[2025-01-15 02:00:26] [INFO] BACKUP COMPLETADO EXITOSAMENTE
```

---

## 🔍 PRUEBA MANUAL

### Ejecutar el backup manualmente:

```bash
php /var/www/html/club_jugadores_app/tools/backup.php
```

**Esto debería mostrar algo como:**
```
[2025-01-15 14:30:00] [INFO] ============================================
[2025-01-15 14:30:00] [INFO] INICIANDO BACKUP AUTOMÁTICO
[2025-01-15 14:30:00] [INFO] ============================================
[2025-01-15 14:30:01] [INFO] Iniciando backup de archivos...
...
[2025-01-15 14:30:15] [INFO] BACKUP COMPLETADO EXITOSAMENTE
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### El cron no se ejecuta:

1. **Verificar que PHP está disponible:**
   ```bash
   which php
   # Debería mostrar algo como: /usr/bin/php
   ```

2. **Verificar permisos:**
   ```bash
   ls -l /var/www/html/club_jugadores_app/tools/backup.php
   # Debería tener permiso de ejecución (x)
   ```

3. **Revisar logs del sistema:**
   ```bash
   # En Ubuntu/Debian
   grep CRON /var/log/syslog | tail -20
   
   # En CentOS/RHEL
   tail -20 /var/log/cron
   ```

4. **Asegurarse que el directorio de backups existe:**
   ```bash
   mkdir -p /var/www/html/club_jugadores_app/backups
   chmod 755 /var/www/html/club_jugadores_app/backups
   ```

### Error de conexión FTP:

- Verificar datos de FTP (host, usuario, contraseña)
- Asegurarse que la extensión FTP está habilitada en PHP:
  ```bash
  php -m | grep ftp
  ```
- Si no está, instalarla:
  ```bash
  # Ubuntu/Debian
  sudo apt-get install php-ftp
  sudo systemctl restart php-fpm  # o apache2, según tu servidor
  ```

### Error de mysqldump:

Si mysqldump no está disponible, el script usa automáticamente el método PHP alternativo. Si aún así hay error:

- Verificar que MySQL está instalado:
  ```bash
  which mysqldump
  ```
- Instalar herramientas MySQL:
  ```bash
  # Ubuntu/Debian
  sudo apt-get install mysql-client
  ```

---

## 📁 ESTRUCTURA DE BACKUPS

Los archivos generados se organizan así:

```
/var/www/html/club_jugadores_app/backups/
├── backup_2025-01-15_02-00-01_COMPLETO.zip  ← Backup completo (archivos + BD)
├── backup_2025-01-14_02-00-02_COMPLETO.zip
├── backup_2025-01-13_02-00-03_COMPLETO.zip
├── backup_2025-01-12_02-00-04_COMPLETO.zip
├── backup_2025-01-11_02-00-05_COMPLETO.zip
└── backup.log                                 ← Log de ejecuciones
```

Solo se mantienen los **últimos 5 backups**. El más antiguo se elimina automáticamente al generar el 6to.

---

## 🔐 SEGURIDAD

**Recomendaciones:**

1. **Cambiar permisos del directorio de backups:**
   ```bash
   chmod 700 /var/www/html/club_jugadores_app/backups
   chown www-data:www-data /var/www/html/club_jugadores_app/backups
   ```

2. **Hacer el directorio no accesible desde web:**
   Agregar a `.htaccess` en la carpeta backups:
   ```apache
   Deny from all
   ```

3. **Usar FTP seguro (SFTP si es posible)**

4. **Cambiar contraseña FTP regularmente**

5. **Monitorear los logs de backup regularmente**

---

## 📝 MANTENIMIENTO

### Limpiar backups antiguos manualmente:

```bash
# Ver backups
ls -la /var/www/html/club_jugadores_app/backups/

# Eliminar un backup específico
rm /var/www/html/club_jugadores_app/backups/backup_2025-01-10_02-00-00_COMPLETO.zip

# Limpiar todo (excepto log)
rm /var/www/html/club_jugadores_app/backups/backup_*.zip
```

### Cambiar número de backups a mantener:

Edita en `tools/backup.php`:
```php
$MAX_BACKUPS = 5;  // Cambia a 10, 3, etc.
```

---

## ✅ CHECKLIST DE CONFIGURACIÓN

- [ ] Descargué `tools/backup.php`
- [ ] Creé directorio `/backups`
- [ ] Edité credenciales FTP en `backup.php`
- [ ] Di permisos de ejecución al script
- [ ] Agregué entrada en crontab
- [ ] Verifiqué con `crontab -l`
- [ ] Ejecuté manualmente: `php backup.php`
- [ ] Revisé el log de backups
- [ ] Configuré rotación de backups (`$MAX_BACKUPS`)

---

## 📞 SOPORTE

Si hay problemas, revisa:
1. El archivo de log: `/backups/backup.log`
2. Los permisos del directorio `/backups`
3. La disponibilidad de `mysqldump` o conexión MySQL
4. Las credenciales FTP (si las usas)
5. La ruta del PHP en el cron
