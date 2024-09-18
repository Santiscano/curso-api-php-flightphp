<?php
namespace App\Auth;

// require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Flight;
use PDO;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class AuthServices {
    private $db;

    private $host;
    private $dbname;
    private $user;
    private $password;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->user = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];

        Flight::register('db', 'PDO', array("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password));
        $this->db = Flight::db();
    }

    public function validateUser() {
        $password = Flight::request()->data->password;
        $email = Flight::request()->data->email;
        $query = $this->db->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $response = [
            'error' => "No se pudo validar su identidad, por favor verifique sus credenciales",
            'status' => 'error'
        ];
    
        if ($query->execute(['email' => $email, 'password' => $password])) {
            $user = $query->fetch(PDO::FETCH_ASSOC);
            $now = strtotime('now');
            $key = 'example_key';
            $payload = array(
                'exp' => $now + 3600,
                'data' => [
                    'nickname' => $user['nickname'],
                    'name' => $user['name'],
                ]
            );
            $jwt = JWT::encode($payload, $key, 'HS256');
            $response = [
                'token' => $jwt,
                'status' => 'success'
            ];
        }
    
        Flight::json($response);
    }

    public function getToken(){
        global $key;
    
        $authorization = Flight::request()->headers()['Authorization'];
        $token = explode(' ', $authorization)[1];
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded;
    }

    public function sendToken() 
    {
        $token = $this->getToken();
        Flight::json([
            'status' => 'success',
            'message' => 'Welcome to the API',
            'token' => $token
        ]);
    }

    public function generateToken()
    {
        $payload = array(
            "data" => [
                $username = "santiago",
                $email = "santiscano@gmail.com",
            ]
        );

        $token = JWT::encode($payload, 'example_key', 'HS256');
        Flight::json([
            'status' => 'success',
            'token' => $token
        ]);
    }
}