<?php

require 'vendor/autoload.php';
require 'database.php';
$database = new SG_Database();
$app = new \Slim\Slim();
// Main route is to return the client
$app->get('/', function () {
    readfile("main.html");
});
$app->get('/index', function () {
    readfile("main.html");
});

$app->get('/test', function() use ($database) {
    $database->test();
});

// otherwise, it's an api call
// @todo force https
$app->get('/api/products', function() use ($database) {
    $products = $database->listAllProducts();
    echo json_encode($products);
});


$app->run();

?>