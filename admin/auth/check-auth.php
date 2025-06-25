<?php 

session_start();
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Content-Type: application/json');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

require "../../../utils/auth-helper.php";

if($requestMethod == 'GET') {

    require "../../../_db-connect.php";
    global $conn;

    $authHeader = getAuthorizationHeader();
    $cookieToken = $_COOKIE['authToken'] ?? '';

    if (!isset($cookieToken) || empty($cookieToken)) {
        $data = [
            'status' => 401,
            'message' => 'Authentication error'
        ];
        header("HTTP/1.0 401 Authentication error");
        echo json_encode($data);
    } else {
        $data = [
            'status' => 200,
            'message' => 'Authenticated',
            'authToken' => $cookieToken
        ];
        header("HTTP/1.0 200 Authenticated");
        echo json_encode($data);
    }

} else{
    $data = [
        'status' => 405,
        'message' => $requestMethod. ' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}

?>