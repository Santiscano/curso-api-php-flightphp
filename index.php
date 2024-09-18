<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

require_once 'users/services.php';
require_once 'auth/services.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$key = $_ENV['SECRET_KEY'];

// $users = new UsersServices();
$usersRoutes = new \App\Users\UsersRoutes();
// $auth = new AuthServices();
$auth = new \App\Auth\AuthServices();


// Then define a route and assign a function to handle the request.
Flight::route('POST /', [$auth, 'sendToken']);
Flight::route('GET /GetToken', [$auth, 'generateToken']);
Flight::route('POST /auth', [$auth, 'validateUser']);

// Flight::route('GET /users', [$users, 'selectAll']);
// Flight::route('GET /users/@id', [$users, 'selectOne']);
// Flight::route('POST /users', [$users, 'InsertOne']);
// Flight::route('PUT /users/@id', [$users, 'updateOne']);
// Flight::route('DELETE /users/@id', [$users, 'deleteOne']);
Flight::group('/users', function () use ($usersRoutes) {
    $usersRoutes->users();
});


// Finally, start the framework.
Flight::start();