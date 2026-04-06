# api-webforms

[![PHP](https://img.shields.io/badge/PHP-%5E8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/manual/en/)
[![GitHub issues](https://img.shields.io/github/issues/fr8train-sv/api-webforms)](https://github.com/fr8train-sv/api-webforms/issues)
[![poweredby](https://img.shields.io/badge/powered%20by-Slim4-green)](https://www.slimframework.com/docs/v4/)

MicroService powered by [Slim4 Framework](https://www.slimframework.com/).

## Installation

NGINX example server configuration:
```nginx
server {
    listen 80;
    listen [::]:80;

    server_name api-webforms.test; [CHANGE THIS]

    root [ROOT DIRECTORY - REPLACE THIS]/public;
    index index.php;

    charset utf-8;

    # Logging - Uncomment to enable, leave commented out to use nginx default
    # access_log /var/log/nginx/slim-api.access.log;
    # error_log /var/log/nginx/slim-api.error.log;

    # Security: Deny access to hidden files (except .well-known for SSL verification)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security: Deny access to sensitive files
    location ~ \.(htaccess|htpasswd|ini|log|sh|sql)$ {
        deny all;
    }

    # Main location block - Front Controller Pattern
    # All requests go to index.php unless the file exists
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        # Check that the PHP file exists before passing to PHP-FPM
        try_files $uri =404;

        # FastCGI settings
        fastcgi_split_path_info ^(.+\.php)(/.+)$;

        # [CHANGE THIS] PHP-FPM socket path
        # Common options:
        # - Unix socket: unix:/var/run/php/php8.4-fpm.sock (recommended)
        # - TCP socket: 127.0.0.1:9000
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;

        fastcgi_index index.php;

        # Include FastCGI parameters
        include fastcgi_params;

        # Set SCRIPT_FILENAME for PHP-FPM
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;

        # Pass request URI properly
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # Performance tuning
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }

}
```

Current project uses:
- msodbcsql18/noble Ubuntu 25.10 system driver
- PHP Extensions sqlsrv and pdo_sqlsrv

### Installing myodbcsql18 in Ubuntu 25.10
First,
```bash
sudo apt update
sudo apt install -y curl gnupg2 apt-transport-https
```
Once successful, then import the Microsoft GPG key.
```bash
curl https://packages.microsoft.com/keys/microsoft.asc | sudo gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg
```
Add the Microsoft repository to the list of sources.
```bash
echo "deb [arch=amd64,arm64,armhf signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/ubuntu/25.10/prod noble main" | sudo tee /etc/apt/sources.list.d/mssql-release.list
```
Update and install
```bash
sudo apt update
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev
```

### PHP Extensions sqlsrv and pdo_sqlsrv Install
Base PHP needs:
```bash
sudo apt install -y php8.4-dev php-pear php8.4-xml
sudo pecl install sqlsrv pdo_sqlsrv
```

Generate the .ini files and enable
```bash
echo "extension=sqlsrv.so" | sudo tee /etc/php/8.4/mods-available/sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/8.4/mods-available/pdo_sqlsrv.ini
sudo phpenmod sqlsrv pdo_sqlsrv
```

Restart services
```bash
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

## Post-Installation

**Please create a .env file at the root directory of the project.** 

```dotenv
DB_HOST=
DB_PORT=
DB_USERNAME=
DB_PASSWORD=
DB_DATABASE=
DB_DW_DATABASE=
```

If for some reason you get an error message indicating that a class in the code cannot be found, first try to reload the autoloaded classes through Composer.

```bash
composer dump-autoload
```



## Contributing
Pull requests are welcome from within Stellar Virtual Organization. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)