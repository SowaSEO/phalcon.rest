<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;
use Phalcon\Http\Request;

$loader = new Loader();
$loader->registerNamespaces(
    [
        'MyApp\Models' => __DIR__ . '/models/',
    ]
);
$loader->register();

$container = new FactoryDefault();
$container->set(
    'db',
    function () {
        return new PdoMysql(
            [
                'host'     => '127.0.0.1',
                'username' => 'root',
                'password' => 'root',
                'dbname'   => 'mservis',
            ]
        );
    }
);

$request = new Request();

$app = new Micro($container);

// всі записи 
$app->get(
    '/api/users',
    function () use ($app) {
        $phql = 'SELECT id, first_name,last_name, login, password '
              . 'FROM MyApp\Models\Users '
              . 'ORDER BY login'
        ;

        $users = $app
            ->modelsManager
            ->executeQuery($phql)
        ;

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id'   => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'login' => $user->login,
                'password' => $user->password
            ];
        }

        echo json_encode($data);
    }
);

// тільки ті де login = Name
$app->get(
    '/api/users/search/{login}',
        
    function ($login) use ($app) {
        $phql = 'SELECT * '
              . 'FROM MyApp\Models\Users '
              . 'WHERE login '
              . 'LIKE :login: '
              . 'ORDER BY login'
        ;

        $users = $app
            ->modelsManager
            ->executeQuery(
                $phql,
                [
                    'login' => '%' . $login . '%'
                ]
            )
        ;

        $data = [];

       foreach ($users as $user) {
            $data[] = [
                'id'   => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'login' => $user->login,
                'password' => $user->password
            ];
        }
        echo json_encode($data);
    }
);

// тільуки ті де id= 

$app->get('/api/users/{id:[0-9]+}',
    function ($id) use ($app) {
    
        $phql = 'SELECT id, first_name,last_name, login, password '
              . 'FROM MyApp\Models\Users '
              . 'WHERE id = :id:'
        ;

        $user = $app
            ->modelsManager
            ->executeQuery(
                $phql,
                [
                    'id' => $id,
                ]
            )
            ->getFirst();

        $response = new Response();
        if ($user === false) {
            $response->setJsonContent(
                [
                    'status' => 'NOT-FOUND'
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    'status' => 'FOUND',
                    'data'   => [
                        'id'   => $user->id,
                        'first_name' => $user->first_name
                    ]
                ]
            );
        }

        return $response;
    }
);


$app->post( '/api/users', function () use ($app) { 
    
       $user = $app->request->getPost();
       
        $phql  = 'INSERT INTO MyApp\Models\Users '
               . '(first_name, last_name, login, password) '
               . 'VALUES '
               . '(:first_name:,:last_name:, :login:, :password:)'
        ;

        $status = $app
                     
            ->modelsManager
            ->executeQuery(
                $phql,
                [            

                    'first_name' => $user['first_name'],                      
                    'last_name' => $user['last_name'],
                    'login' => $user['login'],
                    'password' => $user['password']
                                                          
                ]
            )
        ;

        $response = new Response();

        if ($status->success() === true) {
            $response->setStatusCode(201, 'Created');

            $user->id = $status->getModel()->id;

            $response->setJsonContent(
                [
                    'status' => 'OK',
                    'data'   => $user,
                ]
            );
        } else {
            $response->setStatusCode(409, 'Conflict');

            $errors = [];
            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }

            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }
        return $response;
    }
);

// update
    $app->put('/api/users/{id:[0-9]+}',
            
    function ($id) use ($app) {
            
        $user = $app->request->getJsonRawBody();
                           
        $phql  = 'UPDATE MyApp\Models\Users '
               . 'SET first_name = :first_name:, '
               . 'last_name=:last_name:, '
               . 'login = :login:, '
               . 'password = :password: '
               . 'WHERE id = :id:';

        $status = $app
            ->modelsManager
            ->executeQuery(
                $phql,
                [                    
                    'id'   => $id,
                    'first_name' => $user->first_name,                    
                    'last_name' => $user->last_name,
                    'login' => $user->login,
                    'password' => $user->password                      
                ]
            )
        ;
        
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    'status' => 'OK'
                ]
            );
        } else {
            $response->setStatusCode(409, 'Conflict');

            $errors = [];
            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }

            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }

        return $response;
    }
);

//  delete
$app->delete(
    '/api/users/{id:[0-9]+}',
    function ($id) use ($app) {
        $phql = 'DELETE '
              . 'FROM MyApp\Models\Users '
              . 'WHERE id = :id:';

        $status = $app
            ->modelsManager
            ->executeQuery(
                $phql,
                [
                    'id' => $id,
                ]
            )
        ;

        $response = new Response();
        
        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    'status' => 'OK'
                ]
            );
        } else {
            $response->setStatusCode(409, 'Conflict');

            $errors = [];
            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }

            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }

        return $response;
    }
);

$app->handle(
    $_SERVER["REQUEST_URI"]
);