<?php

require 'vendor/autoload.php';
require 'model.php';


/**
 * Instantiate model and routes
 */
$model = new SG_Model();
$app = new \Slim\Slim(['debug' => false]);


// Root and /index return the client app
$app->get('/', function () {
    readfile("main.html");
});
$app->get('/index', function () {
    readfile("main.html");
});

//$app->get('/api/test', function() use ($model, $app) {
//    $app->halt(200);
//});


/**
 * Cookie authentication middleware. Halts the application if not authenticated.
 * @param $app
 * @param $model
 * @return $auth_key
 */
$cookie_auth = function ($app, $model) {
    $sess_key = $app->getCookie('session');
    if(!is_null($sess_key)) {
        $auth_key = $model->authenticateKey($sess_key);
        if($auth_key != null)
            return $auth_key;
        else
            $app->halt(403);
            return null;
    }
    else {
        $app->halt(403);
        return null;
    }
};

/**
 * API routes
 */

$app->post('/api/authenticate', function() use ($model, $app, $cookie_auth) {
    echo ($cookie_auth($app,$model)); // returns the user's session key if successful; otherwise 403
});

$app->post('/api/authenticate/login', function() use($model, $app) {
    $user_email = $app->request->post('username');
    $password = $app->request->post('password');
    $response = $model->login($user_email, $password);
    if($response !== false) {
        echo json_encode($response);
    }
    else {
        $app->halt(403);
    }
});

$app->post('/api/authenticate/logout', function() use($model, $app, $cookie_auth) {
    $key = $cookie_auth($app, $model);
    $model->destroyKey($key);
    $app->halt(200);
});

$app->get('/api/products', function() use ($model, $app, $cookie_auth) {
    $cookie_auth($app,$model);
    $products = $model->listAllProducts();
    echo json_encode($products);
});


$app->get('/api/products/categories', function() use ($model, $app, $cookie_auth) {
    $cookie_auth($app,$model);
    $categories = $model->getCategories();
    echo json_encode($categories);
});

$app->get('/api/products/:id', function($id) use ($model, $app, $cookie_auth) {
    $cookie_auth($app,$model);
    $product = $model->getProduct($id);
    echo json_encode($product);
});

$app->post('/api/cart', function() use ($model, $app, $cookie_auth) {
    $key = $cookie_auth($app, $model);
    $cart = $app->request->post('cart_data');
    // @todo validate
    if($model->updateCart($key, $cart)) {
        $app->halt(200);
    }
    else {
        $app->halt(500);
    }
});

$app->get('/api/cart', function() use ($model, $app, $cookie_auth) {
    $key = $cookie_auth($app, $model);
    $cart = $model->getCart($key);
    echo json_encode($cart);
});

$app->post('/api/users', function() use ($model, $app) {
    $data = [
        'user_email' => $app->request->post('user_email'),
        'password' => $app->request->post('password')   ,
        'first_name' => $app->request->post('first_name'),
        'last_name' => $app->request->post('last_name'),
        'zip' => $app->request->post('zip'),
    ];
    // @todo validate better
    if($data['user_email'] != '' && !is_null($data['user_email']))
        echo json_encode($model->addUser($data));
    else {
        $app->halt(400);
    }
});

$app->get('/api/user', function() use ($model, $app, $cookie_auth) {
    $key = $cookie_auth($app,$model);
    $ud = $model->getUserInfo($key);
    echo json_encode($ud);
});


$app->run();

?>