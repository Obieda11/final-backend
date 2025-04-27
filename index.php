<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quiz_app";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}



header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$endpoint = $path[count($path) - 1];

function getBody() {
    return json_decode(file_get_contents('php://input'), true);
}

switch ($endpoint) {

    case 'register':
        if ($method == 'POST') {
            $data = getBody();
            $username = $conn->real_escape_string($data['username']);
            $email = $conn->real_escape_string($data['email']);
            $password = password_hash($data['password'], PASSWORD_BCRYPT);

            $sql = "INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$password')";

            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "User registered"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;



    case 'login':
        if ($method == 'POST') {
            $data = getBody();
            $email = $conn->real_escape_string($data['email']);
            $password = $data['password'];
    
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $result = $conn->query($sql);
    
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password_hash'])) {
                    echo json_encode(["status" => "success", "user" => ["id" => $user['id'], "username" => $user['username']]]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid password"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "User not found"]);
            }
        }
        break;
    
    case 'create_quiz':
        if ($method == 'POST') {
            $data = getBody();
            $title = $conn->real_escape_string($data['title']);
            $description = $conn->real_escape_string($data['description']);
            $created_by = intval($data['created_by']);
            $sql = "INSERT INTO quizzes (title, description, created_by) VALUES ('$title', '$description', $created_by)";
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Quiz created"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

        
?>