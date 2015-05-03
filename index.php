<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();
// Main route is to return the client
$app->get('/', function () {
    readfile("main.html");
});
$app->get('/index', function () {
    readfile("main.html");
});

// otherwise, it's an api call

$app->run();

?>