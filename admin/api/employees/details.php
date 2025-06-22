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


if($requestMethod == 'GET') {

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
        if(isset($_GET['id'])) {
            $userId = mysqli_real_escape_string($conn, $_GET['id']);
            $sql = "SELECT * FROM `users` WHERE `id`='$userId' AND `user_type`='admin'";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) === 1) {
                $res = mysqli_fetch_assoc($result);
                $data = [
                    'status' => 200,
                    'message' => 'Employee details fetched',
                    'user' => $res
                ];
                header("HTTP/1.0 200 Employee details fetched");
                echo json_encode($data);
            } else {
                $data = [
                    'status' => 400,
                    'message' => 'No employee found',
                    'user' => $res
                ];
                header("HTTP/1.0 400 No employee found");
                echo json_encode($data);
            }
        } else {
            $data = [
                'status' => 400,
                'message' => 'No id parameter found'
            ];
            header("HTTP/1.0 400");
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