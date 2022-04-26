<?php
namespace Api\Handlers;
use Phalcon\Di\Injectable;
use Phalcon\Url;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
class Product extends Injectable{
    // function get($select = "", $limit= 10,$page =1){
    //     $products = array(
    //         array("select"=>$select,"where"=>$where,"limit"=>$limit,"page"=>$page),
    //         array("name"=>"Product 2", "price"=>40),
    //     );
    //     return json_encode($products);

    // }
    public function search($name=''){
        // print_r($name);
        // echo "<br>";
        if(strpos($name,'%20')==true){
            $n = explode('%20',$name);
            foreach ($n as $str) {
                $strarr[] = array('$or' => array(array("title" => array('$regex' => $str)), array("variations[0].name" => array('$regex' => $str))));
            }
           
            // print_r($strarr);
            // die;
            $product = $this->mongo->products->find(['$or'=>$strarr]);
         
            // print_r($product);
            // die;
            $prod = array();
            foreach($product as $key=>$value){
                $prod[] = (array)$value;
            }
            
            $response = $this->response->SetJsonContent($prod);
            return $response;
           
        }
        else {
            $products = $this->mongo->products->find(['title' => array('$regex' => $name)]);
            // print_r($products);
            // die;
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
        // echo "jhjhjh";
        $arr = array();
        // print_r($product);
        // die;
        foreach($product as $key => $value){
            $arr[] = (array)$value;
            // print_r($arr);
            
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
        // return $response;
    }
    public function login(){
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            //"nbf" => 1357000000,
            "role" => "admin"
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        echo $jwt;
        die;
    }
    public function createorder(){
        $token = $this->request->getHeader('token');
        // echo "<br> aya<br>";
        $pid = $this->request->getHeader('pid');
        // echo $token,"<br>";
        // echo $pid;
        // die;
        $key = "example_key";

        // die;
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        //  print_r($decoded);
        //  die;
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
                    // print_r($succes);
                    $postdata = $succes->getinsertedId();
                    // echo $postdata;
                    // die;
                    // array_push($data,$succes);
                    $data += [
                        '_id'=>$postdata
                    ];
                    // print_r($data);
                    // die;
                    $url="http://192.168.2.10:8080/app/index/insertorder";
                    $client = new Client([
                        'base_uri' => $url,
                    ]);
                    $response = $client->request('POST',"/app/index/insertorder" ,['form_params'=>$data]);
                                
                    $body = $response->getBody()->getContents();
                    // die($body);
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
        // $order = $this->mongo->order->updateOne(['_id'=>new Mongo\BSON\ObjectID($oid)],['$set'=>['status'=>$status]]);
        $order =  $this->mongo->order->updateOne(
            ['_id'=> new \MongoDB\BSON\ObjectID($oid)],
             ['$set' => ["status"=>"$status"]],
        );
        // print_r($order);
        // die;
        $data = array(
            "oid"=>$oid,
            "status" =>$status
        );
        $url="http://192.168.2.10:8080/app/index/updateorder";
        $client = new Client([
            'base_uri' => $url,
        ]);
        $response = $client->request('POST',"/app/index/updateorder" ,['form_params'=>$data]);
                    
        $body = $response->getBody()->getContents();
        die($body);
        if($order){
            echo "Updated order $oid";
            die;
        }
    }
    public function getorder(){
        // echo "yha";
        // die;
        $order = $this->mongo->order->find();
        $arr = array();
        // print_r($product);
        // die;
        foreach($order as $key => $value){
            $arr[] = (array)$value;
            // print_r($arr);
            
        }
        $response = $this->response->SetJsonContent($arr);
        return $response;
    }
    
}




// if (strpos($keyword, "%20") == true) {
//     $newstr = explode("%20", $keyword);
//     foreach ($newstr as $str) {
//         $strarr[] = array('$or' => array(array("name" => array('$regex' => $str)), array("variations[0].name" => array('$regex' => $str))));
//     }
//     $products = $this->mongo->products->find(['$or' => $strarr]);
// } else {
//     $products = $this->mongo->products->find(['name' => array('$regex' => $keyword)]);
// }