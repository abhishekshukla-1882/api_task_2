<?php
namespace Api\Handlers;
use Phalcon\Di\Injectable;
use Phalcon\Url;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Product extends Injectable{

    public function search($name=''){
        // print_r($name);
        // echo "<br>";
        if(strpos($name,'%20')==true){
            $n = explode('%20',$name);
            foreach ($n as $str) {
                $strarr[] = array('$or' => array(array("title" => array('$regex' => $str)), array("variations[0].name" => array('$regex' => $str))));
            }
           
          
            $product = $this->mongo->products->find(['$or'=>$strarr]);
         
            $prod = array();
            foreach($product as $key=>$value){
                $prod[] = (array)$value;
            }
            
            $response = $this->response->SetJsonContent($prod);
            return $response;
           
        }
        else {
            $products = $this->mongo->products->find(['title' => array('$regex' => $name)]);
           
            $prod = array();
            foreach($products as $key=>$value){
                $prod[] = (array)$value;
            }
            
            $response = $this->response->SetJsonContent($prod);
            return $response;
        }
    }
    public function get(){

        $product = $this->mongo->product->find();
        foreach($product as $key => $value){
            $arr[] = (array)$value;
            
        }
        $response = $this->response->SetJsonContent($arr);
        return $response;
        die;

    }
    public function responses($no_of_res){
        $no = (int)$no_of_res;
        $product = $this->mongo->products->find([],['limit'=>$no]);
        $arr = array();
        foreach($product as $key => $value){
            $arr[] = (array)$value;
        }
        $response = $this->response->SetJsonContent($arr);
        return $response;
    }
    public function pages($no){
        $no = (int)$no;
        $product = $this->mongo->products->find([],['limit'=>(1*$no)]);
        $arr = array();
        foreach($product as $key=>$value){
            $arr[] = (array)$value;
        }
        $response = $this->response->SetJsonContent($arr);
        print_r($this->get('url'));
    }
    public function login(){
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000,
            "role" => "admin"
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        echo $jwt;
        die;
    }
    public function createorder(){
        $token = $this->request->getHeader('token');
        $pid = $this->request->getHeader('pid');
        
        $key = "example_key";

        $decoded = JWT::decode($token, new Key($key, 'HS256'));
       
        if($decoded->email){
            $user = $this->mongo->user->find();
            foreach($user as $key => $value){
                if($value->email == $decoded->email){
                    $data = array(
                        'pid'=>$pid,
                        'user_id'=> $value->_id,
                        'status'=>'pending'
                    );
                    $succes = $this->mongo->order->insertOne($data);
                    if($succes){
                        echo "Added Succesfully";
                        die;
                    }

                }
            }
            echo "granted <br>";
            // die;
        }
        else{
            echo "Token is not valid";
            die;
        }
    }
    public function updateorder(){
        $token = $this->request->getHeader('token');
        $oid = $this->request->getHeader('oid');
        $status = $this->request->getHeader('status');
        $order =  $this->mongo->order->updateOne(
            ['_id'=> new \MongoDB\BSON\ObjectID($oid)],
             ['$set' => ["status"=>"$status"]],
        );
        if($order){
            echo "Updated order $oid";
            die;
        }
    }
    public function getorder(){
        $order = $this->mongo->order->find();
        $arr = array();
        foreach($order as $key => $value){
            $arr[] = (array)$value;
            
        }
        $response = $this->response->SetJsonContent($arr);
        return $response;
    }
    
}




