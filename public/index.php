<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use CraftShop\Core\Router;

$router = new Router();

// Define Routes
$router->get('/', 'HomeController@index');
$router->get('/products', 'ProductController@index');
$router->get('/product/{id}', 'ProductController@show');
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/remove', 'CartController@remove');

// Get current request
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Resolve route
$route = $router->resolve($method, $uri);

if (!$route) {
    http_response_code(404);
    echo "404 - Page not found";
    exit;
}

// Create controller and call action
$controllerClass = 'CraftShop\\Controllers\\' . $route['controller'];
$action = $route['action'];

if (!class_exists($controllerClass)) {
    die("Controller {$controllerClass} not found");
}

$controller = new $controllerClass();

if (!method_exists($controller, $action)) {
    die("Method {$action} not found in {$controllerClass}");
}

// Call the controller action
$controller->$action();