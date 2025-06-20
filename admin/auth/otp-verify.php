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

    require "../../_db-connect.php";
    global $conn;

    $userId = $_SESSION['userId'];
    $inputData = json_decode(file_get_contents("php://input"), true);

    if(!empty($inputData)) {
        $otp = mysqli_real_escape_string($conn, $inputData['otp']);
        $data = [
            'status' => 200,
            'userId' => $userId
        ];
        header("HTTP/1.0 200 OK");
        echo json_encode($data);
    } else {
        $data = [
            'status' => 400,
            'message' => 'Validation failed',
        ];
        header("HTTP/1.0 400 Validation failed");
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