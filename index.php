<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ .'/vendor/tecnickcom/tcpdf/tcpdf.php';

use ProdajaKarata\core\Router;
use ProdajaKarata\core\Request;
use ProdajaKarata\model\InventoryModel;
use ProdajaKarata\core\db;

session_start();

$router = new Router();
$response = $router->route(new Request());

if(isset($response[1])){
    echo $response[0];
    echo $response[1];
} else echo $response[0];


//Sta Nedostaje?
//2.Dodati Password
//3.Kontroleri
//4.Warn i logovanje

?>