<?php
// PHP-Code zuerst, um Header-Probleme zu vermeiden
require_once 'config.php';
require_once 'functions.php';

// System-Settings laden
$settings = getSystemSettings();
$system_name = $settings['system_name'] ?? 'QR-Code System';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenschutzerklärung - <?= htmlspecialchars($system_name) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .datenschutz-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .datenschutz-container h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .datenschutz-container h2 {
            color: #34495e;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .datenschutz-container h3 {
            color: #7f8c8d;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .datenschutz-container p,
        .datenschutz-container ul {
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
        }

        .datenschutz-container ul {
            padding-left: 25px;
        }

        .datenschutz-container li {
            margin-bottom: 8px;
        }

        .datenschutz-container a {
            color: #3498db;
            text-decoration: none;
        }

        .datenschutz-container a:hover {
            text-decoration: underline;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-button:hover {
            background: #2980b9;
        }

        .last-updated {
            text-align: right;
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
    </style>
</head>
<body>
    <div class="datenschutz-container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>

        <h1><i class="fas fa-shield-alt"></i> Datenschutzerklärung</h1>

        <h2>1. Datenschutz auf einen Blick</h2>
        
        <h3>Allgemeine Hinweise</h3>
        <p>
            Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten 
            passiert, wenn Sie diese Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie 
            persönlich identifiziert werden können.
        </p>

        <h3>Datenerfassung auf dieser Website</h3>
        <p><strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong></p>
        <p>
            Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten 
            können Sie dem Impressum dieser Website entnehmen.
        </p>

        <p><strong>Wie erfassen wir Ihre Daten?</strong></p>
        <p>
            Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen. Hierbei kann es sich z.B. 
            um Daten handeln, die Sie in ein Kontaktformular eingeben.
        </p>
        <p>
            Andere Daten werden automatisch oder nach Ihrer Einwilligung beim Besuch der Website durch unsere 
            IT-Systeme erfasst. Das sind vor allem technische Daten (z.B. Internetbrowser, Betriebssystem oder 
            Uhrzeit des Seitenaufrufs).
        </p>

        <p><strong>Wofür nutzen wir Ihre Daten?</strong></p>
        <p>
            Ein Teil der Daten wird erhoben, um eine fehlerfreie Bereitstellung der Website zu gewährleisten. 
            Andere Daten können zur Analyse Ihres Nutzerverhaltens verwendet werden.
        </p>

        <h2>2. Hosting</h2>
        <p>
            Diese Website wird bei einem externen Dienstleister gehostet (Hoster). Die personenbezogenen Daten, 
            die auf dieser Website erfasst werden, werden auf den Servern des Hosters gespeichert.
        </p>

        <h2>3. Allgemeine Hinweise und Pflichtinformationen</h2>
        
        <h3>Datenschutz</h3>
        <p>
            Die Betreiber dieser Seiten nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre 
            personenbezogenen Daten vertraulich und entsprechend der gesetzlichen Datenschutzvorschriften sowie 
            dieser Datenschutzerklärung.
        </p>

        <h3>Hinweis zur verantwortlichen Stelle</h3>
        <p>
            Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:
        </p>
        <p>
            <?= htmlspecialchars($settings['footer_company'] ?? 'Ihr Firmenname') ?><br>
            Siehe Impressum für weitere Kontaktdaten
        </p>

        <h2>4. Datenerfassung auf dieser Website</h2>
        
        <h3>Server-Log-Dateien</h3>
        <p>
            Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten Server-Log-Dateien, 
            die Ihr Browser automatisch an uns übermittelt. Dies sind:
        </p>
        <ul>
            <li>Browsertyp und Browserversion</li>
            <li>Verwendetes Betriebssystem</li>
            <li>Referrer URL</li>
            <li>Hostname des zugreifenden Rechners</li>
            <li>Uhrzeit der Serveranfrage</li>
            <li>IP-Adresse</li>
        </ul>
        <p>
            Eine Zusammenführung dieser Daten mit anderen Datenquellen wird nicht vorgenommen.
        </p>

        <h3>Kontaktformular</h3>
        <p>
            Wenn Sie uns per Kontaktformular Anfragen zukommen lassen, werden Ihre Angaben aus dem Anfrageformular 
            inklusive der von Ihnen dort angegebenen Kontaktdaten zwecks Bearbeitung der Anfrage und für den Fall 
            von Anschlussfragen bei uns gespeichert.
        </p>

        <h3>Registrierung auf dieser Website</h3>
        <p>
            Sie können sich auf dieser Website registrieren, um zusätzliche Funktionen auf der Seite zu nutzen. 
            Die dazu eingegebenen Daten verwenden wir nur zum Zwecke der Nutzung des jeweiligen Angebotes oder 
            Dienstes, für den Sie sich registriert haben.
        </p>

        <h2>5. QR-Code und NFC-Chip Funktionen</h2>
        
        <h3>GPS-Daten</h3>
        <p>
            Diese Website nutzt GPS-Funktionen zum Tracking von Geräten und Markern. Die GPS-Daten werden nur 
            mit Ihrer ausdrücklichen Zustimmung erfasst und auf unseren Servern gespeichert.
        </p>
        <ul>
            <li>GPS-Koordinaten werden zur Lokalisierung von Geräten verwendet</li>
            <li>Die Daten werden verschlüsselt übertragen und gespeichert</li>
            <li>Sie können die GPS-Erfassung jederzeit in den Geräteeinstellungen deaktivieren</li>
        </ul>

        <h3>Kamera-Zugriff</h3>
        <p>
            Für das Scannen von QR-Codes und die 3D-Erfassung benötigt diese Website Zugriff auf Ihre Gerätekamera. 
            Die aufgenommenen Bilder werden nur lokal verarbeitet oder mit Ihrer Zustimmung auf den Server hochgeladen.
        </p>

        <h3>NFC-Funktionen</h3>
        <p>
            Diese Website nutzt NFC-Funktionen zum Auslesen von NFC-Chips. Es werden nur die Chip-IDs verarbeitet, 
            keine weiteren personenbezogenen Daten.
        </p>

        <h2>6. Ihre Rechte</h2>
        <p>
            Sie haben jederzeit das Recht:
        </p>
        <ul>
            <li>Auskunft über Ihre bei uns gespeicherten Daten und deren Verarbeitung zu erhalten</li>
            <li>Berichtigung unrichtiger personenbezogener Daten zu verlangen</li>
            <li>Löschung Ihrer bei uns gespeicherten Daten zu verlangen</li>
            <li>Einschränkung der Datenverarbeitung zu verlangen</li>
            <li>Widerspruch gegen die Verarbeitung Ihrer Daten einzulegen</li>
            <li>Datenübertragbarkeit zu fordern</li>
        </ul>

        <h2>7. Cookies</h2>
        <p>
            Diese Website verwendet Cookies. Cookies sind kleine Textdateien, die auf Ihrem Endgerät gespeichert 
            werden. Wir verwenden Cookies ausschließlich für:
        </p>
        <ul>
            <li>Session-Management (Login-Status)</li>
            <li>Speicherung von Benutzereinstellungen</li>
            <li>Sicherheitsfunktionen (CSRF-Schutz)</li>
        </ul>
        <p>
            Sie können Ihren Browser so einstellen, dass Sie über das Setzen von Cookies informiert werden und 
            Cookies nur im Einzelfall erlauben.
        </p>

        <h2>8. Datensicherheit</h2>
        <p>
            Wir verwenden geeignete technische und organisatorische Sicherheitsmaßnahmen, um Ihre Daten gegen 
            zufällige oder vorsätzliche Manipulationen, teilweisen oder vollständigen Verlust, Zerstörung oder 
            gegen den unbefugten Zugriff Dritter zu schützen.
        </p>
        <ul>
            <li>SSL/TLS-Verschlüsselung für die Datenübertragung</li>
            <li>Passwort-geschützte Datenbanken</li>
            <li>Regelmäßige Sicherheits-Updates</li>
            <li>Zugriffsbeschränkungen auf personenbezogene Daten</li>
        </ul>

        <h2>9. Änderung der Datenschutzerklärung</h2>
        <p>
            Wir behalten uns vor, diese Datenschutzerklärung anzupassen, damit sie stets den aktuellen rechtlichen 
            Anforderungen entspricht oder um Änderungen unserer Leistungen in der Datenschutzerklärung umzusetzen.
        </p>

        <h2>10. Kontakt</h2>
        <p>
            Bei Fragen zum Datenschutz wenden Sie sich bitte an:
        </p>
        <p>
            <?= htmlspecialchars($settings['footer_company'] ?? 'Ihr Firmenname') ?><br>
            Siehe <a href="<?= htmlspecialchars($settings['impressum_url'] ?? '/impressum.php') ?>">Impressum</a> 
            für Kontaktdaten
        </p>

        <div class="last-updated">
            <p><small>Stand: November 2025</small></p>
        </div>
    </div>
</body>
</html>