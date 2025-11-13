<?php
/**
 * Wartungs-PDF Generator mit mPDF
 * Generiert professionelle PDF-Wartungsprotokolle
 */

require_once 'config.php';
require_once 'functions.php';

// mPDF Installation prüfen
if (!class_exists('Mpdf\Mpdf')) {
    die('mPDF ist nicht installiert. Bitte installieren Sie mPDF mit: composer require mpdf/mpdf');
}

use Mpdf\Mpdf;

class MaintenancePdfGenerator {
    private $pdo;
    private $maintenanceId;
    private $maintenanceData;
    private $markerData;
    private $checklistData;
    private $itemsData;
    
    public function __construct($pdo, $maintenanceId) {
        $this->pdo = $pdo;
        $this->maintenanceId = $maintenanceId;
        $this->loadData();
    }
    
    private function loadData() {
        // Wartungsinformationen laden
        $stmt = $this->pdo->prepare("
            SELECT mh.*, 
                   m.name as marker_name, m.qr_code, m.serial_number, m.location,
                   mc.name as checklist_name, mc.is_dguv_compliant,
                   u.username as performed_by_name, u.email as performed_by_email
            FROM maintenance_history mh
            LEFT JOIN markers m ON mh.marker_id = m.id
            LEFT JOIN maintenance_checklists mc ON mh.checklist_id = mc.id
            LEFT JOIN users u ON mh.performed_by = u.id
            WHERE mh.id = ?
        ");
        $stmt->execute([$this->maintenanceId]);
        $this->maintenanceData = $stmt->fetch();
        
        if (!$this->maintenanceData) {
            throw new Exception('Wartung nicht gefunden');
        }
        
        // Checklist Items laden
        $stmt = $this->pdo->prepare("
            SELECT * FROM maintenance_checklist_items 
            WHERE checklist_id = ? 
            ORDER BY item_order, id
        ");
        $stmt->execute([$this->maintenanceData['checklist_id']]);
        $this->itemsData = $stmt->fetchAll();
        
        // Checklist Antworten dekodieren
        $this->checklistData = json_decode($this->maintenanceData['checklist_data'], true) ?? [];
    }
    
    public function generate($outputPath = null) {
        try {
            // mPDF konfigurieren
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 40,
                'margin_bottom' => 25,
                'margin_header' => 10,
                'margin_footer' => 10
            ]);
            
            // Metadaten
            $mpdf->SetTitle('Wartungsprotokoll - ' . $this->maintenanceData['marker_name']);
            $mpdf->SetAuthor($this->maintenanceData['performed_by_name']);
            $mpdf->SetCreator('BGG Objekt System');
            
            // Header mit Logo
            $header = $this->generateHeader();
            $mpdf->SetHTMLHeader($header);
            
            // Footer
            $footer = $this->generateFooter();
            $mpdf->SetHTMLFooter($footer);
            
            // Inhalt generieren
            $html = $this->generateHTML();
            $mpdf->WriteHTML($html);
            
            // PDF speichern
            if (!$outputPath) {
                $uploadDir = 'uploads/maintenance/' . $this->maintenanceId . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $outputPath = $uploadDir . 'wartungsprotokoll_' . date('Y-m-d_His') . '.pdf';
            }
            
            $mpdf->Output($outputPath, 'F');
            
            // PDF-Pfad in Datenbank speichern
            $stmt = $this->pdo->prepare("
                UPDATE maintenance_history 
                SET pdf_report_path = ?
                WHERE id = ?
            ");
            $stmt->execute([$outputPath, $this->maintenanceId]);
            
            return $outputPath;
            
        } catch (Exception $e) {
            throw new Exception('PDF-Generierung fehlgeschlagen: ' . $e->getMessage());
        }
    }
    
    private function generateHeader() {
        $logoPath = 'uploads/company_logo.png';
        $logoHtml = '';
        
        if (file_exists($logoPath)) {
            $logoHtml = '<img src="' . $logoPath . '" style="height: 40px; float: left; margin-right: 15px;">';
        }
        
        return '
        <div style="border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-bottom: 10px;">
            ' . $logoHtml . '
            <div style="float: left;">
                <strong style="font-size: 16px; color: #667eea;">Wartungsprotokoll</strong><br>
                <span style="font-size: 11px; color: #666;">BGG Objekt Management System</span>
            </div>
            <div style="float: right; text-align: right; font-size: 11px; color: #666;">
                Datum: ' . date('d.m.Y', strtotime($this->maintenanceData['maintenance_date'])) . '<br>
                Protokoll-ID: #' . str_pad($this->maintenanceId, 6, '0', STR_PAD_LEFT) . '
            </div>
            <div style="clear: both;"></div>
        </div>
        ';
    }
    
    private function generateFooter() {
        return '
        <div style="border-top: 1px solid #ddd; padding-top: 8px; font-size: 10px; color: #666;">
            <table width="100%">
                <tr>
                    <td width="33%" align="left">BGG Objekt System</td>
                    <td width="33%" align="center">Seite {PAGENO} von {nbpg}</td>
                    <td width="33%" align="right">Erstellt: ' . date('d.m.Y H:i') . '</td>
                </tr>
            </table>
        </div>
        ';
    }
    
    private function generateHTML() {
        $html = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 11pt; }
            h1 { color: #667eea; font-size: 20pt; margin-bottom: 5px; }
            h2 { color: #2c3e50; font-size: 14pt; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
            h3 { color: #34495e; font-size: 12pt; margin-top: 15px; margin-bottom: 8px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
            th { background-color: #667eea; color: white; padding: 8px; text-align: left; font-weight: bold; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            tr:nth-child(even) { background-color: #f8f9fa; }
            .info-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 12px; margin-bottom: 15px; }
            .label { font-weight: bold; color: #2c3e50; }
            .badge { display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 9pt; font-weight: bold; margin-right: 5px; }
            .badge-required { background: #dc3545; color: white; }
            .badge-ok { background: #28a745; color: white; }
            .badge-warning { background: #ffc107; color: black; }
            .badge-dguv { background: #17a2b8; color: white; }
            .checklist-item { margin-bottom: 15px; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; }
            .checklist-item.completed { background: #d4edda; border-left: 4px solid #28a745; }
            .signature-box { border: 2px solid #dee2e6; padding: 10px; margin-top: 20px; min-height: 80px; }
        </style>
        ';
        
        // Titel und Übersicht
        $html .= '<h1>Wartungsprotokoll</h1>';
        
        if (isset($this->maintenanceData['is_dguv_compliant']) && $this->maintenanceData['is_dguv_compliant']) {
            $html .= '<p><span class="badge badge-dguv">DGUV RELEVANT</span></p>';
        }
        
        // Geräteinformationen
        $html .= '<div class="info-box">';
        $html .= '<h2>Geräteinformationen</h2>';
        $html .= '<table>';
        $html .= '<tr><td class="label" width="30%">Gerätename:</td><td>' . htmlspecialchars($this->maintenanceData['marker_name']) . '</td></tr>';
        if ($this->maintenanceData['qr_code']) {
            $html .= '<tr><td class="label">QR-Code:</td><td>' . htmlspecialchars($this->maintenanceData['qr_code']) . '</td></tr>';
        }
        if ($this->maintenanceData['serial_number']) {
            $html .= '<tr><td class="label">Seriennummer:</td><td>' . htmlspecialchars($this->maintenanceData['serial_number']) . '</td></tr>';
        }
        if ($this->maintenanceData['location']) {
            $html .= '<tr><td class="label">Standort:</td><td>' . htmlspecialchars($this->maintenanceData['location']) . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '</div>';
        
        // Wartungsinformationen
        $html .= '<div class="info-box">';
        $html .= '<h2>Wartungsinformationen</h2>';
        $html .= '<table>';
        $html .= '<tr><td class="label" width="30%">Checkliste:</td><td>' . htmlspecialchars($this->maintenanceData['checklist_name']) . '</td></tr>';
        $html .= '<tr><td class="label">Wartungsdatum:</td><td>' . date('d.m.Y', strtotime($this->maintenanceData['maintenance_date'])) . '</td></tr>';
        $html .= '<tr><td class="label">Durchgeführt von:</td><td>' . htmlspecialchars($this->maintenanceData['performed_by_name']);
        if ($this->maintenanceData['performed_by_email']) {
            $html .= ' (' . htmlspecialchars($this->maintenanceData['performed_by_email']) . ')';
        }
        $html .= '</td></tr>';
        $html .= '<tr><td class="label">Status:</td><td><span class="badge badge-ok">ABGESCHLOSSEN</span></td></tr>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Checklisten-Ergebnisse
        $html .= '<h2>Prüfpunkte und Ergebnisse</h2>';
        
        foreach ($this->itemsData as $index => $item) {
            $itemData = $this->checklistData[$item['id']] ?? [];
            $isFilled = (isset($itemData['checked']) && $itemData['checked']) || 
                        (isset($itemData['value']) && trim($itemData['value']) !== '');
            
            $html .= '<div class="checklist-item' . ($isFilled ? ' completed' : '') . '">';
            $html .= '<strong>' . ($index + 1) . '. ' . htmlspecialchars($item['item_text']) . '</strong>';
            
            // Badges
            if ($item['is_required']) {
                $html .= ' <span class="badge badge-required">PFLICHT</span>';
            }
            
            $html .= '<br><br>';
            
            // Ergebnis
            switch ($item['field_type']) {
                case 'checkbox':
                    $checked = isset($itemData['checked']) && $itemData['checked'];
                    $html .= '<span class="badge ' . ($checked ? 'badge-ok' : 'badge-warning') . '">';
                    $html .= $checked ? '✓ Geprüft' : '✗ Nicht geprüft';
                    $html .= '</span>';
                    break;
                
                case 'radio':
                case 'select':
                case 'text':
                case 'textarea':
                case 'number':
                case 'date':
                    $value = $itemData['value'] ?? '-';
                    $html .= '<div style="margin-top: 5px;"><span class="label">Antwort:</span> ' . htmlspecialchars($value) . '</div>';
                    break;
                
                case 'measurement':
                    $value = $itemData['value'] ?? '-';
                    $unit = $item['measurement_unit'] ?: '';
                    $html .= '<div style="margin-top: 5px;"><span class="label">Messwert:</span> ' . htmlspecialchars($value) . ' ' . htmlspecialchars($unit) . '</div>';
                    
                    // Min/Max Prüfung
                    if (is_numeric($value)) {
                        if ($item['measurement_min'] !== null && $value < $item['measurement_min']) {
                            $html .= '<span class="badge badge-warning">⚠ Unter Mindestwert (' . $item['measurement_min'] . ')</span>';
                        }
                        if ($item['measurement_max'] !== null && $value > $item['measurement_max']) {
                            $html .= '<span class="badge badge-warning">⚠ Über Maximalwert (' . $item['measurement_max'] . ')</span>';
                        }
                        if (($item['measurement_min'] === null || $value >= $item['measurement_min']) && 
                            ($item['measurement_max'] === null || $value <= $item['measurement_max'])) {
                            $html .= '<span class="badge badge-ok">✓ Im Normbereich</span>';
                        }
                    }
                    break;
            }
            
            // Notizen
            if (isset($itemData['notes']) && trim($itemData['notes'])) {
                $html .= '<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 3px;">';
                $html .= '<span class="label">Bemerkung:</span> ' . nl2br(htmlspecialchars($itemData['notes']));
                $html .= '</div>';
            }
            
            // Foto
            if (isset($itemData['photo']) && file_exists($itemData['photo'])) {
                $html .= '<div style="margin-top: 10px;">';
                $html .= '<img src="' . $itemData['photo'] . '" style="max-width: 400px; max-height: 300px; border: 1px solid #ddd; border-radius: 4px;">';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        // Allgemeine Notizen
        if ($this->maintenanceData['notes']) {
            $html .= '<h2>Allgemeine Notizen</h2>';
            $html .= '<div class="info-box">';
            $html .= nl2br(htmlspecialchars($this->maintenanceData['notes']));
            $html .= '</div>';
        }
        
        // Unterschrift
        $html .= '<h2>Unterschrift</h2>';
        $html .= '<div class="signature-box">';
        if ($this->maintenanceData['signature_data']) {
            $html .= '<img src="' . $this->maintenanceData['signature_data'] . '" style="max-height: 60px;">';
            $html .= '<br><br>';
        }
        $html .= '<div style="border-top: 1px solid #333; width: 300px; padding-top: 5px; margin-top: 50px;">';
        $html .= htmlspecialchars($this->maintenanceData['performed_by_name']) . '<br>';
        $html .= '<small>' . date('d.m.Y', strtotime($this->maintenanceData['maintenance_date'])) . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Rechtlicher Hinweis
        $html .= '<p style="margin-top: 30px; font-size: 9pt; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">';
        $html .= '<strong>Hinweis:</strong> Dieses Wartungsprotokoll wurde elektronisch erstellt und ist ohne Unterschrift gültig. ';
        $html .= 'Die Wartung wurde durchgeführt von ' . htmlspecialchars($this->maintenanceData['performed_by_name']) . ' ';
        $html .= 'am ' . date('d.m.Y \u\m H:i', strtotime($this->maintenanceData['maintenance_date'])) . ' Uhr.';
        $html .= '</p>';
        
        return $html;
    }
}

// Verwendung, wenn direkt aufgerufen
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    requireLogin();
    
    $maintenanceId = $_GET['id'] ?? null;
    
    if (!$maintenanceId) {
        die('Keine Wartungs-ID angegeben');
    }
    
    try {
        $generator = new MaintenancePdfGenerator($pdo, $maintenanceId);
        $pdfPath = $generator->generate();
        
        // PDF zum Download anbieten
        if (isset($_GET['download'])) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="wartungsprotokoll_' . date('Y-m-d') . '.pdf"');
            header('Content-Length: ' . filesize($pdfPath));
            readfile($pdfPath);
            exit;
        }
        
        // Oder im Browser anzeigen
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="wartungsprotokoll_' . date('Y-m-d') . '.pdf"');
        readfile($pdfPath);
        exit;
        
    } catch (Exception $e) {
        die('Fehler: ' . $e->getMessage());
    }
}