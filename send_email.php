<?php
// ——————————————————————
// 1) CONFIGURE ERROR SUPPRESSION & LOG FILE
// ——————————————————————
ini_set('display_errors', 0);
error_reporting(0);

define('LOG_FILE', __DIR__ . '/send_email.log');
function logit($msg) {
    $time = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$time] $msg\n", FILE_APPEND);
}

// ——————————————————————
// 2) ENSURE POST
// ——————————————————————
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logit("Invalid request method: {$_SERVER['REQUEST_METHOD']}");
    echo 'error';
    exit;
}

// ——————————————————————
// 3) GRAB & VALIDATE
// ——————————————————————
$name    = trim($_POST['text']    ?? '');
$email   = trim($_POST['gmail']   ?? '');
$phone   = trim($_POST['number']  ?? '');
$message = trim($_POST['massage'] ?? '');

if (!$name || !$email || !$phone) {
    logit("Validation failed: name='$name', email='$email', phone='$phone'");
    echo 'error';
    exit;
}

// ——————————————————————
// 4) LOAD DOTENV + API KEY
// ——————————————————————
$apiKey = '';
$autoload = __DIR__ . '/../vendor/autoload.php';
if (! file_exists($autoload)) {
    logit("autoload.php not found at $autoload");
} else {
    require $autoload;
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        $apiKey = $_ENV['SENDINBLUE_API_KEY'] ?? '';
        if (! $apiKey) {
            logit("SENDINBLUE_API_KEY missing or empty in .env");
        }
    } catch (Exception $e) {
        logit("Dotenv load error: " . $e->getMessage());
    }
}

if (! $apiKey) {
    echo 'error';
    exit;
}

// ——————————————————————
// 5) BUILD PAYLOAD
// ——————————————————————
$data = [
    'sender'      => ['name' => $name,  'email' => 'website@cypherleap.com'],
    'to'          => [['email' => 'info@cypherleap.com', 'name' => 'Website CypherLeap']],
    'subject'     => 'New Contact Form Submission',
    'htmlContent' =>
          "<h1>Contact Form Enquiry</h1>"
        . "<p><strong>Name:</strong> {$name}</p>"
        . "<p><strong>Email:</strong> {$email}</p>"
        . "<p><strong>Phone:</strong> {$phone}</p>"
        . "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>"
];

// ——————————————————————
// 6) SEND VIA cURL
// ——————————————————————
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

$response    = curl_exec($ch);
$curlError   = curl_error($ch);
$http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ——————————————————————
// 7) LOG AND RETURN
// ——————————————————————
if ($curlError) {
    logit("cURL error: $curlError");
}
logit("API HTTP code: $http_code; Response body: " . substr($response, 0, 200));

echo $http_code === 201 ? 'success' : 'error';
