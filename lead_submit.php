<?php
// public/lead_submit.php
require_once 'config.php';
requireAjaxCSRF(); // CSRF-Schutz
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

// Simple sanitize/validate
$messe_id = isset($_POST['messe_id']) && $_POST['messe_id'] !== '' ? intval($_POST['messe_id']) : null;
$marker_id = isset($_POST['marker_id']) && $_POST['marker_id'] !== '' ? intval($_POST['marker_id']) : null;
$email = trim($_POST['email'] ?? '');
$name = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$interested_in = trim($_POST['interested_in'] ?? '');

// basic email validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['lead_error'] = "Bitte geben Sie eine gültige E-Mail-Adresse an.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'messe_info.php'));
    exit;
}

// Insert into messe_leads
try {
    $stmt = $pdo->prepare("INSERT INTO messe_leads (messe_id, marker_id, email, name, company, phone, message, interested_in, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $messe_id,
        $marker_id,
        $email,
        $name ?: null,
        $company ?: null,
        $phone ?: null,
        $message ?: null,
        $interested_in ?: null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
} catch (Exception $e) {
    // falls DB-Insert fehlschlägt, trotzdem versuchen Mail zu senden
}

// Get messe contact email
$contactEmail = null;
if ($messe_id) {
    $stmt = $pdo->prepare("SELECT contact_email, name FROM messe_config WHERE id = ? LIMIT 1");
    $stmt->execute([$messe_id]);
    $m = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($m) {
        $contactEmail = $m['contact_email'];
        $messeName = $m['name'];
    }
}

// Prepare e-mail
$subject = "Messe-Anfrage: " . ($interested_in ?: 'Gerät');
$body = "Neue Messe-Anfrage\n\n";
$body .= "Messe: " . ($messeName ?? 'n/a') . "\n";
$body .= "Gerät: " . ($interested_in ?: ($marker_id ? "Marker #$marker_id" : '')) . "\n\n";
$body .= "Name: " . ($name ?: '-') . "\n";
$body .= "Firma: " . ($company ?: '-') . "\n";
$body .= "E-Mail: " . $email . "\n";
$body .= "Telefon: " . ($phone ?: '-') . "\n\n";
$body .= "Nachricht:\n" . ($message ?: '-') . "\n\n";
$body .= "Weitere Infos:\n";
$body .= "Marker-ID: " . ($marker_id ?: '-') . "\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n";
$body .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? '-') . "\n";

// Send only if contact email present
if (!empty($contactEmail) && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
    $headers = "From: " . ($email) . "\r\n";
    $headers .= "Reply-To: " . ($email) . "\r\n";
    // Optional: add more headers if needed
    @mail($contactEmail, $subject, $body, $headers);
}

// Redirect back with success
$redirect = isset($_POST['redirect_to']) && $_POST['redirect_to'] ? $_POST['redirect_to'] : ("messe_info.php?m=" . intval($marker_id));
if (!str_starts_with($redirect, 'http') && !str_starts_with($redirect, '/')) {
    // make it relative
    $redirect = $redirect;
}
header("Location: " . $redirect . (strpos($redirect, '?') === false ? '?sent=1' : '&sent=1'));
exit;
