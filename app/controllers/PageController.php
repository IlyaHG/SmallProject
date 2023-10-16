<?php
namespace App\controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\QueryBuilder;
use Delight\Auth\Auth;
use League\Plates\Engine;

class PageController
{
    private $templates;
    private $qb;
    private $auth;
    private $engine;
    private $users;
    public function __construct(QueryBuilder $qb, Engine $engine,UserController $users,Auth $auth)
    {
        $this->qb= $qb;
        $this->templates = $engine;
        $this->users = $users;
        $this->auth = $auth;

    }

    public function page_users()
    {
        $auth = $this->auth;
        $userid = $this->auth->getUserId();
        $users = $this->qb->getAll('view_users');
        echo $this->templates->render('users', ['users' => $users, 'userid'=> $userid, 'auth'=>$auth]);
    }
    public function page_register()
    {
//        d($this->qb);die;
        echo $this->templates->render('page_register', ['name' =>'ALO']);
    }
    public function page_login()
    {
        $isLoggedIn= $this->auth->isLoggedIn();
        echo $this->templates->render('page_login',['isLoggedIn' => $isLoggedIn]);
    }
    public function page_create_user()
    {
        $qb = $this->qb;
        $auth = $this->auth;

        echo $this->templates->render('create_user', ['auth'=>$auth, 'qb'=>$qb]);
    }
    public function edit()
    {

        $qb = $this->qb;
        $auth = $this->auth;

        echo $this->templates->render('edit', ['auth'=>$auth, 'qb'=>$qb]);
    }
    public function security()
    {
        $qb = $this->qb;
        $auth = $this->auth;

        echo $this->templates->render('security', ['auth'=>$auth, 'qb'=>$qb]);
    }
    public function set_status()
    {
        $qb = $this->qb;
        $auth = $this->auth;

        echo $this->templates->render('status', ['auth'=>$auth, 'qb'=>$qb]);
    }
    public function uasdpload_avatar()
    {
        echo $this->templates->render('media');
    }
    public function page_profile()
    {
        $qb = $this->qb;
        $auth = $this->auth;

        echo $this->templates->render('page_profile', ['auth'=>$auth, 'qb'=>$qb]);

    }








}