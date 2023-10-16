<?php

namespace App\controllers;

use App\QueryBuilder;
use Delight\Auth\Auth;
use League\Plates\Engine;
use PHPMailer\PHPMailer\SMTP;
use function Tamtamchik\SimpleFlash\flash;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class UserController
{
    private $templates;
    private $qb;
    private $engine;
    private $email;


    public function __construct(QueryBuilder $qb, Engine $engine, Auth $auth, EmailVerifyController $email)
    {
        $this->qb= $qb;
        $this->templates = $engine;
        $this->auth = $auth;
        $this->email = $email;

    }
    public function logout()
    {
        try {
            $this->auth->logOutEverywhere();
            header('location: /page_login');
        } catch (\Delight\Auth\NotLoggedInException $e) {
            header('location: /page_login');
        }
    }
    public function login()
    {

        try {
            $this->auth->login($_POST['email'], $_POST['password']);
            header('location: /page_profile');
        } catch (\Delight\Auth\InvalidEmailException $e) {
            header('Location: /page_login');
            flash()->error('Wrong email address!');
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            header('Location: /page_login');
            flash()->error('Wrong password!');
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            header('Location: /page_login');
            flash()->error('Email not verified!');
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Too many requests!');
            header('Location: /page_login');
        }
    }
    public function edit()
    {

        if($_POST['vk'] !== ""){
            $this->qb->update([
                'vk' => "{$_POST['vk']}",],
                $_POST['id'],
                'view_users');
        }
        if($_POST['instagram'] !== ""){
            $this->qb->update([
                'instagram' => "{$_POST['instagram']}",],
                $_POST['id'],
                'view_users');
        }
        if($_POST['telegram'] !== ""){
            $this->qb->update([
                'telegram' => "{$_POST['telegram']}",],
                $_POST['id'],
                'view_users');
        }
        $this->qb->update([
            'email' => "{$_POST['email']}",],
            $_POST['id'],
            'users');

        $this->qb->update([
            'name' => "{$_POST['name']}",
            'email' => "{$_POST['email']}",
            'address' => "{$_POST['address']}",
            'phone' => "{$_POST['phone']}",
            'work_place' => "{$_POST['work_place']}",],
            $_POST['id'],
            'view_users');

        if($_FILES['avatar']['name'] !== ""){
        $avatar_name = pathinfo($_FILES['avatar']['name']);
        $avatar_name = uniqid() . "." . $avatar_name['extension'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/'.$avatar_name);
        $this->qb->update(['avatar'=> $avatar_name],$_POST['id'],'view_users');
    }

        if ($_POST['status'] == 'Онлайн') {
            $status = 'success';
        } elseif ($_POST['status'] == 'Отошел') {
            $status = 'warning';
        } else {
            $status = 'danger';
        }
        $this->qb->update(['status'=> $status],$_POST['id'],'view_users');
        flash()->success(' Профиль успешно обновлен');
        header("location: /users");
    }
    public function security()
    {

        if ($_POST['newPassword'] != $_POST['repeat_newPassword']){
            flash()->error('Пароли не совпадают');
            header("location: /security/id={$_POST['id']}");
            exit;
        }else {
            $this->qb->update([
                'email' => "{$_POST['email']}",],
                $_POST['id'],
                'users');

            $this->qb->update([
                'email' => "{$_POST['email']}",],
                $_POST['id'],
                'view_users');

            try {
                $this->auth->changePassword($_POST['oldPassword'], $_POST['newPassword']);
                flash()->success('Пароль успешно изменен');
            } catch (\Delight\Auth\NotLoggedInException $e) {
                header("location: /security/id={$_POST['id']}");
                exit();
            } catch (\Delight\Auth\InvalidPasswordException $e) {
                flash()->error('Неверный пароль');
                header("location: /security/id={$_POST['id']}");
                exit();
            } catch (\Delight\Auth\TooManyRequestsException $e) {
                flash()->error('Слишком много запросов');
                header("location: /security/id={$_POST['id']}");
                exit();
            }
            header('location: /users');
        }
    }
    public function register()
    {
        function isPasswordAllowed($password)
        {
            if (\strlen($password) < 4) {
                flash()->error('Пароль слишком короткий');
                header("Location: /page_register");
                exit;
            }else{
                return true;
            }
        }

        $this->password = $_POST['password'];
        $blacklist = ['password1', '123456qwerty', 'qwerty'];

        if (in_array($_POST['password'], $blacklist)) {
            flash()->error('Пароль плохой');
            header("Location: /page_register");
            exit;
        }

        if (isPasswordAllowed($_POST['password'])) {
            try {
                $userId = $this->auth->register($_POST['email'], $_POST['password'], $_POST['email'], function ($selector, $token) {
                    echo 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email)';
                    echo '  For emails, consider using the mail(...) function, Symfony Mailer, Swiftmailer, PHPMailer, etc.';
                    echo '  For SMS, consider using a third-party service and a compatible SDK';
                    flash()->success('Вы успешно зарегистрированы');
                    $this->email->mail_verify($_POST['email'],$selector,$token);
                    header('Location: /email_verify');
                });

                echo 'We have signed up a new user with the ID ' . $userId;
            } catch (\Delight\Auth\InvalidEmailException $e) {
                flash()->error('Неверный адрес электронной почты');
                header('Location: /page_register');
                die('Invalid email address');
            } catch (\Delight\Auth\InvalidPasswordException $e) {
                flash()->error('Неверный пароль');
                header('Location: /page_register');
                die('Invalid password');
            } catch (\Delight\Auth\UserAlreadyExistsException $e) {
                flash()->error('Пользователь с таким эл.адресом уже существует');
                header('Location: /page_register');
                die('User already exists');
            } catch (\Delight\Auth\TooManyRequestsException $e) {
                flash()->error('Слишком много запросов');
                header('Location: /page_register');
                die('Too many requests');
            }
        }
        $this->qb->insert('view_users', [
            "name" => $_POST['email'],
            "phone" => 'Your Phone',
            "email" => $_POST['email'],
            "address" => 'Your address',
            "work_place" => 'Your work_place',
            "status" => 'success',
            "avatar" => 'Your avatar',
            "datafiltertag" => 'Your tag',
            "vk" => "Your vk",
            "instagram" => "Your instagram",
            "telegram" => "Your telegram",
        ]);
//        $this->qb->update(['verified' => '1']);



    }
    public  function delete_user()
    {
        $id = trim($_GET['route'],'/delete_user/id=');
        try {
            $this->auth->admin()->deleteUserById($id);
        }
        catch (\Delight\Auth\UnknownIdException $e) {
            flash()->error('Пользователя с таким ID не существует');
            header('location: /users');
            exit;
        }

        $this->qb->delete($id,'view_users');
        $this->qb->delete($id,'users_confirmations');

        flash()->success('Пользователь успешно удален');
        header('location: /users');


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
        header("Location: /users");
    }
    public function create_user()
    {
        try {

            $userId = $this->auth->register($_POST['email'], $_POST['password'], $_POST['email'], function ($selector, $token) {
                echo 'Send ' . $selector . ' and ' . $token . ' to the user (e.g. via email)';
                echo '  For emails, consider using the mail(...) function, Symfony Mailer, Swiftmailer, PHPMailer, etc.';
                echo '  For SMS, consider using a third-party service and a compatible SDK';
                flash()->success('Пользователь успешно зарегистрирован');
                header('Location: /users');
            });

            echo 'We have signed up a new user with the ID ' . $userId;
        } catch (\Delight\Auth\InvalidEmailException $e) {
            flash()->error('Неверный адрес электронной почты');
            header('Location: /create_user');
            die('Invalid email address');

        } catch (\Delight\Auth\InvalidPasswordException $e) {
            flash()->error('Неверный пароль');
            header('Location: /create_user');
            die('Invalid password');

        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->error('Пользователь с таким эл.адресом уже существует');
            header('Location: /create_user');
            die('User already exists');


        } catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Слишком много запросов');
            header('Location: /create_user');
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
            "datafiltertag" => $_POST['email'],
            "vk" => $_POST['vk'],
            "instagram" => $_POST['instagram'],
            "telegram" => $_POST['telegram'],
        ]);
        flash()->success('Пользователь успешно зарегистрирован');
        header('Location: /users');
    }
}
