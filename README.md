# Atlético Nova FC - Sistema de Gestión de Jugadores

## Versión

- `1.1.0`

## Descripción

Aplicación web para la gestión de jugadores de un club de fútbol.
Permite crear, editar, eliminar y buscar jugadores, con login de administrador y seguridad básica.

## Funcionalidades principales

- Autenticación de usuario con `password_hash` / `password_verify`
- CRUD completo para jugadores
- Buscador por nombre, apellido o DNI
- Manejo de categorías, posiciones, obra social y estado
- Registro de sesiones seguro con `session_regenerate_id(true)`
- Eliminación por `POST` para evitar accesos directos
- Backup automático de aplicación y base de datos (herramientas en `tools/`)

## Tecnologías

- PHP 7.x/8.x
- MySQL / MariaDB
- HTML/CSS puro
- PDO con prepared statements
- Sessiones PHP

## Requisitos

- Servidor web con PHP habilitado
- Base de datos MySQL o MariaDB
- Extensión PHP `pdo_mysql`
- Extensión PHP `zip` para generar backups
- Opcional: FileZilla Server para subir backups por FTP

## Instalación rápida

### Opción A: XAMPP en Windows

1. Copia la carpeta `labo_parcial` a `E:\xampp\htdocs\labo_parcial`.
2. Inicia Apache y MySQL desde el panel de XAMPP.
3. Abre `http://localhost/phpmyadmin`.
4. Crea la base de datos `club_jugadores_app`.
5. Importa `labo_parcial/schema.sql`.
6. Edita `labo_parcial/config/db.php` con las credenciales de tu servidor MySQL.
7. Genera el hash para el usuario admin:
   ```powershell
   & 'E:\xampp\php\php.exe' 'E:\Labo\TPLaboRedes-Caraccio\labo_parcial\tools\generar_hash.php'
   ```
8. Inserta el usuario en la tabla `login` usando phpMyAdmin:
   ```sql
   INSERT INTO login (user, password) VALUES ('admin', 'HASH_GENERADO');
   ```
9. Abre en tu navegador:
   ```text
   http://localhost/labo_parcial/login.php
   ```

### Opción B: LAMP/Ubuntu

1. Copia la carpeta a `/var/www/html/club_jugadores_app`.
2. Asegúrate de que Apache, PHP y MySQL estén instalados.
3. Importa el esquema:
   ```bash
   mysql -u root -p < /var/www/html/club_jugadores_app/labo_parcial/schema.sql
   ```
4. Edita `labo_parcial/config/db.php` con los datos de tu servidor.
5. Genera el hash de admin:
   ```bash
   php /var/www/html/club_jugadores_app/labo_parcial/tools/generar_hash.php
   ```
6. Crea el usuario admin en la tabla `login` y abre `login.php`.

## Configuración de la base de datos

Edita `labo_parcial/config/db.php` y reemplaza:

```php
$DB_HOST = 'localhost';
$DB_NAME = 'tu_base_de_datos';
$DB_USER = 'tu_usuario';
$DB_PASS = 'tu_password';
```

Para XAMPP en Windows normalmente:

```php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'club_jugadores_app';
$DB_USER = 'root';
$DB_PASS = ''; // o tu contraseña de root
```

## Uso del backup

El proyecto incluye un sistema de backup en `labo_parcial/tools/backup.php` que:

- Crea un backup de archivos de la aplicación
- Genera un dump de la base de datos
- Consolida todo en un ZIP con fecha
- Mantiene los últimos 5 backups
- Sube a FTP si está configurado

Para ejecutarlo manualmente en Windows con XAMPP:

```powershell
& 'E:\xampp\php\php.exe' 'E:\Labo\TPLaboRedes-Caraccio\labo_parcial\tools\backup.php'
```

Si no necesitas FTP, deshabilita `FTP_ENABLED` en `tools/backup.php`.

## Estructura del proyecto

```
labo_parcial/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── img/
├── config/
│   └── db.php
├── includes/
│   ├── auth.php
│   └── validation.php
├── tools/
│   ├── backup.php
│   ├── backup.sh
│   ├── test_db.php
│   ├── verificar_backup.sh
│   ├── generar_hash.php
│   └── README_BACKUP.md
├── index.php
├── login.php
├── logout.php
├── crear.php
├── editar.php
├── eliminar.php
├── form.php
├── schema.sql
└── README.txt
```

## Tablas principales

- `login`
- `jugadores`

### `jugadores`

Campos principales:
- `numero_jugador`
- `nombre`
- `apellido`
- `dni`
- `telefono`
- `categoria`
- `posicion`
- `fecha_nacimiento`
- `obra_social`
- `apto_medico`
- `estado`

## Seguridad incluida

- Contraseñas guardadas con `password_hash`
- Uso de PDO con prepared statements
- CSRF token en formularios
- `session_regenerate_id(true)` al iniciar sesión
- Eliminación solo por `POST`

## Notas adicionales

- `labo_parcial/README.txt` contiene instrucciones originales de instalación.
- `labo_parcial/tools/README_BACKUP.md` explica la configuración detallada del backup y cron.
- Usa `phpinfo()` o `php -m | findstr zip` para confirmar que la extensión `zip` está habilitada.

## ¡Listo!

Abre `labo_parcial/login.php` en tu navegador y comienza a usar la aplicación.

