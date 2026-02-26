<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitization and Filtering
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password'];
    $firstName = filter_input(INPUT_POST, 'first-name', FILTER_SANITIZE_SPECIAL_CHARS);
    $lastName = filter_input(INPUT_POST, 'last-name', FILTER_SANITIZE_SPECIAL_CHARS);
    $dob = $_POST['dob'];
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $gender = $_POST['gender'];
    $course_id = $_POST['courses'];
    $countryCode = $_POST['country-code'];
    $mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_NUMBER_INT);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS);
    $pinCode = filter_input(INPUT_POST, 'pin-code', FILTER_SANITIZE_SPECIAL_CHARS);
    $country = $_POST['country'];
    
    // File Upload handling
    $imageName = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $targetDir = "uploads/";
        $fileName = time() . '_' . basename($_FILES["profile_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array(strtolower($fileType), $allowTypes)) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
                $imageName = $fileName;
            }
        }
    }

    $hobbies = [];
    if (isset($_POST['hobby1'])) $hobbies[] = 'Reading';
    if (isset($_POST['hobby2'])) $hobbies[] = 'Playing';
    if (isset($_POST['hobby3'])) $hobbies[] = 'Singing';
    if (isset($_POST['hobby4'])) $hobbies[] = 'Dancing';
    $hobbiesStr = implode(', ', $hobbies);

    // Basic Validation
    if (empty($username) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid input. Please check your data.");
    }

    try {
        $pdo->beginTransaction();

        // 1. Create User
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'student')");
        $stmt->execute([$username, $hashedPassword, $email]);
        $userId = $pdo->lastInsertId();

        // 2. Create Student
        $phone = $countryCode . $mobile;
        $stmt = $pdo->prepare("INSERT INTO students (user_id, first_name, last_name, dob, gender, phone, address, city, pin_code, country, hobbies, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $firstName, $lastName, $dob, $gender, $phone, $address, $city, $pinCode, $country, $hobbiesStr, $imageName]);
        $studentId = $pdo->lastInsertId();

        // 3. Register for Course
        if (!empty($course_id)) {
            $stmt = $pdo->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
            $stmt->execute([$studentId, $course_id]);
        }

        // 4. Add Qualifications
        $qualifications = [
            ['level' => '10th', 'board' => $_POST['10-board'], 'percentage' => $_POST['10-percentage'], 'year' => $_POST['10-year-of-passing']],
            ['level' => '12th', 'board' => $_POST['12-board'], 'percentage' => $_POST['12-percentage'], 'year' => $_POST['12-year-of-passing']],
            ['level' => 'Graduation', 'board' => $_POST['grd-board'], 'percentage' => $_POST['grd-percentage'], 'year' => $_POST['grd-year-of-passing']]
        ];

        $stmt = $pdo->prepare("INSERT INTO qualifications (student_id, level, board, percentage, year_of_passing) VALUES (?, ?, ?, ?, ?)");
        foreach ($qualifications as $q) {
            $stmt->execute([$studentId, $q['level'], $q['board'], $q['percentage'], $q['year']]);
        }

        // --- FR-23: Confirmation Email ---
        $to = $email;
        $subject = "Welcome to Knowledge Tribe Company!";
        $body = "Dear $firstName,\n\nCongratulations! You have successfully registered for our system. We are excited to have you join our tribe.\n\nYour username: $username\n\nBest regards,\nKnowledge Tribe Company";
        $headers = "From: knowledgetribe@gmail.com";
        
        // Simulating email by logging it for demonstration (as actual mail() requires local SMTP)
        $logEntry = "[" . date('Y-m-d H:i:s') . "] Registration Confirmation To: $to | Subject: $subject\n$body\n---\n";
        file_put_contents("mail_log.txt", $logEntry, FILE_APPEND);

        $pdo->commit();
        echo "<script>alert('Registration Successful! Check your email for confirmation.'); window.location.href='index.html';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error during registration: " . $e->getMessage());
    }
}
?>
