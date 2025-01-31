<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = filter_var(trim($_POST['text']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['gmail']), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($_POST['number']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['massage']), FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        echo "error";
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "error";
        exit;
    }

    // Email details
    $to = "josh.jayan@cypherleap.com"; // Replace with your email
    $subject = "New Contact Form Submission from CypherLeap";
    $headers = "From: website@cypherleap.com\r\n"; // Replace with your domain
    $headers .= "Reply-To: $email\r\n";

    $body = "You have received a new message from the contact form:\n\n";
    $body .= "Name: $name\n";
    $body .= "Email: $email\n";
    $body .= "Phone: $phone\n";
    $body .= "Message:\n$message\n";

    // Send email
    if (mail($to, $subject, $body, $headers)) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "error";
}
?>
