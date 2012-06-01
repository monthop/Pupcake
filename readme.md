Pupcake --- a micro framework for PHP 5.3+
=======================================

##Usage:

###Simple get,post,put,delete requests
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->get("/hello/:name", function($name){
  return "hello ".$name." in get";
});

$app->post("/hello/:name", function($name){
  return "hello ".$name." in post";
});

$app->put("/hello/:name", function($name){
  return "hello ".$name." in put";
});

$app->delete("/hello/:name", function($name){
  return "hello ".$name." in delete";
});

$app->run();
```

###Multiple request methods for one route
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->map("/hello/:name", function($name){
  return "hello ".$name." in get and post";
})->via('GET','POST');

$app->run();
```


###Request redirection
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->post("/hello/:name", function($name) use ($app) {
  $app->redirect("/test");
});

$app->run();
```

###Request forwarding (internal request)
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->get("/hello/:name", function($name){
  return $name;
});

$app->post("/hello/:name", function($name){
  return "posting $name to hello";
});

$app->get("test", function() use ($app){
 return $app->redirect("test2");
});

$app->any("date/:year/:month/:day", function($year, $month, $day){
  return $year."-".$month."-".$day;
});

$app->get("/test2", function(){
  return "testing 2";
});

$app->get("test_internal", function() use ($app) {
  $content = "";
  $content .= $app->forward("POST", "hello/world")."<br/>";
  $content .= $app->forward("GET", "hello/world2")."<br/>";
  $content .= $app->forward("GET", "hello/world3")."<br/>";
  $content .= $app->forward("GET", "test")."<br/>";
  $content .= $app->forward("POST", "date/2012/05/30")."<br/>";
  return $content;
});

$app->run();
```

###Custom request-not-found handler
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->notFound(function(){
    return "not found any routes!";
});

$app->run();
```

###Catch any requests
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->any(":path", function($path){
    return "the current path is ".$path;
});

$app->run();
```

###Request type detection in internal and external request
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->post('api/me/update', function() use ($app) {
    return $app->getRequestType();
});

$app->get('test', function() use ($app) {
    return $app->getRequestType().":".$app->forward('POST','api/me/update');
});

$app->run();
```

###Advance Event handling --- detect request not found
```php
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

/**
 * This is the same as 
 * $app->notFound(function(){
 *   return "request not found handler";
 * });
 */

$app->on('system.request.notfound', function(){
    return "request not found handler";
});

$app->run();
```

###Advance Event Handling --- detect system error
```php
<?php

require "pupcake.php";

$app = new \Pupcake\Pupcake();

/**
 * By defining custom callback for system.error.detected event, we can build a custom error handling system
 */
$app->on('system.error.detected', function($severity, $message, $filepath, $line){
    print "$message in $filepath at line #$line<br/>";
});

print $output; //undefined variable

$app->run();
```

###Advance Event Handling --- custom response output
<?php
require "pupcake.php";

$app = new \Pupcake\Pupcake();

$app->get("/hello/:name", function($name){
  return $name;
});

$app->on('system.request.found', function($callback, $params){
    return "prepend outputs".call_user_func_array($callback, $params);
});

$app->run();
```
