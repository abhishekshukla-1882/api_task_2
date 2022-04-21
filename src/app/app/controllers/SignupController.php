<?php
// namespace App\Controllers;

// use App\Libraries\Controller;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Micro;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SignupController extends Controller
{
    

    public function indexAction()
    {
        if(isset($_POST['submit'])){
            $postdata = $this->request->getPost();
            // print_r($postdata);
            $username = $postdata['username'];
            $password = $postdata['password'];
            $data = array(
                'email'=>"$username",
                'password'=>"$password"
            
            );
            // die;
            $this->mongo->user->insertOne($data);
            $key = "example_key";
            $payload = array(
                "iss" => "http://example.org",
                "aud" => "http://example.com",
                "iat" => 1356999524,
                "nbf" => 1357000000,
                "role" => "user",
                'email' => "$username"
            );
            $jwt = JWT::encode($payload, $key, 'HS256');
            // $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
            die($jwt);
        }
    }
}