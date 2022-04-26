<?php
// namespace App\Controllers;

// use App\Libraries\Controller;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Micro;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class IndexController extends Controller
{
    

    public function indexAction()
    {

    }
    public function insertorderAction(){
        $data = $this->request->getPost();
        print_r($data);
        $succes = $this->mongo->order->insertOne($data);

    }
    public function updateorderAction(){
        // die('hi');
        $data = $this->request->getPost();
        print_r($data);
        $all_data = $this->mongo->order->find();
        foreach($all_data as $key=>$value){
            // print_r($value);
            if($value->_id == $data['oid']){
                $status = $data['status'];
                $order =  $this->mongo->order->updateOne(
                    ['_id'=> new \MongoDB\BSON\ObjectID($data['oid'])],
                     ['$set' => ["status"=>"$status"]],
                );
            }
            }
        

        // print_r($all_data);
        // die;
    }
}