<?php
header('Content-Type: application/json');

// Honeypot-Prüfung
if (!empty($_POST['website'])) {
    http_response_code(400);
    echo json_encode(['success' => false]);
    exit;
}

// Felder auslesen und bereinigen
$name      = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$sport     = htmlspecialchars(trim($_POST['sport'] ?? ''), ENT_QUOTES, 'UTF-8');
$nachricht = htmlspecialchars(trim($_POST['nachricht'] ?? ''), ENT_QUOTES, 'UTF-8');

// Pflichtfelder prüfen
if (empty($name) || empty($email) || empty($nachricht)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Pflichtfelder fehlen']);
    exit;
}

// E-Mail-Adresse prüfen
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültige E-Mail']);
    exit;
}

// Empfänger
$to      = 'info@schlaegerbesaitung.de';
$subject = '=?UTF-8?B?' . base64_encode('Neue Anfrage über schlaegerbesaitung.de') . '?=';

// Nachrichtentext
$sport_text = $sport ? "Sportart: $sport\n" : '';
$body = "Neue Kontaktanfrage von schlaegerbesaitung.de\n";
$body .= str_repeat('-', 40) . "\n";
$body .= "Name:    $name\n";
$body .= "E-Mail:  $email\n";
$body .= $sport_text;
$body .= str_repeat('-', 40) . "\n\n";
$body .= "Nachricht:\n$nachricht\n";

// Header
$headers  = "From: noreply@schlaegerbesaitung.de\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Senden
$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Sendefehler']);
}
