<?php

namespace App\controllers;

use App\QueryBuilder;
use Delight\Auth\Auth;
use League\Plates\Engine;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use function Tamtamchik\SimpleFlash\flash;

class EmailVerifyController

{
    private $templates;
    private $qb;
    private $engine;


    public function __construct(QueryBuilder $qb, Engine $engine, Auth $auth)
    {
        $this->qb= $qb;
        $this->templates = $engine;
        $this->auth = $auth;

    }

    public function page_email_verified()
    {
        $qb = $this->qb;
        $auth = $this->auth;

        echo $this->templates->render('email_verified', ['auth'=>$auth, 'qb'=>$qb]);
    }

    public function page_email_verify()
    {
        $qb = $this->qb;
        $auth = $this->auth;

        echo $this->templates->render('email_verify', ['auth'=>$auth, 'qb'=>$qb]);
    }


    public function mail_verify($user_email,$user_selector, $user_token)
    {

        $url = 'http://mysite/page_profile';
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                   //Enable SMTP authentication
            $mail->Username = 'kurat.ilya@gmail.com';                     //SMTP username
            $mail->Password = 'bxmm tkvv httz dlcb';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('kurat.ilya@gmail.com', 'Admin');
            $mail->addAddress($user_email);     //Add a recipient

            //Content
            $mail->CharSet = 'UTF-8'; // Set the character set to UTF-8
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Подтверждение почты';
            $mail->Body = "Ваш токен и селектор: . ($user_token,$user_selector). Пройдите по ссылке и подтвердите вашу почту: $url";


            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    public function email_check()
    {
        try {
            $this->auth->confirmEmailAndSignIn($_POST['selector'], $_POST['token']);
            flash()->success('Ваш email подтвержден. Добро пожаловать!');
            header('Location: /page_profile');
            exit;
        }
        catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            flash()->error('Неверно введен Token!');
            header('Location: /email_verify');
            exit;
        }
        catch (\Delight\Auth\TokenExpiredException $e) {
            flash()->error('Срок действия токена истек!');
            header('Location: /email_verify');
            exit;
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            flash()->error('Почта уже занята!');
            header('Location: /email_verify');
            exit;
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            flash()->error('Слишком много запросов!');
            header('Location: /email_verify');
            exit;
        }

    }
}