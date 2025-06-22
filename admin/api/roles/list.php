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
        $sql = "SELECT * FROM `roles_permissions`";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            $roles = mysqli_fetch_all($result, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Roles fetched',
                'roles' => $roles,
            ];
            header("HTTP/1.0 200 Roles fetched");
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