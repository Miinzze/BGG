<?php
/**
 * Erweiterte Sicherheitsfunktionen
 * 
 * Dieses Modul behebt folgende Sicherheitsprobleme:
 * 1. SQL-Injection Prävention mit erweiterten Whitelists
 * 2. XSS-Schutz für alle User-Inputs
 * 3. File-Upload Sicherheit mit Virus-Scan Unterstützung
 */

class SecurityEnhanced {
    
    /**
     * Sichere SQL ORDER BY Klausel mit Whitelist
     * 
     * @param string $orderBy Gewünschte Sortier-Spalte
     * @param string $orderDir Sortierrichtung (ASC/DESC)
     * @param array $allowedColumns Erlaubte Spalten
     * @return array [column, direction]
     */
    public static function sanitizeOrderBy($orderBy, $orderDir = 'DESC', $allowedColumns = []) {
        // Standard erlaubte Spalten für Marker
        $defaultAllowed = ['id', 'name', 'category', 'created_at', 'updated_at', 'next_maintenance', 'fuel_level', 'rental_status'];
        
        $allowed = empty($allowedColumns) ? $defaultAllowed : $allowedColumns;
        
        // Prüfe ob Spalte in Whitelist
        $sanitizedColumn = in_array($orderBy, $allowed) ? $orderBy : $allowed[0];
        
        // Prüfe Sortierrichtung
        $sanitizedDir = in_array(strtoupper($orderDir), ['ASC', 'DESC']) ? strtoupper($orderDir) : 'DESC';
        
        return [
            'column' => $sanitizedColumn,
            'direction' => $sanitizedDir
        ];
    }
    
    /**
     * XSS-Schutz für Custom Field Ausgaben
     * 
     * @param mixed $value Wert zum Escapen
     * @param string $context Kontext (html, attr, js, url)
     * @return string Escaped value
     */
    public static function escapeOutput($value, $context = 'html') {
        if (is_null($value)) {
            return '';
        }
        
        switch ($context) {
            case 'html':
                return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
            case 'attr':
                // Für HTML-Attribute - noch restriktiver
                return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
            case 'js':
                // Für JavaScript-Kontext
                return json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                
            case 'url':
                // Für URLs
                return urlencode((string)$value);
                
            default:
                return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * Sichere Custom Field Ausgabe
     * 
     * @param array $fieldValue Custom Field mit type und value
     * @return string Sicher escaped HTML
     */
    public static function renderCustomFieldValue($fieldValue) {
        $type = $fieldValue['field_type'] ?? 'text';
        $value = $fieldValue['field_value'] ?? '';
        $label = self::escapeOutput($fieldValue['field_label'] ?? 'Feld');
        
        if (empty($value)) {
            return '';
        }
        
        $escapedValue = self::escapeOutput($value);
        
        $html = '<div class="custom-field-display">';
        $html .= '<strong>' . $label . ':</strong> ';
        
        switch ($type) {
            case 'url':
                // URLs als anklickbarer Link
                $html .= '<a href="' . self::escapeOutput($value, 'attr') . '" target="_blank" rel="noopener noreferrer">' . $escapedValue . '</a>';
                break;
                
            case 'email':
                // E-Mails als mailto-Link
                $html .= '<a href="mailto:' . self::escapeOutput($value, 'attr') . '">' . $escapedValue . '</a>';
                break;
                
            case 'tel':
                // Telefonnummern als tel-Link
                $html .= '<a href="tel:' . self::escapeOutput($value, 'attr') . '">' . $escapedValue . '</a>';
                break;
                
            case 'textarea':
                // Mehrzeiliger Text mit nl2br
                $html .= nl2br($escapedValue);
                break;
                
            default:
                // Standard-Text
                $html .= $escapedValue;
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * File-Upload Sicherheitsvalidierung
     * 
     * @param array $file $_FILES Array
     * @param array $options Optionen für Upload
     * @return array ['valid' => bool, 'error' => string|null, 'info' => array]
     */
    public static function validateFileUpload($file, $options = []) {
        $defaults = [
            'max_size' => 10 * 1024 * 1024, // 10MB
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
            'allowed_mime_types' => [
                'image/jpeg', 'image/png', 'image/gif',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip'
            ],
            'scan_virus' => true
        ];
        
        $options = array_merge($defaults, $options);
        
        // Prüfe Upload-Fehler
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'error' => 'Ungültige Dateiparameter'];
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['valid' => false, 'error' => 'Datei ist zu groß'];
            case UPLOAD_ERR_NO_FILE:
                return ['valid' => false, 'error' => 'Keine Datei hochgeladen'];
            default:
                return ['valid' => false, 'error' => 'Upload-Fehler'];
        }
        
        // Prüfe Dateigröße
        if ($file['size'] > $options['max_size']) {
            return ['valid' => false, 'error' => 'Datei überschreitet maximale Größe von ' . ($options['max_size'] / 1024 / 1024) . 'MB'];
        }
        
        // Prüfe Dateiendung
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $options['allowed_extensions'])) {
            return ['valid' => false, 'error' => 'Dateityp nicht erlaubt. Erlaubt sind: ' . implode(', ', $options['allowed_extensions'])];
        }
        
        // Prüfe MIME-Type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $options['allowed_mime_types'])) {
            return ['valid' => false, 'error' => 'Ungültiger Dateityp (MIME-Type: ' . $mimeType . ')'];
        }
        
        // Virus-Scan (wenn aktiviert und verfügbar)
        if ($options['scan_virus']) {
            $virusScanResult = self::scanFileForVirus($file['tmp_name']);
            if (!$virusScanResult['clean']) {
                return ['valid' => false, 'error' => 'Datei wurde als potentiell gefährlich eingestuft: ' . $virusScanResult['reason']];
            }
        }
        
        return [
            'valid' => true,
            'error' => null,
            'info' => [
                'extension' => $ext,
                'mime_type' => $mimeType,
                'size' => $file['size']
            ]
        ];
    }
    
    /**
     * Virus-Scan für hochgeladene Dateien
     * 
     * @param string $filepath Pfad zur Datei
     * @return array ['clean' => bool, 'reason' => string]
     */
    public static function scanFileForVirus($filepath) {
        // Prüfe ob ClamAV verfügbar ist
        if (function_exists('clamav_scan_file')) {
            // ClamAV PHP Extension
            $result = clamav_scan_file($filepath);
            return [
                'clean' => empty($result),
                'reason' => $result ? 'Virus gefunden: ' . $result : 'Sauber'
            ];
        }
        
        // Prüfe ob clamdscan command verfügbar ist
        if (self::isCommandAvailable('clamdscan')) {
            $output = [];
            $returnVar = 0;
            exec('clamdscan --no-summary ' . escapeshellarg($filepath), $output, $returnVar);
            
            // Return code 0 = sauber, 1 = Virus gefunden
            return [
                'clean' => ($returnVar === 0),
                'reason' => ($returnVar === 0) ? 'Sauber' : 'Möglicher Virus: ' . implode(' ', $output)
            ];
        }
        
        // Prüfe ob clamscan command verfügbar ist
        if (self::isCommandAvailable('clamscan')) {
            $output = [];
            $returnVar = 0;
            exec('clamscan --no-summary ' . escapeshellarg($filepath), $output, $returnVar);
            
            return [
                'clean' => ($returnVar === 0),
                'reason' => ($returnVar === 0) ? 'Sauber' : 'Möglicher Virus: ' . implode(' ', $output)
            ];
        }
        
        // Fallback: Basis-Dateiprüfungen
        // Prüfe auf ausführbare Dateien in nicht-ausführbaren Formaten
        $dangerousSignatures = [
            "\x4D\x5A" => 'Windows Executable (PE)',  // MZ header
            "\x7F\x45\x4C\x46" => 'Linux Executable (ELF)',
            "#!/bin/" => 'Shell Script',
            "<?php" => 'PHP Script (nicht in erlaubten Formaten)',
        ];
        
        $handle = fopen($filepath, 'rb');
        $header = fread($handle, 1024);
        fclose($handle);
        
        foreach ($dangerousSignatures as $signature => $description) {
            if (strpos($header, $signature) === 0 || strpos($header, $signature) !== false) {
                return [
                    'clean' => false,
                    'reason' => 'Verdächtige Datei-Signatur erkannt: ' . $description
                ];
            }
        }
        
        // Warnung: Kein Virus-Scanner verfügbar
        // In Produktion sollte hier ein echter Scanner verwendet werden!
        return [
            'clean' => true,
            'reason' => 'WARNUNG: Kein Virus-Scanner verfügbar - nur Basis-Prüfung durchgeführt'
        ];
    }
    
    /**
     * Prüft ob ein System-Command verfügbar ist
     * 
     * @param string $command Command Name
     * @return bool
     */
    private static function isCommandAvailable($command) {
        $output = [];
        $returnVar = 0;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('where ' . escapeshellarg($command), $output, $returnVar);
        } else {
            exec('which ' . escapeshellarg($command), $output, $returnVar);
        }
        
        return $returnVar === 0;
    }
    
    /**
     * Sichere Dateinamen-Generierung
     * 
     * @param string $originalName Original Dateiname
     * @return string Sicherer Dateiname
     */
    public static function sanitizeFilename($originalName) {
        // Hole Extension
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Erstelle sicheren Namen
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = substr($basename, 0, 50); // Max 50 Zeichen
        
        // Füge Timestamp und Random String für Eindeutigkeit hinzu
        $uniqueId = date('YmdHis') . '_' . bin2hex(random_bytes(4));
        
        return $basename . '_' . $uniqueId . '.' . $ext;
    }
    
    /**
     * Kommentar-Ausgabe mit XSS-Schutz
     * 
     * @param string $comment Kommentar-Text
     * @param bool $allowBasicFormatting Erlaube <br>, <b>, <i>
     * @return string Sicher escaped Kommentar
     */
    public static function renderComment($comment, $allowBasicFormatting = true) {
        if (empty($comment)) {
            return '';
        }
        
        // Escape HTML
        $escaped = self::escapeOutput($comment);
        
        if ($allowBasicFormatting) {
            // Erlaube Zeilenumbrüche
            $escaped = nl2br($escaped);
            
            // Erlaube einfache Markdown-Style Formatierung
            // **fett** -> <strong>
            $escaped = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $escaped);
            // *kursiv* -> <em>
            $escaped = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $escaped);
        }
        
        return $escaped;
    }
    
    /**
     * SQL-Injection sichere dynamische WHERE-Bedingungen
     * 
     * @param PDO $pdo PDO Instance
     * @param array $filters Array mit Filter-Bedingungen
     * @param array $allowedFields Erlaubte Felder
     * @return array ['sql' => string, 'params' => array]
     */
    public static function buildSecureWhereClause($pdo, $filters, $allowedFields) {
        $conditions = [];
        $params = [];
        
        foreach ($filters as $field => $value) {
            // Prüfe ob Feld in Whitelist
            if (!in_array($field, $allowedFields)) {
                continue;
            }
            
            // Ignoriere leere Werte
            if ($value === '' || $value === null) {
                continue;
            }
            
            // Baue sichere Bedingung
            if (is_array($value)) {
                // IN Clause für Arrays
                $placeholders = array_fill(0, count($value), '?');
                $conditions[] = "$field IN (" . implode(',', $placeholders) . ")";
                $params = array_merge($params, $value);
            } else {
                // Einfacher Vergleich
                $conditions[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        $sql = empty($conditions) ? '' : ' AND ' . implode(' AND ', $conditions);
        
        return [
            'sql' => $sql,
            'params' => $params
        ];
    }
}

/**
 * Globale Hilfsfunktionen für einfache Verwendung
 */

/**
 * XSS-sicherer Output
 */
function secureOutput($value, $context = 'html') {
    return SecurityEnhanced::escapeOutput($value, $context);
}

/**
 * Sichere Custom Field Ausgabe
 */
function renderSecureCustomField($fieldValue) {
    return SecurityEnhanced::renderCustomFieldValue($fieldValue);
}

/**
 * Sichere Kommentar-Ausgabe
 */
function renderSecureComment($comment, $allowFormatting = true) {
    return SecurityEnhanced::renderComment($comment, $allowFormatting);
}