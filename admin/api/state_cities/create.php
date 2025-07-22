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

require "../../../utils/middleware.php";

$authResult = authenticateRequest();

if (!$authResult['authenticated']) {
    $data = [
        'status' => $authResult['status'],
        'message' => $authResult['message']
    ];
    header("HTTP/1.0 " . $authResult['status']);
    echo json_encode($data);
    exit;
}

if ($requestMethod == 'POST') {
    require "../../../_db-connect.php";
    global $conn;

    $inputData = json_decode(file_get_contents("php://input"), true);
    if (!empty($inputData)) {
        $state = mysqli_real_escape_string($conn, $inputData['state']);
        $city = mysqli_real_escape_string($conn, $inputData['city']);

        $checkSql = "SELECT * FROM `state_cities` WHERE `state`='$state' AND `cities`='$city'";
        $checkResult = mysqli_query($conn, $checkSql);

        if(mysqli_num_rows($checkResult) > 0) {
            $data = [
                'status' => 400,
                'message' => 'City already exists'
            ];
            header("HTTP/1.0 400 City exists");
            echo json_encode($data);
            exit;
        }

        $sql = "INSERT INTO `state_cities`(`state`, `cities`) VALUES ('$state','$city')";
        $result = mysqli_query($conn, $sql);

        if($result) {
            $data = [
                'status' => 200,
                'message' => 'City added successfully.'
            ];
            header("HTTP/1.0 200 Ok");
            echo json_encode($data);
        } else {
            $data = [
                'status' => 500,
                'message' => 'Database error: ' . $error
            ];
            header("HTTP/1.0 500 Internal Server Error");
            echo json_encode($data);
        }
    } else {
        $data = [
            'status' => 400,
            'message' => 'Empty request data'
        ];
        header("HTTP/1.0 400 Bad Request");
        echo json_encode($data);
    }
} else {
    $data = [
        'status' => 405,
        'message' => $requestMethod . ' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}

?>