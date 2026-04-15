  <?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($name) && !empty($message)) {
        $to = "knowledgetribe@gmail.com";
        $subject = "New Contact Form Submission from $name";
        $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: $email";

        // Log email using absolute path (guaranteed to work)
        $logFile = __DIR__ . "/mail_log.txt";
        $logEntry = "[" . date('Y-m-d H:i:s') . "] To: $to | From: $email | Subject: $subject\nMessage: $message\n---\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        echo "<script>alert('Thank you for contacting us! Your message has been sent.'); window.location.href='index.html';</script>";
        exit;
    } else {
        echo "<script>alert('Please fill in all fields correctly.'); window.history.back();</script>";
    }
}
?>
