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

    case 'get_quizzes':
        if ($method == 'GET') {
            $sql = "SELECT * FROM quizzes";
            $result = $conn->query($sql);
            
            $quizzes = [];
            while ($row = $result->fetch_assoc()) {
                $quizzes[] = $row;
            }
            echo json_encode(["status" => "success", "quizzes" => $quizzes]);
        }
        break;
        
    case 'edit_quiz':
        if ($method == 'PUT') {
            $data = getBody();
            $quiz_id = intval($data['id']);
            $title = $conn->real_escape_string($data['title']);
            $description = $conn->real_escape_string($data['description']);
            $sql = "UPDATE quizzes SET title='$title', description='$description' WHERE id=$quiz_id";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Quiz updated"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;
    
    case 'delete_quiz':
        if ($method == 'DELETE') {
            $data = getBody();
            $quiz_id = intval($data['id']);
            
            $sql = "DELETE FROM quizzes WHERE id=$quiz_id";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Quiz deleted"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;
    
    case 'create_question':
        if ($method == 'POST') {
            $data = getBody();
            $quiz_id = intval($data['quiz_id']);
            $question_text = $conn->real_escape_string($data['question_text']);
            $question_type = $conn->real_escape_string($data['question_type']);
            
            $sql = "INSERT INTO questions (quiz_id, question_text, question_type) VALUES ($quiz_id, '$question_text', '$question_type')";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Question created"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;

    case 'get_questions':
        if ($method == 'GET') {
            if (isset($_GET['quiz_id'])) {
                $quiz_id = intval($_GET['quiz_id']);
                $sql = "SELECT * FROM questions WHERE quiz_id=$quiz_id";
                $result = $conn->query($sql);
                
                
                $questions = [];
                while ($row = $result->fetch_assoc()) {
                    $questions[] = $row;
                }
                
                echo json_encode(["status" => "success", "questions" => $questions]);
            } else {
                echo json_encode(["status" => "error", "message" => "quiz_id required"]);
            }
        }
        break;
    
    case 'edit_question':
        if ($method == 'PUT') {
            $data = getBody();
            $question_id = intval($data['id']);
            $question_text = $conn->real_escape_string($data['question_text']);
            $question_type = $conn->real_escape_string($data['question_type']);
            
            $sql = "UPDATE questions SET question_text='$question_text', question_type='$question_type' WHERE id=$question_id";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Question updated"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;



    case 'delete_question':
        if ($method == 'DELETE') {
            $data = getBody();
            $question_id = intval($data['id']);
            
            $sql = "DELETE FROM questions WHERE id=$question_id";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Question deleted"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
        break;
        
    default:
    echo json_encode(["status" => "error", "message" => "Invalid endpoint"]);
}

$conn->close();

?>