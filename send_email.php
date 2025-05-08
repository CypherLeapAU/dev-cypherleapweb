<?php
// === 1) QUIETLY SUPPRESS ALL PHP ERRORS ===
ini_set('display_errors', 0);
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'error';
    exit;
}

// === 2) GRAB & VALIDATE FORM DATA ===
$name    = trim($_POST['text']    ?? '');
$email   = trim($_POST['gmail']   ?? '');
$phone   = trim($_POST['number']  ?? '');
$message = trim($_POST['massage'] ?? '');

if (!$name || !$email || !$phone) {
    echo 'error';
    exit;
}

// === 3) LOAD DOTENV & API KEY ===
$apiKey = '';
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
    // adjust path if your .env sits elsewhere
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    $apiKey = $_ENV['SENDINBLUE_API_KEY'] ?? '';
}

if (!$apiKey) {
    echo 'error';
    exit;
}

// === 4) BUILD THE BREVO PAYLOAD ===
$data = [
    'sender'      => ['name' => $name,  'email' => 'website@cypherleap.com'],
    'to'          => [['email' => 'josh.jayan@cypherleap.com', 'name' => 'Website CypherLeap']],
    'subject'     => 'New Contact Form Submission',
    'htmlContent' =>
        "<h1>Contact Form Enquiry</h1>"
      . "<p><strong>Name:</strong> {$name}</p>"
      . "<p><strong>Email:</strong> {$email}</p>"
      . "<p><strong>Phone:</strong> {$phone}</p>"
      . "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>"
];

// === 5) SEND VIA cURL ===
$ch = curl_init('https://api.brevo.com/v3/smtp/email');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'api-key: ' . $apiKey,
    ],
]);

curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// === 6) RETURN ONLY “success” or “error” ===
echo $code === 201 ? 'success' : 'error';
