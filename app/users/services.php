<?php
namespace App\Users;

// require 'vendor/autoload.php';
use App\Auth\AuthServices;
use Flight;
use PDO;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class UsersServices
{
    private $db;
    private $auth;

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
        $this->auth = new AuthServices();
    }

    public function selectAll()
    {
        if (!$this->auth->getToken()) Flight::halt(401, 'Unauthorized');

        $query = $this->db->prepare('SELECT * FROM users');
        $query->execute();
        $total = $query->rowCount();
        $total_per_page = 10;
        $pages = ceil($total / $total_per_page);
        $page = Flight::request()->query['page'] ?? 1;
        $offset = ($page - 1) * $total_per_page;

        if ($pages == 0) $pages = 1;
        if ($page > $pages) Flight::halt(404, 'Page not found');

        $query = $this->db->prepare('SELECT * FROM users LIMIT :offset, :total_per_page');
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->bindParam(':total_per_page', $total_per_page, PDO::PARAM_INT);
        $query->execute();
        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        $array_data = [];
        foreach ($users as $user) {
            $array_data[] = $user['users_name'];
        }
        Flight::json([
            'status' => 'success',
            'total' => count($users),
            'data' => $array_data,
            'pagination' => [
                'total' => $total,
                'total_per_page' => $total_per_page,
                'pages' => $pages,
                'page' => $page
            ]
        ]);
    }

    public function selectOne($id)
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE idusers = :id');
        $query->execute(['id' => $id]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        Flight::json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function InsertOne()
    {
        $query = $this->db->prepare('INSERT INTO LSA_USERS (name, email) VALUES (:name, :email)');
        $query->execute([
            'id' => $this->db->lastInsertId(),
            'name' => Flight::request()->data->name,
            'email' => Flight::request()->data->email
        ]);

        Flight::json([
            'status' => 'success',
            'message' => 'User added'
        ]);
    }

    public function updateOne($id)
    {
        $query = $this->db->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $query->execute([
            'id' => $id,
            'name' => Flight::request()->data->name,
            'email' => Flight::request()->data->email
        ]);

        Flight::json([
            'status' => 'success',
            'message' => 'User updated'
        ]);
    }

    public function deleteOne($id)
    {
        $query = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $query->execute(['id' => $id]);

        Flight::json([
            'status' => 'success',
            'message' => 'User deleted'
        ]);
    }
}
