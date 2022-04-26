<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/app/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
// session_start();
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Headers;
use Phalcon\Http\Cookie;
use Phalcon\Di;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Config;
use Phalcon\Config\ConfigFactory;
use Phalcon\Cache;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Mvc\Micro;
// ---------------------------------------------------------------------------------------------------------
$config = new Config([]);
$filename = '../app/etc/config.php';
$factory = new ConfigFactory();
$config = $factory->newInstance('php',$filename);
// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once("../vendor/autoload.php");
// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/listners/",
        APP_PATH . "/models/",
    ],
    
);
$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH . "/Component"
    ]
);

$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);
// $loader = new Loader();

// $loader->registerDirs(
//     [
//         APP_PATH . "/controllers",
//         APP_PATH . "/models/"

//     ]
//     );
//     $loader->registerNamespaces(
//         [
//             'App\Components' => APP_PATH . "/components"
//         ]
//         );
//     $loader->register();
// --------------------------------------------------------------------------------------------------
$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);
$container->set(
    'date',
    function () {
        // $url = new date();
        // $url->setBaseUri('/');
        return date('Y:M:D:H:M:S');
    }
);
$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client("mongodb://mongo", array("username"=>'root', "password"=>"password123"));
        // mongo "mongodb+srv://sandbox.g819z.mongodb.net/myFirstDatabase" --username root

        return $mongo->store;
    },
    true
);


$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers",
        APP_PATH . "/models/"
    ]);


$loader->registerNamespaces(
    [
        'App\Listeners' => APP_PATH . '/listners'
    ]
    );
    $loader->register();



// --------------------------------------------------------------Cache-------------------------
$serializerFactory = new SerializerFactory();
$adapterFactory    = new AdapterFactory($serializerFactory);

$options = [
    'defaultSerializer' => 'Json',
    'lifetime'          => 7200
];

$adapter = $adapterFactory->newInstance('apcu', $options);

$cache = new Cache($adapter);
$container->set("cache",$cache);

// ---------------------------------------------------------------------------------------------

// ----------------------------------------------Database---------------------------------------

$container->set(
    'db',
    function () {
        $config = $this->get('config');

        return new Mysql(
            [
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->dbname
                ]
            );
        }
);
$application = new Application($container);

$container->set(
    'config',
    $config,
    true

);
// ------------------------------------------------------------Log and Loader--------------------------

$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers",
        APP_PATH . "/models/"
    ]);


$loader->registerNamespaces(
    [
        'App\Listeners' => APP_PATH . '/listners'
    ]
    );
    $loader->register();

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);
// ------------------------------------------------------------------------------------------------------


// -----------------------------------------------Translator -------------------------------------------
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;
$container->set(
    "translator",
    function () use ($application) {
        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        $locale = $application->request->getQuery('locale') ?? "en_US";
        $messages = require APP_PATH . '/translations/' . $locale . ".php";
        return $factory->newInstance(
            'array',
            [
                'content' => $messages,
            ]
        );
    }
);

// ------------------------------------------------------Acl -------------------------------------------
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
$eventsManager = new EventsManager();

$container->set(
    'eventsManager',
    function () use($eventsManager) {
        return $eventsManager;

    }
);

// $eventsManager->attach(
//     'application:beforeHandleRequest',
//     new App\Listeners\NotificationListner()
// );
// $application->setEventsManager($eventsManager);

// ------------------------------------------------------Acl -------------------------------------------


// ------------------------------------------------SESSION SET ------------------------------------------------------
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
            ->setName('login')
            ->start();

        return $session;
    }
);
// ------------------------------------------------------------------------------------------------------------------
// $di->set( 
//     "cookies", function () { 
//        $cookies = new Cookies();  
//        $cookies->useEncryption(false);  
//        return $cookies; 
//     } 
//  ); 

try {
    // Handle the request
    $response = $application->handle(
        str_replace("/app/","/",$_SERVER["REQUEST_URI"])
    );
    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
    echo 'Exception: ', $e->getFile();
    echo 'Exception: ', $e->getLine();
}