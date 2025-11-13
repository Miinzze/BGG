<?php
// public/messe_info.php
require_once 'config.php';
require_once 'functions.php';

// Suche Marker per ID oder Token
$marker = null;
$marker_id = isset($_GET['m']) ? intval($_GET['m']) : null;
$token = isset($_GET['token']) ? trim($_GET['token']) : null;

if ($marker_id) {
    $stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$marker_id]);
    $marker = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($token) {
    $stmt = $pdo->prepare("SELECT * FROM markers WHERE public_token = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$token]);
    $marker = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$marker) {
    http_response_code(404);
    echo "Gerät nicht gefunden.";
    exit;
}

// Versuche zugehörige aktive Messe zu finden (sofern das Gerät einer Messe zugewiesen ist)
$stmt = $pdo->prepare("
    SELECT mc.* , mm.id AS messe_marker_id
    FROM messe_config mc
    JOIN messe_markers mm ON mm.messe_id = mc.id
    WHERE mm.marker_id = ? AND mc.is_active = 1
    LIMIT 1
");
$stmt->execute([$marker['id']]);
$messe = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback: wenn keine aktive Messe, hole generelle messe_config (falls vorhanden)
if (!$messe) {
    $stmt = $pdo->query("SELECT * FROM messe_config WHERE is_active = 1 LIMIT 1");
    $messe = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Hole messe-spezifische Felder (falls messe_marker vorhanden)
$messe_marker_id = $messe['messe_marker_id'] ?? null;
$fields = [];
if ($messe_marker_id) {
    $stmt = $pdo->prepare("SELECT * FROM messe_marker_fields WHERE messe_marker_id = ? ORDER BY display_order ASC");
    $stmt->execute([$messe_marker_id]);
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Bilder
$images = [];
$stmt = $pdo->prepare("SELECT * FROM marker_images WHERE marker_id = ? ORDER BY id ASC");
$stmt->execute([$marker['id']]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for colors
$bg = $messe['background_color'] ?? '#ffffff';
$text = $messe['text_color'] ?? '#000000';
$accent = $messe['accent_color'] ?? '#007bff';
$logo = $messe['logo_path'] ?? '';

?><!doctype html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($marker['name'] . ' — ' . ($messe['name'] ?? 'Messe')) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body{font-family: Arial,Helvetica,sans-serif;background: <?= htmlspecialchars($bg) ?>; color: <?= htmlspecialchars($text) ?>; padding:20px;}
    .wrap{max-width:1000px;margin:0 auto;background: rgba(255,255,255,0.03); padding:20px;border-radius:8px;}
    .header{display:flex;align-items:center;gap:20px}
    .logo{height:70px}
    .title{font-size:24px;font-weight:700}
    .content{display:flex;gap:20px;margin-top:20px;flex-wrap:wrap}
    .col-left{flex:1 1 420px; min-width:280px}
    .col-right{flex:0 0 340px; min-width:260px}
    .card{background:rgba(255,255,255,0.04);padding:15px;border-radius:8px;}
    .field-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(0,0,0,0.05)}
    .field-row:last-child{border-bottom:none}
    .lead-form input,.lead-form textarea{width:100%;padding:10px;margin:6px 0;border:1px solid #ddd;border-radius:6px}
    .btn{display:inline-block;padding:10px 14px;border-radius:6px;background:<?= htmlspecialchars($accent) ?>;color:#fff;text-decoration:none;border:none;cursor:pointer}
    .gallery img{max-width:100%;border-radius:6px;margin-bottom:8px}
    .meta{color:rgba(0,0,0,0.5);font-size:13px;margin-top:8px}
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <?php if (!empty($logo) && file_exists(__DIR__ . '/../' . $logo)): ?>
            <img src="<?= '../' . htmlspecialchars($logo) ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <div>
            <div class="title"><?= htmlspecialchars($marker['name']) ?></div>
            <div class="meta"><?= htmlspecialchars($messe['name'] ?? '') ?></div>
        </div>
    </div>

    <div class="content">
        <div class="col-left">
            <div class="card">
                <h3>Technische Daten</h3>
                <?php if (!empty($fields)): ?>
                    <?php foreach ($fields as $f): ?>
                        <div class="field-row">
                            <div><?= htmlspecialchars($f['field_name']) ?></div>
                            <div><?= htmlspecialchars($f['field_value']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Keine zusätzlichen Informationen vorhanden.</p>
                <?php endif; ?>

                <div style="margin-top:12px">
                    <h4>Beschreibung</h4>
                    <p><?= nl2br(htmlspecialchars($marker['repair_description'] ?: $marker['customer_name'] ?: '')) ?></p>
                </div>
            </div>

            <div class="card" style="margin-top:12px">
                <h3>Bilder</h3>
                <div class="gallery">
                    <?php if ($images): ?>
                        <?php foreach ($images as $img): 
                            $path = $img['image_path'];
                        ?>
                            <?php if (file_exists(__DIR__ . '/../' . $path)): ?>
                                <img src="<?= '../' . htmlspecialchars($path) ?>" alt="" />
                            <?php else: ?>
                                <!-- falls Datei fehlt, nichts anzeigen -->
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Keine Bilder verfügbar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-right">
            <div class="card lead-form">
                <h3><i class="fas fa-envelope"></i> Interesse an diesem Gerät?</h3>
                <p style="color:rgba(0,0,0,0.6)"><?php echo htmlspecialchars($messe['description'] ?? 'Schreiben Sie uns gerne eine Nachricht.'); ?></p>

                <form method="POST" action="lead_submit.php">
                    <input type="hidden" name="messe_id" value="<?= htmlspecialchars($messe['id'] ?? '') ?>">
                    <input type="hidden" name="marker_id" value="<?= htmlspecialchars($marker['id']) ?>">
                    <input type="hidden" name="interested_in" value="<?= htmlspecialchars($marker['name']) ?>">

                    <label>E-Mail *</label>
                    <input type="email" name="email" required placeholder="Ihre E-Mail">

                    <label>Name</label>
                    <input type="text" name="name" placeholder="Ihr Name">

                    <label>Firma</label>
                    <input type="text" name="company" placeholder="Firma">

                    <label>Telefon</label>
                    <input type="tel" name="phone" placeholder="Telefon">

                    <label>Nachricht</label>
                    <textarea name="message" rows="4" placeholder="Ihre Nachricht..."></textarea>

                    <button class="btn" type="submit"><i class="fas fa-paper-plane"></i> Nachricht senden</button>
                </form>
            </div>

            <?php if (!empty($messe['thank_you_message'])): ?>
                <div class="card" style="margin-top:12px">
                    <h4>Hinweis</h4>
                    <p><?= nl2br(htmlspecialchars($messe['thank_you_message'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
