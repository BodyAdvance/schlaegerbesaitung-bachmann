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

// ── 1. Benachrichtigung an André ──────────────────────────
$sent = mail($to, $subject, $body, $headers);

// ── 2. Automatische Bestätigungsmail an Interessenten ─────
$confirm_subject = '=?UTF-8?B?' . base64_encode('Vielen Dank für deine Anfrage – Schlägerbesaitung André Bachmann') . '?=';

$sport_line = $sport ? "\nSportart: $sport" : '';

$confirm_body  = "Hallo $name,\n\n";
$confirm_body .= "vielen Dank für deine Nachricht! Ich habe deine Anfrage erhalten und werde mich so schnell wie möglich – in der Regel innerhalb von 24 Stunden – persönlich bei dir melden.\n\n";
$confirm_body .= str_repeat('-', 40) . "\n";
$confirm_body .= "Deine Anfrage im Überblick:";
$confirm_body .= $sport_line . "\n";
$confirm_body .= "\n" . $nachricht . "\n";
$confirm_body .= str_repeat('-', 40) . "\n\n";
$confirm_body .= "Falls du in der Zwischenzeit Fragen hast, erreichst du mich jederzeit:\n\n";
$confirm_body .= "Telefon:  0162 1793969\n";
$confirm_body .= "E-Mail:   info@schlaegerbesaitung.de\n";
$confirm_body .= "Website:  www.schlaegerbesaitung.de\n\n";
$confirm_body .= "Ich freue mich darauf, deinen Schläger in Bestform zu bringen!\n\n";
$confirm_body .= "Mit sportlichen Grüßen,\n\n";
$confirm_body .= "André Bachmann\n";
$confirm_body .= "Schlägerbesaitung André Bachmann\n";
$confirm_body .= "Rohrhammerweg 12 · 99610 Sömmerda\n";
$confirm_body .= "Tel.: 0162 1793969\n";
$confirm_body .= "info@schlaegerbesaitung.de\n";
$confirm_body .= "www.schlaegerbesaitung.de\n\n";
$confirm_body .= str_repeat('-', 40) . "\n";
$confirm_body .= "Diese E-Mail wurde automatisch generiert. Bitte antworte direkt auf diese Mail oder kontaktiere mich über die oben genannten Wege.\n";
$confirm_body .= "Einzelunternehmen · Kleingewerbe gem. § 19 UStG · Sömmerda, Deutschland\n";

$confirm_headers  = "From: =?UTF-8?B?" . base64_encode("André Bachmann – Schlägerbesaitung") . "?= <info@schlaegerbesaitung.de>\r\n";
$confirm_headers .= "Reply-To: info@schlaegerbesaitung.de\r\n";
$confirm_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$confirm_headers .= "MIME-Version: 1.0\r\n";
$confirm_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($email, $confirm_subject, $confirm_body, $confirm_headers);

// ── Antwort an Browser ────────────────────────────────────
if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Sendefehler']);
}
