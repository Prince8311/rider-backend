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

require "../../../utils/auth-helper.php";

if($requestMethod == 'GET') {

    require "../../../_db-connect.php";
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
            if (empty($cookieToken) || $cookieToken !== $frontendToken) {
                $data = [
                    'status' => 401,
                    'message' => 'Authentication mismatch',
                ];
                header("HTTP/1.0 401 Unauthorized");
                echo json_encode($data);
            } else {
                $sql = "SELECT * FROM `users` WHERE `user_type`='user'";
                $result = mysqli_query($conn, $sql);
                $totalUsers = mysqli_num_rows($result);
                $limit = 10; 
                $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
                ? (int)$_GET['page']
                : 1;
                $offset = ($page - 1) * $limit;
        
                $limitSql = "SELECT * FROM `users` WHERE `user_type`='user' LIMIT $limit OFFSET $offset";
                $limitResult = mysqli_query($conn, $limitSql);
                $users = mysqli_fetch_all($limitResult, MYSQLI_ASSOC);
        
                $data = [
                    'status' => 200,
                    'message' => 'User list fetched',
                    'totalCount' => $totalUsers,
                    'currentPage' => $page,
                    'users' => $users,
                ];
                header("HTTP/1.0 200 User list fetched");
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