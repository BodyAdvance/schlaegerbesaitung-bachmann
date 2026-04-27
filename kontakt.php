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
$headers  = "From: Anfrage Schlaegerbesaitung <info@schlaegerbesaitung.de>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ── 1. Benachrichtigung an André ──────────────────────────
$sent = mail($to, $subject, $body, $headers, '-f info@schlaegerbesaitung.de');

// ── 2. Automatische Bestätigungsmail an Interessenten ─────
$confirm_subject = '=?UTF-8?B?' . base64_encode('Vielen Dank für deine Anfrage – Schlägerbesaitung André Bachmann') . '?=';

$sport_row = $sport ? "
        <tr>
          <td style='padding:6px 0;color:#8b8b99;font-size:14px;width:110px;vertical-align:top;'>Sportart</td>
          <td style='padding:6px 0;color:#dbdbdb;font-size:14px;vertical-align:top;'>$sport</td>
        </tr>" : '';

$confirm_body = '<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#111116;font-family:Inter,Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#111116;padding:40px 16px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

        <!-- Logo -->
        <tr>
          <td align="center" style="padding-bottom:32px;">
            <img src="https://schlaegerbesaitung.de/images/logo.png" alt="Schlägerbesaitung André Bachmann" width="180" style="display:block;max-width:180px;">
          </td>
        </tr>

        <!-- Card -->
        <tr>
          <td style="background:#1b1b22;border-radius:16px;border:1px solid #2a2a32;overflow:hidden;">

            <!-- Accent bar -->
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td style="background:linear-gradient(90deg,#ce88b3,#a06690);height:4px;font-size:0px;">&nbsp;</td></tr>
            </table>

            <!-- Body -->
            <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 40px 32px;">
              <tr>
                <td>
                  <p style="margin:0 0 8px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#ce88b3;">Bestätigung deiner Anfrage</p>
                  <h1 style="margin:0 0 24px;font-size:28px;font-weight:900;color:#ffffff;line-height:1.2;">Hallo ' . $name . ',</h1>
                  <p style="margin:0 0 24px;font-size:16px;color:#dbdbdb;line-height:1.7;">vielen Dank für deine Nachricht! Ich habe deine Anfrage erhalten und werde mich so schnell wie möglich — in der Regel <strong style="color:#ffffff;">innerhalb von 24 Stunden</strong> — persönlich bei dir melden.</p>

                  <!-- Anfrage-Box -->
                  <table width="100%" cellpadding="0" cellspacing="0" style="background:#232329;border-radius:10px;border:1px solid #2a2a32;margin-bottom:32px;">
                    <tr><td style="padding:20px 24px;">
                      <p style="margin:0 0 14px;font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#8b8b99;">Deine Anfrage im Überblick</p>
                      <table cellpadding="0" cellspacing="0" style="width:100%;">
                        <tr>
                          <td style="padding:6px 0;color:#8b8b99;font-size:14px;width:110px;vertical-align:top;">Name</td>
                          <td style="padding:6px 0;color:#dbdbdb;font-size:14px;vertical-align:top;">' . $name . '</td>
                        </tr>' . $sport_row . '
                        <tr>
                          <td style="padding:6px 0;color:#8b8b99;font-size:14px;vertical-align:top;border-top:1px solid #2a2a32;padding-top:12px;margin-top:8px;">Nachricht</td>
                          <td style="padding:6px 0;color:#dbdbdb;font-size:14px;vertical-align:top;border-top:1px solid #2a2a32;padding-top:12px;">' . nl2br($nachricht) . '</td>
                        </tr>
                      </table>
                    </td></tr>
                  </table>

                  <!-- Kontakt -->
                  <p style="margin:0 0 12px;font-size:15px;color:#dbdbdb;line-height:1.6;">Falls du in der Zwischenzeit Fragen hast, erreichst du mich jederzeit:</p>
                  <table cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding:4px 0;">
                        <a href="tel:+4916217939690" style="color:#ce88b3;text-decoration:none;font-size:14px;">📞 0162 1793969</a>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:4px 0;">
                        <a href="mailto:info@schlaegerbesaitung.de" style="color:#ce88b3;text-decoration:none;font-size:14px;">✉️ info@schlaegerbesaitung.de</a>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:4px 0;">
                        <a href="https://schlaegerbesaitung.de" style="color:#ce88b3;text-decoration:none;font-size:14px;">🌐 www.schlaegerbesaitung.de</a>
                      </td>
                    </tr>
                  </table>

                  <!-- Signatur -->
                  <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px;padding-top:24px;border-top:1px solid #2a2a32;">
                    <tr>
                      <td>
                        <p style="margin:0 0 4px;font-size:15px;color:#ffffff;font-weight:600;">Mit sportlichen Grüßen,</p>
                        <p style="margin:0 0 2px;font-size:15px;color:#ffffff;font-weight:700;">André Bachmann</p>
                        <p style="margin:0;font-size:13px;color:#8b8b99;">Schlägerbesaitung André Bachmann · Rohrhammerweg 12 · 99610 Sömmerda</p>
                      </td>
                    </tr>
                  </table>

                </td>
              </tr>
            </table>

          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td align="center" style="padding-top:24px;">
            <p style="margin:0;font-size:12px;color:#8b8b99;line-height:1.6;text-align:center;">
              Diese E-Mail wurde automatisch generiert. Bitte antworte direkt auf diese Mail.<br>
              Einzelunternehmen · Sömmerda, Deutschland<br>
              <a href="https://schlaegerbesaitung.de/datenschutz.html" style="color:#8b8b99;">Datenschutzerklärung</a> &nbsp;·&nbsp;
              <a href="https://schlaegerbesaitung.de/impressum.html" style="color:#8b8b99;">Impressum</a>
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>

</body>
</html>';

$confirm_headers  = "From: info@schlaegerbesaitung.de\r\n";
$confirm_headers .= "Reply-To: info@schlaegerbesaitung.de\r\n";
$confirm_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$confirm_headers .= "MIME-Version: 1.0\r\n";
$confirm_headers .= "Content-Type: text/html; charset=UTF-8\r\n";

mail($email, $confirm_subject, $confirm_body, $confirm_headers, '-f info@schlaegerbesaitung.de');

// ── Antwort an Browser ────────────────────────────────────
if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Sendefehler']);
}
