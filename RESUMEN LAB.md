PROYECTO CLUB - RESUMEN

SERVICIOS:
1. Apache2 (Web + PHP) :80
2. MariaDB (Base de datos) o MySQL
3. DHCP Server (isc-dhcp-server)
4. FTP (samba):445
5. SSH :22035

Antes de instalar el DHCP instalar todos los otros servicios y configuraciones porque sino da error 
por conflicto con el gateway del router del ISP.
--------------------------------

COMANDOS IMPORTANTES:

- systemctl restart networking

WEB:
- sudo apt install apache2 php libapache2-mod-php
- sudo systemctl restart apache2

FTP:
- sudo apt install samba -y
- sudo systemctl enable smbd

DHCP:
- sudo apt install isc-dhcp-server
- sudo systemctl restart isc-dhcp-server
- journalctl -u isc-dhcp-server -f
- tail -f /var/lib/dhcp/dhcpd.leases
- journalctl -f | grep -i dhcp
- tcpdump -i enp0s8 port 67 or port 68
- tail -f /var/lib/dhcp/dhcpd.leases | grep lease

MARIA DB: (MySQL requiere un repo distinto)
- sudo apt install mariadb-server
- sudo mysql_secure_installation

CRON TAB:
- crontab -e

SSH

cambiar puerto 22 por 22035

crear 

UFW

apt install ufw

LINUX:
- GREP TEXTO ARCHIVO (BUSCAR EN ARCHIVO)
- CAT (VER CONTENIDO DEL ARCHIVO)
- NANO (EDITAR)
- > ESCRIBIR EN ARCHIVO
- >> AGREGAR AL FINAL DEL ARCHIVO
- IP A
- IP NEIGH

--------------------------------

# Uso de scp

scp backup.sh fernando@192.168.1.84:/home/fernando/backup.sh

--------------------------------

RUTAS IMPORTANTES:

- /var/www/html (web)
- /etc/apache2/sites-available/

- /etc/samba/smb.conf 
- /srv/shared

- /etc/dhcp/dhcpd.conf
- /var/lib/dhcp/dhcpd.leases
- /etc/default/isc-dhcp-server
- /etc/network/interfaces

- /etc/crontab

- /etc/ssh/ssh_config --ojo este es el archivo de configuración del cliente 
- /etc/ssh/sshd_config --ojo este es el archivo de configuración del servidor
- /usr/local/bin/backup.sh

- /etc/mysql/mariadb.conf.d/50-server.cnf
- /etc/phpmyadmin/config-db.php
- /etc/phpmyadmin/config.inc.php
- /var/www/html/club/config/db.php


--------------------------------

# IP's

192.168.1.69 webserver
192.168.1.84 ftp
192.168.1.97 base de datos
192.168.50.1 DHCP



