Web Admin Installation & Configuration
======================================

## Requirement
* Web server with URL rewriting
* PHP 8.2.x or later with extensions: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Intl,
Mbstring, MySQL native driver, OpenSSL, PCRE, PDO, Session, Tokenizer, XML
* Database server: MariaDB 10.10+ or MySQL 5.7+ or PostgreSQL 11.0+ or SQL Server 2017+
* (optionals) Redis/Valkey, Memcache for caching

## Deployment
When you're ready to deploy your Laravel application to production,
there are some important things you can do to make sure your
application is running as efficiently as possible.
In this document, we'll cover some great starting points
for making sure your Laravel application is deployed properly.

### Nginx Configuration
```sh
vi /etc/nginx/conf.d/virtual.host.conf
```

```
server {
    listen 80;
    listen [::]:80;
    server_name example.com;
    root /srv/example.com/public;
 
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
 
    index index.php;
 
    charset utf-8;
 
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
 
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
 
    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/php8.2.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
 
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Installation
Install composer & dependencies
```sh
dnf -y install composer
composer install --optimize-autoloader --no-dev --prefer-dist
```

Copy over example configuration.
Don't forget to set the database config in .env.example correctly
```sh
cp .env.example .env
```

Environment variables:
| Name                 | Description             |
|----------------------|-------------------------|
| APP_NAME             | Application Name        |
| APP_ENV              | Application Environment |
| APP_KEY              | Encryption Key          |
| APP_DEBUG            | Application Debug Mode  |
| APP_TIMEZONE         | Application Timezone    |
| APP_URL              | Application URL         |
| APP_DOMAIN           | Application Domain      |
| FORCE_HTTPS          | Force Https             |
| DB_CONNECTION        | Database Connection     |
| DB_HOST              | Database Host           |
| DB_PORT              | Database Port           |
| DB_DATABASE          | Database Name           |
| DB_USERNAME          | Database User Name      |
| DB_PASSWORD          | Database User Password  |
| MAIL_MAILER          | Default Mailer          |
| MAIL_HOST            | Mail Host Name          |
| MAIL_PORT            | Mail Port               |
| MAIL_ENCRYPTION      | Mail Encryption         |
| MAIL_USERNAME        | Mail User Name          |
| MAIL_PASSWORD        | Mail User Password      |
| MAIL_EHLO_DOMAIN     | Mail Ehlo Domain        |
| MAIL_QUEUE_NAME      | Mail Queue Name         |
| MAIL_SHOULD_QUEUE    | Message Should Queue    |
| MAIL_TRACK_CLICK     | Mail Track Click        |
| QUEUE_CONNECTION     | Queue Connection Name   |
| API_REQUEST_RATE     | Api Request Rate        |
| POLICY_SERVER_NAME   | Policy Server Name      |
| POLICY_SERVER_WORKER | Policy Server Worker    |
| POLICY_DAEMONIZE     | Policy Daemonize        |
| POLICY_LISTEN_HOST   | Policy Listen Host      |
| POLICY_LISTEN_PORT   | Policy Listen Port      |
| SENDER_TRANSPORT     | Sender Transport Map    |

Generate the application key & passport keys. Re-cache.
```sh
php artisan key:generate
php artisan passport:keys
php artisan config:cache
```

Run database migrations.
```sh
php artisan migrate
```

Create a new user account:
```sh
php artisan make:filament-user
```

### Running Policy Service as a Systemd Service
Create a new file in the “/etc/systemd/system/” directory with a .service extension,
such as policy.service”.
```sh
vi /lib/systemd/system/policy.service
```
Add the following content to the file:
```
[Unit]
Description=Email mass delivery policy service
After=network.target

[Service]
User=www-data
Group=www-data
Restart=on-failure
WorkingDirectory=/path/to/emd
ExecStart=php artisan policy:listen start

[Install]
WantedBy=multi-user.target
```
Set `User` & `Group` with your web server user & group.
Set `WorkingDirectory` with your Laravel application directory.

Enabling & starting the policy service
```sh
systemctl daemon-reload
systemctl enable policy.service
systemctl start policy.service
```

### Running Queue Worker as a Systemd Service
Create a new file in the “/etc/systemd/system/” directory with a .service extension,
such as queue.service”.
```sh
vi /lib/systemd/system/queue.service
```
Add the following content to the file:
```
[Unit]
Description=Email mass delivery queue service
After=network.target

[Service]
User=www-data
Group=www-data
Restart=on-failure
WorkingDirectory=/path/to/emd
ExecStart=php artisan queue:work

[Install]
WantedBy=multi-user.target
```
Set `User` & `Group` with your web server user & group.
Set `WorkingDirectory` with your Laravel application directory.

Enabling & starting the policy service
```sh
systemctl daemon-reload
systemctl enable queue.service
systemctl start queue.service
```
