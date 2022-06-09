<?php
#Karel Pacheco Ramírez

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Slim;

require '../../../vendor/autoload.php';
require '../config.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['dbms'] = $dbms;
$config['db']['host'] = $host;
$config['db']['user'] = $user;
$config['db']['pass'] = $pass;
$config['db']['dbname'] = $db;

#echo ia($config);

$app = new \Slim\App([
  'settings' => $config
]);

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $res, $next) {
  $response = $next($req, $res);
  return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
}); 

$container = $app->getContainer();
$container['db'] = function ($c) {
  $db = $c['settings']['db'];
  $pdo = new PDO("{$db['dbms']}:host={$db['host']};dbname={$db['dbname']};charset=utf8", $db['user'], $db['pass']);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  return $pdo;
};

$app->post('/registro', function (Request $request, Response $response, array $args) {
  $conn = $this->db;
  $bd = $GLOBALS['db'];

  $data = $request->getParsedBody();
  $arrData = str_replace("'", "\"", $data);
  $http_status = 200;

  $arr = array();
  $user = $data['username'];

  if (!empty($user)) {
    $sql = "SELECT * 
              FROM {$bd}.usuarios 
              WHERE username = '{$user}'";

    $rs = query($sql, $conn);

    if (count($rs) > 0) {
      $arr = array(
        "error" => array(
          "code" => 228,
          "detail" => "usuario o correo ya registrado"
        )
      );

      $http_status = 401;
    } else {
      $data = [
        'username' => $arrData['username'],
      ];
      $sql = "INSERT INTO {$bd}.usuarios 
          SET username=:username;";
      $stmt = $conn->prepare($sql);
      $stmt->execute($data);
      $error = $conn->errorInfo();
      if (intval($error[0]) != 0) {
        $arr = array(
          "error" => array(
            "code" => 230,
            "detail" => "error al insertar usuario: {$error[1]}"
          )
        );

        $http_status = 401;
      } else {
        $arr = array(
          "success" => true,
          "detail" => "usuario insertado correctamente"
        );
      }
    }
  } else {
    $arr = array(
      "error" => array(
        "code" => 230,
        "detail" => "usuario no valido"
      )
    );
    $http_status = 401;
  }

  $response->getBody()->write(json_encode($arr, JSON_UNESCAPED_UNICODE));

  if ($http_status != 200) {
    $newResponse = $response->withStatus($http_status);
  }

  $newResponse = $response->withHeader(
    'Content-Type',
    'application/json; charset=UTF-8'
  );


  return $newResponse;
});

$app->run();
