<?php
require_once __DIR__ . '/token_validation.php';
require_once __DIR__ . '/time_validation.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate timing
if (!validateTiming($_POST['form_start'], $_POST['captcha_solve'], time())) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid submission timing']);
    exit;
}

// Validate CSRF token
if (!validateToken($_POST['csrf_token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

// Check honeypot fields
if (!empty($_POST['phone']) || !empty($_POST['surname'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Form submission rejected']);
    exit;
}

// Basic input validation
$required_fields = ['name', 'email', 'phone_number', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

$postmark_token = getenv('POSTMARK_API_TOKEN');
if (empty($postmark_token)) {
    http_response_code(500);
    error_log('Postmark token is not set');
    echo json_encode(['error' => 'Server configuration error']);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone_number = htmlspecialchars($_POST['phone_number'], ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');

$data = [
    'From' => 'contact@particledynamics.org',
    'To' => 'contact@particledynamics.org',
    'ReplyTo' => $email,
    'Subject' => 'Contact Form: ' . $subject,
    'HtmlBody' => "
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Phone Number:</strong> {$phone_number}</p>
        <p><strong>Subject:</strong> {$subject}</p>
        <p><strong>Message:</strong></p>
        <p>{$message}</p>
    ",
    'MessageStream' => 'outbound'
];

$ch = curl_init('https://api.postmarkapp.com/email');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'X-Postmark-Server-Token: ' . $postmark_token
    ]
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    error_log('Curl error: ' . curl_error($ch));
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send email']);
    exit;
}

curl_close($ch);

// Debug logging
error_log('Postmark API Response: ' . $response);
error_log('HTTP Code: ' . $http_code);

http_response_code($http_code);
echo $response;
