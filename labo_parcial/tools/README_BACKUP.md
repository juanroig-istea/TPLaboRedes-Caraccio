# 🔄 SISTEMA DE BACKUP AUTOMÁTICO - INICIO RÁPIDO

## 📦 Archivos Creados

```
tools/
├── backup.php                          ← Script principal (PHP)
├── backup.sh                           ← Script alternativo (Bash, más eficiente)
├── verificar_backup.sh                 ← Verificador de configuración
├── CONFIGURAR_BACKUP_CRON.md           ← Guía completa de configuración
├── EJEMPLOS_CRON.txt                   ← Ejemplos listos para copiar/pegar
└── README.md                           ← Este archivo
```

---

## ⚡ INICIO RÁPIDO (5 minutos)

### 1️⃣ Editar Credenciales de Base de Datos

En `tools/backup.php`, líneas 18-22:
```php
$FTP_ENABLED = true;                    // ← Cambiar a true si usas FTP
$FTP_HOST = 'your-ftp-host.com';       // ← Cambiar por tu servidor FTP
$FTP_USER = 'tu_usuario_ftp';          // ← Tu usuario FTP
$FTP_PASS = 'tu_password_ftp';         // ← Tu contraseña FTP
$FTP_REMOTE_DIR = '/backups';          // ← Carpeta en servidor FTP
```

### 2️⃣ Crear Directorio de Backups

```bash
mkdir -p /var/www/html/club_jugadores_app/backups
chmod 755 /var/www/html/club_jugadores_app/backups
```

### 3️⃣ Agregar al Crontab

```bash
# Abre el editor
crontab -e

# Copia una línea del archivo EJEMPLOS_CRON.txt
# Ejemplo: Backup diario a las 2 AM
0 2 * * * /usr/bin/php /var/www/html/club_jugadores_app/tools/backup.php

# Guarda (Ctrl+X, Y, Enter en nano)
```

### 4️⃣ Verificar Configuración

```bash
# Verifica que se agregó
crontab -l

# Prueba manual el backup
php /var/www/html/club_jugadores_app/tools/backup.php

# Ver logs
tail -f /var/www/html/club_jugadores_app/backups/backup.log
```

---

## 🔍 ¿Qué hace el Sistema?

### ✅ Todos los días:
1. **Crea backup de ARCHIVOS** (ZIP) - Toda la aplicación web
2. **Crea backup de BASE DE DATOS** (SQL dump)
3. **Comprime ambos** en un único ZIP
4. **Mantiene últimos 5 backups** - El 6to sobrescribe al 1ro
5. **Sube a FTP** (si está configurado)
6. **Registra todo** en un log detallado

### 📁 Estructura de Backups:
```
/var/www/html/club_jugadores_app/backups/
├── backup_2025-01-15_02-00-01_COMPLETO.zip  ← Hoy
├── backup_2025-01-14_02-00-02_COMPLETO.zip  ← Ayer
├── backup_2025-01-13_02-00-03_COMPLETO.zip
├── backup_2025-01-12_02-00-04_COMPLETO.zip
├── backup_2025-01-11_02-00-05_COMPLETO.zip  ← 5 días atrás
└── backup.log                                 ← Registro de ejecuciones
```

---

## 🧪 Pruebas

### Ejecutar Manual (Sincrónico)
```bash
php /var/www/html/club_jugadores_app/tools/backup.php
```

### Ver Resultado Inmediato
```bash
ls -lh /var/www/html/club_jugadores_app/backups/
```

### Ver Logs en Tiempo Real
```bash
tail -f /var/www/html/club_jugadores_app/backups/backup.log
```

### Verificar Instalación
```bash
bash /var/www/html/club_jugadores_app/tools/verificar_backup.sh
```

---

## 📋 Opciones de Horarios

Edita la línea del crontab. Ejemplos:

| Horario | Línea Cron |
|---------|-----------|
| **2 AM diario** | `0 2 * * * /usr/bin/php /path/backup.php` |
| **12 PM diario** | `0 12 * * * /usr/bin/php /path/backup.php` |
| **6 PM diario** | `0 18 * * * /usr/bin/php /path/backup.php` |
| **Cada 6 horas** | `0 */6 * * * /usr/bin/php /path/backup.php` |
| **2x al día (2 AM y 2 PM)** | Agrega dos líneas con `0 2` y `0 14` |

Ver más en `EJEMPLOS_CRON.txt`

---

## ⚙️ Configuración FTP (Opcional)

Si quieres que el backup se suba automáticamente a FTP:

1. Edita `tools/backup.php` y configura:
   ```php
   $FTP_ENABLED = true;
   $FTP_HOST = 'ftp.tuservidor.com';
   $FTP_USER = 'usuario';
   $FTP_PASS = 'contraseña';
   $FTP_REMOTE_DIR = '/backups';
   ```

2. Instala extensión FTP en PHP (si no está):
   ```bash
   sudo apt-get install php-ftp
   sudo systemctl restart php-fpm
   ```

3. Prueba con:
   ```bash
   php /var/www/html/club_jugadores_app/tools/backup.php
   ```

4. Verifica en el log:
   ```bash
   grep FTP /var/www/html/club_jugadores_app/backups/backup.log
   ```

---

## 🔐 Seguridad

### Proteger Directorio de Backups

En `.htaccess` dentro de `/backups/`:
```apache
Deny from all
```

O con permisos:
```bash
chmod 700 /var/www/html/club_jugadores_app/backups
chown www-data:www-data /var/www/html/club_jugadores_app/backups
```

### Cambiar Contraseña FTP

- Cambia regularmente `$FTP_PASS` en `backup.php`
- No la compartas en repositorios Git

---

## 🐛 Troubleshooting

### ❌ "Cron no se ejecuta"
1. Verifica que PHP está en `/usr/bin/php`: `which php`
2. Revisa permisos: `ls -l tools/backup.php`
3. Ver logs: `tail -20 /var/log/syslog | grep CRON`
4. Ejecuta manualmente: `php tools/backup.php`

### ❌ "Error en mysqldump"
- El script usa método PHP automáticamente
- Instala mysql-client: `sudo apt-get install mysql-client`

### ❌ "FTP no conecta"
- Verifica credenciales en `backup.php`
- Instala FTP: `sudo apt-get install php-ftp`
- Prueba conexión manual: `ftp ftp.servidor.com`

### ❌ "Espacio insuficiente"
- Reduce número de backups: `$MAX_BACKUPS = 3`
- Comprime backups más antiguos
- Sumérgeles a FTP para liberar espacio

---

## 📖 Documentación Completa

Para información más detallada, consulta:
- **[CONFIGURAR_BACKUP_CRON.md](CONFIGURAR_BACKUP_CRON.md)** - Guía completa
- **[EJEMPLOS_CRON.txt](EJEMPLOS_CRON.txt)** - Ejemplos listos para usar

---

## 📊 Monitoreo

### Ver Backups Disponibles
```bash
ls -lhS /var/www/html/club_jugadores_app/backups/
```

### Ver Últimas Ejecuciones
```bash
tail -20 /var/www/html/club_jugadores_app/backups/backup.log
```

### Búsqueda de Errores
```bash
grep ERROR /var/www/html/club_jugadores_app/backups/backup.log
```

### Seguimiento en Tiempo Real
```bash
tail -f /var/www/html/club_jugadores_app/backups/backup.log
```

---

## 🚀 Próximos Pasos

- [ ] Editar credenciales FTP en `backup.php`
- [ ] Crear directorio `/backups` con permisos correctos
- [ ] Agregar línea al crontab
- [ ] Ejecutar manual: `php tools/backup.php`
- [ ] Verificar log y archivos creados
- [ ] Monitorear próximas ejecuciones automáticas

---

## 📞 Soporte

Si tienes dudas:
1. Lee [CONFIGURAR_BACKUP_CRON.md](CONFIGURAR_BACKUP_CRON.md)
2. Ejecuta verificador: `bash tools/verificar_backup.sh`
3. Revisa el log: `tail -f backups/backup.log`
4. Consulta [EJEMPLOS_CRON.txt](EJEMPLOS_CRON.txt)

---

**Última actualización:** Enero 2025
**Versión:** 1.0
