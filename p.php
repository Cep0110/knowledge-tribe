  <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

// If accessed directly (GET), show error and link back
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error - Knowledge Tribe</title>
        <style>
            body { font-family: Arial; background: #f4f4f4; text-align: center; padding: 50px; }
            .error-box { background: white; padding: 40px; border-radius: 10px; max-width: 500px; margin: 0 auto; border-left: 5px solid #ff7200; }
            .btn { background: #ff7200; color: white; padding: 12px 24px; text-decoration: none; display: inline-block; margin-top: 20px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>⚠️ Form Not Submitted</h2>
            <p>You accessed this page directly. Please submit the registration form from the homepage.</p>
            <a href="index.html#registration" class="btn">Go to Registration Form</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Process POST request
try {
    // Validate required fields
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['first-name']) || 
        empty($_POST['last-name']) || empty($_POST['email']) || empty($_POST['mobile'])) {
        throw new Exception("All required fields must be filled!");
    }

    // Check database connection
    if (!isset($pdo)) {
        throw new Exception("Database connection failed");
    }

    // Prepare data
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $firstName = htmlspecialchars(trim($_POST['first-name']));
    $lastName = htmlspecialchars(trim($_POST['last-name']));
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $gender = $_POST['gender'] ?? null;
    $course_id = $_POST['courses'] ?? null;
    $countryCode = $_POST['country-code'] ?? '+251';
    $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile']);
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $city = htmlspecialchars(trim($_POST['city'] ?? ''));
    $pinCode = htmlspecialchars(trim($_POST['pin-code'] ?? ''));
    $country = $_POST['country'] ?? '';

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format!");
    }

    // Check duplicate username
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    if ($check->fetch()) {
        throw new Exception("Username '$username' already exists! Please choose another.");
    }

    // Create uploads directory if missing
    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }

    // Handle file upload
    $imageName = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $targetDir = "uploads/";
        $fileName = time() . '_' . basename($_FILES["profile_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            throw new Exception("Only JPG, PNG, JPEG & GIF files are allowed");
        }
        
        if ($_FILES['profile_image']['size'] > 2097152) {
            throw new Exception("File size must be less than 2MB");
        }
        
        if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
            throw new Exception("Failed to upload image. Check folder permissions (uploads/ must be writable)");
        }
        $imageName = $fileName;
    }

    // Hobbies
    $hobbies = [];
    if (isset($_POST['hobby1'])) $hobbies[] = 'Reading';
    if (isset($_POST['hobby2'])) $hobbies[] = 'Playing';
    if (isset($_POST['hobby3'])) $hobbies[] = 'Singing';
    if (isset($_POST['hobby4'])) $hobbies[] = 'Dancing';
    $hobbiesStr = implode(', ', $hobbies);

    // TRANSACTION START
    $pdo->beginTransaction();
    
    // 1. Insert User
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'student')");
    if (!$stmt->execute([$username, $hashedPassword, $email])) {
        throw new Exception("Failed to create user account");
    }
    $userId = $pdo->lastInsertId();
    
    if (!$userId) {
        throw new Exception("Could not retrieve user ID after insertion");
    }

    // 2. Insert Student
    $phone = $countryCode . $mobile;
    $stmt = $pdo->prepare("INSERT INTO students (user_id, first_name, last_name, dob, gender, phone, address, city, pin_code, country, hobbies, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $studentData = [$userId, $firstName, $lastName, $dob, $gender, $phone, $address, $city, $pinCode, $country, $hobbiesStr, $imageName];
    
    if (!$stmt->execute($studentData)) {
        throw new Exception("Failed to create student record: " . print_r($stmt->errorInfo(), true));
    }
    
    $studentId = $pdo->lastInsertId();
    
    if (!$studentId) {
        throw new Exception("Could not retrieve student ID after insertion");
    }

    // 3. Course Registration
    if (!empty($course_id)) {
        $stmt = $pdo->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
        $stmt->execute([$studentId, $course_id]);
    }

    // 4. Qualifications
    $qualifications = [
        ['level' => '10th', 'board' => $_POST['10-board'] ?? '', 'percentage' => $_POST['10-percentage'] ?? '', 'year' => $_POST['10-year-of-passing'] ?? ''],
        ['level' => '12th', 'board' => $_POST['12-board'] ?? '', 'percentage' => $_POST['12-percentage'] ?? '', 'year' => $_POST['12-year-of-passing'] ?? ''],
        ['level' => 'Graduation', 'board' => $_POST['grd-board'] ?? '', 'percentage' => $_POST['grd-percentage'] ?? '', 'year' => $_POST['grd-year-of-passing'] ?? '']
    ];

    $stmt = $pdo->prepare("INSERT INTO qualifications (student_id, level, board, percentage, year_of_passing) VALUES (?, ?, ?, ?, ?)");
    foreach ($qualifications as $q) {
        if (!empty($q['board'])) {
            $stmt->execute([$studentId, $q['level'], $q['board'], $q['percentage'], $q['year']]);
        }
    }

    // COMMIT
    $pdo->commit();
    
    // ========== EMAIL SECTION (FR-23) ==========
    // Send confirmation email (logged to file for XAMPP)
    $to = $email;
    $subject = "Welcome to Knowledge Tribe Company!";
    $body = "Dear $firstName,\n\nCongratulations! You have successfully registered for our system. We are excited to have you join our tribe.\n\nYour username: $username\n\nBest regards,\nKnowledge Tribe Company";
    $headers = "From: knowledgetribe@gmail.com";
    
    // Log email using absolute path (guaranteed to work)
    $logFile = __DIR__ . "/mail_log.txt";
    $logEntry = "[" . date('Y-m-d H:i:s') . "] Registration Confirmation To: $to | Subject: $subject\n$body\n---\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    // ========== END EMAIL SECTION ==========
    
    // Verify insertion immediately
    $verify = $pdo->prepare("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
    $verify->execute([$studentId]);
    $record = $verify->fetch();
    
    // Log success to separate log
    $regLog = date('Y-m-d H:i:s') . " - SUCCESS: User $username (ID: $userId, Student ID: $studentId) registered\n";
    file_put_contents(__DIR__ . "/registration_log.txt", $regLog, FILE_APPEND);

    // Show success message
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Success - Knowledge Tribe</title>
        <style>
            body { font-family: Arial; background: #f4f4f4; text-align: center; padding: 50px; }
            .success-box { background: white; padding: 40px; border-radius: 10px; max-width: 600px; margin: 0 auto; border-left: 5px solid #28a745; }
            .btn { background: #ff7200; color: white; padding: 12px 24px; text-decoration: none; display: inline-block; margin: 10px; border-radius: 5px; }
            .debug-info { background: #f8f9fa; padding: 15px; margin-top: 20px; text-align: left; font-size: 12px; border: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <div class="success-box">
            <h2 style="color: #28a745;">✓ Registration Successful!</h2>
            <p><strong>Welcome, <?php echo htmlspecialchars($firstName); ?>!</strong></p>
            <p>Your account has been created successfully.</p>
            <p>Username: <strong><?php echo htmlspecialchars($username); ?></strong></p>
            
            <div style="margin-top: 30px;">
                <a href="login.php" class="btn">Login Now</a>
                <a href="index.html" class="btn">Back to Home</a>
            </div>
            
            <div class="debug-info">
                <strong>Debug Info:</strong><br>
                User ID: <?php echo $userId; ?><br>
                Student ID: <?php echo $studentId; ?><br>
                Email Logged: <?php echo htmlspecialchars($email); ?><br>
                <?php if ($record): ?>
                <span style="color:green;">✓ Verified in database: <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></span>
                <?php else: ?>
                <span style="color:red;">✗ WARNING: Not found in database immediately after insert!</span>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    $errorLog = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . "/error_log.txt", $errorLog, FILE_APPEND);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error - Knowledge Tribe</title>
        <style>
            body { font-family: Arial; background: #f4f4f4; text-align: center; padding: 50px; }
            .error-box { background: white; padding: 40px; border-radius: 10px; max-width: 600px; margin: 0 auto; border-left: 5px solid #dc3545; }
            .btn { background: #ff7200; color: white; padding: 12px 24px; text-decoration: none; display: inline-block; margin-top: 20px; border-radius: 5px; }
            .error-msg { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2 style="color: #dc3545;">✗ Registration Failed</h2>
            <div class="error-msg">
                <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>
            <a href="javascript:history.back()" class="btn">Go Back</a>
            <a href="index.html" class="btn">Go to Home</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
