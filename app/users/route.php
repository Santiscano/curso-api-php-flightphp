<?php

namespace App\Users;

use Flight;

class UsersRoutes
{
    private $users;

    public function __construct()
    {
        $this->users = new \App\Users\UsersServices();
    }
    
    public function users() {
        Flight::route('GET /users', [$this->users, 'selectAll']);
        Flight::route('GET /users/@id', [$this->users, 'selectOne']);
        Flight::route('POST /users', [$this->users, 'InsertOne']);
        Flight::route('PUT /users/@id', [$this->users, 'updateOne']);
        Flight::route('DELETE /users/@id', [$this->users, 'deleteOne']);
    }
}