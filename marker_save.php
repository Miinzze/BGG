<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth_check.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Ungültige Anfrage');
    }

    // Marker-Typ prüfen
    $marker_type = $_POST['marker_type'] ?? 'qr_code';
    
    // Validierung basierend auf Marker-Typ
    if ($marker_type === 'qr_code') {
        if (empty($_POST['qr_code'])) {
            throw new Exception('Bitte wähle einen QR-Code aus!');
        }
        $identifier = $_POST['qr_code'];
        $nfc_enabled = 0;
        $nfc_chip_id = null;
    } else {
        if (empty($_POST['nfc_chip_id'])) {
            throw new Exception('Bitte wähle einen NFC-Chip aus!');
        }
        $identifier = $_POST['nfc_chip_id']; // Verwenden wir als QR-Code Ersatz
        $nfc_enabled = 1;
        $nfc_chip_id = $_POST['nfc_chip_id'];
    }

    if (empty($_POST['name'])) {
        throw new Exception('Bitte gib einen Gerätenamen ein!');
    }

    // Prüfe ob QR-Code/NFC-Chip verfügbar ist
    if ($marker_type === 'qr_code') {
        $stmt = $pdo->prepare("SELECT is_assigned FROM qr_code_pool WHERE qr_code = ?");
        $stmt->execute([$identifier]);
        $code = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$code) {
            throw new Exception('QR-Code existiert nicht im Pool!');
        }
        if ($code['is_assigned'] == 1) {
            throw new Exception('QR-Code ist bereits vergeben!');
        }
    } else {
        $stmt = $pdo->prepare("SELECT is_assigned FROM nfc_chip_pool WHERE nfc_chip_id = ?");
        $stmt->execute([$nfc_chip_id]);
        $chip = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$chip) {
            throw new Exception('NFC-Chip existiert nicht im Pool!');
        }
        if ($chip['is_assigned'] == 1) {
            throw new Exception('NFC-Chip ist bereits vergeben!');
        }
    }

    // Public Token generieren
    $public_token = bin2hex(random_bytes(32));

    // Daten vorbereiten
    $data = [
        'qr_code' => $identifier,
        'name' => trim($_POST['name']),
        'category' => !empty($_POST['category']) ? $_POST['category'] : null,
        'serial_number' => !empty($_POST['serial_number']) ? trim($_POST['serial_number']) : null,
        'is_storage' => isset($_POST['is_storage']) ? 1 : 0,
        'rental_status' => $_POST['rental_status'] ?? 'verfuegbar',
        'operating_hours' => !empty($_POST['operating_hours']) ? floatval($_POST['operating_hours']) : 0,
        'fuel_level' => !empty($_POST['fuel_level']) ? intval($_POST['fuel_level']) : 0,
        'fuel_unit' => $_POST['fuel_unit'] ?? 'percent',
        'fuel_capacity' => !empty($_POST['fuel_capacity']) ? floatval($_POST['fuel_capacity']) : null,
        'maintenance_interval_months' => !empty($_POST['maintenance_interval_months']) ? intval($_POST['maintenance_interval_months']) : 6,
        'latitude' => !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null,
        'longitude' => !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null,
        'created_by' => $_SESSION['user_id'],
        'is_customer_device' => isset($_POST['is_customer_device']) ? 1 : 0,
        'customer_name' => !empty($_POST['customer_name']) ? trim($_POST['customer_name']) : null,
        'order_number' => !empty($_POST['order_number']) ? trim($_POST['order_number']) : null,
        'is_repair_device' => isset($_POST['is_repair_device']) ? 1 : 0,
        'repair_description' => !empty($_POST['repair_description']) ? trim($_POST['repair_description']) : null,
        'maintenance_set_id' => !empty($_POST['maintenance_set_id']) ? intval($_POST['maintenance_set_id']) : null,
        'public_token' => $public_token,
        'nfc_enabled' => $nfc_enabled,
        'nfc_chip_id' => $nfc_chip_id,
        'marker_type' => $marker_type
    ];

    // Wartungsdatum berechnen
    if ($data['maintenance_interval_months'] > 0) {
        $data['last_maintenance'] = date('Y-m-d');
        $data['next_maintenance'] = date('Y-m-d', strtotime('+' . $data['maintenance_interval_months'] . ' months'));
    }

    $pdo->beginTransaction();

    // Marker erstellen
    $stmt = $pdo->prepare("
        INSERT INTO markers (
            qr_code, name, category, serial_number, is_storage, rental_status,
            operating_hours, fuel_level, fuel_unit, fuel_capacity,
            maintenance_interval_months, last_maintenance, next_maintenance,
            latitude, longitude, created_by, is_customer_device, customer_name,
            order_number, is_repair_device, repair_description, maintenance_set_id,
            public_token, nfc_enabled, nfc_chip_id, marker_type
        ) VALUES (
            :qr_code, :name, :category, :serial_number, :is_storage, :rental_status,
            :operating_hours, :fuel_level, :fuel_unit, :fuel_capacity,
            :maintenance_interval_months, :last_maintenance, :next_maintenance,
            :latitude, :longitude, :created_by, :is_customer_device, :customer_name,
            :order_number, :is_repair_device, :repair_description, :maintenance_set_id,
            :public_token, :nfc_enabled, :nfc_chip_id, :marker_type
        )
    ");
    
    $stmt->execute($data);
    $marker_id = $pdo->lastInsertId();

    // QR-Code oder NFC-Chip als vergeben markieren
    if ($marker_type === 'qr_code') {
        $stmt = $pdo->prepare("
            UPDATE qr_code_pool 
            SET is_assigned = 1, marker_id = ?, assigned_at = NOW() 
            WHERE qr_code = ?
        ");
        $stmt->execute([$marker_id, $identifier]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE nfc_chip_pool 
            SET is_assigned = 1, assigned_to_marker_id = ?, assigned_at = NOW() 
            WHERE nfc_chip_id = ?
        ");
        $stmt->execute([$marker_id, $nfc_chip_id]);
    }

    // Activity Log
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, username, action, details, marker_id, ip_address, user_agent)
        VALUES (?, ?, 'marker_created', ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'],
        'Marker "' . $data['name'] . '" erstellt (' . ($marker_type === 'qr_code' ? 'QR-Code' : 'NFC-Chip') . ')',
        $marker_id,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    // Wenn Wartungsset zugewiesen wurde, Wartungseinträge erstellen
    if ($data['maintenance_set_id']) {
        $stmt = $pdo->prepare("
            SELECT * FROM maintenance_set_items 
            WHERE set_id = ? 
            ORDER BY display_order
        ");
        $stmt->execute([$data['maintenance_set_id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO maintenance_tasks (
                    marker_id, task_name, task_description, is_completed, 
                    created_at, display_order
                ) VALUES (?, ?, ?, 0, NOW(), ?)
            ");
            $stmt->execute([
                $marker_id,
                $item['item_name'],
                $item['item_description'],
                $item['display_order']
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Marker erfolgreich erstellt!',
        'marker_id' => $marker_id,
        'redirect' => 'marker_view.php?id=' . $marker_id
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
