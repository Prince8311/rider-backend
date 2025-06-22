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
        $sql = "SELECT * FROM `users` WHERE `user_type`='rider'";
        $result = mysqli_query($conn, $sql);
        $totalUsers = mysqli_num_rows($result);
        $limit = 10; 
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
        ? (int)$_GET['page']
        : 1;
        $offset = ($page - 1) * $limit;

        $limitSql = "SELECT * FROM `users` WHERE `user_type`='rider' LIMIT $limit OFFSET $offset";
        $limitResult = mysqli_query($conn, $limitSql);
        $users = mysqli_fetch_all($limitResult, MYSQLI_ASSOC);

        $data = [
            'status' => 200,
            'message' => 'Rider list fetched',
            'totalCount' => $totalUsers,
            'currentPage' => $page,
            'users' => $users,
        ];
        header("HTTP/1.0 200 Rider list fetched");
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