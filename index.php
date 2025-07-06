<?php
require __DIR__ . '/vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/api/models/Erreur.php'; 

// --- Début du Routage ---

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));

$scriptNameSegments = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
$requestSegments = array_slice($uriSegments, count($scriptNameSegments) -1);


$controllerName = array_shift($requestSegments);

if (empty($controllerName)) {
    http_response_code(404);
    echo json_encode(new Erreur("Ressource non spécifiée.", false));
    exit();
}

$controllerFile = __DIR__ . '/api/controllers/' . ucfirst($controllerName) . 'Controller.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    require_once __DIR__ . '/api/controllers/BaseController.php'; 

    $className = ucfirst($controllerName) . 'Controller';
    
    $param = !empty($requestSegments) ? $requestSegments[0] : null;

    $database = new Database();
    $db = $database->connect();
    
    $controller = new $className($db);
    $controller->handleRequest($param);

} else {
    http_response_code(404);
    echo json_encode(new Erreur("La ressource '$controllerName' n'a pas été trouvée.", false));
    exit();
}
?> 