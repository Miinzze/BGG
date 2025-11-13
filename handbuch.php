<?php
/**
 * BGG Geräte-Verwaltung - System Handbuch
 * Vollständiges Benutzerhandbuch mit Admin-Kapitel Option
 * Optimiert für DIN A4 Druck
 */

// Session starten für Einstellungen
session_start();

// Admin-Kapitel Einstellung verarbeiten
if (isset($_POST['toggle_admin'])) {
    $_SESSION['show_admin_chapter'] = !empty($_POST['show_admin_chapter']);
}

$showAdminChapter = isset($_SESSION['show_admin_chapter']) ? $_SESSION['show_admin_chapter'] : false;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGG Geräte-Verwaltung - Systemhandbuch</title>
    <style>
        /* ==================== DRUCK-OPTIMIERUNG (DIN A4) ==================== */
        @page {
            size: A4;
            margin: 2cm;
        }

        @media print {
            body {
                font-size: 10pt;
                line-height: 1.4;
                color: #000;
                background: white;
            }

            .no-print {
                display: none !important;
            }

            h1 {
                page-break-before: always;
                margin-top: 0;
                font-size: 20pt;
            }

            h1:first-of-type {
                page-break-before: avoid;
            }

            h2 {
                page-break-after: avoid;
                font-size: 16pt;
                margin-top: 1.5em;
            }

            h3 {
                page-break-after: avoid;
                font-size: 13pt;
            }

            h4 {
                page-break-after: avoid;
                font-size: 11pt;
            }

            table, figure, .code-block, .info-box {
                page-break-inside: avoid;
            }

            .page-number {
                display: block;
                text-align: center;
                font-size: 9pt;
                color: #666;
                margin-top: 2em;
            }

            a {
                color: #000;
                text-decoration: none;
            }

            .toc a::after {
                content: leader('.') target-counter(attr(href), page);
            }
        }

        /* ==================== BILDSCHIRM-STYLES ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 21cm; /* DIN A4 Breite */
            margin: 0 auto;
            background: white;
            padding: 2cm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* ==================== CONTROL PANEL ==================== */
        .control-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .control-panel h2 {
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .control-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .checkbox-wrapper:hover {
            background: rgba(255,255,255,0.2);
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-wrapper label {
            cursor: pointer;
            font-size: 1.1em;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76,175,80,0.3);
        }

        .btn-print {
            background: #2196F3;
            color: white;
        }

        .btn-print:hover {
            background: #0b7dda;
        }

        /* ==================== TYPOGRAPHY ==================== */
        .cover-page {
            text-align: center;
            padding: 100px 0;
            border-bottom: 3px solid #667eea;
            margin-bottom: 40px;
        }

        .cover-page h1 {
            font-size: 3em;
            color: #667eea;
            margin-bottom: 20px;
        }

        .cover-page .version {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 10px;
        }

        .cover-page .date {
            color: #999;
            font-size: 1em;
        }

        h1 {
            color: #667eea;
            font-size: 2.2em;
            margin: 40px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }

        h2 {
            color: #764ba2;
            font-size: 1.8em;
            margin: 30px 0 15px 0;
        }

        h3 {
            color: #555;
            font-size: 1.4em;
            margin: 25px 0 12px 0;
        }

        h4 {
            color: #666;
            font-size: 1.2em;
            margin: 20px 0 10px 0;
        }

        p {
            margin-bottom: 15px;
            text-align: justify;
        }

        /* ==================== TABLES ==================== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f5f5f5;
        }

        /* ==================== SPECIAL BOXES ==================== */
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info-box.warning {
            background: #fff3e0;
            border-left-color: #ff9800;
        }

        .info-box.success {
            background: #e8f5e9;
            border-left-color: #4CAF50;
        }

        .info-box.danger {
            background: #ffebee;
            border-left-color: #f44336;
        }

        .info-box h4 {
            margin-top: 0;
            color: inherit;
        }

        /* ==================== CODE BLOCKS ==================== */
        .code-block {
            background: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            overflow-x: auto;
        }

        /* ==================== LISTS ==================== */
        ul, ol {
            margin: 15px 0 15px 30px;
        }

        li {
            margin-bottom: 8px;
        }

        /* ==================== TOC ==================== */
        .toc {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }

        .toc h2 {
            margin-top: 0;
            color: #667eea;
        }

        .toc ul {
            list-style: none;
            margin: 0;
        }

        .toc li {
            margin: 8px 0;
        }

        .toc a {
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s;
        }

        .toc a:hover {
            color: #764ba2;
            padding-left: 5px;
        }

        /* ==================== ICONS ==================== */
        .icon {
            display: inline-block;
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }

        /* ==================== PAGE NUMBERS ==================== */
        .page-number {
            display: none;
        }

        @media print {
            .page-number {
                display: block;
            }
        }

        /* ==================== ADMIN CHAPTER HIGHLIGHT ==================== */
        .admin-chapter {
            border: 3px solid #f44336;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            background: #ffebee;
        }

        .admin-badge {
            background: #f44336;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- Control Panel (nicht drucken) -->
    <div class="control-panel no-print">
        <h2>📖 Handbuch-Einstellungen</h2>
        <form method="POST" class="control-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" 
                       id="show_admin_chapter" 
                       name="show_admin_chapter" 
                       value="1" 
                       <?= $showAdminChapter ? 'checked' : '' ?>
                       onchange="this.form.submit()">
                <label for="show_admin_chapter">🔐 Admin-Kapitel einschließen</label>
            </div>
            <input type="hidden" name="toggle_admin" value="1">
        </form>
        <div style="margin-top: 15px;">
            <button onclick="window.print()" class="btn btn-print">
                🖨️ Handbuch drucken / Als PDF speichern
            </button>
        </div>
        <p style="margin-top: 15px; font-size: 0.9em; opacity: 0.9;">
            ℹ️ Wählen Sie die gewünschten Kapitel und klicken Sie auf "Drucken". 
            Im Druckdialog können Sie auch "Als PDF speichern" wählen.
        </p>
    </div>

    <div class="container">
        <!-- ==================== DECKBLATT ==================== -->
        <div class="cover-page">
            <h1>BGG Geräte-Verwaltung</h1>
            <div class="version">Systemhandbuch Version 1.0</div>
            <div class="date"><?= date('d.m.Y') ?></div>
            <p style="margin-top: 40px; font-size: 1.1em;">
                Vollständiges Benutzer- und Administratorhandbuch
            </p>
        </div>

        <!-- ==================== INHALTSVERZEICHNIS ==================== -->
        <div class="toc">
            <h2>📑 Inhaltsverzeichnis</h2>
            <ul>
                <li><a href="#kapitel-1">1. Einleitung und Systemübersicht</a></li>
                <li><a href="#kapitel-2">2. Erste Schritte und Login</a></li>
                <li><a href="#kapitel-3">3. Dashboard und Navigation</a></li>
                <li><a href="#kapitel-4">4. Marker-Verwaltung</a></li>
                <li><a href="#kapitel-5">5. QR-Codes und NFC-Chips</a></li>
                <li><a href="#kapitel-6">6. Wartung und Inspektionen</a></li>
                <li><a href="#kapitel-7">7. Dokumentenverwaltung</a></li>
                <li><a href="#kapitel-8">8. 3D-Funktionen und AR-Navigation</a></li>
                <li><a href="#kapitel-9">9. Messe-System und Event-Management</a></li>
                <li><a href="#kapitel-10">10. Bug-Tracking und Feedback</a></li>
                <li><a href="#kapitel-11">11. Lead-Verwaltung und Interessenten</a></li>
                <li><a href="#kapitel-12">12. Öffentliche Ansicht und Sharing</a></li>
                <li><a href="#kapitel-13">13. Inaktive Marker-Verwaltung</a></li>
                <li><a href="#kapitel-14">14. Berichte und Exports</a></li>
                <li><a href="#kapitel-15">15. Kalender und Termine</a></li>
                <li><a href="#kapitel-16">16. Benutzereinstellungen</a></li>
                <?php if ($showAdminChapter): ?>
                <li><a href="#kapitel-17">17. Administrator-Bereich 🔐</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ==================== KAPITEL 1: EINLEITUNG ==================== -->
        <h1 id="kapitel-1">1. Einleitung und Systemübersicht</h1>
        
        <h2>1.1 Willkommen</h2>
        <p>
            Willkommen beim BGG Geräte-Verwaltungssystem! Dieses umfassende Tool wurde entwickelt, 
            um die Verwaltung, Verfolgung und Wartung Ihrer Geräte und Assets zu optimieren. 
            Mit modernster GPS-Technologie, QR-Code-Integration und umfangreichen Wartungsfunktionen 
            bietet das System eine zentrale Plattform für alle gerätebezogenen Aufgaben.
        </p>

        <h2>1.2 Systemzweck</h2>
        <p>
            Das BGG Geräte-Verwaltungssystem dient folgenden Hauptzwecken:
        </p>
        <ul>
            <li><strong>Asset-Tracking:</strong> GPS-basierte Echtzeitverfolgung aller Geräte</li>
            <li><strong>Wartungsmanagement:</strong> Automatisierte Wartungspläne und Erinnerungen</li>
            <li><strong>Dokumentation:</strong> Zentrale Ablage aller gerätebezogenen Dokumente</li>
            <li><strong>Vermietung:</strong> Verwaltung von Mietgeräten und deren Status</li>
            <li><strong>Qualitätskontrolle:</strong> Inspektionschecklisten und Prüfprotokolle</li>
            <li><strong>Reporting:</strong> Umfassende Berichte und Analysen</li>
        </ul>

        <h2>1.3 Hauptfunktionen im Überblick</h2>
        
        <table>
            <tr>
                <th>Funktion</th>
                <th>Beschreibung</th>
            </tr>
            <tr>
                <td>🗺️ GPS-Tracking</td>
                <td>Echtzeitverfolgung aller Geräte auf einer interaktiven Karte</td>
            </tr>
            <tr>
                <td>📱 QR & NFC</td>
                <td>Schneller Zugriff über QR-Codes und NFC-Chips</td>
            </tr>
            <tr>
                <td>🔧 Wartung</td>
                <td>Automatische Wartungspläne mit Erinnerungen</td>
            </tr>
            <tr>
                <td>📄 Dokumente</td>
                <td>Zentrale Dokumentenverwaltung pro Gerät</td>
            </tr>
            <tr>
                <td>🎨 3D/AR</td>
                <td>3D-Modelle und Augmented Reality Navigation</td>
            </tr>
            <tr>
                <td>🎪 Messe-System</td>
                <td>Event- und Messepräsentation mit Lead-Erfassung</td>
            </tr>
            <tr>
                <td>🐛 Bug-Tracking</td>
                <td>Feedback-System für Fehlerberichte und Verbesserungen</td>
            </tr>
            <tr>
                <td>🎯 Lead-Verwaltung</td>
                <td>Erfassung und Management von Interessenten</td>
            </tr>
            <tr>
                <td>🌐 Öffentliche Ansicht</td>
                <td>Teilen von Geräten mit externen Personen</td>
            </tr>
            <tr>
                <td>📊 Reporting</td>
                <td>Detaillierte Berichte und Statistiken</td>
            </tr>
            <tr>
                <td>👥 Mehrbenutzerfähig</td>
                <td>Rollen- und Rechtemanagement</td>
            </tr>
        </table>

        <div class="info-box">
            <h4>💡 Tipp</h4>
            <p>
                Nutzen Sie die interaktive Kartenansicht auf dem Dashboard, um einen schnellen 
                Überblick über alle Ihre Geräte zu erhalten. Durch Klick auf einen Marker 
                erhalten Sie sofortigen Zugriff auf alle relevanten Informationen.
            </p>
        </div>

        <h2>1.4 Systemvoraussetzungen</h2>
        
        <h3>1.4.1 Serverseitig</h3>
        <ul>
            <li>PHP 7.4 oder höher</li>
            <li>MySQL 5.7 oder MariaDB 10.2+</li>
            <li>Apache oder Nginx Webserver</li>
            <li>HTTPS-Verbindung (empfohlen)</li>
        </ul>

        <h3>1.4.2 Clientseitig</h3>
        <ul>
            <li>Moderner Webbrowser (Chrome, Firefox, Safari, Edge)</li>
            <li>JavaScript aktiviert</li>
            <li>Für GPS-Funktionen: Standortfreigabe im Browser</li>
            <li>Für optimale Nutzung: Bildschirmauflösung mindestens 1280x720</li>
        </ul>

        <h3>1.4.3 Mobilgeräte</h3>
        <ul>
            <li>iOS 12+ oder Android 8+</li>
            <li>GPS-fähiges Gerät</li>
            <li>Kamera für QR-Code Scanning</li>
            <li>NFC-fähig (optional, für NFC-Chip-Funktionen)</li>
        </ul>

        <div class="page-number">Seite 1</div>

        <!-- ==================== KAPITEL 2: ERSTE SCHRITTE ==================== -->
        <h1 id="kapitel-2">2. Erste Schritte und Login</h1>

        <h2>2.1 Erstes Login</h2>
        <p>
            Um auf das System zuzugreifen, öffnen Sie Ihren Webbrowser und navigieren Sie zur 
            System-URL. Sie werden zur Login-Seite weitergeleitet.
        </p>

        <h3>2.1.1 Anmeldeprozess</h3>
        <ol>
            <li>Geben Sie Ihren <strong>Benutzernamen</strong> ein</li>
            <li>Geben Sie Ihr <strong>Passwort</strong> ein</li>
            <li>Optional: Aktivieren Sie "Angemeldet bleiben" für automatisches Login</li>
            <li>Klicken Sie auf <strong>"Anmelden"</strong></li>
        </ol>

        <div class="info-box warning">
            <h4>⚠️ Sicherheitshinweis</h4>
            <p>
                Verwenden Sie sichere Passwörter mit mindestens 8 Zeichen, Groß- und Kleinbuchstaben, 
                Zahlen und Sonderzeichen. Teilen Sie Ihre Anmeldedaten niemals mit anderen Personen.
            </p>
        </div>

        <h2>2.2 Zwei-Faktor-Authentifizierung (2FA)</h2>
        <p>
            Für erhöhte Sicherheit unterstützt das System Zwei-Faktor-Authentifizierung.
        </p>

        <h3>2.2.1 2FA aktivieren</h3>
        <ol>
            <li>Navigieren Sie zu <strong>Profil → Sicherheit</strong></li>
            <li>Klicken Sie auf <strong>"2FA einrichten"</strong></li>
            <li>Scannen Sie den QR-Code mit einer Authenticator-App (z.B. Google Authenticator, Authy)</li>
            <li>Geben Sie den generierten 6-stelligen Code ein</li>
            <li>Speichern Sie die Backup-Codes an einem sicheren Ort</li>
        </ol>

        <div class="info-box">
            <h4>💡 Empfohlene Authenticator Apps</h4>
            <ul>
                <li>Google Authenticator (iOS/Android)</li>
                <li>Microsoft Authenticator (iOS/Android)</li>
                <li>Authy (iOS/Android/Desktop)</li>
            </ul>
        </div>

        <h2>2.3 Erstanmeldung und Passwortänderung</h2>
        <p>
            Bei der ersten Anmeldung oder nach einem Administrator-Reset werden Sie aufgefordert, 
            Ihr Passwort zu ändern:
        </p>
        <ol>
            <li>Geben Sie Ihr <strong>aktuelles Passwort</strong> ein</li>
            <li>Wählen Sie ein <strong>neues, sicheres Passwort</strong></li>
            <li>Bestätigen Sie das neue Passwort</li>
            <li>Klicken Sie auf <strong>"Passwort ändern"</strong></li>
        </ol>

        <div class="info-box success">
            <h4>✅ Passwortanforderungen</h4>
            <ul>
                <li>Mindestens 8 Zeichen lang</li>
                <li>Mindestens ein Großbuchstabe</li>
                <li>Mindestens ein Kleinbuchstabe</li>
                <li>Mindestens eine Zahl</li>
                <li>Mindestens ein Sonderzeichen</li>
            </ul>
        </div>

        <div class="page-number">Seite 2</div>

        <!-- ==================== KAPITEL 3: DASHBOARD ==================== -->
        <h1 id="kapitel-3">3. Dashboard und Navigation</h1>

        <h2>3.1 Dashboard-Übersicht</h2>
        <p>
            Nach erfolgreicher Anmeldung gelangen Sie zum Hauptdashboard. Hier erhalten Sie 
            einen schnellen Überblick über alle wichtigen Kennzahlen und können direkt auf 
            die wichtigsten Funktionen zugreifen.
        </p>

        <h3>3.1.1 Dashboard-Elemente</h3>
        
        <table>
            <tr>
                <th>Element</th>
                <th>Beschreibung</th>
            </tr>
            <tr>
                <td>Statistik-Kacheln</td>
                <td>Zeigen Gesamtanzahl von Lagergeräten, verfügbaren und vermieteten Geräten</td>
            </tr>
            <tr>
                <td>Wartungs-Übersicht</td>
                <td>Anzeige fälliger Wartungen und Wartungen der aktuellen Woche</td>
            </tr>
            <tr>
                <td>Interaktive Karte</td>
                <td>GPS-Positionen aller aktiven Geräte in Echtzeit</td>
            </tr>
            <tr>
                <td>Auslastungsanzeige</td>
                <td>Prozentuale Auslastung der Mietgeräte</td>
            </tr>
            <tr>
                <td>Schnellzugriff</td>
                <td>Buttons für häufig genutzte Funktionen</td>
            </tr>
        </table>

        <h3>3.1.2 Statistik-Kacheln verstehen</h3>
        
        <div class="code-block">
📦 Lagergeräte: Geräte, die im Lager vorrätig sind
✅ Verfügbar: Mietgeräte, die zur Vermietung bereitstehen
🚛 Vermietet: Aktuell vermietete Geräte
🔧 Wartung fällig: Geräte mit überfälliger Wartung
⚠️ Wartung diese Woche: Anstehende Wartungen in den nächsten 7 Tagen
        </div>

        <h2>3.2 Hauptnavigation</h2>
        <p>
            Die Hauptnavigation befindet sich am oberen Bildschirmrand und bietet Zugriff auf 
            alle Systemfunktionen.
        </p>

        <h3>3.2.1 Navigationselemente</h3>
        
        <ul>
            <li><strong>🏠 Dashboard:</strong> Zurück zur Übersichtsseite</li>
            <li><strong>📍 Marker:</strong> Alle Geräte/Assets verwalten</li>
            <li><strong>📱 QR-Codes:</strong> QR-Code und NFC-Chip Verwaltung</li>
            <li><strong>🔧 Wartung:</strong> Wartungspläne und Inspektionen</li>
            <li><strong>📄 Dokumente:</strong> Dokumentenverwaltung</li>
            <li><strong>📊 Berichte:</strong> Statistiken und Reports</li>
            <li><strong>⚙️ Einstellungen:</strong> Systemkonfiguration</li>
            <li><strong>👤 Profil:</strong> Benutzerprofil und Einstellungen</li>
        </ul>

        <h2>3.3 Kartenansicht</h2>
        <p>
            Die interaktive Karte auf dem Dashboard zeigt die GPS-Positionen aller aktiven Geräte.
        </p>

        <h3>3.3.1 Kartensteuerung</h3>
        <ul>
            <li><strong>Zoom:</strong> Verwenden Sie das Mausrad oder die +/- Buttons</li>
            <li><strong>Verschieben:</strong> Klicken und ziehen Sie die Karte</li>
            <li><strong>Marker-Info:</strong> Klicken Sie auf einen Marker für Details</li>
            <li><strong>Layer-Kontrolle:</strong> Wechseln Sie zwischen verschiedenen Kartenansichten</li>
        </ul>

        <h3>3.3.2 Marker-Symbole</h3>
        
        <table>
            <tr>
                <th>Symbol</th>
                <th>Bedeutung</th>
            </tr>
            <tr>
                <td>🟢 Grüner Marker</td>
                <td>Gerät verfügbar</td>
            </tr>
            <tr>
                <td>🔴 Roter Marker</td>
                <td>Gerät vermietet</td>
            </tr>
            <tr>
                <td>🟡 Gelber Marker</td>
                <td>Wartung fällig</td>
            </tr>
            <tr>
                <td>🟠 Oranger Marker</td>
                <td>In Reparatur</td>
            </tr>
            <tr>
                <td>🔵 Blauer Marker</td>
                <td>Lagergerät</td>
            </tr>
        </table>

        <div class="info-box">
            <h4>💡 Tipp: Marker filtern</h4>
            <p>
                Nutzen Sie die Layer-Kontrolle rechts oben auf der Karte, um nur bestimmte 
                Gerätekategorien anzuzeigen. Dies erhöht die Übersichtlichkeit bei vielen Geräten.
            </p>
        </div>

        <div class="page-number">Seite 3</div>

        <!-- ==================== KAPITEL 4: MARKER-VERWALTUNG ==================== -->
        <h1 id="kapitel-4">4. Marker-Verwaltung</h1>

        <h2>4.1 Was sind Marker?</h2>
        <p>
            Marker repräsentieren Geräte, Assets oder andere zu verwaltende Objekte im System. 
            Jeder Marker enthält umfassende Informationen wie Standort, Status, Wartungshistorie 
            und zugehörige Dokumente.
        </p>

        <h2>4.2 Neuen Marker erstellen</h2>
        
        <h3>4.2.1 Schritt-für-Schritt Anleitung</h3>
        <ol>
            <li>Klicken Sie in der Navigation auf <strong>"Marker"</strong></li>
            <li>Klicken Sie auf den Button <strong>"+ Neuer Marker"</strong></li>
            <li>Füllen Sie das Formular aus (siehe Abschnitt 4.2.2)</li>
            <li>Speichern Sie den Marker mit <strong>"Erstellen"</strong></li>
        </ol>

        <h3>4.2.2 Pflichtfelder und optionale Informationen</h3>
        
        <table>
            <tr>
                <th>Feld</th>
                <th>Pflicht</th>
                <th>Beschreibung</th>
            </tr>
            <tr>
                <td>Name</td>
                <td>✅ Ja</td>
                <td>Eindeutiger Name des Geräts</td>
            </tr>
            <tr>
                <td>Kategorie</td>
                <td>✅ Ja</td>
                <td>Gerätekategorie (z.B. Baumaschine, Werkzeug)</td>
            </tr>
            <tr>
                <td>Beschreibung</td>
                <td>❌ Nein</td>
                <td>Detaillierte Beschreibung</td>
            </tr>
            <tr>
                <td>GPS-Position</td>
                <td>❌ Nein</td>
                <td>Kann manuell oder automatisch erfasst werden</td>
            </tr>
            <tr>
                <td>Seriennummer</td>
                <td>❌ Nein</td>
                <td>Herstellerseriennummer</td>
            </tr>
            <tr>
                <td>Inventarnummer</td>
                <td>❌ Nein</td>
                <td>Interne Inventarnummer</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>✅ Ja</td>
                <td>Verfügbar, Vermietet, In Wartung, etc.</td>
            </tr>
            <tr>
                <td>Standort</td>
                <td>❌ Nein</td>
                <td>Textuelle Standortbeschreibung</td>
            </tr>
        </table>

        <h3>4.2.3 GPS-Position erfassen</h3>
        <p>
            Es gibt mehrere Möglichkeiten, die GPS-Position eines Markers zu erfassen:
        </p>

        <ul>
            <li><strong>Automatisch:</strong> Klicken Sie auf "Aktuellen Standort verwenden" - 
                das System nutzt die GPS-Funktion Ihres Geräts</li>
            <li><strong>Manuell:</strong> Geben Sie Breiten- und Längengrad manuell ein</li>
            <li><strong>Auf Karte:</strong> Klicken Sie auf der Karte auf den gewünschten Standort</li>
            <li><strong>Per QR-Scan:</strong> Scannen Sie den QR-Code vor Ort (mobile Geräte)</li>
        </ul>

        <div class="info-box warning">
            <h4>⚠️ GPS-Genauigkeit</h4>
            <p>
                Die GPS-Genauigkeit kann je nach Gerät und Umgebung variieren. In Gebäuden oder 
                bei schlechtem Wetter kann die Genauigkeit reduziert sein. Für beste Ergebnisse 
                nehmen Sie die GPS-Erfassung im Freien vor.
            </p>
        </div>

        <h2>4.3 Marker bearbeiten</h2>
        
        <h3>4.3.1 Einzelnen Marker bearbeiten</h3>
        <ol>
            <li>Öffnen Sie die <strong>Marker-Liste</strong></li>
            <li>Klicken Sie auf den zu bearbeitenden Marker</li>
            <li>Klicken Sie auf <strong>"Bearbeiten"</strong></li>
            <li>Nehmen Sie die gewünschten Änderungen vor</li>
            <li>Speichern Sie mit <strong>"Aktualisieren"</strong></li>
        </ol>

        <h3>4.3.2 Massenbearbeitung</h3>
        <p>
            Sie können mehrere Marker gleichzeitig bearbeiten:
        </p>
        <ol>
            <li>Wählen Sie die Marker über die Checkboxen aus</li>
            <li>Klicken Sie auf <strong>"Massenbearbeitung"</strong></li>
            <li>Wählen Sie die zu ändernden Felder</li>
            <li>Bestätigen Sie die Änderungen</li>
        </ol>

        <div class="info-box success">
            <h4>✅ Massenbearbeitung nutzen für:</h4>
            <ul>
                <li>Kategorie-Änderungen mehrerer Geräte</li>
                <li>Status-Updates (z.B. alle auf "In Wartung")</li>
                <li>Zuweisungen zu Standorten oder Mitarbeitern</li>
            </ul>
        </div>

        <h2>4.4 Marker-Status</h2>
        <p>
            Jeder Marker kann verschiedene Status haben, die seinen aktuellen Zustand widerspiegeln:
        </p>

        <table>
            <tr>
                <th>Status</th>
                <th>Bedeutung</th>
                <th>Verwendung</th>
            </tr>
            <tr>
                <td>Verfügbar</td>
                <td>Gerät kann vermietet/verwendet werden</td>
                <td>Standardstatus für einsatzbereite Geräte</td>
            </tr>
            <tr>
                <td>Vermietet</td>
                <td>Gerät ist aktuell vermietet</td>
                <td>Automatisch bei Vermietung gesetzt</td>
            </tr>
            <tr>
                <td>In Wartung</td>
                <td>Gerät wird gewartet</td>
                <td>Während Wartungsarbeiten</td>
            </tr>
            <tr>
                <td>In Reparatur</td>
                <td>Gerät ist defekt und wird repariert</td>
                <td>Bei Schäden oder Defekten</td>
            </tr>
            <tr>
                <td>Außer Betrieb</td>
                <td>Gerät nicht nutzbar</td>
                <td>Dauerhaft defekt oder ausgemustert</td>
            </tr>
            <tr>
                <td>Lager</td>
                <td>Gerät im Lager</td>
                <td>Für Lagergeräte und Ersatzteile</td>
            </tr>
        </table>

        <h2>4.5 Marker löschen und Papierkorb</h2>
        
        <h3>4.5.1 Marker löschen</h3>
        <p>
            Gelöschte Marker werden nicht sofort entfernt, sondern in den Papierkorb verschoben:
        </p>
        <ol>
            <li>Öffnen Sie den zu löschenden Marker</li>
            <li>Klicken Sie auf <strong>"Löschen"</strong></li>
            <li>Bestätigen Sie die Löschung</li>
        </ol>

        <h3>4.5.2 Papierkorb verwalten</h3>
        <p>
            Im Papierkorb können Sie gelöschte Marker wiederherstellen oder endgültig löschen:
        </p>
        <ul>
            <li>Klicken Sie auf <strong>"Papierkorb"</strong> in der Navigation</li>
            <li>Wählen Sie einen Marker aus</li>
            <li>Klicken Sie auf <strong>"Wiederherstellen"</strong> oder <strong>"Endgültig löschen"</strong></li>
        </ul>

        <div class="info-box danger">
            <h4>⚠️ Wichtig: Endgültiges Löschen</h4>
            <p>
                Endgültig gelöschte Marker können NICHT wiederhergestellt werden! 
                Alle zugehörigen Daten (GPS-Historie, Dokumente, Wartungshistorie) 
                werden unwiderruflich gelöscht. 
            </p>
        </div>

        <h2>4.6 Marker-Filter und Suche</h2>
        
        <h3>4.6.1 Schnellsuche</h3>
        <p>
            Nutzen Sie das Suchfeld oben in der Marker-Liste, um schnell nach Markern zu suchen. 
            Die Suche durchsucht folgende Felder:
        </p>
        <ul>
            <li>Name</li>
            <li>Seriennummer</li>
            <li>Inventarnummer</li>
            <li>Beschreibung</li>
            <li>Standort</li>
        </ul>

        <h3>4.6.2 Erweiterte Suche</h3>
        <p>
            Für komplexere Suchanfragen nutzen Sie die erweiterte Suche:
        </p>
        <ol>
            <li>Klicken Sie auf <strong>"Erweiterte Suche"</strong></li>
            <li>Wählen Sie Filterkriterien:
                <ul>
                    <li>Kategorie</li>
                    <li>Status</li>
                    <li>Standort</li>
                    <li>Wartungsstatus</li>
                    <li>Datum (Erstellung, letzte Änderung)</li>
                </ul>
            </li>
            <li>Kombinieren Sie mehrere Filter für präzise Ergebnisse</li>
        </ol>

        <h2>4.7 Marker-Vorlagen</h2>
        <p>
            Erstellen Sie Vorlagen für häufig verwendete Marker-Typen, um Zeit zu sparen:
        </p>
        <ol>
            <li>Erstellen Sie einen Marker mit allen Standardeinstellungen</li>
            <li>Klicken Sie auf <strong>"Als Vorlage speichern"</strong></li>
            <li>Geben Sie der Vorlage einen Namen</li>
            <li>Bei neuen Markern: Wählen Sie <strong>"Aus Vorlage erstellen"</strong></li>
        </ol>

        <div class="page-number">Seite 4</div>

        <!-- ==================== KAPITEL 5: QR-CODES UND NFC ==================== -->
        <h1 id="kapitel-5">5. QR-Codes und NFC-Chips</h1>

        <h2>5.1 QR-Code Grundlagen</h2>
        <p>
            QR-Codes ermöglichen einen schnellen und einfachen Zugriff auf Marker-Informationen. 
            Durch Scannen eines QR-Codes mit einem Smartphone kann sofort auf alle relevanten 
            Daten zugegriffen werden.
        </p>

        <h2>5.2 QR-Code erstellen</h2>
        
        <h3>5.2.1 Automatische QR-Code Generierung</h3>
        <p>
            Beim Erstellen eines neuen Markers wird automatisch ein eindeutiger QR-Code generiert.
        </p>

        <h3>5.2.2 Manueller QR-Code</h3>
        <ol>
            <li>Öffnen Sie den Marker</li>
            <li>Wechseln Sie zum Tab <strong>"QR-Codes"</strong></li>
            <li>Klicken Sie auf <strong>"QR-Code generieren"</strong></li>
            <li>Wählen Sie die gewünschten Optionen:
                <ul>
                    <li>Größe (klein, mittel, groß)</li>
                    <li>Format (PNG, SVG, PDF)</li>
                    <li>Mit/ohne Logo</li>
                    <li>Farbschema</li>
                </ul>
            </li>
            <li>Klicken Sie auf <strong>"Generieren"</strong></li>
        </ol>

        <h2>5.3 QR-Code drucken</h2>
        
        <h3>5.3.1 Einzeldruck</h3>
        <ol>
            <li>Öffnen Sie den Marker mit dem gewünschten QR-Code</li>
            <li>Klicken Sie auf <strong>"QR-Code drucken"</strong></li>
            <li>Wählen Sie das Druckformat:
                <ul>
                    <li>Etikett (Klein, 5x5 cm)</li>
                    <li>Standard (Mittel, 10x10 cm)</li>
                    <li>Poster (Groß, A4)</li>
                </ul>
            </li>
            <li>Optional: Fügen Sie Zusatzinformationen hinzu (Name, Inventarnummer)</li>
            <li>Drucken Sie den QR-Code</li>
        </ol>

        <h3>5.3.2 Massen-QR-Code Druck</h3>
        <p>
            Für mehrere Marker gleichzeitig:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"QR-Codes → Stapeldruck"</strong></li>
            <li>Wählen Sie die Marker aus (oder alle)</li>
            <li>Wählen Sie Layout:
                <ul>
                    <li>Etikettenbogen (z.B. Avery Zweckform)</li>
                    <li>Liste (mehrere Codes pro Seite)</li>
                    <li>Individuell</li>
                </ul>
            </li>
            <li>Generieren und drucken Sie die PDF-Datei</li>
        </ol>

        <div class="info-box success">
            <h4>✅ Drucktipps</h4>
            <ul>
                <li>Verwenden Sie hochwertige Etiketten für lange Haltbarkeit</li>
                <li>Laminieren Sie Outdoor-QR-Codes gegen Witterung</li>
                <li>Testen Sie jeden gedruckten QR-Code vor dem Anbringen</li>
                <li>Bewahren Sie eine Backup-Liste aller QR-Codes auf</li>
            </ul>
        </div>

        <h2>5.4 QR-Code scannen</h2>
        
        <h3>5.4.1 Mit dem Smartphone</h3>
        <ol>
            <li>Öffnen Sie die Kamera-App Ihres Smartphones</li>
            <li>Richten Sie die Kamera auf den QR-Code</li>
            <li>Tippen Sie auf die erscheinende Benachrichtigung</li>
            <li>Sie werden zur Marker-Detailseite weitergeleitet</li>
        </ol>

        <h3>5.4.2 Mit dem integrierten Scanner</h3>
        <p>
            Das System verfügt über einen eingebauten QR-Scanner:
        </p>
        <ol>
            <li>Klicken Sie auf <strong>"QR-Code scannen"</strong> in der Navigation</li>
            <li>Erlauben Sie den Kamerazugriff</li>
            <li>Halten Sie den QR-Code vor die Kamera</li>
            <li>Der Marker wird automatisch geöffnet</li>
        </ol>

        <h2>5.5 NFC-Chips</h2>
        <p>
            NFC (Near Field Communication) bietet eine kontaktlose Alternative zu QR-Codes 
            für NFC-fähige Geräte.
        </p>

        <h3>5.5.1 NFC-Chip konfigurieren</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"QR-Codes → NFC-Chips"</strong></li>
            <li>Klicken Sie auf <strong>"Neuer NFC-Chip"</strong></li>
            <li>Wählen Sie den zugehörigen Marker</li>
            <li>Halten Sie einen leeren NFC-Tag an Ihr Gerät</li>
            <li>Der Chip wird automatisch programmiert</li>
        </ol>

        <h3>5.5.2 NFC-Chip verwenden</h3>
        <p>
            So nutzen Sie programmierte NFC-Chips:
        </p>
        <ol>
            <li>Öffnen Sie die System-App auf Ihrem NFC-fähigen Gerät</li>
            <li>Halten Sie Ihr Gerät an den NFC-Chip</li>
            <li>Der zugehörige Marker wird automatisch geöffnet</li>
        </ol>

        <h3>5.5.3 NFC-Chip Typen</h3>
        
        <table>
            <tr>
                <th>Typ</th>
                <th>Eigenschaften</th>
                <th>Verwendung</th>
            </tr>
            <tr>
                <td>NTAG213</td>
                <td>144 Bytes, günstig</td>
                <td>Standard-Anwendungen</td>
            </tr>
            <tr>
                <td>NTAG215</td>
                <td>504 Bytes</td>
                <td>Mit zusätzlichen Daten</td>
            </tr>
            <tr>
                <td>NTAG216</td>
                <td>888 Bytes</td>
                <td>Umfangreiche Informationen</td>
            </tr>
            <tr>
                <td>Metal NFC</td>
                <td>Wetterfest, robust</td>
                <td>Outdoor, Industrieumgebung</td>
            </tr>
        </table>

        <div class="info-box">
            <h4>💡 Wann QR-Code, wann NFC?</h4>
            <ul>
                <li><strong>QR-Code:</strong> Universell, funktioniert auf jedem Smartphone, 
                    günstig, kann von weiter weg gescannt werden</li>
                <li><strong>NFC:</strong> Schneller, funktioniert auch bei Verschmutzung, 
                    robuster, benötigt aber NFC-fähiges Gerät</li>
            </ul>
            <p><strong>Empfehlung:</strong> Nutzen Sie beide Technologien parallel für maximale Flexibilität!</p>
        </div>

        <h2>5.6 QR-Code Branding</h2>
        <p>
            Passen Sie QR-Codes an Ihr Corporate Design an:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"Einstellungen → QR-Branding"</strong></li>
            <li>Laden Sie Ihr Firmenlogo hoch</li>
            <li>Wählen Sie Farben passend zu Ihrem Corporate Design</li>
            <li>Definieren Sie Standard-Texte (z.B. Firmennamen)</li>
            <li>Speichern Sie das Branding-Profil</li>
        </ol>

        <div class="page-number">Seite 5</div>

        <!-- ==================== KAPITEL 6: WARTUNG ==================== -->
        <h1 id="kapitel-6">6. Wartung und Inspektionen</h1>

        <h2>6.1 Wartungsmanagement Übersicht</h2>
        <p>
            Das Wartungsmanagement-System ermöglicht die Planung, Durchführung und Dokumentation 
            von Wartungsarbeiten und Inspektionen. Automatische Erinnerungen stellen sicher, 
            dass keine Wartung vergessen wird.
        </p>

        <h2>6.2 Wartungspläne erstellen</h2>
        
        <h3>6.2.1 Neuen Wartungsplan anlegen</h3>
        <ol>
            <li>Öffnen Sie einen Marker</li>
            <li>Wechseln Sie zum Tab <strong>"Wartung"</strong></li>
            <li>Klicken Sie auf <strong>"Wartungsplan erstellen"</strong></li>
            <li>Füllen Sie das Formular aus:
                <ul>
                    <li><strong>Wartungsintervall:</strong> Täglich, Wöchentlich, Monatlich, Jährlich, 
                        oder benutzerdefiniert</li>
                    <li><strong>Nächste Wartung:</strong> Datum der nächsten fälligen Wartung</li>
                    <li><strong>Wartungstyp:</strong> Inspektion, Ölwechsel, etc.</li>
                    <li><strong>Verantwortlich:</strong> Zuständiger Mitarbeiter</li>
                    <li><strong>Beschreibung:</strong> Details zu den durchzuführenden Arbeiten</li>
                </ul>
            </li>
            <li>Speichern Sie den Wartungsplan</li>
        </ol>

        <h3>6.2.2 Wartungsintervalle</h3>
        
        <table>
            <tr>
                <th>Intervall</th>
                <th>Beschreibung</th>
                <th>Beispiel</th>
            </tr>
            <tr>
                <td>Täglich</td>
                <td>Wartung jeden Tag</td>
                <td>Sichtprüfung Baustellengeräte</td>
            </tr>
            <tr>
                <td>Wöchentlich</td>
                <td>Wartung jede Woche</td>
                <td>Funktionsprüfung Sicherheitsausrüstung</td>
            </tr>
            <tr>
                <td>Monatlich</td>
                <td>Wartung jeden Monat</td>
                <td>Ölstand prüfen, Schmierung</td>
            </tr>
            <tr>
                <td>Quartalsweise</td>
                <td>Alle 3 Monate</td>
                <td>Umfangreiche Inspektion</td>
            </tr>
            <tr>
                <td>Halbjährlich</td>
                <td>Alle 6 Monate</td>
                <td>Hauptwartung</td>
            </tr>
            <tr>
                <td>Jährlich</td>
                <td>Einmal pro Jahr</td>
                <td>TÜV, Hauptuntersuchung</td>
            </tr>
            <tr>
                <td>Nach Betriebsstunden</td>
                <td>Basierend auf Nutzung</td>
                <td>Alle 100 Betriebsstunden</td>
            </tr>
            <tr>
                <td>Benutzerdefiniert</td>
                <td>Eigene Intervalle</td>
                <td>Alle 45 Tage</td>
            </tr>
        </table>

        <h2>6.3 Wartung durchführen</h2>
        
        <h3>6.3.1 Wartung starten</h3>
        <ol>
            <li>Öffnen Sie die <strong>Wartungsübersicht</strong></li>
            <li>Wählen Sie eine fällige Wartung aus</li>
            <li>Klicken Sie auf <strong>"Wartung durchführen"</strong></li>
            <li>Das Wartungsformular öffnet sich</li>
        </ol>

        <h3>6.3.2 Wartungsformular ausfüllen</h3>
        <p>
            Während der Wartung dokumentieren Sie alle durchgeführten Arbeiten:
        </p>
        <ul>
            <li><strong>Durchgeführt von:</strong> Name des Wartungstechnikers</li>
            <li><strong>Datum und Uhrzeit:</strong> Zeitpunkt der Wartung</li>
            <li><strong>Dauer:</strong> Benötigte Zeit in Stunden</li>
            <li><strong>Durchgeführte Arbeiten:</strong> Detaillierte Beschreibung</li>
            <li><strong>Verwendete Materialien:</strong> Ersatzteile, Verbrauchsmaterialien</li>
            <li><strong>Kosten:</strong> Gesamtkosten der Wartung</li>
            <li><strong>Nächste Wartung:</strong> Wird automatisch berechnet</li>
            <li><strong>Fotos:</strong> Dokumentationsfotos hochladen</li>
            <li><strong>Unterschrift:</strong> Digitale Unterschrift</li>
        </ul>

        <h3>6.3.3 Wartung abschließen</h3>
        <ol>
            <li>Überprüfen Sie alle eingegebenen Daten</li>
            <li>Klicken Sie auf <strong>"Wartung abschließen"</strong></li>
            <li>Die nächste Wartung wird automatisch geplant</li>
            <li>Eine Benachrichtigung wird an den Verantwortlichen gesendet</li>
        </ol>

        <div class="info-box success">
            <h4>✅ Best Practices für Wartungsdokumentation</h4>
            <ul>
                <li>Fotografieren Sie den Zustand vor und nach der Wartung</li>
                <li>Notieren Sie alle festgestellten Mängel</li>
                <li>Dokumentieren Sie verwendete Ersatzteile mit Artikelnummern</li>
                <li>Fügen Sie Messwerte hinzu (z.B. Reifendruck, Ölstand)</li>
                <li>Lassen Sie die Wartung digital unterschreiben</li>
            </ul>
        </div>

        <h2>6.4 Inspektionen und Checklisten</h2>
        
        <h3>6.4.1 Inspektionschecklisten erstellen</h3>
        <p>
            Erstellen Sie standardisierte Checklisten für wiederkehrende Inspektionen:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"Wartung → Checklisten"</strong></li>
            <li>Klicken Sie auf <strong>"Neue Checkliste"</strong></li>
            <li>Geben Sie einen Namen ein (z.B. "Tägliche Gerätekontrolle")</li>
            <li>Fügen Sie Checklistenpunkte hinzu:
                <ul>
                    <li>Sichtprüfung auf Beschädigungen</li>
                    <li>Funktionsprüfung Sicherheitseinrichtungen</li>
                    <li>Kontrolle Flüssigkeitsstände</li>
                    <li>etc.</li>
                </ul>
            </li>
            <li>Weisen Sie die Checkliste Markern oder Kategorien zu</li>
        </ol>

        <h3>6.4.2 Inspektion durchführen</h3>
        <ol>
            <li>Öffnen Sie den zu inspizierenden Marker</li>
            <li>Klicken Sie auf <strong>"Inspektion durchführen"</strong></li>
            <li>Wählen Sie die passende Checkliste</li>
            <li>Arbeiten Sie die Checkliste ab:
                <ul>
                    <li>✅ = In Ordnung</li>
                    <li>❌ = Mangel festgestellt</li>
                    <li>⚠️ = Warnung/Beobachtung</li>
                </ul>
            </li>
            <li>Bei Mängeln: Fügen Sie Fotos und Beschreibungen hinzu</li>
            <li>Speichern Sie die Inspektion</li>
        </ol>

        <h3>6.4.3 Mängel beheben</h3>
        <p>
            Festgestellte Mängel können direkt in Wartungsaufträge überführt werden:
        </p>
        <ol>
            <li>Öffnen Sie die Inspektion mit Mängeln</li>
            <li>Klicken Sie auf einen Mangel</li>
            <li>Wählen Sie <strong>"Wartungsauftrag erstellen"</strong></li>
            <li>Der Mangel wird automatisch als Wartungsauftrag angelegt</li>
            <li>Weisen Sie einen Verantwortlichen zu</li>
            <li>Setzen Sie eine Frist</li>
        </ol>

        <h2>6.5 Wartungshistorie</h2>
        <p>
            Die komplette Wartungshistorie jedes Markers ist jederzeit einsehbar:
        </p>
        <ul>
            <li>Öffnen Sie einen Marker</li>
            <li>Wechseln Sie zum Tab <strong>"Historie"</strong></li>
            <li>Filtern Sie nach Wartungstyp, Zeitraum oder Techniker</li>
            <li>Exportieren Sie die Historie als PDF oder Excel</li>
        </ul>

        <h2>6.6 Eskalation und Benachrichtigungen</h2>
        
        <h3>6.6.1 Automatische Erinnerungen</h3>
        <p>
            Das System versendet automatisch Erinnerungen:
        </p>
        <ul>
            <li>7 Tage vor fälliger Wartung</li>
            <li>Am Tag der Fälligkeit</li>
            <li>Täglich nach Überfälligkeit</li>
        </ul>

        <h3>6.6.2 Eskalationsstufen konfigurieren</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"Einstellungen → Eskalation"</strong></li>
            <li>Definieren Sie Eskalationsstufen:
                <ul>
                    <li>Stufe 1: Nach 3 Tagen → Benachrichtigung Vorgesetzter</li>
                    <li>Stufe 2: Nach 7 Tagen → Benachrichtigung Management</li>
                    <li>Stufe 3: Nach 14 Tagen → Automatische Sperrung des Geräts</li>
                </ul>
            </li>
            <li>Speichern Sie die Einstellungen</li>
        </ol>

        <div class="info-box warning">
            <h4>⚠️ Wichtig: Gerätesperrung</h4>
            <p>
                Überfällige kritische Wartungen können eine automatische Sperrung des Geräts 
                auslösen. Das Gerät kann dann nicht mehr vermietet oder verwendet werden, 
                bis die Wartung durchgeführt wurde.
            </p>
        </div>

        <h2>6.7 Wartungsberichte</h2>
        <p>
            Erstellen Sie detaillierte Wartungsberichte:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"Berichte → Wartung"</strong></li>
            <li>Wählen Sie den Berichtszeitraum</li>
            <li>Filtern Sie nach:
                <ul>
                    <li>Kategorie</li>
                    <li>Wartungstyp</li>
                    <li>Techniker</li>
                    <li>Kosten</li>
                </ul>
            </li>
            <li>Generieren Sie den Bericht als PDF oder Excel</li>
        </ol>

        <div class="page-number">Seite 6</div>

        <!-- ==================== KAPITEL 7: DOKUMENTE ==================== -->
        <h1 id="kapitel-7">7. Dokumentenverwaltung</h1>

        <h2>7.1 Dokumenten-System Übersicht</h2>
        <p>
            Das integrierte Dokumentenmanagementsystem ermöglicht die zentrale Verwaltung 
            aller gerätebezogenen Dokumente. Jeder Marker kann unbegrenzt viele Dokumente 
            unterschiedlicher Typen zugeordnet werden.
        </p>

        <h2>7.2 Dokumente hochladen</h2>
        
        <h3>7.2.1 Einzelnes Dokument hochladen</h3>
        <ol>
            <li>Öffnen Sie einen Marker</li>
            <li>Wechseln Sie zum Tab <strong>"Dokumente"</strong></li>
            <li>Klicken Sie auf <strong>"Dokument hochladen"</strong></li>
            <li>Wählen Sie das Dokument von Ihrem Computer aus</li>
            <li>Vergeben Sie:
                <ul>
                    <li><strong>Dokumententyp:</strong> z.B. Bedienungsanleitung, Rechnung, Zertifikat</li>
                    <li><strong>Titel:</strong> Aussagekräftiger Name</li>
                    <li><strong>Beschreibung:</strong> Optional</li>
                    <li><strong>Gültig bis:</strong> Optional für zeitlich begrenzte Dokumente</li>
                </ul>
            </li>
            <li>Klicken Sie auf <strong>"Hochladen"</strong></li>
        </ol>

        <h3>7.2.2 Mehrere Dokumente gleichzeitig hochladen</h3>
        <ol>
            <li>Ziehen Sie mehrere Dateien per Drag & Drop in den Upload-Bereich</li>
            <li>Oder: Wählen Sie mehrere Dateien mit gedrückter Strg-Taste</li>
            <li>Das System lädt alle Dateien parallel hoch</li>
            <li>Vergeben Sie anschließend die Metadaten</li>
        </ol>

        <h3>7.2.3 Unterstützte Dateiformate</h3>
        
        <table>
            <tr>
                <th>Kategorie</th>
                <th>Formate</th>
                <th>Max. Größe</th>
            </tr>
            <tr>
                <td>Dokumente</td>
                <td>PDF, DOC, DOCX, ODT, TXT</td>
                <td>25 MB</td>
            </tr>
            <tr>
                <td>Tabellen</td>
                <td>XLS, XLSX, ODS, CSV</td>
                <td>25 MB</td>
            </tr>
            <tr>
                <td>Bilder</td>
                <td>JPG, PNG, GIF, BMP, WEBP</td>
                <td>10 MB</td>
            </tr>
            <tr>
                <td>CAD</td>
                <td>DWG, DXF, PDF</td>
                <td>50 MB</td>
            </tr>
            <tr>
                <td>Sonstige</td>
                <td>ZIP, RAR, 7Z</td>
                <td>100 MB</td>
            </tr>
        </table>

        <div class="info-box">
            <h4>💡 Automatische Bild-Optimierung</h4>
            <p>
                Hochgeladene Bilder werden automatisch optimiert, um Speicherplatz zu sparen. 
                Das Original bleibt erhalten und kann jederzeit heruntergeladen werden.
            </p>
        </div>

        <h2>7.3 Dokumententypen</h2>
        <p>
            Organisieren Sie Dokumente nach Typen für bessere Übersichtlichkeit:
        </p>

        <table>
            <tr>
                <th>Dokumententyp</th>
                <th>Verwendung</th>
            </tr>
            <tr>
                <td>Bedienungsanleitung</td>
                <td>Handbücher und Anleitungen</td>
            </tr>
            <tr>
                <td>Technische Dokumentation</td>
                <td>Datenblätter, Spezifikationen</td>
            </tr>
            <tr>
                <td>Wartungsprotokoll</td>
                <td>Durchgeführte Wartungen</td>
            </tr>
            <tr>
                <td>Zertifikat</td>
                <td>TÜV, CE, ISO etc.</td>
            </tr>
            <tr>
                <td>Rechnung</td>
                <td>Kaufbelege, Rechnungen</td>
            </tr>
            <tr>
                <td>Versicherung</td>
                <td>Versicherungsunterlagen</td>
            </tr>
            <tr>
                <td>Foto</td>
                <td>Gerätefotos, Schäden</td>
            </tr>
            <tr>
                <td>Sonstiges</td>
                <td>Andere Dokumente</td>
            </tr>
        </table>

        <h3>7.3.1 Eigene Dokumententypen definieren</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"Einstellungen → Dokumententypen"</strong></li>
            <li>Klicken Sie auf <strong>"Neuer Typ"</strong></li>
            <li>Geben Sie einen Namen ein</li>
            <li>Optional: Wählen Sie ein Icon</li>
            <li>Speichern Sie den neuen Typ</li>
        </ol>

        <h2>7.4 Dokumente verwalten</h2>
        
        <h3>7.4.1 Dokumente anzeigen</h3>
        <ul>
            <li>Klicken Sie auf ein Dokument, um es im Browser zu öffnen</li>
            <li>PDF-Dateien werden direkt im Browser angezeigt</li>
            <li>Andere Formate werden heruntergeladen</li>
        </ul>

        <h3>7.4.2 Dokumente bearbeiten</h3>
        <ol>
            <li>Klicken Sie auf das Stift-Symbol neben dem Dokument</li>
            <li>Ändern Sie:
                <ul>
                    <li>Titel</li>
                    <li>Beschreibung</li>
                    <li>Dokumententyp</li>
                    <li>Gültigkeitsdatum</li>
                </ul>
            </li>
            <li>Speichern Sie die Änderungen</li>
        </ol>

        <h3>7.4.3 Dokumente ersetzen</h3>
        <p>
            Um eine neue Version eines Dokuments hochzuladen:
        </p>
        <ol>
            <li>Klicken Sie auf <strong>"Ersetzen"</strong> neben dem Dokument</li>
            <li>Wählen Sie die neue Datei</li>
            <li>Die alte Version wird automatisch archiviert</li>
            <li>Alle Verweise bleiben erhalten</li>
        </ol>

        <h3>7.4.4 Dokumente löschen</h3>
        <ol>
            <li>Klicken Sie auf das Papierkorb-Symbol</li>
            <li>Bestätigen Sie die Löschung</li>
            <li>Das Dokument wird in den Papierkorb verschoben</li>
            <li>Kann später endgültig gelöscht werden</li>
        </ol>

        <h2>7.5 Dokumentensuche</h2>
        <p>
            Finden Sie Dokumente schnell über die Suchfunktion:
        </p>
        <ul>
            <li>Suchen Sie nach Dateinamen, Titel oder Beschreibung</li>
            <li>Filtern Sie nach Dokumententyp</li>
            <li>Sortieren Sie nach Datum, Größe oder Typ</li>
            <li>Nutzen Sie die Volltext-Suche in PDF-Dokumenten</li>
        </ul>

        <h2>7.6 Versionierung</h2>
        <p>
            Das System verwaltet automatisch alle Versionen eines Dokuments:
        </p>
        <ul>
            <li>Jede Änderung erstellt eine neue Version</li>
            <li>Alte Versionen bleiben verfügbar</li>
            <li>Versionsnummer wird automatisch hochgezählt</li>
            <li>Vergleichen Sie Versionen direkt im System</li>
        </ul>

        <h2>7.7 Ablaufende Dokumente</h2>
        <p>
            Für Dokumente mit Ablaufdatum (z.B. Zertifikate):
        </p>
        <ul>
            <li>Automatische Erinnerung 30 Tage vor Ablauf</li>
            <li>Warnung bei abgelaufenen Dokumenten</li>
            <li>Optional: Automatische Sperrung des Geräts</li>
            <li>Dashboard zeigt alle ablaufenden Dokumente</li>
        </ul>

        <div class="info-box warning">
            <h4>⚠️ Kritische Dokumente</h4>
            <p>
                Markieren Sie wichtige Dokumente als "kritisch" (z.B. TÜV-Abnahmen). 
                Bei Ablauf dieser Dokumente wird das Gerät automatisch gesperrt, 
                bis ein neues gültiges Dokument hochgeladen wurde.
            </p>
        </div>

        <div class="page-number">Seite 7</div>

        <!-- ==================== KAPITEL 8: 3D UND AR ==================== -->
        <h1 id="kapitel-8">8. 3D-Funktionen und AR-Navigation</h1>

        <h2>8.1 3D-Modelle Übersicht</h2>
        <p>
            Das System unterstützt das Hochladen und Anzeigen von 3D-Modellen der Geräte. 
            Dies ermöglicht eine realistische Voransicht und AR-gestützte Navigation zum Gerät.
        </p>

        <h2>8.2 3D-Modell hochladen</h2>
        
        <h3>8.2.1 Unterstützte 3D-Formate</h3>
        <ul>
            <li><strong>GLB/GLTF:</strong> Empfohlen, optimal für Web (max. 50 MB)</li>
            <li><strong>OBJ:</strong> Mit MTL-Texturen (max. 100 MB)</li>
            <li><strong>FBX:</strong> Autodesk Format (max. 100 MB)</li>
            <li><strong>STL:</strong> Für CAD-Modelle (max. 50 MB)</li>
        </ul>

        <h3>8.2.2 Upload-Prozess</h3>
        <ol>
            <li>Öffnen Sie einen Marker</li>
            <li>Wechseln Sie zum Tab <strong>"3D-Modell"</strong></li>
            <li>Klicken Sie auf <strong>"3D-Modell hochladen"</strong></li>
            <li>Wählen Sie Ihr 3D-Modell (inkl. Texturen)</li>
            <li>Warten Sie, bis das Upload und die Verarbeitung abgeschlossen sind</li>
            <li>Das Modell wird automatisch optimiert</li>
        </ol>

        <div class="info-box">
            <h4>💡 3D-Modell Optimierung</h4>
            <p>
                Hochgeladene 3D-Modelle werden automatisch für Web-Darstellung optimiert:
            </p>
            <ul>
                <li>Polygon-Reduktion für schnellere Ladezeiten</li>
                <li>Textur-Komprimierung</li>
                <li>Automatische LOD (Level of Detail) Erstellung</li>
                <li>Konvertierung zu GLB für beste Kompatibilität</li>
            </ul>
        </div>

        <h2>8.3 3D-Viewer</h2>
        <p>
            Der integrierte 3D-Viewer ermöglicht interaktive Betrachtung:
        </p>

        <h3>8.3.1 Steuerung</h3>
        <ul>
            <li><strong>Drehen:</strong> Linke Maustaste gedrückt halten und ziehen</li>
            <li><strong>Zoomen:</strong> Mausrad oder Pinch-Geste (Touchscreen)</li>
            <li><strong>Verschieben:</strong> Rechte Maustaste oder Zwei-Finger-Geste</li>
            <li><strong>Zurücksetzen:</strong> Doppelklick auf Modell</li>
        </ul>

        <h3>8.3.2 Funktionen</h3>
        <ul>
            <li>🔄 <strong>Automatische Rotation</strong> ein/ausschalten</li>
            <li>📐 <strong>Maßanzeigen</strong> einblenden</li>
            <li>🎨 <strong>Wireframe-Modus</strong> für technische Details</li>
            <li>💡 <strong>Beleuchtung</strong> anpassen</li>
            <li>📸 <strong>Screenshot</strong> erstellen</li>
            <li>🔗 <strong>3D-Modell teilen</strong> (öffentlicher Link)</li>
        </ul>

        <h2>8.4 AR-Navigation</h2>
        <p>
            Mit Augmented Reality können Sie direkt zum Gerät navigieren:
        </p>

        <h3>8.4.1 AR-Navigation starten</h3>
        <ol>
            <li>Öffnen Sie die <strong>Marker-Detailseite</strong></li>
            <li>Klicken Sie auf <strong>"AR-Navigation starten"</strong></li>
            <li>Erlauben Sie Kamera- und Standortzugriff</li>
            <li>Folgen Sie den eingeblendeten Richtungspfeilen</li>
            <li>Die Entfernung wird in Echtzeit angezeigt</li>
        </ol>

        <h3>8.4.2 AR-Funktionen</h3>
        <ul>
            <li>Richtungspfeile zur Zielposition</li>
            <li>Entfernungsanzeige in Metern</li>
            <li>3D-Modell am Zielort (wenn verfügbar)</li>
            <li>Kompass-Integration</li>
            <li>Höhenunterschiede werden berücksichtigt</li>
        </ul>

        <div class="info-box warning">
            <h4>⚠️ AR-Voraussetzungen</h4>
            <ul>
                <li>Smartphone mit AR-Unterstützung (ARCore/ARKit)</li>
                <li>GPS-Signal erforderlich</li>
                <li>Kamerazugriff muss erlaubt sein</li>
                <li>Funktioniert am besten im Freien</li>
            </ul>
        </div>

        <h2>8.5 Photogrammetrie</h2>
        <p>
            Erstellen Sie 3D-Modelle direkt aus Fotos:
        </p>

        <h3>8.5.1 Fotos für 3D-Rekonstruktion aufnehmen</h3>
        <ol>
            <li>Öffnen Sie die <strong>3D-Capture Funktion</strong></li>
            <li>Fotografieren Sie das Gerät aus allen Winkeln:
                <ul>
                    <li>Mindestens 20-30 Fotos</li>
                    <li>Rundherum um das Objekt</li>
                    <li>Auch von oben und unten</li>
                    <li>Überlappung von ca. 60% zwischen Fotos</li>
                </ul>
            </li>
            <li>Laden Sie alle Fotos hoch</li>
            <li>Starten Sie die <strong>3D-Rekonstruktion</strong></li>
            <li>Die Verarbeitung kann 10-30 Minuten dauern</li>
        </ol>

        <h3>8.5.2 Tipps für beste Ergebnisse</h3>
        <div class="info-box success">
            <h4>✅ Fotografie-Tipps</h4>
            <ul>
                <li>Gleichmäßige Beleuchtung (Tageslicht ideal)</li>
                <li>Vermeiden Sie Schatten und Reflexionen</li>
                <li>Objekt sollte gut sichtbar sein</li>
                <li>Hintergrund möglichst einfach</li>
                <li>Keine beweglichen Teile während der Aufnahme</li>
                <li>Fester Standpunkt des Objekts</li>
            </ul>
        </div>

        <h2>8.6 3D-Modell teilen</h2>
        <p>
            Teilen Sie 3D-Modelle mit externen Personen:
        </p>
        <ol>
            <li>Öffnen Sie das 3D-Modell</li>
            <li>Klicken Sie auf <strong>"Teilen"</strong></li>
            <li>Wählen Sie:
                <ul>
                    <li><strong>Öffentlicher Link:</strong> Jeder mit Link kann zugreifen</li>
                    <li><strong>Passwortgeschützt:</strong> Link benötigt Passwort</li>
                    <li><strong>Zeitlich begrenzt:</strong> Link läuft automatisch ab</li>
                </ul>
            </li>
            <li>Kopieren Sie den generierten Link</li>
            <li>Teilen Sie ihn per E-Mail oder Chat</li>
        </ol>

        <div class="page-number">Seite 8</div>

<!-- ==================== KAPITEL 9: MESSE-SYSTEM ==================== -->
        <h1 id="kapitel-9">9. Messe-System und Event-Management</h1>

        <h2>9.1 Messe-System Übersicht</h2>
        <p>
            Das integrierte Messe-System ermöglicht die professionelle Präsentation Ihrer Geräte 
            auf Messen, Events und Ausstellungen. Erstellen Sie individuelle Messepräsenzen mit 
            eigenem Branding, interaktiven Gerätekatalogen und Lead-Erfassung.
        </p>

        <h2>9.2 Neue Messe erstellen</h2>
        
        <h3>9.2.1 Messe-Grundeinstellungen</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"Messe-Verwaltung"</strong></li>
            <li>Klicken Sie auf <strong>"Neue Messe erstellen"</strong></li>
            <li>Füllen Sie die Basisinformationen aus:
                <ul>
                    <li><strong>Messenam:</strong> Eindeutiger Name (z.B. "Bauma 2024")</li>
                    <li><strong>Beschreibung:</strong> Kurze Beschreibung der Veranstaltung</li>
                    <li><strong>Datum:</strong> Startdatum und Enddatum</li>
                    <li><strong>Ort:</strong> Veranstaltungsort</li>
                    <li><strong>Stand-Nummer:</strong> Ihre Standnummer</li>
                </ul>
            </li>
            <li>Speichern Sie die Grundeinstellungen</li>
        </ol>

        <h3>9.2.2 Branding und Design</h3>
        <p>
            Passen Sie das Erscheinungsbild der Messe-Ansicht an:
        </p>
        <ul>
            <li><strong>Logo:</strong> Laden Sie Ihr Firmenlogo hoch</li>
            <li><strong>Hero-Bild:</strong> Großes Header-Bild für die Startseite</li>
            <li><strong>Hintergrundbild:</strong> Optional für die Messe-Ansicht</li>
            <li><strong>Primärfarbe:</strong> Hauptfarbe für Buttons und Akzente</li>
            <li><strong>Sekundärfarbe:</strong> Ergänzende Farbe</li>
            <li><strong>Begrüßungstext:</strong> Willkommensnachricht für Besucher</li>
        </ul>

        <div class="info-box success">
            <h4>✅ Design-Tipps für Messen</h4>
            <ul>
                <li>Verwenden Sie hochauflösende Bilder (mind. 1920px Breite)</li>
                <li>Wählen Sie kontrastreiche Farben für gute Lesbarkeit</li>
                <li>Halten Sie Texte kurz und prägnant</li>
                <li>Testen Sie die Ansicht auf verschiedenen Geräten</li>
            </ul>
        </div>

        <h2>9.3 Geräte zur Messe hinzufügen</h2>
        
        <h3>9.3.1 Marker auswählen</h3>
        <ol>
            <li>Öffnen Sie die erstellte Messe</li>
            <li>Wechseln Sie zum Tab <strong>"Geräte"</strong></li>
            <li>Klicken Sie auf <strong>"Geräte hinzufügen"</strong></li>
            <li>Wählen Sie Marker aus der Liste:
                <ul>
                    <li>Einzelauswahl per Klick</li>
                    <li>Mehrfachauswahl mit Strg/Cmd</li>
                    <li>Kategorieweise auswählen</li>
                </ul>
            </li>
            <li>Bestätigen Sie die Auswahl</li>
        </ol>

        <h3>9.3.2 Messe-spezifische Geräteinformationen</h3>
        <p>
            Für jedes Gerät können Sie zusätzliche Messe-Informationen hinterlegen:
        </p>
        <ul>
            <li><strong>Messe-Titel:</strong> Alternativer Anzeigename</li>
            <li><strong>Messe-Beschreibung:</strong> Verkaufstext für die Messe</li>
            <li><strong>Highlights:</strong> Besondere Features (Bulletpoints)</li>
            <li><strong>Messe-Bilder:</strong> Zusätzliche Produktfotos</li>
            <li><strong>Demo-Videos:</strong> Links zu Vorführvideos</li>
            <li><strong>Technische Daten:</strong> Datenblatt zum Download</li>
            <li><strong>Preisinformation:</strong> Optional anzeigbar</li>
        </ul>

        <h2>9.4 Öffentliche Messe-Ansicht</h2>
        
        <h3>9.4.1 Messe aktivieren und teilen</h3>
        <ol>
            <li>Aktivieren Sie die Messe über den Toggle-Schalter</li>
            <li>Das System generiert automatisch eine öffentliche URL</li>
            <li>Die URL hat das Format: <code>https://ihr-system.de/messe_view.php?id=XXXXX</code></li>
            <li>Teilen Sie die URL mit:
                <ul>
                    <li>QR-Codes für Messestände</li>
                    <li>E-Mail-Einladungen</li>
                    <li>Soziale Medien</li>
                    <li>Messekatalogen</li>
                </ul>
            </li>
        </ol>

        <h3>9.4.2 Besucher-Features</h3>
        <p>
            Besucher der Messe-Ansicht können:
        </p>
        <ul>
            <li>Geräte durchstöbern und filtern</li>
            <li>Detailansichten öffnen</li>
            <li>3D-Modelle betrachten (falls vorhanden)</li>
            <li>Dokumente herunterladen</li>
            <li>Kontaktanfragen stellen</li>
            <li>Favoritenlisten erstellen</li>
        </ul>

        <h2>9.5 QR-Codes für Messestände</h2>
        
        <h3>9.5.1 Messe-QR-Codes generieren</h3>
        <ol>
            <li>Öffnen Sie die Messe</li>
            <li>Klicken Sie auf <strong>"Messe-QR-Codes"</strong></li>
            <li>Wählen Sie QR-Code-Typ:
                <ul>
                    <li><strong>Messe-Übersicht:</strong> Link zur Hauptseite</li>
                    <li><strong>Einzelnes Gerät:</strong> Direktlink zu Gerät</li>
                    <li><strong>Kategorie:</strong> Gefilterte Ansicht</li>
                </ul>
            </li>
            <li>Generieren und drucken Sie die Codes</li>
        </ol>

        <h3>9.5.2 Messe-Beschilderung</h3>
        <p>
            Erstellen Sie professionelle Beschilderung:
        </p>
        <ul>
            <li>QR-Code mit Firmenlogo</li>
            <li>Anleitung für Besucher</li>
            <li>Call-to-Action Text</li>
            <li>Verschiedene Größen (A6 bis A1)</li>
        </ul>

        <div class="info-box">
            <h4>💡 Tipp: Messe-Stand Setup</h4>
            <p>
                Platzieren Sie QR-Codes an folgenden Stellen:
            </p>
            <ul>
                <li>Haupteingang des Stands</li>
                <li>Bei jedem ausgestellten Gerät</li>
                <li>An der Empfangstheke</li>
                <li>In Broschüren und Flyern</li>
            </ul>
        </div>

        <h2>9.6 Messe-Statistiken</h2>
        
        <h3>9.6.1 Auswertungen in Echtzeit</h3>
        <p>
            Verfolgen Sie den Erfolg Ihrer Messe live:
        </p>
        
        <table>
            <tr>
                <th>Metrik</th>
                <th>Beschreibung</th>
            </tr>
            <tr>
                <td>Besucher gesamt</td>
                <td>Anzahl eindeutiger Besucher</td>
            </tr>
            <tr>
                <td>Seitenaufrufe</td>
                <td>Gesamte Seitenaufrufe</td>
            </tr>
            <tr>
                <td>Beliebteste Geräte</td>
                <td>Meist angesehene Produkte</td>
            </tr>
            <tr>
                <td>Verweildauer</td>
                <td>Durchschnittliche Besuchsdauer</td>
            </tr>
            <tr>
                <td>Leads generiert</td>
                <td>Anzahl Kontaktanfragen</td>
            </tr>
            <tr>
                <td>Downloads</td>
                <td>Heruntergeladene Dokumente</td>
            </tr>
            <tr>
                <td>QR-Scans</td>
                <td>Anzahl gescannter QR-Codes</td>
            </tr>
        </table>

        <h3>9.6.2 Nach-Messe Reporting</h3>
        <ol>
            <li>Generieren Sie einen Abschlussbericht</li>
            <li>Exportieren Sie alle Leads als Excel</li>
            <li>Analysieren Sie die erfolgreichsten Geräte</li>
            <li>Nutzen Sie die Erkenntnisse für zukünftige Messen</li>
        </ol>

        <h2>9.7 Messe-Geräte verwalten</h2>
        
        <h3>9.7.1 Geräte-Upload vor Ort</h3>
        <p>
            Laden Sie Bilder direkt vom Messestand hoch:
        </p>
        <ol>
            <li>Öffnen Sie die Messe-Ansicht auf Mobilgerät</li>
            <li>Melden Sie sich als Administrator an</li>
            <li>Wählen Sie ein Gerät</li>
            <li>Fotografieren Sie das Gerät im Messebetrieb</li>
            <li>Laden Sie das Foto sofort hoch</li>
            <li>Das Bild wird in Echtzeit aktualisiert</li>
        </ol>

        <h3>9.7.2 Messe-Notizen</h3>
        <p>
            Halten Sie wichtige Informationen fest:
        </p>
        <ul>
            <li>Kundeninteresse an bestimmten Geräten</li>
            <li>Häufig gestellte Fragen</li>
            <li>Technische Probleme</li>
            <li>Verbesserungsvorschläge</li>
        </ul>

        <div class="page-number">Seite 9</div>

        <!-- ==================== KAPITEL 10: BUG-TRACKING ==================== -->
        <h1 id="kapitel-10">10. Bug-Tracking und Feedback</h1>

        <h2>10.1 Bug-Tracking System Übersicht</h2>
        <p>
            Das integrierte Bug-Tracking System ermöglicht es Benutzern, Fehler zu melden, 
            Verbesserungsvorschläge einzureichen und den Status ihrer Tickets zu verfolgen.
        </p>

        <h2>10.2 Bug-Ticket erstellen</h2>
        
        <h3>10.2.1 Neuen Bug melden</h3>
        <ol>
            <li>Klicken Sie auf <strong>"Bug melden"</strong> (meist im Footer)</li>
            <li>Füllen Sie das Formular aus:
                <ul>
                    <li><strong>Titel:</strong> Kurze Beschreibung des Problems</li>
                    <li><strong>Kategorie:</strong> 
                        <ul>
                            <li>Bug/Fehler</li>
                            <li>Verbesserungsvorschlag</li>
                            <li>Feature-Request</li>
                            <li>Frage</li>
                        </ul>
                    </li>
                    <li><strong>Priorität:</strong> Niedrig, Mittel, Hoch, Kritisch</li>
                    <li><strong>Beschreibung:</strong> Detaillierte Problembeschreibung</li>
                    <li><strong>Schritte zur Reproduktion:</strong> Wie tritt der Fehler auf?</li>
                    <li><strong>Screenshots:</strong> Bis zu 5 Bilder hochladen</li>
                </ul>
            </li>
            <li>Senden Sie das Ticket</li>
            <li>Sie erhalten eine Bestätigung per E-Mail</li>
        </ol>

        <div class="info-box success">
            <h4>✅ Gute Bug-Reports enthalten:</h4>
            <ul>
                <li>Aussagekräftigen Titel</li>
                <li>Schritt-für-Schritt Anleitung zur Reproduktion</li>
                <li>Screenshots des Fehlers</li>
                <li>Browser und Betriebssystem</li>
                <li>Erwartetes vs. tatsächliches Verhalten</li>
            </ul>
        </div>

        <h2>10.3 Eigene Tickets verwalten</h2>
        
        <h3>10.3.1 Ticket-Übersicht</h3>
        <p>
            Unter <strong>"Meine Tickets"</strong> sehen Sie alle Ihre Bug-Reports:
        </p>
        
        <table>
            <tr>
                <th>Status</th>
                <th>Bedeutung</th>
                <th>Farbe</th>
            </tr>
            <tr>
                <td>Offen</td>
                <td>Ticket wurde erstellt, noch nicht bearbeitet</td>
                <td>🔴 Rot</td>
            </tr>
            <tr>
                <td>In Bearbeitung</td>
                <td>Team arbeitet am Problem</td>
                <td>🟡 Gelb</td>
            </tr>
            <tr>
                <td>Rückmeldung</td>
                <td>Wir benötigen weitere Informationen</td>
                <td>🟠 Orange</td>
            </tr>
            <tr>
                <td>Erledigt</td>
                <td>Problem wurde behoben</td>
                <td>🟢 Grün</td>
            </tr>
            <tr>
                <td>Abgelehnt</td>
                <td>Kein Bug oder nicht umsetzbar</td>
                <td>⚫ Grau</td>
            </tr>
        </table>

        <h3>10.3.2 Auf Tickets antworten</h3>
        <p>
            Kommunizieren Sie mit dem Support-Team:
        </p>
        <ol>
            <li>Öffnen Sie ein Ticket</li>
            <li>Scrollen Sie zum Kommentar-Bereich</li>
            <li>Schreiben Sie Ihre Antwort</li>
            <li>Fügen Sie bei Bedarf weitere Screenshots hinzu</li>
            <li>Senden Sie den Kommentar</li>
        </ol>

        <h2>10.4 Benachrichtigungen</h2>
        <p>
            Sie erhalten automatisch E-Mail-Benachrichtigungen bei:
        </p>
        <ul>
            <li>Statusänderung Ihres Tickets</li>
            <li>Neuen Kommentaren vom Support-Team</li>
            <li>Anfragen nach zusätzlichen Informationen</li>
            <li>Lösung des Problems</li>
        </ul>

        <h2>10.5 Ticket-Archiv</h2>
        <p>
            Erledigte Tickets werden automatisch archiviert und 
            bleiben aber weiterhin einsehbar für:
        </p>
        <ul>
            <li>Referenzzwecke</li>
            <li>Historische Übersicht</li>
            <li>Wiederkehrende Probleme</li>
        </ul>

        <div class="page-number">Seite 10</div>

        <!-- ==================== KAPITEL 11: LEAD-VERWALTUNG ==================== -->
        <h1 id="kapitel-11">11. Lead-Verwaltung und Interessenten</h1>

        <h2>11.1 Lead-System Übersicht</h2>
        <p>
            Das Lead-Management System erfasst und verwaltet Interessenten, die über 
            Messe-Ansichten, öffentliche Links oder QR-Codes Kontakt aufnehmen.
        </p>

        <h2>11.2 Lead-Erfassung</h2>
        
        <h3>11.2.1 Automatische Lead-Generierung</h3>
        <p>
            Leads werden automatisch erfasst, wenn Besucher:
        </p>
        <ul>
            <li>Kontaktformular auf Messe-Ansicht ausfüllen</li>
            <li>Interesse an einem Gerät bekunden</li>
            <li>Dokumente anfordern</li>
        </ul>

        <h3>11.2.2 Erfasste Informationen</h3>
        <p>
            Jeder Lead enthält:
        </p>
        <ul>
            <li><strong>Persönliche Daten:</strong> Name, E-Mail, Telefon</li>
            <li><strong>Firmendaten:</strong> Firma, Position, Branche</li>
            <li><strong>Interesse:</strong> Welche Geräte wurden angesehen</li>
            <li><strong>Quelle:</strong> Messe, QR-Code, Direct Link</li>
            <li><strong>Zeitstempel:</strong> Wann Kontakt aufgenommen</li>
            <li><strong>Nachricht:</strong> Optional Freitext</li>
            <li><strong>IP-Adresse:</strong> Für Spam-Schutz</li>
        </ul>

        <h2>11.3 Lead-Verwaltung</h2>
        
        <h3>11.3.1 Lead-Übersicht</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"Lead-Verwaltung"</strong></li>
            <li>Sehen Sie alle erfassten Leads</li>
            <li>Filtern Sie nach:
                <ul>
                    <li>Datum</li>
                    <li>Quelle (Messe)</li>
                    <li>Status (Neu, Kontaktiert, Qualifiziert, etc.)</li>
                    <li>Interessierte Geräte</li>
                </ul>
            </li>
        </ol>

        <h3>11.3.2 Lead-Status</h3>
        
        <table>
            <tr>
                <th>Status</th>
                <th>Bedeutung</th>
                <th>Aktion</th>
            </tr>
            <tr>
                <td>Neu</td>
                <td>Lead gerade eingegangen</td>
                <td>Schnellstmöglich kontaktieren</td>
            </tr>
            <tr>
                <td>Kontaktiert</td>
                <td>Erstkontakt hergestellt</td>
                <td>Follow-up planen</td>
            </tr>
            <tr>
                <td>Qualifiziert</td>
                <td>Echter Interessent</td>
                <td>Angebot erstellen</td>
            </tr>
            <tr>
                <td>Angebot gesendet</td>
                <td>Angebot liegt vor</td>
                <td>Auf Rückmeldung warten</td>
            </tr>
            <tr>
                <td>Gewonnen</td>
                <td>Kunde gewonnen</td>
                <td>In CRM übernehmen</td>
            </tr>
            <tr>
                <td>Verloren</td>
                <td>Kein Geschäft</td>
                <td>Grund dokumentieren</td>
            </tr>
        </table>

        <h3>11.3.3 Lead bearbeiten</h3>
        <ol>
            <li>Öffnen Sie einen Lead</li>
            <li>Aktualisieren Sie:
                <ul>
                    <li>Status</li>
                    <li>Verantwortlicher Mitarbeiter</li>
                    <li>Notizen</li>
                    <li>Nächste Schritte</li>
                    <li>Follow-up Datum</li>
                </ul>
            </li>
            <li>Speichern Sie die Änderungen</li>
        </ol>

        <h2>11.4 Lead-Qualifizierung</h2>
        
        <h3>11.4.1 Automatische Bewertung</h3>
        <p>
            Das System bewertet Leads automatisch nach:
        </p>
        <ul>
            <li><strong>Engagement:</strong> Wie viele Geräte angesehen?</li>
            <li><strong>Verweildauer:</strong> Wie lange auf der Seite?</li>
            <li><strong>Downloads:</strong> Dokumente heruntergeladen?</li>
            <li><strong>Kontaktqualität:</strong> Vollständige Angaben?</li>
            <li><strong>Firmengröße:</strong> Potenzielle Auftragsgröße</li>
        </ul>

        <h3>11.4.2 Lead-Score</h3>
        <p>
            Leads erhalten einen Score von 0-100:
        </p>
        <ul>
            <li><strong>80-100 Punkte:</strong> 🔥 Hot Lead - Sofort kontaktieren</li>
            <li><strong>60-79 Punkte:</strong> 🟡 Warm Lead - Innerhalb 24h kontaktieren</li>
            <li><strong>40-59 Punkte:</strong> 🔵 Cold Lead - Follow-up in 3-5 Tagen</li>
            <li><strong>0-39 Punkte:</strong> ⚫ Niedriges Interesse</li>
        </ul>

        <h2>11.5 Lead-Export und CRM-Integration</h2>
        
        <h3>11.5.1 Leads exportieren</h3>
        <ol>
            <li>Wählen Sie Leads aus (oder alle)</li>
            <li>Klicken Sie auf <strong>"Exportieren"</strong></li>
            <li>Wählen Sie Format:
                <ul>
                    <li>Excel (.xlsx)</li>
                    <li>CSV</li>
                    <li>vCard</li>
                </ul>
            </li>
            <li>Importieren Sie in Ihr CRM-System</li>
        </ol>

        <h3>11.5.2 E-Mail-Benachrichtigungen</h3>
        <p>
            Konfigurieren Sie automatische Benachrichtigungen:
        </p>
        <ul>
            <li>Sofortbenachrichtigung bei neuem Lead</li>
            <li>Tägliche Zusammenfassung neuer Leads</li>
            <li>Erinnerung bei ausstehenden Follow-ups</li>
            <li>Eskalation bei ungekontaktierten Leads</li>
        </ul>

        <h2>11.6 Lead-Berichte</h2>
        
        <h3>11.6.1 Verfügbare Auswertungen</h3>
        <ul>
            <li>Leads pro Messe/Event</li>
            <li>Conversion-Rate nach Quelle</li>
            <li>Beliebteste Geräte bei Leads</li>
            <li>Durchschnittliche Bearbeitungszeit</li>
            <li>Gewonnene vs. verlorene Leads</li>
            <li>ROI pro Messe</li>
        </ul>

        <div class="info-box success">
            <h4>✅ Best Practices Lead-Management</h4>
            <ul>
                <li>Kontaktieren Sie Hot Leads innerhalb 1 Stunde</li>
                <li>Dokumentieren Sie alle Kontakte</li>
                <li>Setzen Sie klare Follow-up Termine</li>
                <li>Fragen Sie nach dem Budget</li>
                <li>Erfassen Sie die Entscheidungsträger</li>
                <li>Notieren Sie Wettbewerber</li>
            </ul>
        </div>

        <div class="page-number">Seite 11</div>

        <!-- ==================== KAPITEL 12: ÖFFENTLICHE ANSICHT ==================== -->
        <h1 id="kapitel-12">12. Öffentliche Ansicht und Sharing</h1>

        <h2>12.1 Öffentliche Ansicht Übersicht</h2>
        <p>
            Teilen Sie einzelne Marker oder ganze Kataloge mit externen Personen, 
            ohne dass diese einen Login benötigen.
        </p>

        <h2>12.2 Marker öffentlich teilen</h2>
        
        <h3>12.2.1 Öffentlichen Link erstellen</h3>
        <ol>
            <li>Öffnen Sie einen Marker</li>
            <li>Klicken Sie auf <strong>"Teilen"</strong></li>
            <li>Aktivieren Sie <strong>"Öffentliche Ansicht"</strong></li>
            <li>Konfigurieren Sie Optionen:
                <ul>
                    <li><strong>Passwortschutz:</strong> Optional Passwort vergeben</li>
                    <li><strong>Ablaufdatum:</strong> Link automatisch deaktivieren</li>
                    <li><strong>Ansichtsrechte:</strong> Was darf gesehen werden?</li>
                    <li><strong>Download erlauben:</strong> Dokumente downloadbar?</li>
                    <li><strong>Kontaktformular:</strong> Anfragen ermöglichen?</li>
                </ul>
            </li>
            <li>Kopieren Sie den generierten Link</li>
        </ol>

        <h3>12.2.2 Sichtbare Informationen</h3>
        <p>
            Wählen Sie, welche Informationen öffentlich sichtbar sind:
        </p>
        <ul>
            <li>☑️ Name und Beschreibung</li>
            <li>☑️ Bilder und 3D-Modelle</li>
            <li>☑️ Technische Daten</li>
            <li>☑️ Dokumente</li>
            <li>☐ GPS-Position</li>
            <li>☐ Wartungshistorie</li>
            <li>☐ Seriennummer</li>
            <li>☐ Preisinformationen</li>
        </ul>

        <h2>12.3 Öffentliche Ansicht für Kunden</h2>
        
        <h3>12.3.1 Anwendungsfälle</h3>
        <ul>
            <li><strong>Vermietungsangebote:</strong> Teilen Sie Verfügbarkeit mit Kunden</li>
            <li><strong>Verkaufspräsentation:</strong> Zeigen Sie Geräte potenziellen Käufern</li>
            <li><strong>Kundendokumentation:</strong> Geben Sie Zugriff auf Handbücher</li>
            <li><strong>Projektpartner:</strong> Teilen Sie Geräteinformationen</li>
            <li><strong>Versicherung:</strong> Dokumentation für Versicherungsfälle</li>
        </ul>

        <h3>12.3.2 Kundenfreundliche Features</h3>
        <p>
            Die öffentliche Ansicht bietet:
        </p>
        <ul>
            <li>Responsive Design für alle Geräte</li>
            <li>Interaktive 3D-Modelle</li>
            <li>Bildergalerien mit Zoom</li>
            <li>Dokumenten-Download</li>
            <li>Kontaktformular</li>
            <li>Mehrsprachigkeit (optional)</li>
        </ul>

        <h2>12.4 Öffentliche Links verwalten</h2>
        
        <h3>12.4.1 Link-Übersicht</h3>
        <p>
            Unter <strong>"Öffentliche Links"</strong> sehen Sie alle aktiven Freigaben:
        </p>
        <ul>
            <li>Welche Marker sind öffentlich?</li>
            <li>Wann wurden Links erstellt?</li>
            <li>Wie oft wurden sie aufgerufen?</li>
            <li>Wann laufen sie ab?</li>
        </ul>

        <h3>12.4.2 Links widerrufen</h3>
        <ol>
            <li>Öffnen Sie die Link-Übersicht</li>
            <li>Wählen Sie einen Link</li>
            <li>Klicken Sie auf <strong>"Deaktivieren"</strong></li>
            <li>Der Link ist sofort ungültig</li>
        </ol>

        <h2>12.5 Tracking und Statistiken</h2>
        
        <h3>12.5.1 Aufruf-Statistiken</h3>
        <p>
            Für jeden öffentlichen Link sehen Sie:
        </p>
        <ul>
            <li>Anzahl Aufrufe gesamt</li>
            <li>Eindeutige Besucher</li>
            <li>Durchschnittliche Verweildauer</li>
            <li>Download-Statistiken</li>
            <li>Geografische Herkunft (Land)</li>
            <li>Gerätetypen (Desktop, Mobile)</li>
        </ul>

        <h3>12.5.2 Kontaktanfragen</h3>
        <p>
            Kontaktanfragen über öffentliche Links werden als Leads erfasst und enthalten:
        </p>
        <ul>
            <li>Kontaktdaten des Anfragenden</li>
            <li>Welcher Marker interessiert</li>
            <li>Freitext-Nachricht</li>
            <li>Zeitstempel der Anfrage</li>
        </ul>

        <div class="info-box warning">
            <h4>⚠️ Sicherheitshinweise</h4>
            <ul>
                <li>Teilen Sie keine sensiblen internen Informationen</li>
                <li>Nutzen Sie Passwortschutz für vertrauliche Daten</li>
                <li>Setzen Sie Ablaufdaten für temporäre Freigaben</li>
                <li>Überprüfen Sie regelmäßig aktive Links</li>
                <li>Deaktivieren Sie ungenutzte Links</li>
            </ul>
        </div>

        <div class="page-number">Seite 12</div>

        <!-- ==================== KAPITEL 13: INAKTIVE MARKER ==================== -->
        <h1 id="kapitel-13">13. Inaktive Marker-Verwaltung</h1>

        <h2>13.1 Was sind inaktive Marker?</h2>
        <p>
            Inaktive Marker sind Geräte, die vorübergehend oder dauerhaft nicht im System 
            aktiv sind, aber nicht gelöscht werden sollen. Dies ist nützlich für:
        </p>
        <ul>
            <li>Ausgemusterte Geräte (Archivierung)</li>
            <li>Saisonal genutzte Geräte</li>
            <li>Geräte in Langzeitreparatur</li>
            <li>Verkaufte Geräte (Historische Referenz)</li>
            <li>Testgeräte</li>
        </ul>

        <h2>13.2 Marker deaktivieren</h2>
        
        <h3>13.2.1 Einzelnen Marker deaktivieren</h3>
        <ol>
            <li>Öffnen Sie den Marker</li>
            <li>Klicken Sie auf <strong>"Deaktivieren"</strong></li>
            <li>Geben Sie einen Grund an:
                <ul>
                    <li>Verkauft</li>
                    <li>Ausgemustert</li>
                    <li>Saisonal nicht genutzt</li>
                    <li>In Reparatur</li>
                    <li>Sonstiges (Freitext)</li>
                </ul>
            </li>
            <li>Optional: Datum der Deaktivierung</li>
            <li>Bestätigen Sie die Deaktivierung</li>
        </ol>

        <h3>13.2.2 Massendeaktivierung</h3>
        <p>
            Deaktivieren Sie mehrere Marker gleichzeitig:
        </p>
        <ol>
            <li>Wählen Sie Marker in der Liste</li>
            <li>Klicken Sie auf <strong>"Massenbearbeitung"</strong></li>
            <li>Wählen Sie <strong>"Deaktivieren"</strong></li>
            <li>Geben Sie einen gemeinsamen Grund an</li>
            <li>Bestätigen Sie</li>
        </ol>

        <h2>13.3 Inaktive Marker verwalten</h2>
        
        <h3>13.3.1 Inaktive-Marker-Ansicht</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"Marker → Inaktive"</strong></li>
            <li>Sehen Sie alle deaktivierten Marker</li>
            <li>Filtern Sie nach:
                <ul>
                    <li>Deaktivierungsgrund</li>
                    <li>Datum der Deaktivierung</li>
                    <li>Kategorie</li>
                    <li>Letzter Status</li>
                </ul>
            </li>
        </ol>

        <h3>13.3.2 Marker reaktivieren</h3>
        <ol>
            <li>Öffnen Sie einen inaktiven Marker</li>
            <li>Klicken Sie auf <strong>"Reaktivieren"</strong></li>
            <li>Bestätigen Sie die Reaktivierung</li>
            <li>Der Marker ist sofort wieder aktiv</li>
            <li>Alle Daten bleiben erhalten</li>
        </ol>

        <h2>13.4 Unterschied: Inaktiv vs. Papierkorb</h2>
        
        <table>
            <tr>
                <th>Merkmal</th>
                <th>Inaktiv</th>
                <th>Papierkorb</th>
            </tr>
            <tr>
                <td>Zweck</td>
                <td>Vorübergehende Deaktivierung</td>
                <td>Vorbereitung zur Löschung</td>
            </tr>
            <tr>
                <td>Daten</td>
                <td>Vollständig erhalten</td>
                <td>Vollständig erhalten (temporär)</td>
            </tr>
            <tr>
                <td>Sichtbarkeit</td>
                <td>In Inaktiv-Ansicht</td>
                <td>In Papierkorb-Ansicht</td>
            </tr>
            <tr>
                <td>Wiederherstellung</td>
                <td>Jederzeit reaktivierbar</td>
                <td>30 Tage wiederherstellbar</td>
            </tr>
            <tr>
                <td>Dashboard</td>
                <td>Nicht in Statistiken</td>
                <td>Nicht in Statistiken</td>
            </tr>
            <tr>
                <td>GPS-Tracking</td>
                <td>Gestoppt</td>
                <td>Gestoppt</td>
            </tr>
            <tr>
                <td>Historie</td>
                <td>Einsehbar</td>
                <td>Einsehbar</td>
            </tr>
        </table>

        <div class="info-box">
            <h4>💡 Wann was verwenden?</h4>
            <ul>
                <li><strong>Inaktiv setzen:</strong> Wenn Sie das Gerät eventuell wieder benötigen 
                    oder aus historischen Gründen behalten möchten</li>
                <li><strong>In Papierkorb:</strong> Wenn Sie das Gerät definitiv nicht mehr 
                    benötigen und es manuell endgültig löschen möchten</li>
            </ul>
        </div>

        <h2>13.5 Inaktive Marker Berichte</h2>
        
        <h3>13.5.1 Auswertungen</h3>
        <p>
            Erstellen Sie Berichte über inaktive Marker:
        </p>
        <ul>
            <li>Anzahl inaktiver Marker pro Kategorie</li>
            <li>Deaktivierungsgründe (Verteilung)</li>
            <li>Durchschnittliche Inaktivitätsdauer</li>
            <li>Wert der inaktiven Geräte</li>
            <li>Reaktivierungsrate</li>
        </ul>

        <h3>13.5.2 Automatische Deaktivierung</h3>
        <p>
            Konfigurieren Sie Regeln für automatische Deaktivierung:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"Einstellungen → Automatisierung"</strong></li>
            <li>Erstellen Sie eine Regel:
                <ul>
                    <li>Marker ohne GPS-Update seit X Tagen</li>
                    <li>Marker mit Status "Außer Betrieb" seit X Tagen</li>
                    <li>Marker ohne Wartung seit X Monaten</li>
                </ul>
            </li>
            <li>Wählen Sie Aktion: <strong>"Als inaktiv markieren"</strong></li>
            <li>Optional: E-Mail-Benachrichtigung</li>
        </ol>

        <h2>13.6 Saisonale Aktivierung/Deaktivierung</h2>
        
        <h3>13.6.1 Zeitgesteuerte Deaktivierung</h3>
        <p>
            Für saisonal genutzte Geräte (z.B. Winterdienst, Sommerbedarf):
        </p>
        <ol>
            <li>Erstellen Sie eine Gerätegruppe (z.B. "Wintergeräte")</li>
            <li>Planen Sie automatische Deaktivierung:
                <ul>
                    <li>Datum: 31. März jeden Jahres</li>
                    <li>Aktion: Gruppe deaktivieren</li>
                </ul>
            </li>
            <li>Planen Sie automatische Reaktivierung:
                <ul>
                    <li>Datum: 1. November jeden Jahres</li>
                    <li>Aktion: Gruppe reaktivieren</li>
                </ul>
            </li>
        </ol>

        <div class="page-number">Seite 13</div>
        <!-- ==================== KAPITEL 9: BERICHTE ==================== -->
        <h1 id="kapitel-14">9. Berichte und Exports</h1>

        <h2>15.1 Berichts-System Übersicht</h2>
        <p>
            Das Berichtssystem bietet umfangreiche Analyse- und Exportfunktionen für alle 
            Systemdaten. Erstellen Sie standardisierte oder individuelle Berichte für 
            verschiedene Zwecke.
        </p>

        <h2>15.2 Standard-Berichte</h2>
        
        <h3>15.2.1 Verfügbare Standard-Berichte</h3>
        
        <table>
            <tr>
                <th>Bericht</th>
                <th>Inhalt</th>
            </tr>
            <tr>
                <td>Geräteübersicht</td>
                <td>Alle Geräte mit Status, Standort, Wartungsstand</td>
            </tr>
            <tr>
                <td>Wartungsbericht</td>
                <td>Durchgeführte und anstehende Wartungen</td>
            </tr>
            <tr>
                <td>Vermietungsstatistik</td>
                <td>Auslastung, Einnahmen, Trends</td>
            </tr>
            <tr>
                <td>GPS-Verlauf</td>
                <td>Bewegungsprofile einzelner Geräte</td>
            </tr>
            <tr>
                <td>Kostenbericht</td>
                <td>Wartungskosten, Reparaturen, Anschaffungen</td>
            </tr>
            <tr>
                <td>Nutzungsstatistik</td>
                <td>Benutzeraktivitäten, Zugriffe</td>
            </tr>
            <tr>
                <td>Compliance-Bericht</td>
                <td>Zertifikate, Prüfungen, Fristen</td>
            </tr>
        </table>

        <h3>15.2.2 Bericht erstellen</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"Berichte"</strong></li>
            <li>Wählen Sie einen Berichtstyp</li>
            <li>Definieren Sie den Zeitraum</li>
            <li>Wählen Sie Filter (optional):
                <ul>
                    <li>Kategorien</li>
                    <li>Standorte</li>
                    <li>Verantwortliche</li>
                    <li>Status</li>
                </ul>
            </li>
            <li>Klicken Sie auf <strong>"Bericht generieren"</strong></li>
        </ol>

        <h2>15.3 Export-Formate</h2>
        
        <h3>15.3.1 PDF-Export</h3>
        <p>
            Professionelle Berichte im PDF-Format:
        </p>
        <ul>
            <li>Corporate Design mit eigenem Logo</li>
            <li>Seitennummerierung</li>
            <li>Inhaltsverzeichnis</li>
            <li>Diagramme und Grafiken</li>
            <li>Unterschriftenfeld</li>
        </ul>

        <h3>15.3.2 Excel-Export</h3>
        <p>
            Rohdaten zur weiteren Bearbeitung:
        </p>
        <ul>
            <li>Alle Daten in Tabellenform</li>
            <li>Formatierte Tabellen mit Formeln</li>
            <li>Mehrere Arbeitsblätter für komplexe Berichte</li>
            <li>Pivot-Tabellen vorbereitet</li>
        </ul>

        <h3>15.3.3 CSV-Export</h3>
        <p>
            Einfache Textdateien für Import in andere Systeme:
        </p>
        <ul>
            <li>Kompatibel mit allen Tabellenkalkulationen</li>
            <li>Ideal für Datenaustausch</li>
            <li>UTF-8 Encoding für Sonderzeichen</li>
        </ul>

        <h2>15.4 Benutzerdefinierte Berichte</h2>
        
        <h3>15.4.1 Eigenen Bericht erstellen</h3>
        <ol>
            <li>Klicken Sie auf <strong>"Neuer Bericht"</strong></li>
            <li>Geben Sie einen Namen ein</li>
            <li>Wählen Sie Datenquellen:
                <ul>
                    <li>Marker</li>
                    <li>Wartungen</li>
                    <li>Dokumente</li>
                    <li>GPS-Daten</li>
                    <li>Benutzeraktivitäten</li>
                </ul>
            </li>
            <li>Definieren Sie Spalten und Sortierung</li>
            <li>Fügen Sie Berechnungen hinzu (Summen, Durchschnitte)</li>
            <li>Wählen Sie Visualisierungen (Diagramme)</li>
            <li>Speichern Sie die Berichtsvorlage</li>
        </ol>

        <h3>15.4.2 Berechnete Felder</h3>
        <p>
            Fügen Sie berechnete Werte zu Berichten hinzu:
        </p>
        <ul>
            <li>Summen und Durchschnitte</li>
            <li>Prozentuale Anteile</li>
            <li>Differenzen und Trends</li>
            <li>Benutzerdefinierte Formeln</li>
        </ul>

        <h2>15.5 Automatische Berichte</h2>
        <p>
            Planen Sie regelmäßige Berichterstellung:
        </p>

        <h3>15.5.1 Berichtsplan erstellen</h3>
        <ol>
            <li>Öffnen Sie einen gespeicherten Bericht</li>
            <li>Klicken Sie auf <strong>"Automatisierung"</strong></li>
            <li>Wählen Sie Intervall:
                <ul>
                    <li>Täglich</li>
                    <li>Wöchentlich (mit Wochentag)</li>
                    <li>Monatlich (mit Tag im Monat)</li>
                    <li>Quartalsweise</li>
                    <li>Jährlich</li>
                </ul>
            </li>
            <li>Definieren Sie Empfänger (E-Mail-Adressen)</li>
            <li>Wählen Sie Format (PDF, Excel, CSV)</li>
            <li>Aktivieren Sie den Berichtsplan</li>
        </ol>

        <h3>15.5.2 Vorteile automatischer Berichte</h3>
        <div class="info-box success">
            <h4>✅ Automatische Berichte nutzen</h4>
            <ul>
                <li>Zeit sparen durch Automatisierung</li>
                <li>Regelmäßige Information der Geschäftsleitung</li>
                <li>Keine Berichte vergessen</li>
                <li>Konsistente Dokumentation</li>
                <li>Compliance-Anforderungen erfüllen</li>
            </ul>
        </div>

        <h2>15.6 Dashboard-Widgets</h2>
        <p>
            Fügen Sie Berichts-Widgets zu Ihrem Dashboard hinzu:
        </p>
        <ol>
            <li>Klicken Sie auf <strong>"Dashboard anpassen"</strong></li>
            <li>Wählen Sie <strong>"Widget hinzufügen"</strong></li>
            <li>Wählen Sie einen Bericht oder eine Statistik</li>
            <li>Konfigurieren Sie die Anzeige:
                <ul>
                    <li>Diagrammtyp (Balken, Linie, Kuchen)</li>
                    <li>Zeitraum</li>
                    <li>Größe</li>
                </ul>
            </li>
            <li>Positionieren Sie das Widget</li>
        </ol>

        <h2>15.7 Datenexport</h2>
        
        <h3>15.7.1 Vollständiger Datenexport</h3>
        <p>
            Exportieren Sie alle Systemdaten auf einmal:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"Einstellungen → Datenexport"</strong></li>
            <li>Wählen Sie zu exportierende Bereiche:
                <ul>
                    <li>Alle Marker</li>
                    <li>Wartungshistorie</li>
                    <li>Dokumente</li>
                    <li>GPS-Tracks</li>
                    <li>Benutzer und Rollen</li>
                </ul>
            </li>
            <li>Wählen Sie Format (ZIP-Archiv mit Excel/CSV)</li>
            <li>Klicken Sie auf <strong>"Export starten"</strong></li>
            <li>Download-Link wird per E-Mail zugesendet</li>
        </ol>

        <div class="info-box warning">
            <h4>⚠️ Datenschutz beachten</h4>
            <p>
                Exportierte Daten können sensible Informationen enthalten. Behandeln Sie 
                Exports vertraulich und löschen Sie nicht benötigte Dateien sicher.
            </p>
        </div>

        <div class="page-number">Seite 14</div>

        <!-- ==================== KAPITEL 10: KALENDER ==================== -->
        <h1 id="kapitel-15">10. Kalender und Termine</h1>

        <h2>16.1 Kalender-Übersicht</h2>
        <p>
            Der integrierte Kalender zeigt alle wichtigen Termine und Fristen auf einen Blick:
        </p>
        <ul>
            <li>Wartungstermine</li>
            <li>Vermietungen (Übergabe und Rückgabe)</li>
            <li>Ablaufende Dokumente</li>
            <li>Inspektionstermine</li>
            <li>Benutzerdefinierte Termine</li>
        </ul>

        <h2>16.2 Kalenderansichten</h2>
        
        <h3>16.2.1 Monatsansicht</h3>
        <p>
            Zeigt alle Termine des aktuellen Monats in übersichtlicher Form.
        </p>

        <h3>16.2.2 Wochenansicht</h3>
        <p>
            Detaillierte Ansicht einer einzelnen Woche mit Zeitslots.
        </p>

        <h3>16.2.3 Tagesansicht</h3>
        <p>
            Stundengenauer Tagesplan mit allen Terminen.
        </p>

        <h3>16.2.4 Listenansicht</h3>
        <p>
            Chronologische Liste aller anstehenden Termine.
        </p>

        <h2>16.3 Termine erstellen</h2>
        
        <h3>16.3.1 Manuellen Termin anlegen</h3>
        <ol>
            <li>Öffnen Sie den Kalender</li>
            <li>Klicken Sie auf das gewünschte Datum</li>
            <li>Füllen Sie das Formular aus:
                <ul>
                    <li>Titel</li>
                    <li>Beschreibung</li>
                    <li>Datum und Uhrzeit</li>
                    <li>Dauer</li>
                    <li>Kategorie</li>
                    <li>Zugewiesene Geräte</li>
                    <li>Teilnehmer</li>
                </ul>
            </li>
            <li>Speichern Sie den Termin</li>
        </ol>

        <h3>16.3.2 Automatische Termine</h3>
        <p>
            Das System erstellt automatisch Kalendereinträge für:
        </p>
        <ul>
            <li>Geplante Wartungen (aus Wartungsplänen)</li>
            <li>Vermietungsperioden</li>
            <li>Ablaufende Zertifikate (30 Tage vor Ablauf)</li>
            <li>Erinnerungen für Inspektionen</li>
        </ul>

        <h2>16.4 Outlook-Integration</h2>
        <p>
            Synchronisieren Sie den Systemkalender mit Microsoft Outlook:
        </p>

        <h3>16.4.1 Outlook verbinden</h3>
        <ol>
            <li>Navigieren Sie zu <strong>"Einstellungen → Kalender"</strong></li>
            <li>Klicken Sie auf <strong>"Outlook verbinden"</strong></li>
            <li>Melden Sie sich mit Ihrem Microsoft-Konto an</li>
            <li>Erteilen Sie die erforderlichen Berechtigungen</li>
            <li>Wählen Sie den Synchronisationsmodus:
                <ul>
                    <li>Nur lesen (System → Outlook)</li>
                    <li>Bidirektional (beide Richtungen)</li>
                </ul>
            </li>
        </ol>

        <h3>16.4.2 Synchronisationseinstellungen</h3>
        <p>
            Konfigurieren Sie, was synchronisiert werden soll:
        </p>
        <ul>
            <li>Alle Termine</li>
            <li>Nur Wartungstermine</li>
            <li>Nur eigene Termine</li>
            <li>Termine mit bestimmten Kategorien</li>
        </ul>

        <h2>16.5 iCal-Feed</h2>
        <p>
            Nutzen Sie den iCal-Feed für andere Kalender-Apps:
        </p>
        <ol>
            <li>Öffnen Sie <strong>"Einstellungen → Kalender"</strong></li>
            <li>Kopieren Sie die iCal-Feed-URL</li>
            <li>Fügen Sie die URL in Ihrer Kalender-App hinzu:
                <ul>
                    <li>Google Kalender</li>
                    <li>Apple Kalender</li>
                    <li>Thunderbird</li>
                    <li>Andere iCal-kompatible Apps</li>
                </ul>
            </li>
        </ol>

        <h2>16.6 Erinnerungen</h2>
        
        <h3>16.6.1 Erinnerungen konfigurieren</h3>
        <p>
            Für jeden Termin können Sie Erinnerungen einrichten:
        </p>
        <ul>
            <li>5, 15, 30 Minuten vorher</li>
            <li>1, 2, 24 Stunden vorher</li>
            <li>1, 3, 7 Tage vorher</li>
            <li>Benutzerdefinierter Zeitraum</li>
        </ul>

        <h3>16.6.2 Erinnerungskanäle</h3>
        <ul>
            <li><strong>E-Mail:</strong> Erinnerung per E-Mail</li>
            
            <li><strong>Push:</strong> Mobile Benachrichtigung</li>
            <li><strong>Browser:</strong> Desktop-Benachrichtigung</li>
        </ul>

        <h2>16.7 Termin-Kategorien</h2>
        
        <table>
            <tr>
                <th>Kategorie</th>
                <th>Farbe</th>
                <th>Verwendung</th>
            </tr>
            <tr>
                <td>Wartung</td>
                <td>🟠 Orange</td>
                <td>Geplante Wartungen</td>
            </tr>
            <tr>
                <td>Inspektion</td>
                <td>🟡 Gelb</td>
                <td>Inspektionstermine</td>
            </tr>
            <tr>
                <td>Vermietung</td>
                <td>🟢 Grün</td>
                <td>Übergabe/Rückgabe</td>
            </tr>
            <tr>
                <td>Frist</td>
                <td>🔴 Rot</td>
                <td>Ablaufende Dokumente</td>
            </tr>
            <tr>
                <td>Meeting</td>
                <td>🔵 Blau</td>
                <td>Besprechungen</td>
            </tr>
            <tr>
                <td>Sonstiges</td>
                <td>⚫ Grau</td>
                <td>Andere Termine</td>
            </tr>
        </table>

        <div class="page-number">Seite 15</div>

        <!-- ==================== KAPITEL 11: BENUTZEREINSTELLUNGEN ==================== -->
        <h1 id="kapitel-16">11. Benutzereinstellungen</h1>

        <h2>17.1 Profil bearbeiten</h2>
        <p>
            Passen Sie Ihr Benutzerprofil an:
        </p>
        <ol>
            <li>Klicken Sie auf Ihren Namen oben rechts</li>
            <li>Wählen Sie <strong>"Profil"</strong></li>
            <li>Bearbeiten Sie:
                <ul>
                    <li>Vorname und Nachname</li>
                    <li>E-Mail-Adresse</li>
                    <li>Telefonnummer</li>
                    <li>Abteilung/Position</li>
                    <li>Profilbild</li>
                    <li>Sprache</li>
                </ul>
            </li>
            <li>Speichern Sie die Änderungen</li>
        </ol>

        <h2>17.2 Passwort ändern</h2>
        <ol>
            <li>Navigieren Sie zu <strong>"Profil → Sicherheit"</strong></li>
            <li>Klicken Sie auf <strong>"Passwort ändern"</strong></li>
            <li>Geben Sie Ihr aktuelles Passwort ein</li>
            <li>Geben Sie zweimal das neue Passwort ein</li>
            <li>Klicken Sie auf <strong>"Aktualisieren"</strong></li>
        </ol>

        <h2>17.3 Benachrichtigungseinstellungen</h2>
        <p>
            Steuern Sie, welche Benachrichtigungen Sie erhalten:
        </p>

        <h3>17.3.1 E-Mail-Benachrichtigungen</h3>
        <ul>
            <li>☑️ Wartungserinnerungen</li>
            <li>☑️ Ablaufende Dokumente</li>
            <li>☑️ System-Updates</li>
            <li>☐ Tägliche Zusammenfassung</li>
            <li>☐ Wöchentlicher Bericht</li>
        </ul>

        <h3>17.3.2 Push-Benachrichtigungen</h3>
        <ul>
            <li>☑️ Dringende Wartungen</li>
            <li>☑️ Kritische Alarme</li>
            <li>☐ Neue Aufgaben</li>
            <li>☐ Statusänderungen</li>
        </ul>

        <h2>17.4 Anzeigeeinstellungen</h2>
        
        <h3>17.4.1 Sprache</h3>
        <p>
            Verfügbare Sprachen:
        </p>
        <ul>
            <li>Deutsch</li>
            <li>Englisch</li>
            <li>Französisch</li>
            <li>Spanisch</li>
        </ul>

        <h3>17.4.2 Zeitzone</h3>
        <p>
            Wählen Sie Ihre lokale Zeitzone für korrekte Zeitangaben.
        </p>

        <h3>17.4.3 Datumsformat</h3>
        <ul>
            <li>DD.MM.YYYY (Europa)</li>
            <li>MM/DD/YYYY (USA)</li>
            <li>YYYY-MM-DD (ISO)</li>
        </ul>

        <h3>17.4.4 Maßeinheiten</h3>
        <ul>
            <li>Metrisch (km, m, kg)</li>
            <li>Imperial (mi, ft, lb)</li>
        </ul>

        <h2>17.5 Dashboard-Anpassung</h2>
        <p>
            Personalisieren Sie Ihr Dashboard:
        </p>
        <ol>
            <li>Klicken Sie auf <strong>"Dashboard anpassen"</strong></li>
            <li>Verschieben Sie Widgets per Drag & Drop</li>
            <li>Ändern Sie Widget-Größen</li>
            <li>Fügen Sie neue Widgets hinzu oder entfernen Sie welche</li>
            <li>Speichern Sie das Layout</li>
        </ol>

        <h2>17.6 Digitale Unterschrift</h2>
        <p>
            Hinterlegen Sie Ihre digitale Unterschrift für Wartungsberichte:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"Profil → Unterschrift"</strong></li>
            <li>Wählen Sie eine Methode:
                <ul>
                    <li>Zeichnen mit Maus/Touchscreen</li>
                    <li>Bild hochladen</li>
                    <li>Text-Signatur (Name in Schreibschrift)</li>
                </ul>
            </li>
            <li>Speichern Sie die Unterschrift</li>
            <li>Die Unterschrift wird automatisch bei Wartungen verwendet</li>
        </ol>

        <h2>17.7 API-Zugriff (für Entwickler)</h2>
        <p>
            Für Entwickler: Generieren Sie API-Keys für programmischen Zugriff:
        </p>
        <ol>
            <li>Navigieren Sie zu <strong>"Profil → API"</strong></li>
            <li>Klicken Sie auf <strong>"Neuer API-Key"</strong></li>
            <li>Geben Sie einen Namen ein (z.B. "Mobile App")</li>
            <li>Wählen Sie Berechtigungen</li>
            <li>Kopieren Sie den generierten Key (nur einmal sichtbar!)</li>
        </ol>

        <div class="info-box warning">
            <h4>⚠️ API-Key Sicherheit</h4>
            <p>
                Behandeln Sie API-Keys wie Passwörter. Teilen Sie sie niemals öffentlich 
                und rotieren Sie sie regelmäßig. Löschen Sie nicht mehr benötigte Keys.
            </p>
        </div>

        <div class="page-number">Seite 16</div>

        <?php if ($showAdminChapter): ?>
        <!-- ==================== KAPITEL 12: ADMIN-BEREICH ==================== -->
        <div class="admin-chapter">
            <h1 id="kapitel-17">12. Administrator-Bereich <span class="admin-badge">Admin-Kapitel</span></h1>
            
            <div class="info-box danger">
                <h4>🔐 Nur für Administratoren</h4>
                <p>
                    Dieses Kapitel enthält sensible Informationen und Funktionen, die nur für 
                    Systemadministratoren bestimmt sind. Änderungen in diesem Bereich können 
                    erhebliche Auswirkungen auf das gesamte System haben.
                </p>
            </div>

            <h2>17.1 Systemeinstellungen</h2>
            <p>
                Zentrale Konfiguration des gesamten Systems:
            </p>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Systemeinstellungen"</strong></li>
                <li>Konfigurieren Sie:
                    <ul>
                        <li>Firmenname und Logo</li>
                        <li>Systemsprache</li>
                        <li>Zeitzone</li>
                        <li>E-Mail-Server (SMTP)</li>
                        
                        <li>Backup-Einstellungen</li>
                    </ul>
                </li>
            </ol>

            <h2>17.2 Benutzerverwaltung</h2>
            
            <h3>17.2.1 Neuen Benutzer anlegen</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Benutzer"</strong></li>
                <li>Klicken Sie auf <strong>"Neuer Benutzer"</strong></li>
                <li>Füllen Sie das Formular aus:
                    <ul>
                        <li>Benutzername (eindeutig)</li>
                        <li>E-Mail-Adresse</li>
                        <li>Vorname, Nachname</li>
                        <li>Abteilung/Position</li>
                        <li>Telefonnummer</li>
                    </ul>
                </li>
                <li>Wählen Sie eine <strong>Rolle</strong></li>
                <li>Setzen Sie ein temporäres Passwort</li>
                <li>Optional: Senden Sie Willkommens-E-Mail</li>
                <li>Speichern Sie den Benutzer</li>
            </ol>

            <h3>17.2.2 Benutzer bearbeiten</h3>
            <ul>
                <li>Bearbeiten Sie alle Benutzerinformationen</li>
                <li>Ändern Sie Rollen und Berechtigungen</li>
                <li>Setzen Sie Passwörter zurück</li>
                <li>Aktivieren/Deaktivieren Sie Benutzerkonten</li>
                <li>Löschen Sie Benutzer (nur wenn keine Zuordnungen bestehen)</li>
            </ul>

            <h3>17.2.3 Massenimport</h3>
            <p>
                Importieren Sie mehrere Benutzer gleichzeitig:
            </p>
            <ol>
                <li>Laden Sie die Excel-Vorlage herunter</li>
                <li>Füllen Sie die Vorlage mit Benutzerdaten</li>
                <li>Laden Sie die ausgefüllte Datei hoch</li>
                <li>Überprüfen Sie die Vorschau</li>
                <li>Bestätigen Sie den Import</li>
            </ol>

            <h2>17.3 Rollen und Berechtigungen</h2>
            
            <h3>17.3.1 Standard-Rollen</h3>
            
            <table>
                <tr>
                    <th>Rolle</th>
                    <th>Berechtigungen</th>
                </tr>
                <tr>
                    <td>Administrator</td>
                    <td>Vollzugriff auf alle Funktionen</td>
                </tr>
                <tr>
                    <td>Manager</td>
                    <td>Verwaltung, Berichte, keine Systemeinstellungen</td>
                </tr>
                <tr>
                    <td>Techniker</td>
                    <td>Wartung durchführen, Marker bearbeiten</td>
                </tr>
                <tr>
                    <td>Leser</td>
                    <td>Nur Lesezugriff, keine Änderungen</td>
                </tr>
                <tr>
                    <td>Mobiler Nutzer</td>
                    <td>QR-Scan, GPS-Update, Wartung mobil</td>
                </tr>
            </table>

            <h3>17.3.2 Benutzerdefinierte Rolle erstellen</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Rollen"</strong></li>
                <li>Klicken Sie auf <strong>"Neue Rolle"</strong></li>
                <li>Geben Sie einen Namen ein</li>
                <li>Wählen Sie Berechtigungen:
                    <ul>
                        <li>✅ Marker anzeigen</li>
                        <li>✅ Marker erstellen</li>
                        <li>✅ Marker bearbeiten</li>
                        <li>❌ Marker löschen</li>
                        <li>✅ Wartung durchführen</li>
                        <li>❌ Systemeinstellungen</li>
                        <li>etc.</li>
                    </ul>
                </li>
                <li>Speichern Sie die Rolle</li>
            </ol>

            <h3>17.3.3 Granulare Berechtigungen</h3>
            <p>
                Verfügbare Berechtigungsebenen:
            </p>
            <ul>
                <li><strong>Keine:</strong> Kein Zugriff</li>
                <li><strong>Lesen:</strong> Nur ansehen</li>
                <li><strong>Erstellen:</strong> Neue Einträge anlegen</li>
                <li><strong>Bearbeiten:</strong> Bestehende Einträge ändern</li>
                <li><strong>Löschen:</strong> Einträge entfernen</li>
                <li><strong>Verwalten:</strong> Vollzugriff inkl. Berechtigungen</li>
            </ul>

            <h2>17.4 Kategorien verwalten</h2>
            
            <h3>17.4.1 Neue Kategorie erstellen</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Kategorien"</strong></li>
                <li>Klicken Sie auf <strong>"Neue Kategorie"</strong></li>
                <li>Geben Sie einen Namen ein (z.B. "Baumaschinen")</li>
                <li>Wählen Sie ein Icon</li>
                <li>Definieren Sie eine Farbe</li>
                <li>Erstellen Sie Unterkategorien (optional)</li>
                <li>Speichern Sie die Kategorie</li>
            </ol>

            <h3>17.4.2 Kategorien organisieren</h3>
            <ul>
                <li>Verschieben Sie Kategorien per Drag & Drop</li>
                <li>Erstellen Sie Hierarchien (Haupt- und Unterkategorien)</li>
                <li>Fusionieren Sie ähnliche Kategorien</li>
                <li>Löschen Sie ungenutzte Kategorien</li>
            </ul>

            <h2>17.5 Benutzerdefinierte Felder</h2>
            <p>
                Erweitern Sie Marker um eigene Felder:
            </p>
            
            <h3>17.5.1 Feldtypen</h3>
            <ul>
                <li><strong>Text:</strong> Einzeilige Texteingabe</li>
                <li><strong>Textarea:</strong> Mehrzeiliger Text</li>
                <li><strong>Zahl:</strong> Numerische Werte</li>
                <li><strong>Datum:</strong> Datumsfeld</li>
                <li><strong>Auswahl:</strong> Dropdown-Menü</li>
                <li><strong>Mehrfachauswahl:</strong> Checkboxen</li>
                <li><strong>Ja/Nein:</strong> Boolean-Feld</li>
                <li><strong>Datei:</strong> Datei-Upload</li>
            </ul>

            <h3>17.5.2 Feld hinzufügen</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Benutzerdefinierte Felder"</strong></li>
                <li>Klicken Sie auf <strong>"Neues Feld"</strong></li>
                <li>Konfigurieren Sie:
                    <ul>
                        <li>Feldname</li>
                        <li>Feldtyp</li>
                        <li>Pflichtfeld? (Ja/Nein)</li>
                        <li>Validierungsregeln</li>
                        <li>Standardwert</li>
                        <li>Hilfetext</li>
                    </ul>
                </li>
                <li>Weisen Sie das Feld Kategorien zu</li>
                <li>Speichern Sie das Feld</li>
            </ol>

            <h2>17.6 E-Mail-Vorlagen</h2>
            <p>
                Passen Sie alle System-E-Mails an:
            </p>
            
            <h3>17.6.1 Verfügbare Vorlagen</h3>
            <ul>
                <li>Willkommens-E-Mail für neue Benutzer</li>
                <li>Passwort-Reset</li>
                <li>Wartungserinnerung</li>
                <li>Ablaufendes Dokument</li>
                <li>Automatische Berichte</li>
            </ul>

            <h3>17.6.2 Vorlage bearbeiten</h3>
            <ol>
                <li>Wählen Sie eine Vorlage aus</li>
                <li>Bearbeiten Sie:
                    <ul>
                        <li>Betreff</li>
                        <li>E-Mail-Text (HTML-Editor verfügbar)</li>
                        <li>Platzhalter für dynamische Inhalte</li>
                    </ul>
                </li>
                <li>Testen Sie die Vorlage (Test-E-Mail versenden)</li>
                <li>Speichern Sie die Änderungen</li>
            </ol>

            <h3>17.6.3 Verfügbare Platzhalter</h3>
            <div class="code-block">
{{user_name}}          - Name des Benutzers
{{user_email}}         - E-Mail-Adresse
{{marker_name}}        - Name des Markers
{{maintenance_date}}   - Wartungsdatum
{{document_name}}      - Dokumentenname
{{expiry_date}}        - Ablaufdatum
{{company_name}}       - Firmenname
            </div>

            <h2>17.7 Backup und Wiederherstellung</h2>
            
            <h3>17.7.1 Automatische Backups</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Backup"</strong></li>
                <li>Aktivieren Sie automatische Backups</li>
                <li>Konfigurieren Sie:
                    <ul>
                        <li>Backup-Zeitplan (täglich, wöchentlich)</li>
                        <li>Aufbewahrungsdauer (z.B. 30 Tage)</li>
                        <li>Speicherort (lokal, Cloud)</li>
                        <li>Benachrichtigung bei Fehler</li>
                    </ul>
                </li>
            </ol>

            <h3>17.7.2 Manuelles Backup</h3>
            <ol>
                <li>Klicken Sie auf <strong>"Backup jetzt erstellen"</strong></li>
                <li>Wählen Sie zu sichernde Bereiche:
                    <ul>
                        <li>Datenbank</li>
                        <li>Hochgeladene Dateien</li>
                        <li>Systemkonfiguration</li>
                    </ul>
                </li>
                <li>Warten Sie auf Abschluss</li>
                <li>Laden Sie das Backup herunter</li>
            </ol>

            <h3>17.7.3 Wiederherstellung</h3>
            <div class="info-box danger">
                <h4>⚠️ WICHTIG: Vor Wiederherstellung</h4>
                <ul>
                    <li>Erstellen Sie immer ein aktuelles Backup</li>
                    <li>Informieren Sie alle Benutzer</li>
                    <li>Planen Sie ausreichend Ausfallzeit ein</li>
                    <li>Testen Sie die Wiederherstellung in Testumgebung</li>
                </ul>
            </div>

            <ol>
                <li>Wählen Sie ein Backup aus der Liste</li>
                <li>Klicken Sie auf <strong>"Wiederherstellen"</strong></li>
                <li>Bestätigen Sie die Wiederherstellung</li>
                <li>Warten Sie auf Abschluss (kann einige Minuten dauern)</li>
                <li>System wird automatisch neu gestartet</li>
            </ol>

            <h2>17.8 Aktivitätsprotokolle</h2>
            <p>
                Überwachen Sie alle Systemaktivitäten:
            </p>
            
            <h3>17.8.1 Protokollierte Ereignisse</h3>
            <ul>
                <li>Benutzer-Logins und -Logouts</li>
                <li>Erstellung, Änderung und Löschung von Markern</li>
                <li>Wartungsdurchführungen</li>
                <li>Dokumenten-Uploads und -Downloads</li>
                <li>Systemeinstellungs-Änderungen</li>
                <li>Export von Daten</li>
                <li>API-Zugriffe</li>
                <li>Fehlgeschlagene Login-Versuche</li>
            </ul>

            <h3>17.8.2 Protokolle durchsuchen</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Aktivitätsprotokolle"</strong></li>
                <li>Filtern Sie nach:
                    <ul>
                        <li>Zeitraum</li>
                        <li>Benutzer</li>
                        <li>Ereignistyp</li>
                        <li>Suchbegriff</li>
                    </ul>
                </li>
                <li>Exportieren Sie Protokolle als CSV</li>
            </ol>

            <h2>17.9 Systemleistung</h2>
            
            <h3>17.9.1 Performance-Monitor</h3>
            <p>
                Überwachen Sie die Systemleistung in Echtzeit:
            </p>
            <ul>
                <li>CPU-Auslastung</li>
                <li>Speicherverbrauch</li>
                <li>Datenbankgröße</li>
                <li>Anzahl aktiver Benutzer</li>
                <li>API-Requests pro Minute</li>
                <li>Durchschnittliche Antwortzeit</li>
            </ul>

            <h3>17.9.2 Cache-Verwaltung</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → System-Cache"</strong></li>
                <li>Zeigen Sie Cache-Statistiken an</li>
                <li>Leeren Sie Cache bei Bedarf:
                    <ul>
                        <li>Seiten-Cache</li>
                        <li>Datenbank-Cache</li>
                        <li>Bild-Cache</li>
                        <li>API-Cache</li>
                    </ul>
                </li>
            </ol>

            <div class="info-box">
                <h4>💡 Wann Cache leeren?</h4>
                <ul>
                    <li>Nach System-Updates</li>
                    <li>Bei unerwarteten Anzeigeproblemen</li>
                    <li>Nach Änderungen an Vorlagen</li>
                    <li>Bei Performance-Problemen</li>
                </ul>
            </div>

            <h2>17.10 Systemwartung</h2>
            
            <h3>17.10.1 Wartungsmodus</h3>
            <p>
                Aktivieren Sie den Wartungsmodus für Updates:
            </p>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Wartungsmodus"</strong></li>
                <li>Klicken Sie auf <strong>"Wartungsmodus aktivieren"</strong></li>
                <li>Geben Sie eine Nachricht für Benutzer ein</li>
                <li>Optional: Erlaube Login für Administratoren</li>
                <li>Führen Sie Wartungsarbeiten durch</li>
                <li>Deaktivieren Sie den Wartungsmodus</li>
            </ol>

            <h3>17.10.2 Datenbank-Optimierung</h3>
            <p>
                Regelmäßige Datenbankwartung verbessert die Performance:
            </p>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Datenbank"</strong></li>
                <li>Klicken Sie auf <strong>"Optimieren"</strong></li>
                <li>Warten Sie auf Abschluss</li>
                <li>Überprüfen Sie den Bericht</li>
            </ol>

            <h2>17.11 Lizenz und Updates</h2>
            
            <h3>17.11.1 Lizenzverwaltung</h3>
            <ul>
                <li>Zeigen Sie aktuelle Lizenzinformationen an</li>
                <li>Überprüfen Sie Nutzungsgrenzen</li>
                <li>Erneuern Sie die Lizenz</li>
                <li>Upgraden Sie auf höhere Edition</li>
            </ul>

            <h3>17.11.2 System-Updates</h3>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Updates"</strong></li>
                <li>Prüfen Sie auf verfügbare Updates</li>
                <li>Lesen Sie die Versionshinweise</li>
                <li>Erstellen Sie ein Backup</li>
                <li>Installieren Sie das Update</li>
                <li>Testen Sie das System nach dem Update</li>
            </ol>

            <div class="info-box danger">
                <h4>⚠️ WICHTIG: Vor System-Updates</h4>
                <ol>
                    <li>✅ Vollständiges Backup erstellen</li>
                    <li>✅ Benutzer informieren</li>
                    <li>✅ Wartungsmodus aktivieren</li>
                    <li>✅ Versionshinweise lesen</li>
                    <li>✅ Ausreichend Zeit einplanen</li>
                </ol>
            </div>

            <h2>17.12 Support und Fehlerberichte</h2>
            
            <h3>17.12.1 Fehlerbericht erstellen</h3>
            <p>
                Bei Problemen können Sie einen detaillierten Fehlerbericht erstellen:
            </p>
            <ol>
                <li>Navigieren Sie zu <strong>"Administration → Support"</strong></li>
                <li>Klicken Sie auf <strong>"Fehlerbericht erstellen"</strong></li>
                <li>Der Bericht enthält automatisch:
                    <ul>
                        <li>Systemkonfiguration</li>
                        <li>Fehlerprotokolle</li>
                        <li>Browser-Informationen</li>
                        <li>Aktuelle Performance-Daten</li>
                    </ul>
                </li>
                <li>Fügen Sie eine Problembeschreibung hinzu</li>
                <li>Optional: Fügen Sie Screenshots hinzu</li>
                <li>Senden Sie den Bericht an den Support</li>
            </ol>

            <h3>17.12.2 Debug-Modus</h3>
            <p>
                Für Fehlersuche aktivieren Sie temporär den Debug-Modus:
            </p>
            <div class="info-box warning">
                <h4>⚠️ Achtung</h4>
                <p>
                    Der Debug-Modus zeigt detaillierte Fehlerinformationen an und sollte 
                    NIEMALS im Produktivbetrieb aktiviert bleiben! Deaktivieren Sie ihn 
                    sofort nach der Fehlersuche.
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- ==================== ANHANG ==================== -->
        <h1>Anhang</h1>

        <h2>A. Tastenkombinationen</h2>
        
        <table>
            <tr>
                <th>Tastenkombination</th>
                <th>Funktion</th>
            </tr>
            <tr>
                <td>Strg + K</td>
                <td>Schnellsuche öffnen</td>
            </tr>
            <tr>
                <td>Strg + N</td>
                <td>Neuer Marker</td>
            </tr>
            <tr>
                <td>Strg + S</td>
                <td>Speichern</td>
            </tr>
            <tr>
                <td>Strg + P</td>
                <td>Drucken</td>
            </tr>
            <tr>
                <td>Esc</td>
                <td>Dialog schließen</td>
            </tr>
            <tr>
                <td>F1</td>
                <td>Hilfe öffnen</td>
            </tr>
            <tr>
                <td>Alt + H</td>
                <td>Zum Dashboard</td>
            </tr>
        </table>

        <h2>B. Glossar</h2>
        
        <table>
            <tr>
                <th>Begriff</th>
                <th>Definition</th>
            </tr>
            <tr>
                <td>Asset</td>
                <td>Vermögensgegenstand, Gerät oder Ausrüstung</td>
            </tr>
            <tr>
                <td>GPS</td>
                <td>Global Positioning System - Satellitengestützte Positionsbestimmung</td>
            </tr>
            <tr>
                <td>Marker</td>
                <td>Digitale Repräsentation eines Geräts/Assets im System</td>
            </tr>
            <tr>
                <td>NFC</td>
                <td>Near Field Communication - Kontaktlose Datenübertragung über kurze Distanz</td>
            </tr>
            <tr>
                <td>QR-Code</td>
                <td>Quick Response Code - Zweidimensionaler Barcode</td>
            </tr>
            <tr>
                <td>2FA</td>
                <td>Zwei-Faktor-Authentifizierung - Zweistufiger Login-Prozess</td>
            </tr>
        </table>

        <h2>C. Support-Kontakt</h2>
        <p>
            Bei Fragen oder Problemen wenden Sie sich bitte an:
        </p>
        <div class="info-box">
            <p>
                <strong>E-Mail:</strong> support@bgg-geraete.de<br>
                <strong>Telefon:</strong> +49 (0) 123 456789<br>
                <strong>Support-Zeiten:</strong> Montag bis Freitag, 8:00 - 18:00 Uhr<br>
                <strong>Notfall-Hotline:</strong> +49 (0) 123 456789 (24/7)
            </p>
        </div>

        <h2>D. Datenschutz und Compliance</h2>
        <p>
            Das System entspricht folgenden Standards:
        </p>
        <ul>
            <li>DSGVO-konform</li>
            <li>ISO 27001 (Informationssicherheit)</li>
            <li>SSL/TLS Verschlüsselung</li>
            <li>Regelmäßige Security-Audits</li>
            <li>Datensicherung gemäß Best Practices</li>
        </ul>

        <div class="page-number">Seite 17</div>

        <!-- ==================== ÄNDERUNGSHISTORIE ==================== -->
        <h1>Änderungshistorie</h1>
        
        <table>
            <tr>
                <th>Version</th>
                <th>Datum</th>
                <th>Änderungen</th>
            </tr>
            <tr>
                <td>1.0</td>
                <td><?= date('d.m.Y') ?></td>
                <td>Erstveröffentlichung des Handbuchs</td>
            </tr>
        </table>

        <div style="margin-top: 50px; padding-top: 20px; border-top: 2px solid #ddd; text-align: center; color: #666;">
            <p>© <?= date('Y') ?> BGG Geräte-Verwaltung. Alle Rechte vorbehalten.</p>
            <p style="font-size: 0.9em;">Dieses Handbuch wurde automatisch generiert am <?= date('d.m.Y H:i') ?> Uhr</p>
        </div>

    </div>

    <script>
        // Smooth Scrolling für Anker-Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Verhindere doppeltes Submit beim Checkbox-Change
        let isSubmitting = false;
        document.querySelector('input[type="checkbox"]').addEventListener('change', function() {
            if (!isSubmitting) {
                isSubmitting = true;
                this.form.submit();
            }
        });
    </script>
</body>
</html>