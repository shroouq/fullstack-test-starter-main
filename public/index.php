<?php

$origin = 'http://localhost:3000';

header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../vendor/autoload.php';
use App\Classes\Database;



if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->post('/graphql', [App\Controller\GraphQL::class, 'handle']);
    $r->get('/graphql', [App\Controller\GraphQL::class, 'handle']);


    // Serve GraphiQL HTML file
    $r->get('/graphiql', function () {
        $filePath = __DIR__ . '/../graphiql.html';

        if (!file_exists($filePath)) {
            http_response_code(404);
            return "File not found at: " . $filePath;
        }

        header('Content-Type: text/html');
        return file_get_contents($filePath);
    });
});


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Remove the folder prefix (e.g., /fullstack-test-starter-main/public)
if (strpos($uri, $scriptName) === 0) {
    $uri = substr($uri, strlen($scriptName));
}

$routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], rtrim($uri, '/'));



switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'hello']);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        header('Allow: ' . implode(', ', $routeInfo[1]));
        echo json_encode(['error' => 'Method Not Allowed']);
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $response = is_callable($handler) ? $handler($vars) : call_user_func($handler, $vars);
        echo $response;
        break;
}


