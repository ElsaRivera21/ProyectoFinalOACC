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

$app -> post('/post', function(Request $request, Response $response, array $args){
    $conn = $this -> db;
    $bd = $GLOBALS['db'];
    $status_http = 200;

    $data = $request -> getParsedBody();
    $arrData = str_replace("'", "\"", $data);

    #$token = $arrData['token'];
    #$arrTk = validaToken($token, $bd, $conn);
    $data = array();
    echo ia($arrData); exit;
    $sql = "INSERT INTO {$bd}.usuarios SET username=:username";

    #$sql .= "fedita=:fedita";
    #$data['fedita'] = date("Y-m-d H:i:s");
    $data['username'] = $arrData['username'];
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    $error = $conn->errorInfo();
    if(intval($error[0] != 0)){
        $arr = array("error"=>array("code" => '230', "detail"=>"Error al insertar {$error[1]}"));
        $status_http = 401;
    }else{
        $arr = array("sucess"=>true, "detail"=>"Datos insertados correctamente");
    }
    #$arr = array("sucess" => true, "meta" => $metadata, "data" => $rs);

    $response -> getBody() -> write(json_encode($arr, JSON_UNESCAPED_UNICODE));
    $newResponse = $response -> withHeader(
        'Content-Type', 'application/json; charset=UTF-8'
    );

    if ($status_http != 200) {
      $newResponse = $response -> withStatus($status_http) -> withHeader(
          'Content-Type', 'application/json; charset=UTF-8'
      );
    }

    return $newResponse;
});

$app->run();
