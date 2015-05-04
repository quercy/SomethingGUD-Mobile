<?php

require 'vendor/autoload.php';

require 'model.php';

$model = new SG_Model();
$app = new \Slim\Slim(['debug' => true]);
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
        return null;
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

$app->post('/api/cart', function() use ($model, $app) {
//    var_dump($app->request->post('cart_data'));
    $key = $app->getCookie('session');
    $cart = $app->request->post('cart_data');
//    echo($cart);
    if(!is_null($key)) {
        if($model->authenticateKey($key) != false) {
            $model->updateCart($key, $cart);
            echo json_encode(true);
        }
        else {
            return null;
        }
    }
    else {
        return null;
    }
});

$app->get('/api/cart', function() use ($model, $app) {
//    var_dump($app->request->post('cart_data'));
    $key = $app->getCookie('session');
//    echo($cart);
    if(!is_null($key)) {
        if($model->authenticateKey($key) != false) {
            $cart = $model->getCart($key);
            echo json_encode($cart);
        }
        else {
            return null;
        }
    }
    else {
        return null;
    }
});

$app->post('/api/users', function() use ($model, $app) {
    $data = [
        'user_email' => $app->request->post('user_email'),
        'password' => $app->request->post('password')   ,
        'first_name' => $app->request->post('first_name'),
        'last_name' => $app->request->post('last_name'),
        'zip' => $app->request->post('zip'),
    ];
//    var_dump($app->request->post);
    if($data['user_email'] != '' && !is_null($data['user_email']))
        echo json_encode($model->addUser($data));
    else {
        return null;
    }
});

$app->get('/api/user', function() use ($model, $app) {
    $key = $app->getCookie('session');
//    echo($cart);
    if(!is_null($key)) {
        if($model->authenticateKey($key) != false) {
            $ud = $model->getUserInfo($key);
            echo json_encode($ud);
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