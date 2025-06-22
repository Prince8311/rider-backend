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


if($requestMethod == 'POST') {

    require "../../../_db-connect.php";
    global $conn;

    if (!isset($_COOKIE['authToken']) || empty($_COOKIE['authToken'])) {
        $data = [
            'status' => 401,
            'message' => 'Authentication error'
        ];
        header("HTTP/1.0 401 Authentication error");
        echo json_encode($data);
    } else {
        $inputData = json_decode(file_get_contents("php://input"), true);

        if(!empty($inputData)) {

        } else {
            $data = [
                'status' => 400,
                'message' => 'Validation error'
            ];
            header("HTTP/1.0 400 Validation error");
            echo json_encode($data);
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