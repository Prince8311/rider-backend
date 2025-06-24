<?php 

session_start();
header('Access-Control-Allow-Origin: *');
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
    file_put_contents('auth_debug.log', "Header: " . var_export($authHeader, true) . PHP_EOL, FILE_APPEND);

    if (!isset($cookieToken) || empty($cookieToken)) {
        $data = [
            'status' => 401,
            'message' => 'Authentication error',
            'all_headers' => getallheaders()
        ];
        header("HTTP/1.0 401 Authentication error");
        echo json_encode($data);
        exit;
    } 

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $data = [
            'status' => 401,
            'message' => 'Missing or malformed Authorization token',
        ];
        header("HTTP/1.0 401 Unauthorized");
        echo json_encode($data);
        exit;
    } 

    $frontendToken = $matches[1];

    $data = [
        'status' => 200,
        'token' => $frontendToken,
    ];
    header("HTTP/1.0 200 User list fetched");
    echo json_encode($data);

} else{
    $data = [
        'status' => 405,
        'message' => $requestMethod. ' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}

?>