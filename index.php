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

$app->post('/api/authenticate', function() use ($model, $app) {
    $key = $app->request->post('session_key');
    if(!is_null($key)) {
        return $model->authenticateKey($key);
    }
    else {
        return false;
    }
});

$app->post('/api/authenticate/login', function() use($model, $app) {
    $user_email = $app->request->post('username');
    $password = $app->request->post('password');
    echo json_encode($model->login($user_email, md5($password)));
});

$app->post('/api/authenticate/logout', function() use($model, $app) {
    $key = $app->getCookie('session');
    if(!is_null($key)) {
        $model->destroyKey($key);
        return true;
    }
    else {
        return false;
    }
});

$app->get('/api/products', function() use ($model, $app) {
    $key = $app->getCookie('session');
    if(!is_null($key)) {
        if($model->authenticateKey($key) != false) {
            $products = $model->listAllProducts();
            echo json_encode($products);
        }
        else {
            return null;
        }
    }
    else {
            return null;
    }

});


$app->get('/api/products/categories', function() use ($model, $app) {
    $key = $app->getCookie('session');
    if(!is_null($key)) {
        if($model->authenticateKey($key) != false) {
            $categories = $model->getCategories();
            echo json_encode($categories);
        }
        else {
            return null;
        }
    }
    else {
        return null;
    }
});

$app->get('/api/products/:id', function($id) use ($model, $app) {
    $key = $app->getCookie('session');
    if(!is_null($key)) {
        if($model->authenticateKey($key) != false) {
            $product = $model->getProduct($id);
            echo json_encode($product);
        }
        else {
            return null;
        }
    }
    else {
        return null;
    }
});




$app->run();

?>