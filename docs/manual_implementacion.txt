# Manual de Implementación — SIGEINV
## Sistema de Gestión de Inventario Tecnológico
### Plataforma: Linux Debian 11+ (Bullseye/Bookworm)

---

## 1. Requisitos del Sistema

### 1.1 Hardware Mínimo Recomendado

| Componente | Mínimo | Recomendado |
|---|---|---|
| CPU | 2 núcleos 2.0 GHz | 4 núcleos 2.5 GHz |
| RAM | 2 GB | 4 GB |
| Almacenamiento | 20 GB SSD | 50 GB SSD |
| Red | 100 Mbps | 1 Gbps |

### 1.2 Software Requerido

| Software | Versión Mínima | Instalación |
|---|---|---|
| OS | Debian 11 (Bullseye) o Debian 12 (Bookworm) | — |
| PHP | 8.2+ (recomendado 8.3) | apt / ondrej PPA |
| MariaDB | 10.6+ | apt |
| Nginx | 1.18+ | apt |
| Composer | 2.x | Manual |
| Node.js | 18 LTS | NodeSource |
| Git | 2.x | apt |

---

## 2. Preparación del Servidor

### 2.1 Actualizar el Sistema

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip software-properties-common
```

### 2.2 Instalar PHP 8.3 y Extensiones

```bash
# Agregar repositorio de PHP (Ondřej Surý)
sudo apt install -y lsb-release apt-transport-https ca-certificates
sudo wget -O /etc/apt/trusted.gpg.d/php.gpg \
  https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" \
  | sudo tee /etc/apt/sources.list.d/php.list
sudo apt update

# Instalar PHP 8.3 y extensiones requeridas
sudo apt install -y \
  php8.3 \
  php8.3-cli \
  php8.3-fpm \
  php8.3-mysql \
  php8.3-mbstring \
  php8.3-xml \
  php8.3-curl \
  php8.3-zip \
  php8.3-bcmath \
  php8.3-intl \
  php8.3-gd \
  php8.3-tokenizer \
  php8.3-fileinfo

# Verificar instalación
php -v
```

### 2.3 Instalar MariaDB

```bash
sudo apt install -y mariadb-server mariadb-client

# Iniciar y habilitar servicio
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Asegurar instalación
sudo mysql_secure_installation
# Responder:
#   Set root password? → Y → ingresar contraseña segura
#   Remove anonymous users? → Y
#   Disallow root login remotely? → Y
#   Remove test database? → Y
#   Reload privilege tables? → Y
```

### 2.4 Crear Base de Datos y Usuario

```bash
sudo mysql -u root -p
```

```sql
-- Dentro de MariaDB:
CREATE DATABASE sigeinvGDC CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sigeinv_user'@'localhost' IDENTIFIED BY 'TuContraseñaSegura123!';
GRANT ALL PRIVILEGES ON sigeinvGDC.* TO 'sigeinv_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2.5 Instalar Nginx

```bash
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2.6 Instalar Composer

```bash
# Descargar e instalar Composer globalmente
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verificar instalación
composer --version
```

### 2.7 Instalar Node.js 20 LTS

```bash
# Agregar repositorio NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verificar instalación
node -v
npm -v
```

---

## 3. Instalación de SIGEINV

### 3.1 Crear Usuario del Sistema (Recomendado)

```bash
# Crear usuario dedicado para la aplicación
sudo adduser --system --group --no-create-home sigeinv
sudo usermod -aG www-data sigeinv
```

### 3.2 Clonar el Repositorio

```bash
# Directorio de aplicaciones web
sudo mkdir -p /var/www
cd /var/www

# Clonar el proyecto
sudo git clone https://github.com/tu-organizacion/sigeinvGDC.git sigeinvGDC
cd sigeinvGDC

# Asignar propietario
sudo chown -R www-data:www-data /var/www/sigeinvGDC
```

### 3.3 Instalar Dependencias PHP

```bash
cd /var/www/sigeinvGDC

# Instalar dependencias de producción (sin paquetes de desarrollo)
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
```

### 3.4 Configurar el Archivo .env

```bash
# Copiar plantilla de entorno
sudo -u www-data cp .env.example .env

# Editar configuración
sudo nano .env
```

**Valores a configurar en `.env`:**

```ini
APP_NAME=SIGEINV
APP_ENV=production
APP_DEBUG=false
APP_URL=http://tu-dominio-o-ip

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigeinvGDC
DB_USERNAME=sigeinv_user
DB_PASSWORD=TuContraseñaSegura123!

# Configuración Organizacional
ORG_NOMBRE="NOMBRE DE TU ORGANIZACIÓN"
ORG_DEPENDENCIA="Nombre de la Dependencia"
DOMINIO_ORGANIZACION="@tudominio.gob"

# Sesiones y Caché
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### 3.5 Generar Clave de Aplicación

```bash
sudo -u www-data php artisan key:generate
```

### 3.6 Instalar Dependencias JS y Compilar Assets

```bash
# Instalar Node modules
sudo -u www-data npm install

# Compilar assets para producción
sudo -u www-data npm run build
```

### 3.7 Ejecutar Migraciones y Seeders

```bash
# Crear todas las tablas
sudo -u www-data php artisan migrate --force

# Poblar datos iniciales (roles, permisos, configuraciones)
sudo -u www-data php artisan db:seed --force
```

### 3.8 Crear Enlace de Almacenamiento

```bash
# Necesario para que los avatares de usuario sean accesibles
sudo -u www-data php artisan storage:link
```

### 3.9 Configurar Permisos de Directorios

```bash
cd /var/www/sigeinvGDC

# Dar permisos correctos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Verificar
ls -la storage/
```

### 3.10 Optimizar para Producción

```bash
sudo -u www-data php artisan optimize
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan config:cache
```

---

## 4. Configuración de Nginx

### 4.1 Crear Virtual Host

```bash
sudo nano /etc/nginx/sites-available/sigeinvGDC
```

**Contenido del archivo de configuración:**

```nginx
server {
    listen 80;
    listen [::]:80;

    # Cambiar por tu dominio o IP del servidor
    server_name tu-dominio.com www.tu-dominio.com;

    root /var/www/sigeinvGDC/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/sigeinv_access.log;
    error_log  /var/log/nginx/sigeinv_error.log;

    # Tamaño máximo de subida (para avatares)
    client_max_body_size 10M;

    # Laravel: redirigir todo al index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Procesamiento PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Denegar acceso a archivos ocultos
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache de assets estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 4.2 Activar el Sitio

```bash
# Crear enlace simbólico
sudo ln -s /etc/nginx/sites-available/sigeinvGDC \
           /etc/nginx/sites-enabled/sigeinvGDC

# Deshabilitar sitio por defecto (opcional)
sudo rm /etc/nginx/sites-enabled/default

# Verificar configuración de Nginx
sudo nginx -t

# Si no hay errores, recargar Nginx
sudo systemctl reload nginx
```

### 4.3 Configurar PHP-FPM

```bash
# Verificar que PHP-FPM está activo
sudo systemctl status php8.3-fpm

# Si no está activo, iniciarlo
sudo systemctl start php8.3-fpm
sudo systemctl enable php8.3-fpm
```

---

## 5. Configurar SSL con Let's Encrypt (Producción)

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado SSL (reemplazar con tu dominio real)
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Verificar renovación automática
sudo certbot renew --dry-run
```

El archivo Nginx se actualizará automáticamente para usar HTTPS. Actualiza también `.env`:
```ini
APP_URL=https://tu-dominio.com
```

---

## 6. Configuración del Firewall (UFW)

```bash
# Instalar UFW si no está presente
sudo apt install -y ufw

# Permitir SSH (¡IMPORTANTE: hacer esto ANTES de habilitar UFW!)
sudo ufw allow OpenSSH

# Permitir HTTP y HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Habilitar firewall
sudo ufw enable

# Verificar reglas
sudo ufw status verbose
```

---

## 7. Creación del Super Administrador

Una vez instalado el sistema, el primer usuario debe crearse via Artisan o directamente en la base de datos. Se recomienda vía Tinker:

```bash
cd /var/www/sigeinvGDC
sudo -u www-data php artisan tinker
```

```php
// Dentro de Tinker:
$user = App\Models\User::create([
    'name'     => 'Administrador Principal',
    'email'    => 'admin@tudominio.com',
    'username' => 'admin',
    'password' => bcrypt('ContraseñaSegura123!'),
    'activo'   => true,
]);

$user->assignRole('super-admin');

echo "Usuario creado: " . $user->id;
exit;
```

---

## 8. Configurar Cron Job (Scheduler de Laravel)

```bash
# Editar el crontab del usuario www-data
sudo crontab -u www-data -e

# Agregar esta línea:
* * * * * cd /var/www/sigeinvGDC && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Verificación Post-Instalación

### 9.1 Checklist de Verificación

```bash
# 1. Verificar que Nginx responde
curl -I http://localhost

# 2. Verificar conexión a la base de datos
cd /var/www/sigeinvGDC
sudo -u www-data php artisan migrate:status

# 3. Verificar que el storage link existe
ls -la public/storage

# 4. Verificar permisos
ls -la storage/logs/
ls -la bootstrap/cache/

# 5. Verificar logs de Nginx
sudo tail -f /var/log/nginx/sigeinv_error.log

# 6. Verificar logs de Laravel
sudo tail -f /var/www/sigeinvGDC/storage/logs/laravel.log
```

### 9.2 Prueba Funcional

1. Abrir el navegador en `http://tu-dominio-o-ip`
2. Verificar que aparece la pantalla de login con el logo
3. Iniciar sesión con las credenciales del super-admin creado
4. Navegar al Dashboard y verificar que los módulos cargan correctamente

---

## 10. Backup y Recuperación

### 10.1 Script de Backup Automático

Crear el archivo `/usr/local/bin/sigeinv-backup.sh`:

```bash
sudo nano /usr/local/bin/sigeinv-backup.sh
```

```bash
#!/bin/bash
# Script de Backup SIGEINV
FECHA=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/opt/backups/sigeinv"
DB_NAME="sigeinvGDC"
DB_USER="sigeinv_user"
DB_PASS="TuContraseñaSegura123!"
APP_DIR="/var/www/sigeinvGDC"

mkdir -p "$BACKUP_DIR"

# Backup de la base de datos
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  | gzip > "$BACKUP_DIR/db_${FECHA}.sql.gz"

# Backup de archivos de storage (avatares)
tar -czf "$BACKUP_DIR/storage_${FECHA}.tar.gz" \
  -C "$APP_DIR" storage/app/public/

# Eliminar backups más antiguos de 30 días
find "$BACKUP_DIR" -type f -mtime +30 -delete

echo "[$(date)] Backup completado: $FECHA"
```

```bash
# Hacer ejecutable el script
sudo chmod +x /usr/local/bin/sigeinv-backup.sh

# Programar backup diario a las 2:00 AM
sudo crontab -e
# Agregar:
0 2 * * * /usr/local/bin/sigeinv-backup.sh >> /var/log/sigeinv-backup.log 2>&1
```

### 10.2 Restaurar desde Backup

```bash
# Restaurar base de datos
gunzip < /opt/backups/sigeinv/db_YYYYMMDD_HHmmss.sql.gz \
  | mysql -u sigeinv_user -p sigeinvGDC

# Restaurar archivos de storage
tar -xzf /opt/backups/sigeinv/storage_YYYYMMDD_HHmmss.tar.gz \
  -C /var/www/sigeinvGDC/
```

---

## 11. Actualización del Sistema

```bash
cd /var/www/sigeinvGDC

# 1. Activar modo mantenimiento
sudo -u www-data php artisan down --message="Actualización en progreso..."

# 2. Descargar cambios del repositorio
sudo -u www-data git pull origin main

# 3. Actualizar dependencias PHP
sudo -u www-data composer install --no-dev --optimize-autoloader

# 4. Actualizar dependencias JS y recompilar
sudo -u www-data npm install
sudo -u www-data npm run build

# 5. Ejecutar nuevas migraciones
sudo -u www-data php artisan migrate --force

# 6. Limpiar y regenerar cachés
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan optimize

# 7. Desactivar modo mantenimiento
sudo -u www-data php artisan up
```

---

## 12. Solución de Problemas Comunes

### Error 500 / Página en blanco

```bash
# Revisar logs de Laravel
sudo tail -n 50 /var/www/sigeinvGDC/storage/logs/laravel.log

# Verificar permisos
sudo chown -R www-data:www-data /var/www/sigeinvGDC/storage
sudo chmod -R 775 /var/www/sigeinvGDC/storage

# Limpiar caché
cd /var/www/sigeinvGDC
sudo -u www-data php artisan optimize:clear
```

### Error de Conexión a Base de Datos

```bash
# Verificar que MariaDB está corriendo
sudo systemctl status mariadb

# Probar conexión manual
mysql -u sigeinv_user -p sigeinvGDC -e "SELECT 1;"

# Verificar credenciales en .env
cat /var/www/sigeinvGDC/.env | grep DB_
```

### Assets CSS/JS no cargan (404)

```bash
# Recompilar assets
cd /var/www/sigeinvGDC
sudo -u www-data npm run build

# Verificar que el directorio public/build existe
ls -la public/build/
```

### Avatares no se muestran

```bash
# Verificar que existe el symlink
ls -la /var/www/sigeinvGDC/public/storage

# Si no existe, recrear
sudo -u www-data php artisan storage:link
```

### PHP-FPM no responde

```bash
# Reiniciar PHP-FPM
sudo systemctl restart php8.3-fpm

# Verificar el socket
ls -la /run/php/php8.3-fpm.sock
```

---

## 13. Monitoreo del Sistema

### 13.1 Logs Importantes

| Log | Ubicación |
|---|---|
| Laravel (errores app) | `/var/www/sigeinvGDC/storage/logs/laravel.log` |
| Nginx (accesos) | `/var/log/nginx/sigeinv_access.log` |
| Nginx (errores) | `/var/log/nginx/sigeinv_error.log` |
| PHP-FPM | `/var/log/php8.3-fpm.log` |
| MariaDB | `/var/log/mysql/error.log` |
| Backups | `/var/log/sigeinv-backup.log` |

### 13.2 Comandos de Monitoreo Rápido

```bash
# Estado general de servicios
sudo systemctl status nginx php8.3-fpm mariadb

# Uso de recursos
htop
df -h   # Espacio en disco
free -h # Memoria RAM

# Conexiones activas a Nginx
sudo ss -tlnp | grep :80

# Procesos PHP activos
ps aux | grep php
```

---

*Manual de Implementación SIGEINV v1.0 — Plataforma Debian 11+ (Bullseye/Bookworm)*
