Web Admin Installation & Configuration
======================================

## Requirement
* Web server with URL rewriting
* PHP 8.2.x or later with extensions: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Intl,
Mbstring, MySQL native driver, OpenSSL, PCRE, PDO, Session, Tokenizer, XML
* Database server: MariaDB 10.10+ or MySQL 5.7+ or PostgreSQL 11.0+ or SQL Server 2017+
* [Laravel](https://laravel.com) framework version 11.x
* [Filament](https://filamentphp.com) Admin Panel version 3.x

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

```nginx
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
| Name                 | Description             | Default                |
|----------------------|-------------------------|------------------------|
| APP_NAME             | Application Name        | SMTP API               |
| APP_ENV              | Application Environment | production             |
| APP_KEY              | Encryption Key          | null                   |
| APP_DEBUG            | Application Debug Mode  | false                  |
| APP_TIMEZONE         | Application Timezone    | Asia/Ho_Chi_Minh       |
| APP_URL              | Application URL         | http://localhost       |
| APP_DOMAIN           | Application Domain      | yourdomain.com         |
| FORCE_HTTPS          | Force Https             | false                  |
| DB_CONNECTION        | Database Connection     | mysql                  |
| DB_HOST              | Database Host           | 127.0.0.1              |
| DB_PORT              | Database Port           | 3306                   |
| DB_DATABASE          | Database Name           | laravel                |
| DB_USERNAME          | Database User Name      | root                   |
| DB_PASSWORD          | Database User Password  | null                   |
| MAIL_MAILER          | Default Mailer          | smtp                   |
| MAIL_HOST            | Mail Host Name          | 127.0.0.1              |
| MAIL_PORT            | Mail Port               | 587                    |
| MAIL_ENCRYPTION      | Mail Encryption         | tls                    |
| MAIL_USERNAME        | Mail User Name          | null                   |
| MAIL_PASSWORD        | Mail User Password      | null                   |
| MAIL_EHLO_DOMAIN     | Mail Ehlo Domain        | null                   |
| MAIL_QUEUE_NAME      | Mail Queue Name         | default                |
| MAIL_SHOULD_QUEUE    | Message Should Queue    | true                   |
| MAIL_TRACK_CLICK     | Mail Track Click        | false                  |
| QUEUE_CONNECTION     | Queue Connection Name   | sync                   |
| API_REQUEST_RATE     | Api Request Rate        | 600                    |
| POLICY_SERVER_NAME   | Policy Server Name      | Access Policy          |
| POLICY_SERVER_WORKER | Policy Server Worker    | 4                      |
| POLICY_DAEMONIZE     | Policy Daemonize        | true                   |
| POLICY_LISTEN_HOST   | Policy Listen Host      | 0.0.0.0                |
| POLICY_LISTEN_PORT   | Policy Listen Port      | 54321                  |

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
vi /etc/systemd/system/policy.service 
```
Add the following content to the file:
```sh
[Unit]
Description=Email mass delivery policy service
After=network.target

[Service]
User=www-data
Group=www-data
Restart=on-failure
WorkingDirectory=/path/to/emd
ExecStart=php artisan policy:listen start
ExecStop=php artisan policy:listen stop
ExecReload=php artisan policy:listen restart

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
vi /etc/systemd/system/queue.service 
```
Add the following content to the file:
```sh
[Unit]
Description=Email mass delivery queue service
After=network.target

[Service]
User=www-data
Group=www-data
Restart=on-failure
WorkingDirectory=/path/to/emd
ExecStart=php artisan queue:work --env=production

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
