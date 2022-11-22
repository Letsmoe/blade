---
title: "Introduction - Blade"
author: "Moritz Utcke"
file: {{__FILE__}}
---

# Introduction

## What is Blade?
Blade is a minimal and lightweight PHP framework for creating simple, but powerful web apps and APIs quickly and easily.

Blade is centered around developer experience and usability, relying on functional design patterns and minimal overhead.

## Getting started
Our official guide assumes that you already have a basic understanding of **PHP** and its core principles. If that is not the case we highly advise you to take a look at the [W3Schools PHP Tutorial](https://www.w3schools.com/php/default.asp).

### Installation
Installing Blade is very straightforward, if you don't already have `composer` installed, make sure to follow the [installation guide](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) on their website.

Once composer is installed you can create a new project.

- Navigate to your favorite directory and create a new folder.
- Open a command line at the folder location.
- Install Blade via composer.

If you are already in a project directory you can simply type

```sh
composer require letsmoe/blade
```

this will create a new `composer.json` if it didn't exist already and add the Blade package to your dependency list.
And that's basically it. A `vendor` folder has been created which we are going to use in the following example, take a look at it and you'll be ready to go in no time!

<hr>

## Hello World example
Blade uses a `functional design pattern`, meaning every component of Blade is encapsulated in a globally accessible function.

**index.php**
```php
<?php

require __DIR__ . '/vendor/autoload.php';

app()->get('/', function (Request $request, Response $response) {
	return $response->plain("Hello World!");
});

app()->run();
```

**.htaccess**
```htaccess
RewriteEngine on

RewriteRule ^(?!index)(.*) /index/$1 [L]

RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

And there you go! This is your first Blade App...
Don't get confused by the `.htaccess` file, we don't really understand what's going on here ourselves 😅

<hr>

## Ready for more?
This has been the introduction to the most basic features Blade has to offer. The rest of this guide will cover all the features in much finer detail.

Have fun reading it!

- Maybe check out how [[url-rewriting|URL Rewriting]] works.