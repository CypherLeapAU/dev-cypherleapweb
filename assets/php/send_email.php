<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['text']);
    $email = htmlspecialchars($_POST['gmail']);
    $phone = htmlspecialchars($_POST['number']);
    $message = htmlspecialchars($_POST['massage']);

    $to = "josh.jayan@cypherleap.com; // Replace with your email
    $subject = "New Contact Form Submission from CypherLeap";
    $headers = "From: website@cypherleap.com\r\n"; // Replace with your domain
    $headers .= "Reply-To: $email\r\n";

    $body = "You have received a new message from the contact form:\n\n";
    $body .= "Name: $name\n";
    $body .= "Email: $email\n";
    $body .= "Phone: $phone\n";
    $body .= "Message:\n$message\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
