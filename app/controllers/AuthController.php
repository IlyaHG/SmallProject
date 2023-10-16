<?php
namespace App\controllers;

if (!session_id()) {
    session_start();
}


require_once __DIR__ . '/../../vendor/autoload.php';

use App\QueryBuilder;
use League\Plates\Engine;
use PDO;
use \Delight\Auth\Auth;
use \Tamtamchik\SimpleFlash\Flash;
use function Tamtamchik\SimpleFlash\flash;
use Aura\SqlQuery\QueryFactory;


class AuthController
{
    private $pdo;
    private $qb;
    private $auth;
    private $queryFactory;
    private $password;


    public function __construct(QueryBuilder $qb, PDO $pdo, Auth $auth, QueryFactory $queryFactory)
    {
        $this->qb = $qb;
        $this->auth = $auth;
        $this->queryFactory = $queryFactory;

    }








    public function create_user()
    {
        try {

            $userId = $this->auth->register($_POST['email'], $_POST['password'], $_POST['email'], function ($selector, $token) {
                echo 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email)';
                echo '  For emails, consider using the mail(...) function, Symfony Mailer, Swiftmailer, PHPMailer, etc.';
                echo '  For SMS, consider using a third-party service and a compatible SDK';
                flash()->success('Пользователь успешно зарегистрирован');
                header('Location: /public/users');
            });

            echo 'We have signed up a new user with the ID ' . $userId;
        } catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->error('Неверный адрес электронной почты');
            header('Location: /public/create_user');
            die('Invalid email address');

        } catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->error('Неверный пароль');
            header('Location: /public/create_user');
            die('Invalid password');

        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->error('Пользователь с таким эл.адресом уже существует');
            header('Location: /public/create_user');
            die('User already exists');


        } catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Слишком много запросов');
            header('Location: /public/create_user');
            die('Too many requests');
        }

        $avatar_name = pathinfo($_FILES['avatar']['name']);
        $avatar_name = uniqid() . "." . $avatar_name['extension'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/' . $avatar_name);

        if ($_POST['status'] == 'Онлайн') {
            $status = 'success';
        } elseif ($_POST['status'] == 'Отошел') {
            $status = 'warning';
        } else {
            $status = 'danger';
        }
        $this->qb->insert('view_users', [
            "name" => $_POST['name'],
            "phone" => $_POST['phone'],
            "email" => $_POST['email'],
            "address" => $_POST['address'],
            "work_place" => $_POST['work_place'],
            "status" => $status,
            "avatar" => $avatar_name,
            "datafiltertag" => $_POST['name'],
            "vk" => $_POST['vk'],
            "instagram" => $_POST['instagram'],
            "telegram" => $_POST['telegram'],
        ]);

    }


    public function set_status(){
        if ($_POST['status'] == 'Онлайн') {
            $status = 'success';
        } elseif ($_POST['status'] == 'Отошел') {
            $status = 'warning';
        } else {
            $status = 'danger';
        }
        $this->qb->update(['status'=> $status],$_POST['id'],'view_users');
        header("Location: /public/users");
    }

    public  function delete_user(){
        $id = trim($_GET['route'],'users/deleteuser/id=');

        try {
            $this->auth->admin()->deleteUserById($id);
        }
        catch (\Delight\Auth\UnknownIdException $e) {
            die('Unknown ID');
        }

        $this->qb->delete($id,'view_users');
        flash()->success('Пользователь успешно удален');
        header('location: /public/users');


    }


    public function upload_avatar(){

        $avatar_name = pathinfo($_FILES['avatar']['name']);
        $avatar_name = uniqid() . "." . $avatar_name['extension'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/' . $avatar_name);

        $this->qb->update(['avatar'=> $avatar_name],$_POST['id'],'view_users');
        flash()->success('Аватар успешно изменен');
        header("location: /users");
    }
}

