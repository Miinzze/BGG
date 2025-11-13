<?php
require_once 'config.php';
require_once 'functions.php';

// Token aus URL holen
$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(401);
    die('Unauthorized');
}

// Benutzer anhand des Tokens finden
$stmt = $pdo->prepare("
    SELECT u.*, ucs.* 
    FROM user_calendar_settings ucs
    JOIN users u ON ucs.user_id = u.id
    WHERE ucs.calendar_token = ? AND ucs.ical_enabled = 1
");
$stmt->execute([$token]);
$userCalendar = $stmt->fetch();

if (!$userCalendar) {
    http_response_code(404);
    die('Not Found');
}

$userId = $userCalendar['user_id'];

// Wartungstermine abrufen
$stmt = $pdo->prepare("
    SELECT 
        m.id,
        m.name,
        m.qr_code,
        m.location,
        m.status,
        is_schedule.next_inspection as maintenance_date,
        is_schedule.interval_days,
        'Wartung' as event_type
    FROM inspection_schedules is_schedule
    JOIN markers m ON is_schedule.marker_id = m.id
    WHERE is_schedule.next_inspection >= CURDATE()
    AND m.deleted_at IS NULL
    ORDER BY is_schedule.next_inspection ASC
    LIMIT 100
");
$stmt->execute();
$events = $stmt->fetchAll();

// iCal Header
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="wartungen.ics"');

// iCal generieren
echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//RFID Marker System//Wartungskalender//DE\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "METHOD:PUBLISH\r\n";
echo "X-WR-CALNAME:Wartungen & Prüfungen\r\n";
echo "X-WR-TIMEZONE:Europe/Berlin\r\n";
echo "X-WR-CALDESC:Automatisch generierter Kalender für fällige Wartungen und Prüfungen\r\n";

foreach ($events as $event) {
    $eventId = 'event-' . $event['id'] . '-' . date('Ymd', strtotime($event['maintenance_date']));
    $dtstart = date('Ymd', strtotime($event['maintenance_date']));
    $summary = $event['event_type'] . ': ' . $event['name'];
    $description = "Geräte-ID: " . $event['qr_code'] . "\\n";
    $description .= "Status: " . ucfirst($event['status']) . "\\n";
    if (!empty($event['location'])) {
        $description .= "Standort: " . $event['location'] . "\\n";
    }
    $description .= "\\nAutomatisch generiert vom RFID Marker System";
    
    $location = !empty($event['location']) ? $event['location'] : 'Nicht angegeben';
    
    // Erinnerung vor X Tagen
    $notificationDays = $userCalendar['notification_days_before'] ?? 3;
    $alarmTime = 'P' . $notificationDays . 'D';
    
    echo "BEGIN:VEVENT\r\n";
    echo "UID:" . $eventId . "@rfid-marker-system\r\n";
    echo "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
    echo "DTSTART;VALUE=DATE:" . $dtstart . "\r\n";
    echo "SUMMARY:" . $summary . "\r\n";
    echo "DESCRIPTION:" . $description . "\r\n";
    echo "LOCATION:" . $location . "\r\n";
    echo "STATUS:CONFIRMED\r\n";
    echo "SEQUENCE:0\r\n";
    
    // Erinnerung hinzufügen
    echo "BEGIN:VALARM\r\n";
    echo "ACTION:DISPLAY\r\n";
    echo "DESCRIPTION:Erinnerung: " . $summary . "\r\n";
    echo "TRIGGER:-" . $alarmTime . "\r\n";
    echo "END:VALARM\r\n";
    
    echo "END:VEVENT\r\n";
}

echo "END:VCALENDAR\r\n";
exit;