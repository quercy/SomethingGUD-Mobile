<?php

require 'vendor/autoload.php';

require 'model.php';

$model = new SG_Model();
$app = new \Slim\Slim();
// Main route is to return the client
$app->get('/', function () {
    readfile("main.html");
});
$app->get('/index', function () {
    readfile("main.html");
});

$app->get('/test', function() use ($model) {
    $model->test();
});

// otherwise, it's an api call
// @todo force https
$app->get('/api/products', function() use ($model) {
    $products = $model->listAllProducts();
    echo json_encode($products);
});

$app->post('/api/authenticate', function() use ($model) {
    $key = isset($_POST['session_key']) ? $_POST['session_key'] : '';
    if( $key != '') {
        return $model->authenticateKey($key);
    }
    else {
        return false;
    }
});

$app->post('/api/authenticate/login', function() use($model) {
    $user_email = isset($_POST['user_email']) ?$_POST['user_email'] :'';
    $password = isset($_POST['password']) ? $_POST['password'] :'';
//    echo("$user_email, $password");
    var_dump ($model->login($user_email, md5($password)));
});

$app->post('/api/authenticate/logout', function() use($model) {
    $key = isset($_POST['session_key']) ? $_POST['session_key'] : '';
    if($key != '') {
        $model->destroyKey($key);
        return true;
    }
    else {
        return false;
    }
});


$app->run();

?>