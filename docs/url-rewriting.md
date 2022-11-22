# URL Rewriting
We're trying to redirect every request to a single root file. In most cases, this file is named `index.php` and acts as the entry point to our application.

This can be achieved by editing the default routing of your webserver most commonly by editing the `.htaccess` file.

<hr>

## Apache .htaccess

In the [[readme|introduction]] we told you that even we don't really know what's going on with the `.htaccess` configuration.

Well... Turns out we kinda' lied to you...

```htaccess
RewriteEngine on

RewriteRule ^(?!index)(.*) /index/$1 [L]

RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

What really happens, is that we're trying to capture every request made, which does not already include the `/index/` prefix so that we don't end up in an endless loop.
We then redirect every request to our `index.php` file where our App is located.

The redirect appends the actual route that was searched for at the end of our request URL and sends the client to our `index.php` file.

Sometimes the `Authorization` header gets messed up, that's why we're making sure it is included in our redirect.

<hr>

## Nginx - nginx.conf

Everything that works with the .htaccess file also works with Nginx.

```nginx
try_files $uri /index.php
```

This will redirect the client to our `index.php` if the file was not found on the server.

