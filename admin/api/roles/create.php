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

require "../../../utils/auth-helper.php";

if ($requestMethod == 'POST') {

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
                $inputData = json_decode(file_get_contents("php://input"), true);
                if (!empty($inputData)) {
                    $roleName = mysqli_real_escape_string($conn, $inputData['roleName']);
                    $permissions = $inputData['permissions'];
                    $success = true;

                    foreach ($permissions as $permissionType => $perms) {
                        $create = isset($perms['create']) && $perms['create'] ? 1 : 0;
                        $view   = isset($perms['view']) && $perms['view'] ? 1 : 0;
                        $edit   = isset($perms['edit']) && $perms['edit'] ? 1 : 0;
                        $delete = isset($perms['delete']) && $perms['delete'] ? 1 : 0;

                        $permissionTypeEscaped = mysqli_real_escape_string($conn, $permissionType);

                        $sql = "INSERT INTO `roles_permissions`(`role_name`, `permission`, `p_create`, `p_view`, `p_edit`, `p_delete`) VALUES ('$roleName', '$permissionTypeEscaped', $create, $view, $edit, $delete)";

                        if (!mysqli_query($conn, $sql)) {
                            $success = false;
                            $error = mysqli_error($conn);
                            break;
                        }
                    }

                    if ($success) {
                        $data = [
                            'status' => 200,
                            'message' => 'Role created successfully.'
                        ];
                        header("HTTP/1.0 200 OK");
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
                        'message' => 'Validation error'
                    ];
                    header("HTTP/1.0 400 Validation error");
                    echo json_encode($data);
                }
            }
        }
    }
} else {
    $data = [
        'status' => 405,
        'message' => $requestMethod . ' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
