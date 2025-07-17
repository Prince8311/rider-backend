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

if ($requestMethod == 'GET') {
    require "../../../_db-connect.php";
    global $conn;

    if(isset($_GET['name'])){
        $roleName = mysqli_real_escape_string($conn, $_GET['name']);
        $sql = "SELECT * FROM `roles_permissions` WHERE `role_name`='$roleName'";
        $result = mysqli_query($conn, $sql);
        $permissions = mysqli_fetch_all($result, MYSQLI_ASSOC); 

        $data = [
            'status' => 200,
            'message' => 'Role details fetched',
            'permissions' => $permissions
        ];
        header("HTTP/1.0 200 Details fetched");
        echo json_encode($data); 
    } else {
        $data = [
            'status' => 400,
            'message' => 'No parameter found',
        ];
        header("HTTP/1.0 400 No parameter");
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