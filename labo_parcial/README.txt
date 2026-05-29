ATLÉTICO NOVA FC - Sistema de Gestión de Jugadores

Stack:
- PHP simple
- MySQL / phpMyAdmin
- CSS puro
- Sesiones PHP
- Login con password_hash/password_verify

Instalación:
1. Subí esta carpeta al DocumentRoot de tu servidor Ubuntu, por ejemplo /var/www/html/club_jugadores_app.
2. Importá schema.sql desde phpMyAdmin o desde consola MySQL.
3. Editá config/db.php con host, nombre de base, usuario y contraseña.
4. Generá un hash para el usuario admin ejecutando:
   php tools/generar_hash.php
5. Copiá el hash generado y ejecutá en phpMyAdmin:
   INSERT INTO login (user, password) VALUES ('admin', 'HASH_GENERADO');
6. Abrí login.php en el navegador.

Tabla principal:
- jugadores

Columnas:
- numero_jugador
- nombre
- apellido
- dni
- telefono
- categoria
- posicion
- fecha_nacimiento
- obra_social
- apto_medico
- estado

Buscador:
Busca por nombre, apellido o DNI.

Seguridad incluida:
- Contraseñas hasheadas
- PDO con prepared statements
- CSRF token en formularios
- session_regenerate_id(true) al iniciar sesión
- Eliminación solamente por POST
