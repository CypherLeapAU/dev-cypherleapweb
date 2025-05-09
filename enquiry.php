<?php
header('Content-Type: application/json; charset=utf-8');

// ─── 0) SIMPLE .env LOADER ───────────────────────────────────────────────────
function loadEnv(string $path)
{
    if (! file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // only set if not already in env
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
loadEnv(__DIR__ . '/.env');

// ─── 1) PULL CONFIG ───────────────────────────────────────────────────────────
$apiKey   = getenv('BREVO_API_KEY');
$fromEmail = getenv('FROM_EMAIL');
$fromName  = getenv('FROM_NAME');
$toEmail   = getenv('TO_EMAIL');
$toName    = getenv('TO_NAME');
$endpoint  = 'https://api.brevo.com/v3/smtp/email';

// ─── 2) ENSURE POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status'=>'error','message'=>'Only POST allowed.']));
}

// ─── 3) SANITIZE & VALIDATE ───────────────────────────────────────────────────
$name    = trim(filter_input(INPUT_POST, 'name',    FILTER_SANITIZE_STRING) ?: '');
$email   = trim(filter_input(INPUT_POST, 'email',   FILTER_VALIDATE_EMAIL) ?: '');
$phone   = trim(filter_input(INPUT_POST, 'phone',   FILTER_SANITIZE_STRING) ?: '');
$company = trim(filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING) ?: '');
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING) ?: '');

if (!$name || !$email || !$message) {
    http_response_code(400);
    exit(json_encode([
        'status'=>'error',
        'message'=>'Name, valid email & message are required.'
    ]));
}

// ─── 4) BUILD PAYLOAD ──────────────────────────────────────────────────────────
$payload = [
    'sender'      => ['name'=>$fromName,'email'=>$fromEmail],
    'to'          => [['email'=>$toEmail,'name'=>$toName]],
    'subject'     => '📩 Form Submission - Home Page',
    'htmlContent' => "
      <h2>New contact form submission</h2>
      <p><strong>Name:</strong> {$name}</p>
      <p><strong>Email:</strong> {$email}</p>
      <p><strong>Phone:</strong> {$phone}</p>
      <p><strong>Company:</strong> {$company}</p>
      <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
    ",
    'replyTo'     => ['email'=>$email,'name'=>$name]
];

// ─── 5) SEND VIA cURL ─────────────────────────────────────────────────────────
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "api-key: {$apiKey}",
        'Accept: application/json',
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload)
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ─── 6) HANDLE RESPONSE ───────────────────────────────────────────────────────
if ($curlError) {
    http_response_code(500);
    exit(json_encode(['status'=>'error','message'=>"cURL error: {$curlError}"]));
}

if ($httpCode === 201) {
    echo json_encode(['status'=>'success','message'=>'Thank you! We will be in touch with you soon.']);
} else {
    http_response_code($httpCode);
    echo json_encode([
        'status'=>'error',
        'message'=>"Brevo API returned HTTP {$httpCode}",
        'detail'=> json_decode($response, true)
    ]);
}
file_put_contents(__DIR__ . '/env_debug.log', "BREVO_API_KEY=[{$apiKey}]\n", FILE_APPEND);