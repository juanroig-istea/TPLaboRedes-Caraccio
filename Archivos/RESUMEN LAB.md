PROYECTO CLUB - RESUMEN

SERVICIOS:
1. Apache2 (Web + PHP)
2. MariaDB (Base de datos) o MySQL
3. DHCP Server (isc-dhcp-server)
4. FTP (samba)
5. SSH

Antes de instalar el DHCP instalar todos los otros servicios y configuraciones porque sino da error 
por conflicto con el gateway del router del ISP.
--------------------------------

COMANDOS IMPORTANTES:

WEB:
- sudo apt install apache2 php libapache2-mod-php
- sudo systemctl restart apache2

FTP:
- sudo apt install samba -y
- sudo systemctl enable smbd

DHCP:
- sudo apt install isc-dhcp-server
- sudo systemctl restart isc-dhcp-server

MARIA DB: (MySQL requiere un repo distinto)
- sudo apt install mariadb-server
- sudo mysql_secure_installation

CRON TAB:
- crontab -e

LINUX:
- GREP TEXTO ARCHIVO (BUSCAR EN ARCHIVO)
- CAT (VER CONTENIDO DEL ARCHIVO)
- NANO (EDITAR)
- > ESCRIBIR EN ARCHIVO
- >> AGREGAR AL FINAL DEL ARCHIVO
- IP A
- IP NEIGH

--------------------------------

RUTAS IMPORTANTES:
- /var/www/html (web)
- /etc/apache2/sites-available/
- /etc/samba/smb.conf 
- /etc/dhcp/dhcpd.conf
- /etc/default/isc-dhcp-server
- /etc/network/interfaces
- /etc/crontab
