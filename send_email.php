<?php
try {
    require '../vendor/autoload.php';
    use Dotenv\Dotenv;

    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $apiKey = $_ENV['SENDINBLUE_API_KEY'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['text'] ?? '';
        $email = $_POST['gmail'] ?? '';
        $phone = $_POST['number'] ?? '';
        $message = $_POST['massage'] ?? '';

        if (!$name || !$email || !$phone) {
            echo "error";
            exit;
        }

        $data = array(
            "sender" => array("name" => $name, "email" => "website@cypherleap.com"),
            "to" => array(array("email" => "josh.jayan@cypherleap.com", "name" => "Website CypherLeap")),
            "subject" => "New Contact Form Submission",
            "htmlContent" => "
                <h1>Contact Form Enquiry</h1>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Message:</strong><br>$message</p>"
        );

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key: ' . $apiKey,
        ));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 201) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }
} catch (Throwable $e) {
    echo "error";
}
?>
