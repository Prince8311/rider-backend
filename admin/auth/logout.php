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

require "../../utils/auth-helper.php";

if ($requestMethod == 'POST') {

    require "../../_db-connect.php";
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
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $data = [
                'status' => 401,
                'message' => 'Missing or malformed Authorization token',
            ];
            header("HTTP/1.0 401 Unauthorized");
            echo json_encode($data);
        } else {
            $frontendToken = $matches[1];
            if ($cookieToken !== $frontendToken) {
                $data = [
                    'status' => 401,
                    'message' => 'Authentication mismatch',
                ];
                header("HTTP/1.0 401 Unauthorized");
                echo json_encode($data);
            } else {
                session_destroy();
                setcookie("authToken", "", time() - 3600, "/", ".ticketbay.in", true, true);
                $data = [
                    'status' => 200,
                    'message' => 'Logged out successfylly.',
                ];
                header("HTTP/1.0 200 Logged Out");
                echo json_encode($data);
            }
        }
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