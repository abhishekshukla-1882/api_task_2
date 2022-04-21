<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Bag as SessionBag;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Router;
//use Phalcon\Config;
use Phalcon\Config\ConfigFactory;
require_once("../app/vendor/autoload.php");
$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->register();

$container = new FactoryDefault();


$container->set(
    'config',
    function () {
        $filename = '../app/etc/config.php';
        $config = new Config([]);
        $array =  new \Phalcon\Config\Adapter\Php($filename);
        $config->merge($array);
        return $config;
    },
    true
);

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);



// $container->set(
//     'db',
//     function () {
//         return new Mysql(
//             [
//                 'host'     => $this['config']['db']->host,
//                 'username' => $this['config']['db']->username,
//                 'password' => $this['config']['db']->password,
//                 'dbname'   => $this['config']['db']->dbname
//             ]
//         );
//     }
// );
$container->set(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );

        $session
            ->setAdapter($files)
            ->start();

        return $session;
    }
);
$container->set(
    'cookies',
    function () {
        $cookies = new Cookies();

        $cookies->useEncryption(false);

        return $cookies;
    }
);
$container->set(
    'time',
    function () {
        return date('D-M-Y, h:m:s');
    }
);

// $container->set(
//     'mongo',
//     function () {
//         $mongo = new MongoClient();

//         return $mongo->selectDB('phalt');
//     },
//     true
// );
$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client("mongodb://mongo", array("username"=>'root', "password"=>"password123"));
        // mongo "mongodb+srv://sandbox.g819z.mongodb.net/myFirstDatabase" --username root

        return $mongo->store;
    },
    true
);


$container->set(
    'router',
    function () {
        $router = new Router();

        $router->setDefaultModule('frontend');

        $router->add('/:controller/:action', [
            'module'     => 'admin',
            'controller' => 1,
            'action'     => 2,
        ])->setName('admin');
        
        $router->add(
            '/login',
            [
                'module'     => 'admin',
                'controller' => 'login',
                'action'     => 'logo',
            ]
        );
        $router->add(
            '',
            [
                'module'     => 'frontend',
                'controller' => 'index',
                'action'     => 'index',
                ]
            );
            $router->add(
                '/login/l',
                [
                    'module'     => 'admin',
                    'controller' => 'login',
                    'action'     => 'logout',
                    ]
                );
            $router->add(
                '/addproduct',
                [
                    'module'     => 'admin',
                    'controller' => 'product',
                    'action'     => 'addproduct',
                ]
            );
            $router->add(
                '/editproduct',
                [
                    'module'     => 'admin',
                    'controller' => 'product',
                    'action'     => 'editproduct',
                ]
            );
            $router->add(
                '/delete_product',
                [
                    'module'     => 'admin',
                    'controller' => 'product',
                    'action'     => 'delete_product',
                ]
            );
            $router->add(
                '/update',
                [
                    'module'     => 'admin',
                    'controller' => 'product',
                    'action'     => 'update',
                ]
            );
            $router->add(
                '/deleteproduct',
                [
                    'module'     => 'admin',
                    'controller' => 'product',
                    'action'     => 'deleteproduct',
                ]
            );
            
            $router->add(
                '/display',
                [
                    'module'     => 'admin',
                    'controller' => 'product',
                    'action'     => 'display',
                ]
            ); 
        $router->add(
            '/admin/products/:action',
            [
                'module'     => 'back',
                'controller' => 'products',
                'action'     => 1,
            ]
        );

        $router->add(
            '/products/:action',
            [
                'controller' => 'products',
                'action'     => 1,
            ]
        );

        return $router;
    }
);

$application->registerModules(
    [
        'admin' => [
            'className' => \Multi\Back\Module::class,
            'path'      => APP_PATH.'/admin/Module.php',
        ],
        'frontend'  => [
            'className' => \Multi\Frontend\Module::class,
            'path'      => '../app/frontend/Module.php',
        ]
    ]
);




try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
$key = "example_key";
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vZXhhbXBsZS5vcmciLCJhdWQiOiJodHRwOi8vZXhhbXBsZS5jb20iLCJpYXQiOjEzNTY5OTk1MjQsIm5iZiI6MTM1NzAwMDAwMCwicm9sZSI6ImFkbWluIn0.73zdZihRYVg2Lb1ol6wd39RYtE_BpuAHxXq6WLmYDHk