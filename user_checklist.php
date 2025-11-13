<?php
// user_checklist.php - Komponente für Benutzer-Checkliste
// Include in Dashboard/Index pages

if (!isset($_SESSION['user_id'])) return;

// Checkliste laden
$stmt = $pdo->prepare("SELECT * FROM user_checklist WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$checklistItems = $stmt->fetchAll();

// Wenn keine Items vorhanden, initialisieren
if (empty($checklistItems)) {
    $defaultItems = ['profile_completed', 'password_changed', '2fa_enabled', 'first_marker_created', 'tour_completed'];
    foreach ($defaultItems as $item) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_checklist (user_id, checklist_item) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $item]);
    }
    // Neu laden
    $stmt = $pdo->prepare("SELECT * FROM user_checklist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $checklistItems = $stmt->fetchAll();
}

// Fortschritt berechnen
$totalItems = count($checklistItems);
$completedItems = count(array_filter($checklistItems, function($item) { return $item['completed']; }));
$progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

// Nur anzeigen wenn nicht alle Schritte abgeschlossen
if ($progress < 100):
?>
<div class="admin-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 25px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; color: white;"><i class="fas fa-tasks"></i> Ihre Erste-Schritte Checkliste</h3>
        <span style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; font-weight: bold;">
            <?= $completedItems ?> / <?= $totalItems ?> abgeschlossen
        </span>
    </div>
    
    <div style="background: rgba(255,255,255,0.1); border-radius: 10px; height: 10px; margin-bottom: 20px; overflow: hidden;">
        <div style="background: #28a745; height: 100%; width: <?= $progress ?>%; transition: width 0.5s;"></div>
    </div>
    
    <div style="display: grid; gap: 10px;">
        <?php
        $checklistLabels = [
            'profile_completed' => ['icon' => 'user-circle', 'text' => 'Profil vervollständigen', 'link' => 'profile.php'],
            'password_changed' => ['icon' => 'key', 'text' => 'Passwort geändert', 'link' => 'profile.php#password'],
            '2fa_enabled' => ['icon' => 'shield-alt', 'text' => '2FA eingerichtet', 'link' => 'profile.php#2fa'],
            'first_marker_created' => ['icon' => 'map-marker-alt', 'text' => 'Ersten Marker erstellt', 'link' => 'create_marker.php'],
            'tour_completed' => ['icon' => 'route', 'text' => 'Tour abgeschlossen', 'link' => 'index.php?show_tour=1']
        ];
        
        foreach ($checklistItems as $item):
            $itemKey = $item['checklist_item'];
            if (!isset($checklistLabels[$itemKey])) continue;
            $label = $checklistLabels[$itemKey];
            $isCompleted = $item['completed'];
        ?>
        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 5px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center;">
                <?php if ($isCompleted): ?>
                    <i class="fas fa-check-circle" style="color: #28a745; font-size: 24px; margin-right: 15px;"></i>
                <?php else: ?>
                    <i class="far fa-circle" style="color: rgba(255,255,255,0.5); font-size: 24px; margin-right: 15px;"></i>
                <?php endif; ?>
                <div>
                    <strong><?= $label['text'] ?></strong>
                    <?php if ($isCompleted && $item['completed_at']): ?>
                        <br><small style="opacity: 0.7;">Erledigt am <?= date('d.m.Y', strtotime($item['completed_at'])) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!$isCompleted): ?>
                <a href="<?= $label['link'] ?>" class="btn btn-sm" style="background: white; color: #667eea; border: none;">
                    <i class="fas fa-<?= $label['icon'] ?>"></i> Jetzt erledigen
                </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($progress >= 80): ?>
    <div style="margin-top: 20px; padding: 15px; background: rgba(40, 167, 69, 0.2); border-radius: 5px; text-align: center;">
        <i class="fas fa-star"></i> Großartig! Sie sind fast fertig. Nur noch <?= $totalItems - $completedItems ?> Schritt<?= ($totalItems - $completedItems) > 1 ? 'e' : '' ?>!
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
