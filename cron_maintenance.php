<?php
/**
 * Cron Job für Wartungs- und Prüfungsbenachrichtigungen
 * 
 * Dieser Cron-Job sollte täglich ausgeführt werden, z.B.:
 * 0 8 * * * /usr/bin/php /pfad/zu/cron_maintenance.php
 * 
 * Funktion:
 * - Prüft alle Geräte auf fällige Wartungen
 * - Prüft alle Geräte auf fällige DGUV/UVV/TÜV Prüfungen
 * - Sendet E-Mails an Benutzer, die Benachrichtigungen aktiviert haben
 * - Protokolliert alle Benachrichtigungen
 */

require_once 'config.php';

// Logging-Funktion
function logMessage($message) {
    $logFile = __DIR__ . '/logs/maintenance_cron.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// E-Mail senden Funktion
function sendNotificationEmail($to, $toName, $maintenanceDevices, $inspectionDevices) {
    $subject = "Wartungs- und Prüfungserinnerung - RFID System";
    
    // HTML-E-Mail erstellen
    $message = "
    <html>
    <head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; margin-top: 20px; }
            .section-title { background: #007bff; color: white; padding: 10px; margin: 20px 0 10px 0; font-weight: bold; }
            .device { background: white; border-left: 4px solid #dc3545; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .device-name { font-weight: bold; font-size: 18px; color: #dc3545; }
            .device-info { margin: 5px 0; color: #666; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .urgent { background: #fff3cd; border-left-color: #ffc107; }
            .overdue { background: #f8d7da; border-left-color: #dc3545; }
            .ok { border-left-color: #28a745; }
            .inspection-badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; margin-left: 10px; }
            .badge-danger { background: #dc3545; color: white; }
            .badge-warning { background: #ffc107; color: #333; }
            .badge-info { background: #17a2b8; color: white; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>⚠️ Wartungs- und Prüfungserinnerung</h1>
            </div>
            <div class='content'>
                <p>Hallo " . htmlspecialchars($toName) . ",</p>
                <p>folgende Wartungen und Prüfungen sind demnächst fällig oder bereits überfällig:</p>
    ";
    
    // WARTUNGEN
    if (!empty($maintenanceDevices)) {
        $message .= "<div class='section-title'>🔧 WARTUNGEN</div>";
        
        foreach ($maintenanceDevices as $device) {
            $daysUntil = (strtotime($device['next_maintenance']) - time()) / (60 * 60 * 24);
            $urgencyClass = '';
            $urgencyText = '';
            
            if ($daysUntil < 0) {
                $urgencyClass = 'overdue';
                $urgencyText = '<strong style="color: #dc3545;">ÜBERFÄLLIG seit ' . abs(round($daysUntil)) . ' Tagen!</strong>';
            } elseif ($daysUntil <= 7) {
                $urgencyClass = 'urgent';
                $urgencyText = '<strong style="color: #ffc107;">In ' . round($daysUntil) . ' Tagen fällig</strong>';
            } else {
                $urgencyText = 'In ' . round($daysUntil) . ' Tagen fällig';
            }
            
            $message .= "
                <div class='device $urgencyClass'>
                    <div class='device-name'>🔧 " . htmlspecialchars($device['name']) . "</div>
                    <div class='device-info'>📋 Kategorie: " . htmlspecialchars($device['category'] ?? 'Nicht angegeben') . "</div>
                    <div class='device-info'>🔢 Seriennummer: " . htmlspecialchars($device['serial_number'] ?? 'Nicht angegeben') . "</div>
                    <div class='device-info'>⏰ Wartung fällig am: " . date('d.m.Y', strtotime($device['next_maintenance'])) . " ($urgencyText)</div>
                </div>
            ";
        }
    }
    
    // PRÜFUNGEN (DGUV/UVV/TÜV)
    if (!empty($inspectionDevices)) {
        $message .= "<div class='section-title'>📋 PRÜFUNGEN (DGUV / UVV / TÜV)</div>";
        
        foreach ($inspectionDevices as $inspection) {
            $daysUntil = (strtotime($inspection['next_inspection']) - time()) / (60 * 60 * 24);
            $urgencyClass = '';
            $urgencyText = '';
            $badgeClass = 'badge-info';
            
            if ($daysUntil < 0) {
                $urgencyClass = 'overdue';
                $urgencyText = '<strong style="color: #dc3545;">ÜBERFÄLLIG seit ' . abs(round($daysUntil)) . ' Tagen!</strong>';
                $badgeClass = 'badge-danger';
            } elseif ($daysUntil <= 7) {
                $urgencyClass = 'urgent';
                $urgencyText = '<strong style="color: #ffc107;">In ' . round($daysUntil) . ' Tagen fällig</strong>';
                $badgeClass = 'badge-warning';
            } else {
                $urgencyText = 'In ' . round($daysUntil) . ' Tagen fällig';
            }
            
            $message .= "
                <div class='device $urgencyClass'>
                    <div class='device-name'>
                        📋 " . htmlspecialchars($inspection['marker_name']) . "
                        <span class='inspection-badge $badgeClass'>" . htmlspecialchars($inspection['inspection_type']) . "</span>
                    </div>
                    <div class='device-info'>🏢 Prüfstelle: " . htmlspecialchars($inspection['inspection_authority'] ?? 'Nicht angegeben') . "</div>
                    <div class='device-info'>📅 Intervall: Alle " . $inspection['inspection_interval_months'] . " Monate</div>
                    <div class='device-info'>⏰ Prüfung fällig am: " . date('d.m.Y', strtotime($inspection['next_inspection'])) . " ($urgencyText)</div>
            ";
            
            if (!empty($inspection['certificate_number'])) {
                $message .= "
                    <div class='device-info'>🎫 Zertifikat: " . htmlspecialchars($inspection['certificate_number']) . "</div>
                ";
            }
            
            if (!empty($inspection['responsible_person'])) {
                $message .= "
                    <div class='device-info'>👤 Verantwortlich: " . htmlspecialchars($inspection['responsible_person']) . "</div>
                ";
            }
            
            $message .= "
                </div>
            ";
        }
    }
    
    $message .= "
                <p style='margin-top: 30px; padding: 15px; background: white; border-left: 4px solid #17a2b8; border-radius: 5px;'>
                    <strong>ℹ️ Zusammenfassung:</strong><br>
                    • Fällige Wartungen: " . count($maintenanceDevices) . "<br>
                    • Fällige Prüfungen: " . count($inspectionDevices) . "<br>
                    • Gesamt: " . (count($maintenanceDevices) + count($inspectionDevices)) . "
                </p>
                
                <p style='margin-top: 20px; text-align: center;'>
                    <a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/index.php' 
                       style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        📊 Zur Übersicht
                    </a>
                </p>
            </div>
            <div class='footer'>
                <p>Dies ist eine automatische E-Mail vom RFID Wartungs- und Prüfungssystem.</p>
                <p>Sie erhalten diese E-Mail, weil Sie Benachrichtigungen aktiviert haben.</p>
                <p style='margin-top: 10px; font-size: 11px;'>
                    Um diese Benachrichtigungen zu deaktivieren, ändern Sie Ihre Einstellungen in Ihrem Profil.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // E-Mail Header
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: RFID System <noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\n";
    
    // E-Mail senden
    $success = mail($to, $subject, $message, $headers);
    
    if ($success) {
        logMessage("E-Mail erfolgreich gesendet an: $to (Wartungen: " . count($maintenanceDevices) . ", Prüfungen: " . count($inspectionDevices) . ")");
    } else {
        logMessage("FEHLER: E-Mail konnte nicht gesendet werden an: $to");
    }
    
    return $success;
}

// Script Start
logMessage("=== Cron Job gestartet ===");

try {
    // 1. WARTUNGEN - Fällige Wartungen finden (bis zu 14 Tage im Voraus)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            category,
            serial_number,
            next_maintenance,
            DATEDIFF(next_maintenance, CURDATE()) as days_until
        FROM markers 
        WHERE next_maintenance IS NOT NULL 
        AND next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
        AND is_storage = 0
        ORDER BY next_maintenance ASC
    ");
    $stmt->execute();
    $dueMaintenanceDevices = $stmt->fetchAll();
    
    logMessage("Gefunden: " . count($dueMaintenanceDevices) . " Geräte mit fälliger Wartung");
    
    // 2. PRÜFUNGEN - Fällige DGUV/UVV/TÜV Prüfungen finden (bis zu 14 Tage im Voraus)
    $stmt = $pdo->prepare("
        SELECT 
            ins.id,
            ins.inspection_type,
            ins.next_inspection,
            ins.inspection_interval_months,
            ins.inspection_authority,
            ins.certificate_number,
            ins.responsible_person,
            m.id as marker_id,
            m.name as marker_name,
            DATEDIFF(ins.next_inspection, CURDATE()) as days_until
        FROM inspection_schedules ins
        JOIN markers m ON ins.marker_id = m.id
        WHERE ins.next_inspection IS NOT NULL 
        AND ins.next_inspection <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
        ORDER BY ins.next_inspection ASC
    ");
    $stmt->execute();
    $dueInspections = $stmt->fetchAll();
    
    logMessage("Gefunden: " . count($dueInspections) . " Geräte mit fälliger Prüfung");
    
    // 3. Nur E-Mails senden, wenn es fällige Wartungen ODER Prüfungen gibt
    if (count($dueMaintenanceDevices) > 0 || count($dueInspections) > 0) {
        
        // Benutzer mit aktivierten E-Mail-Benachrichtigungen laden
        $stmt = $pdo->prepare("
            SELECT id, username, email 
            FROM users 
            WHERE receive_maintenance_emails = 1 
            AND email IS NOT NULL 
            AND email != ''
        ");
        $stmt->execute();
        $notifyUsers = $stmt->fetchAll();
        
        logMessage("Gefunden: " . count($notifyUsers) . " Benutzer mit aktivierten Benachrichtigungen");
        
        // E-Mails an alle benachrichtigungs-berechtigten Benutzer senden
        $emailsSent = 0;
        foreach ($notifyUsers as $user) {
            if (sendNotificationEmail(
                $user['email'], 
                $user['username'], 
                $dueMaintenanceDevices,
                $dueInspections
            )) {
                $emailsSent++;
            }
            
            // Kleine Verzögerung zwischen E-Mails (verhindert Spam-Filter)
            sleep(1);
        }
        
        logMessage("E-Mails gesendet: $emailsSent von " . count($notifyUsers));
        
        // Benachrichtigung in Datenbank protokollieren
        $stmt = $pdo->prepare("
            INSERT INTO maintenance_notifications (sent_at, devices_count, users_notified, inspections_count)
            VALUES (NOW(), ?, ?, ?)
        ");
        $stmt->execute([
            count($dueMaintenanceDevices), 
            $emailsSent,
            count($dueInspections)
        ]);
        
    } else {
        logMessage("Keine fälligen Wartungen oder Prüfungen gefunden");
    }
    
    // 4. Status-Update für überfällige Prüfungen
    if (count($dueInspections) > 0) {
        foreach ($dueInspections as $inspection) {
            $status = 'aktuell';
            if ($inspection['days_until'] < 0) {
                $status = 'überfällig';
            } elseif ($inspection['days_until'] <= 30) {
                $status = 'fällig';
            }
            
            $stmt = $pdo->prepare("
                UPDATE inspection_schedules 
                SET status = ?,
                    last_notification_sent = CURDATE()
                WHERE id = ?
            ");
            $stmt->execute([$status, $inspection['id']]);
        }
        logMessage("Status aktualisiert für " . count($dueInspections) . " Prüfungen");
    }
    
    logMessage("=== Cron Job erfolgreich beendet ===\n");
    
} catch (Exception $e) {
    logMessage("FEHLER: " . $e->getMessage());
    logMessage("Stack Trace: " . $e->getTraceAsString());
    logMessage("=== Cron Job mit Fehler beendet ===\n");
}
?>