<?php
require_once 'config.php';

$pageTitle = 'Impressum';

// Firmeninformationen aus Datenbank oder Konfiguration laden
// Falls getSetting() nicht existiert, verwenden wir Standardwerte
$company_name = 'Ihre Firma';
$company_address = 'Musterstraße 123';
$company_city = '12345 Musterstadt';
$company_phone = '+49 123 456789';
$company_email = 'info@ihre-firma.de';
$company_ceo = 'Max Mustermann';

// Versuche Einstellungen aus Datenbank zu laden falls settings-Tabelle existiert
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'company_%'");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (!empty($settings)) {
        $company_name = $settings['company_name'] ?? $company_name;
        $company_address = $settings['company_address'] ?? $company_address;
        $company_city = $settings['company_city'] ?? $company_city;
        $company_phone = $settings['company_phone'] ?? $company_phone;
        $company_email = $settings['company_email'] ?? $company_email;
        $company_ceo = $settings['company_ceo'] ?? $company_ceo;
    }
} catch (PDOException $e) {
    // Tabelle existiert nicht oder Fehler - verwende Standardwerte
}

require_once 'header.php';
?>

<div class="main-container">
    <div class="content-wrapper">
        <div class="page-header">
            <h1><i class="bi bi-info-circle"></i> Impressum</h1>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h2 class="mb-4">Angaben gemäß § 5 TMG</h2>
                
                <div class="impressum-section">
                    <h3>Betreiber</h3>
                    <p>
                        <strong><?= htmlspecialchars($company_name) ?></strong><br>
                        <?= htmlspecialchars($company_address) ?><br>
                        <?= htmlspecialchars($company_city) ?>
                    </p>
                </div>
                
                <div class="impressum-section">
                    <h3>Kontakt</h3>
                    <p>
                        <i class="bi bi-telephone"></i> Telefon: <?= htmlspecialchars($company_phone) ?><br>
                        <i class="bi bi-envelope"></i> E-Mail: <a href="mailto:<?= htmlspecialchars($company_email) ?>"><?= htmlspecialchars($company_email) ?></a>
                    </p>
                </div>
                
                <div class="impressum-section">
                    <h3>Vertreten durch</h3>
                    <p><?= htmlspecialchars($company_ceo) ?></p>
                </div>
                
                <div class="impressum-section">
                    <h3>Haftungsausschluss</h3>
                    
                    <h4>Haftung für Inhalte</h4>
                    <p>
                        Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. 
                        Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte 
                        können wir jedoch keine Gewähr übernehmen.
                    </p>
                    
                    <h4>Haftung für Links</h4>
                    <p>
                        Unser Angebot enthält Links zu externen Webseiten Dritter, auf 
                        deren Inhalte wir keinen Einfluss haben. Deshalb können wir für 
                        diese fremden Inhalte auch keine Gewähr übernehmen.
                    </p>
                    
                    <h4>Urheberrecht</h4>
                    <p>
                        Die durch die Seitenbetreiber erstellten Inhalte und Werke auf 
                        diesen Seiten unterliegen dem deutschen Urheberrecht. Die 
                        Vervielfältigung, Bearbeitung, Verbreitung und jede Art der 
                        Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen 
                        der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.
                    </p>
                </div>
                
                <div class="impressum-section">
                    <h3>Datenschutz</h3>
                    <p>
                        Die Nutzung unserer Webseite ist in der Regel ohne Angabe 
                        personenbezogener Daten möglich. Soweit auf unseren Seiten 
                        personenbezogene Daten (beispielsweise Name, Anschrift oder 
                        E-Mail-Adressen) erhoben werden, erfolgt dies, soweit möglich, 
                        stets auf freiwilliger Basis.
                    </p>
                    <p>
                        Weitere Informationen finden Sie in unserer 
                        <a href="datenschutz.php">Datenschutzerklärung</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.impressum-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.impressum-section:last-child {
    border-bottom: none;
}

.impressum-section h3 {
    color: #667eea;
    font-size: 1.3em;
    font-weight: 600;
    margin-bottom: 15px;
}

.impressum-section h4 {
    color: #333;
    font-size: 1.1em;
    font-weight: 600;
    margin-top: 20px;
    margin-bottom: 10px;
}

.impressum-section p {
    color: #666;
    line-height: 1.8;
    margin-bottom: 10px;
}

.impressum-section a {
    color: #667eea;
    text-decoration: none;
}

.impressum-section a:hover {
    text-decoration: underline;
}

.impressum-section i {
    margin-right: 8px;
    color: #667eea;
}
</style>

<?php require_once 'footer.php'; ?>