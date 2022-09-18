# Blade Router
Blade is a fully featured API framework for building web applications that require fast and lightweight development.

## Installation
Blade can be installed via **composer** using the following command
```bash
composer require letsmoe/blade
```

Once it has been installed, you can include the composer **autoload** script in your application like so:
```php
include_once "vendor/autoload.php";
```

## Usage
Blade provides multiple methods for loading a new [App](#app).
Since Blade relies on a specific form of htaccess, we recommend you use this template:
```htaccess
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . /your/path/to/index.php [L]
```


## App
Blade comes preloaded with an `App` class which provides all the methods you need to specify routes in your application.

