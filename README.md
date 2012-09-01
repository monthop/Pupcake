Pupcake --- a micro framework for PHP 5.3+
=======================================

##About Pupcake Framework
+ Pupcake is a minimal but extensible microframework for PHP 5.3+
+ Pupcake can be run in traditional web server such as Apache.
+ Pupcake can be run in command line with event based functionalities like Node.js by using the Node plugin together withg php-uv and php-httpparser extensions
+ For more detail usages on using pupcake in general and on traditional web servers, please see https://github.com/superjimpupcake/Pupcake/wiki/_pages
+ To see what pupcake can do like Node.js, check out this README page, the Node plugin is under actively development and will provide more features down the road

##Installation:

###If you plan to use it on Apache
#### install package "Pupcake/Pupcake" using composer (http://getcomposer.org/)
####.htaccess File for Apache
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/$1 [L]

###If you plan to use it like Node.js
#### install package "Pupcake/Pupcake" using composer (http://getcomposer.org/)
#### install sockets extension (http://www.php.net/manual/en/sockets.installation.php)
#### install php-uv and php-httpparser
    git clone https://github.com/chobie/php-uv.git --recursive
    cd php-uv/libuv
    make && cp uv.a libuv.a (my experience on both centos 64bit and ubuntu 64bit server is, we need to add -fPIC flag in config.m4)
    cd ..
    phpize
    ./configure
    make && make install (my experience on both centos64bit and ubuntu64bit  server is, we need to add -fPIC flag in config.m4)

    git clone https://github.com/chobie/php-httpparser.git --recursive
    cd php-httpparser
    phpize
    ./configure
    make && make install

    add following extensions to your php.ini
    extension=uv.so
    extension=httpparser.so


###Simple requests when running on Apache
#### For more details on running Pupcake in general and in traditional web server, please see https://github.com/superjimpupcake/Pupcake/wiki/_pages
```php
<?php
//Assuming this is public/index.php and the composer vendor directory is ../vendor

require_once __DIR__.'/../vendor/autoload.php';

$app = new Pupcake\Pupcake();

$app->get("date/:year/:month/:day", function($req, $res){
    $output = $req->params('year').'-'.$req->params('month').'-'.$req->params('day');
    $res->send($output);
});

$app->get("/hello/:name", function($req, $res){
  $res->send("hello ".$req->params('name')." in get");
});

$app->post("/hello/:name", function($req, $res){
  $res->send("hello ".$req->params('name')." in post");
});

$app->put("/hello/:name", function($req, $res){
  $res->send("hello ".$req->params('name')." in put");
});

$app->delete("/hello/:name", function($req, $res){
  $res->send("hello ".$req->params('name')." in delete");
});

/**
 * Multiple request methods for one route
 */
$app->map("/api/hello/:action", function($req, $res){
  $res->send("hello ".$req->params('action')." in get and post");
})->via('GET','POST');


$app->run();
```

### Using the node plugin: console.log
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor

require_once __DIR__.'/../vendor/autoload.php';

$app = new Pupcake\Pupcake();
$node = $app->usePlugin("Pupcake.Plugin.Node"); //here we import the node plugin
$console = $node->import("console");
$console->log("hello");
```
To run the code above, type php server/server.php

In the code above, we simply use the node plugin and then import the console module to output "hello" to the console.

### Using the node plugin: process.nextTick
In the script below, we define a dynamic method named "hello" in a Node plugin instance, then we use the process module to keep calling the hello method in every single "tick" 
in an async fashion.
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$app = new Pupcake\Pupcake();
$node = $app->usePlugin("Pupcake.Plugin.Node");
$node->method("hello", function() use ($node){
  $console = $node->import("console"); //use the console module
  $console->log("doing some tasks");
  $process = $node->import("process"); //use the process module
  $process->nextTick(function() use ($node){
    $node->hello(); 
  });
});

$node->hello();
```

### Using the node plugin: http.createServer
In the example below, we mimic the node.js's http server creation process and send "Hello World" to the browser
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$app = new Pupcake\Pupcake();
$node = $app->usePlugin("Pupcake.Plugin.Node");

$console = $node->import("console");
$http = $node->import("http");

$http->createServer(function($req, $res) {
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("Hello World\n");
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');
```
Simply run php server/server.php and you can see the result from http://127.0.0.1:1337/

### Using the node plugin: http.createServer and multiple servers on different ports
In the example below, we created 3 different server instances to serve port 1337, 9000 and 9001, all in one single event loop
```php
<?php
//Assuming this is server/server.php and the composer vendor directory is ../vendor
require_once __DIR__.'/../vendor/autoload.php';

$app = new Pupcake\Pupcake();
$node = $app->usePlugin("Pupcake.Plugin.Node");

$console = $node->import("console");
$http = $node->import("http");

$http->createServer(function($req, $res) use ($node){
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("Hello World On Port 1337\n");
})->listen(1337, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:1337/');

$http->createServer(function($req, $res) {
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("Hello World On Port 9000\n");
})->listen(9000, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:9000/');

$http->createServer(function($req, $res) {
  $res->writeHead(200, array('Content-Type' => 'text/plain'));
  $res->end("Hello World On Port 9001\n");
})->listen(9001, '127.0.0.1');
$console->log('Server running at http://127.0.0.1:9001/');
```
