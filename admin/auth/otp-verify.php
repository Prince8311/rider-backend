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

        $sql = "SELECT * FROM `users` WHERE `id` = '$userId'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $savedOtp = $row['mail_otp'];

        if($savedOtp == $otp) {
            $authToken = bin2hex(random_bytes(32));
            setcookie("authToken", $authToken, time() + 86400, "/", "ticketbay.in", true, true);

            $data = [
                'status' => 200,
                'message' => 'Login Success',
                'userId' => $userId,
                'authToken' => $authToken
            ];
            header("HTTP/1.0 200 OK");
            echo json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'Wrong OTP',
                'token' => $token
            ];
            header("HTTP/1.0 404 Wrong OTP");
            echo json_encode($data);
        }
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