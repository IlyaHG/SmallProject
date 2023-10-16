<?php
if(!session_id()) @ session_start();

require_once '../vendor/autoload.php';
require '../settings.php';

use App\QueryBuilder;
use DI\ContainerBuilder;
use League\Plates\Engine;
use Aura\SqlQuery\QueryFactory;
use Delight\Auth\Auth;


$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions([
    Engine::class => function() {
    return new Engine('../app/views');
    },

    PDO::class => function() {
    $driver = "mysql";
    $host = "localhost";
    $database_name = "oppro";
    $username = "root";
    $password = "";
    return new PDO("$driver:host=$host;dbname=$database_name;",$username,$password);
    },

    QueryFactory::class => function() {
    return new QueryFactory('mysql');
    },

    Auth::class => function($container) {
    return new Delight\Auth\Auth($container->get('PDO'));
    }
]);


$container= $containerBuilder->build();



$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', ['App\controllers\PageController', 'page_login']);
    $r->addRoute('GET', '/users', ['App\controllers\PageController', 'page_users']);
    $r->addRoute('GET', '/page_register', ['App\controllers\PageController', 'page_register']);
    $r->addRoute('GET', '/create_user', ['App\controllers\PageController', 'page_create_user']);
    $r->addRoute('GET', '/page_login', ['App\controllers\PageController', 'page_login']);
    $r->addRoute('GET', '/upload_avatar/id={id:\d+}', ['App\controllers\PageController', 'upload_avatar']);
    $r->addRoute('GET', '/page_profile/id={id:\d+}', ['App\controllers\PageController', 'page_profile']);
    $r->addRoute('GET', '/edit/id={id:\d+}', ['App\controllers\PageController', 'edit']);
    $r->addRoute('GET', '/set_status/id={id:\d+}', ['App\controllers\PageController', 'set_status']);

    $r->addRoute('GET', '/delete_user/id={id:\d+}', ['App\controllers\UserController', 'delete_user']);
    $r->addRoute('GET', '/page_profile', ['App\controllers\PageController', 'page_profile']);
    $r->addRoute('GET', '/edit/id={id:\d+}/edit_profile', ['App\controllers\UserController', 'edit_profile']);
    $r->addRoute('GET', '/security/id={id:\d+}', ['App\controllers\PageController', 'security']);

    $r->addRoute('GET', '/email_verify', ['App\controllers\EmailVerifyController', 'page_email_verify']);
    $r->addRoute('GET', '/email_verified', ['App\controllers\EmailVerifyController', 'page_email_verified']);
    $r->addRoute('POST', '/email_check', ['App\controllers\EmailVerifyController', 'email_check']);

    $r->addRoute('GET', '/logout', ['App\controllers\UserController', 'logout']);
    $r->addRoute('POST', '/create_user/create', ['App\controllers\UserController', 'create_user']);
    $r->addRoute('POST', '/page_register/register', ['App\controllers\UserController', 'register']);
    $r->addRoute('POST', '/page_login/login', ['App\controllers\UserController', 'login']);
    $r->addRoute('POST', '/edit/id={id:\d+}/edit', ['App\controllers\UserController', 'edit']);
    $r->addRoute('POST', '/set_status/id={id:\d+}/status', ['App\controllers\UserController', 'set_status']);
    $r->addRoute('POST', '/upload_avatar/id={id:\d+}/upload', ['App\controllers\AuthController', 'upload_avatar']);
    $r->addRoute('POST', '/security/id={id:\d+}/security', ['App\controllers\UserController', 'security']);




});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        echo '404asvd';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        echo '405 Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $container->call($routeInfo[1], $routeInfo[2]);
        // ... call $handler with $vars
        break;
}
