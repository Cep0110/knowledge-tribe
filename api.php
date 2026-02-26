<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

// Very basic API authentication (could be token-based)
// For this demo, we check if there's a valid session or a basic key
$apiKey = isset($_GET['api_key']) ? $_GET['api_key'] : null;

if ($apiKey !== "tribe_secret_key") {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized access. Invalid API key."]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    case 'list':
        $stmt = $pdo->query("SELECT s.id, s.first_name, s.last_name, s.country, c.name as course 
                             FROM students s 
                             LEFT JOIN student_courses sc ON s.id = sc.student_id
                             LEFT JOIN courses c ON sc.course_id = c.id");
        $students = $stmt->fetchAll();
        echo json_encode(["status" => "success", "data" => $students]);
        break;
        
    case 'stats':
        $total = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        echo json_encode(["status" => "success", "total_students" => $total]);
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Invalid action."]);
        break;
}
?>
