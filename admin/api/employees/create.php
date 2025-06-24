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

            $userType = 'admin';
            $user = mysqli_real_escape_string($conn, $inputData['name']);
            $phone = mysqli_real_escape_string($conn, $inputData['phone']);
            $email = mysqli_real_escape_string($conn, $inputData['email']);
            $userRole = mysqli_real_escape_string($conn, $inputData['user_role']);
            $password = mysqli_real_escape_string($conn, $inputData['password']);
            $cPassword = mysqli_real_escape_string($conn, $inputData['confirm_password']);

            if($cPassword === $password) {

                $checkSql = "SELECT * FROM `users` WHERE `email`='$email' AND `phone`='$phone' AND `user_type`='$userType'";
                $checkResult = mysqli_query($conn, $checkResult);

                if(mysqli_num_rows($checkResult) === 1) {

                    $hashPass = password_hash($password,PASSWORD_DEFAULT);
                    $insertSql = "INSERT INTO `users`(`name`, `email`, `phone`, `user_type`, `password`, `user_role`) VALUES ('$user','$email','$phone','$userType','$hashPass','$userRole')";
                    $insertResult = mysqli_query($conn, $insertSql);

                    if($insertResult) {
                        $data = [
                            'status' => 200,
                            'message' => 'Admin user created'
                        ];
                        header("HTTP/1.0 200 Admin created");
                        echo json_encode($data);
                    } else {
                        $data = [
                            'status' => 500,
                            'message' => 'Internal Server Error'
                        ];
                        header("HTTP/1.0 500 Internal Server Error");
                        echo json_encode($data);
                    }

                } else {
                    $data = [
                        'status' => 400,
                        'message' => 'User already exists'
                    ];
                    header("HTTP/1.0 400 User exists");
                    echo json_encode($data);
                }

            } else {
                $data = [
                    'status' => 400,
                    'message' => 'Validation error'
                ];
                header("HTTP/1.0 400 Validation error");
                echo json_encode($data);
            }

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