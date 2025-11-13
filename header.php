<?php
// System-Einstellungen laden
$settings = getSystemSettings();
$systemName = $settings['system_name'] ?? 'Marker System';
$systemLogo = $settings['system_logo'] ?? '';

// Bug-Tickets des aktuellen Users zählen
$bugCount = 0;
if (isset($_SESSION['user_id'])) {
    $bugStmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM bug_reports 
        WHERE email = (SELECT email FROM users WHERE id = ?)
        AND status != 'erledigt'
        AND archived_at IS NULL
    ");
    $bugStmt->execute([$_SESSION['user_id']]);
    $bugCount = $bugStmt->fetch()['count'];
}

// Alle Benachrichtigungen abrufen
$notifications = [];
$notificationCount = 0;
if (isset($_SESSION['user_id'])) {
    try {
        // Bug-Tickets
        $bugNotifications = $pdo->prepare("
            SELECT 
                'bug' as type,
                id,
                title as message,
                created_at,
                'Neuer Bug-Report' as category
            FROM bug_reports 
            WHERE email = (SELECT email FROM users WHERE id = ?)
            AND status != 'erledigt'
            AND archived_at IS NULL
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $bugNotifications->execute([$_SESSION['user_id']]);
        $notifications = array_merge($notifications, $bugNotifications->fetchAll(PDO::FETCH_ASSOC));
        
        // Wartungs-Benachrichtigungen
        if (hasPermission('maintenance_view')) {
            $maintenanceNotifications = $pdo->prepare("
                SELECT 
                    'maintenance' as type,
                    m.id,
                    CONCAT('Wartung fällig: ', ma.name) as message,
                    m.scheduled_date as created_at,
                    'Wartungstermin' as category
                FROM maintenance_schedules m
                JOIN markers ma ON m.marker_id = ma.id
                WHERE m.status = 'geplant'
                AND m.scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY m.scheduled_date ASC
                LIMIT 5
            ");
            $maintenanceNotifications->execute();
            $notifications = array_merge($notifications, $maintenanceNotifications->fetchAll(PDO::FETCH_ASSOC));
        }
        
        // Inspektions-Benachrichtigungen
        if (hasPermission('markers_view')) {
            $inspectionNotifications = $pdo->prepare("
                SELECT 
                    'inspection' as type,
                    i.id,
                    CONCAT('Inspektion fällig: ', m.name) as message,
                    i.next_inspection_date as created_at,
                    'Inspektionstermin' as category
                FROM inspection_schedules i
                JOIN markers m ON i.marker_id = m.id
                WHERE i.next_inspection_date <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                AND i.next_inspection_date IS NOT NULL
                ORDER BY i.next_inspection_date ASC
                LIMIT 5
            ");
            $inspectionNotifications->execute();
            $notifications = array_merge($notifications, $inspectionNotifications->fetchAll(PDO::FETCH_ASSOC));
        }
        
        $notificationCount = count($notifications);
    } catch (Exception $e) {
        // Fehler beim Laden der Benachrichtigungen ignorieren
    }
}

// Breadcrumb-Funktion
function renderBreadcrumbs($breadcrumbs = []) {
    if (empty($breadcrumbs)) {
        return '';
    }
    
    $html = '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    $html .= '<ol class="breadcrumb-list">';
    
    $count = count($breadcrumbs);
    foreach ($breadcrumbs as $index => $crumb) {
        $isLast = ($index === $count - 1);
        $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '">';
        
        if (!$isLast && isset($crumb['url'])) {
            $html .= '<a href="' . htmlspecialchars($crumb['url']) . '">';
            $html .= '<i class="' . ($crumb['icon'] ?? 'fas fa-home') . '"></i> ';
            $html .= htmlspecialchars($crumb['label']);
            $html .= '</a>';
        } else {
            $html .= '<span>';
            if (isset($crumb['icon'])) {
                $html .= '<i class="' . $crumb['icon'] . '"></i> ';
            }
            $html .= htmlspecialchars($crumb['label']);
            $html .= '</span>';
        }
        
        if (!$isLast) {
            $html .= '<i class="fas fa-chevron-right breadcrumb-separator"></i>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

// Header wird in jeder Seite inkludiert
$isMobile = isMobileDevice();
?>
<header class="main-header">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <div class="header-container">
        <div class="logo">
            <a href="index.php" style="display: flex; align-items: center; gap: 15px;">
                <?php if (!empty($systemLogo) && file_exists($systemLogo)): ?>
                    <img src="<?= htmlspecialchars($systemLogo) ?>?v=<?= time() ?>" 
                         alt="<?= htmlspecialchars($systemName) ?>" 
                         style="max-height: 50px; max-width: 200px;">
                <?php endif; ?>
                <h1><?= htmlspecialchars($systemName) ?></h1>
            </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <!-- Dashboard -->
                <li>
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Marker Dropdown -->
                <?php if (hasPermission('markers_view') || hasPermission('markers_create')): ?>
                <li class="has-dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-qrcode"></i>
                        <span>Marker</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (hasPermission('markers_view')): ?>
                        <li><a href="markers.php"><i class="fas fa-list"></i> Alle Marker</a></li>
                        <li><a href="advanced_search.php"><i class="fas fa-search"></i> Erweiterte Suche</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('markers_create')): ?>
                        <li class="divider"></li>
                        <?php if (!$isMobile): ?>
                        <li><a href="create_marker.php"><i class="fas fa-plus-circle"></i> Marker erstellen</a></li>
                        <?php endif; ?>
                        <li><a href="inactive_markers.php"><i class="fas fa-clock"></i> Zu aktivieren</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('markers_bulk_edit')): ?>
                        <li class="divider"></li>
                        <li><a href="markers_bulk_edit.php"><i class="fas fa-tasks"></i> Massenbearbeitung</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('marker_templates_manage')): ?>
                        <li><a href="marker_templates.php"><i class="fas fa-layer-group"></i> Templates</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="mobile-only mobile-important">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Mobile Tools (nur auf Mobilgeräten) -->
                <?php if ($isMobile && hasPermission('markers_create')): ?>
                <li class="mobile-only mobile-important">
                    <a href="qr_scanner.php" class="nav-link">
                        <i class="fas fa-camera"></i>
                        <span>Scanner</span>
                    </a>
                </li>
                <li class="mobile-only mobile-important">
                    <a href="inactive_markers.php" class="nav-link">
                        <i class="fas fa-clock"></i>
                        <span>Zu aktivieren</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Verwaltung Dropdown -->
                <?php if (hasPermission('users_view') || hasPermission('roles_view') || hasPermission('qr_list_view')): ?>
                <li class="has-dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-cog"></i>
                        <span>Verwaltung</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (hasPermission('users_view')): ?>
                        <li><a href="users.php"><i class="fas fa-users"></i> Benutzer</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('roles_view')): ?>
                        <li><a href="roles.php"><i class="fas fa-user-tag"></i> Rollen</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('qr_list_view')): ?>
                        <li><a href="qr_code_generator.php"><i class="fas fa-qrcode"></i> QR-Codes Generieren</a></li>
                        <li><a href="nfc_chip_generator.php"><i class="fas fa-broadcast-tower me-2"></i>NFC-Pool</a></li>
                        <li><a href="nfc_chip_list.php"><i class="fas fa-broadcast-tower me-2"></i>NFC-Liste</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('custom_fields_manage')): ?>
                        <li><a href="custom_fields.php"><i class="fas fa-sliders-h"></i> Custom Fields</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- System Dropdown -->
                <li class="has-dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-tools"></i>
                        <span>System</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (hasPermission('settings_manage')): ?>
                            <li><a href="messe_admin.php"><i class="fas fa-bullhorn"></i> Messe-Modus</a></li>
                        <?php endif; ?>

                        <?php if (hasPermission('maintenance_view') || hasPermission('manage_checklists') || hasPermission('perform_maintenance') || $_SESSION['role'] === 'Admin'): ?>
                        <li class="divider"></li>
                        <li><a href="maintenance_checklists.php"><i class="fas fa-clipboard-check"></i> Wartungs-Checklisten</a></li>
                        <li><a href="maintenance_sets.php"><i class="fas fa-tools"></i> Wartungssätze</a></li>
                        <li><a href="maintenance_timeline.php"><i class="fas fa-history"></i> Wartungszeitleiste</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('file_manager_view')): ?>
                        <li><a href="file_manager.php"><i class="fas fa-folder-open"></i> Dateiverwaltung</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('activity_log_view')): ?>
                        <li><a href="activity_log.php"><i class="fas fa-clipboard-list"></i> Activity Log</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('reports_view')): ?>
                        <li><a href="reports.php"><i class="fas fa-file-alt"></i> Berichte</a></li>
                        <?php endif; ?>

                        <?php if (hasPermission('settings_manage')): ?>
                        <li><a href="escalation_settings.php"><i class="fas fa-file-alt"></i> Eskalations-Einstellungen</a></li>
                        <li><a href="calendar_settings.php"><i class="fas fa-file-alt"></i> Kalender-Einstellungen</a></li>
                        <?php endif; ?>

                        <?php if (hasPermission('categories_manage')): ?>
                        <li><a href="categories.php"><i class="fas fa-file-alt"></i> Kategorien</a></li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('trash_view')): ?>
                        <li class="divider"></li>
                        <li><a href="trash.php"><i class="fas fa-trash"></i> Papierkorb</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <div class="user-menu">
            <!-- Notification Center -->
            <div class="notification-center">
                <button class="notification-btn" id="notificationToggle">
                    <i class="fas fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="notification-badge"><?= $notificationCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Benachrichtigungen</h3>
                        <?php if ($notificationCount > 0): ?>
                            <span class="notification-count"><?= $notificationCount ?> neue</span>
                        <?php endif; ?>
                    </div>
                    <div class="notification-list">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item notification-<?= htmlspecialchars($notification['type']) ?>">
                                    <div class="notification-icon">
                                        <?php 
                                        $icon = match($notification['type']) {
                                            'bug' => 'fa-bug',
                                            'maintenance' => 'fa-wrench',
                                            'inspection' => 'fa-clipboard-check',
                                            default => 'fa-bell'
                                        };
                                        ?>
                                        <i class="fas <?= $icon ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-category"><?= htmlspecialchars($notification['category']) ?></div>
                                        <div class="notification-message"><?= htmlspecialchars($notification['message']) ?></div>
                                        <div class="notification-time">
                                            <?php 
                                            $date = new DateTime($notification['created_at']);
                                            $now = new DateTime();
                                            $diff = $now->diff($date);
                                            
                                            if ($diff->days == 0) {
                                                echo 'Heute';
                                            } elseif ($diff->days == 1) {
                                                echo 'Gestern';
                                            } else {
                                                echo $date->format('d.m.Y');
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="notification-empty">
                                <i class="fas fa-check-circle"></i>
                                <p>Keine neuen Benachrichtigungen</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($notificationCount > 5): ?>
                        <div class="notification-footer">
                            <a href="notifications.php">Alle anzeigen</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- User Dropdown -->
            <div class="user-dropdown-container">
                <button class="user-dropdown-btn" id="userDropdownToggle">
                    <i class="fas fa-user-circle"></i>
                    <span class="username-text"><?= htmlspecialchars($_SESSION['username']) ?></span>
                    <span class="badge badge-info"><?= htmlspecialchars($_SESSION['role']) ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div>
                            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                            <br><small style="color: #6c757d;"><?= htmlspecialchars($_SESSION['role']) ?></small>
                        </div>
                    </div>
                
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i> Mein Profil
                    </a>

                    <a href="my_bug_tickets.php" class="dropdown-item" style="background: #fff5f5;">
                        <i class="fas fa-bug" style="color: #dc3545;"></i> Meine Bug-Tickets
                        <span class="badge badge-danger badge-sm"><?= $bugCount ?></span>
                    </a>
                    
                    <a href="setup_2fa.php" class="dropdown-item">
                        <i class="fas fa-shield-alt"></i> Zwei-Faktor-Auth
                        <?php
                        $stmt = $pdo->prepare("SELECT has_2fa_enabled FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user2faStatus = $stmt->fetchColumn();
                        if ($user2faStatus):
                        ?>
                            <span class="badge badge-success badge-sm">Aktiv</span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="user_signature.php" class="dropdown-item">
                        <i class="fas fa-signature"></i> Signatur verwalten
                    </a>
                    
                    <a href="user_calendar_settings.php" class="dropdown-item">
                        <i class="fas fa-calendar-alt"></i> Kalender-Einstellungen
                    </a>

                    <?php if (hasPermission('settings_manage')): ?>
                    <div class="dropdown-divider"></div>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i> Einstellungen
                    </a>
                    <?php endif; ?>

                    <div class="dropdown-divider"></div>
                    
                    <a href="logout.php" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i> Abmelden
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
/* ===== NAVIGATION STYLES ===== */

.main-header {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 10000;
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 70px;
    position: relative;
    z-index: 10001;
}

.logo h1 {
    color: #2c3e50;
    font-size: 24px;
    margin: 0;
}

.logo a {
    color: #2c3e50;
    text-decoration: none;
}

.main-nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 5px;
    position: relative;
    z-index: 10002;
}

.main-nav > ul > li {
    position: relative;
    z-index: 10003;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    white-space: nowrap;
    cursor: pointer;
}

.nav-link:hover {
    background: #f8f9fa;
    color: #667eea;
}

.nav-link i:first-child {
    font-size: 16px;
}

.nav-link .fa-chevron-down {
    font-size: 10px;
    margin-left: 4px;
    transition: transform 0.3s ease;
}

.has-dropdown.open .fa-chevron-down {
    transform: rotate(180deg);
}

/* Dropdown Menu - wird von style.css überschrieben, daher auskommentiert */
/*
.dropdown-menu {
    ... style.css übernimmt das Styling ...
}
*/

.has-dropdown.open .dropdown-menu {
    /* Wird von style.css gehandhabt - öffnet per Click */
}

.dropdown-menu li {
    margin: 0;
}

.dropdown-menu .divider {
    height: 1px;
    background: #e9ecef;
    margin: 8px 0;
}

/* Mobile Only Navigation */
.mobile-only {
    display: none;
}

@media (hover: none) and (pointer: coarse) {
    .mobile-only {
        display: block;
    }
}

/* User Dropdown Container */
.user-dropdown-container {
    position: relative;
}

.user-dropdown-btn {
    background: transparent;
    border: 2px solid #dee2e6;
    color: #2c3e50;
    padding: 8px 15px;
    border-radius: 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.user-dropdown-btn:hover {
    background: #f8f9fa;
    border-color: #667eea;
}

.user-dropdown-btn .username-text {
    font-weight: 500;
}

.user-dropdown-btn .badge {
    font-size: 11px;
    padding: 3px 8px;
}

.user-dropdown-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 250px;
    display: none;
    z-index: 1000;
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.user-dropdown-menu.show {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.dropdown-header {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: #2c3e50;
    text-decoration: none;
    transition: background 0.2s;
}

.dropdown-item:hover {
    background: #f8f9fa;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.dropdown-item.text-danger {
    color: #dc3545;
}

.dropdown-item.text-danger:hover {
    background: #fff5f5;
}

.dropdown-divider {
    height: 1px;
    background: #dee2e6;
    margin: 5px 0;
}

.badge-sm {
    font-size: 10px;
    padding: 2px 6px;
    margin-left: auto;
}

@media (max-width: 768px) {
    /* Verstecke Dropdown-Pfeile auf Mobil */
    .main-nav .fa-chevron-down {
        display: none !important;
    }
    
    /* Dropdown-Menüs komplett ausblenden auf Mobil */
    .main-nav .dropdown-menu {
        display: none !important;
    }
    
    /* Dropdowns nicht mehr als dropdown behandeln */
    .main-nav .has-dropdown {
        position: static;
    }
    
    /* Dropdown-Toggle zu normalen Links machen */
    .main-nav .dropdown-toggle {
        pointer-events: auto;
        cursor: pointer;
    }
    
    /* Mobile-Only Links anzeigen */
    .mobile-only {
        display: block !important;
    }
    
    /* Navigation horizontal anordnen mit Scroll */
    .main-nav ul {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        gap: 8px;
        padding-bottom: 10px;
        scrollbar-width: thin;
    }
    
    .main-nav ul::-webkit-scrollbar {
        height: 4px;
    }
    
    .main-nav ul::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 4px;
    }
    
    /* Mobile Buttons größer und touch-friendly */
    .nav-link {
        min-width: fit-content;
        padding: 10px 16px;
        font-size: 14px;
        white-space: nowrap;
    }
    
    /* Mobile: Nur wichtigste Buttons zeigen */
    .main-nav > ul > li:not(.mobile-important) {
        display: none;
    }
    
    /* Ausnahme: Mobile-Only Items immer zeigen */
    .main-nav > ul > li.mobile-only {
        display: block !important;
    }
}

/* Spezielle Markierung für wichtige Mobile-Buttons */
.mobile-important {
    /* Bleibt auf Mobil sichtbar */
}
</style>

<script>
// Mobile Navigation Fix - Füge dieses Script in header.php ein (VOR dem bestehenden Script)

(function() {
    'use strict';
    
    console.log('🚀 MOBILE DROPDOWN - Bulletproof Init');
    
    // Warte auf DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        console.log('✅ DOM Ready');
        
        // Erstelle Overlay
        createOverlay();
        
        // Setup Navigation
        setupNavigation();
        
        // Setup User Dropdown
        setupUserDropdown();
        
        console.log('✅ Dropdown initialisiert');
    }
    
    function createOverlay() {
        let overlay = document.getElementById('mobile-dropdown-overlay');
        if (overlay) return;
        
        overlay = document.createElement('div');
        overlay.id = 'mobile-dropdown-overlay';
        overlay.style.cssText = `
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        `;
        document.body.appendChild(overlay);
        
        // Klick auf Overlay schließt alles
        overlay.addEventListener('click', closeAllDropdowns);
        overlay.addEventListener('touchstart', closeAllDropdowns);
    }
    
    function setupNavigation() {
        const dropdowns = document.querySelectorAll('.main-nav .has-dropdown');
        console.log('📋 Gefundene Dropdowns:', dropdowns.length);
        
        if (dropdowns.length === 0) {
            console.warn('⚠️ Keine Dropdowns gefunden!');
            return;
        }
        
        dropdowns.forEach((dropdown, index) => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (!toggle || !menu) {
                console.warn(`⚠️ Dropdown ${index} fehlt Toggle oder Menu`);
                return;
            }
            
            console.log(`✓ Setup Dropdown ${index}:`, toggle.textContent.trim());
            
            // Entferne alte Events (wichtig!)
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            // Füge neue Events hinzu
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleDropdownClick(dropdown);
            });
            
            // Für Touch-Geräte auch touchstart
            newToggle.addEventListener('touchstart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleDropdownClick(dropdown);
            });
        });
    }
    
    function handleDropdownClick(dropdown) {
        const menu = dropdown.querySelector('.dropdown-menu');
        const isOpen = dropdown.classList.contains('open');
        
        console.log('🔄 Dropdown clicked, open:', isOpen);
        
        // Schließe alle
        closeAllDropdowns();
        
        // Öffne dieses (wenn es geschlossen war)
        if (!isOpen) {
            dropdown.classList.add('open');
            
            // Zeige Menu explizit
            if (menu) {
                menu.style.display = 'block';
                menu.style.opacity = '1';
                menu.style.visibility = 'visible';
                menu.style.pointerEvents = 'auto';
            }
            
            // Zeige Overlay
            showOverlay();
            
            console.log('✅ Dropdown geöffnet');
        }
    }
    
    function setupUserDropdown() {
        const toggle = document.getElementById('userDropdownToggle');
        const menu = document.getElementById('userDropdownMenu');
        
        if (!toggle || !menu) {
            console.log('ℹ️ User Dropdown nicht gefunden');
            return;
        }
        
        console.log('✓ Setup User Dropdown');
        
        // Entferne alte Events
        const newToggle = toggle.cloneNode(true);
        toggle.parentNode.replaceChild(newToggle, toggle);
        
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleUserMenu();
        });
        
        newToggle.addEventListener('touchstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleUserMenu();
        });
        
        function toggleUserMenu() {
            const isOpen = menu.classList.contains('show');
            
            closeAllDropdowns();
            
            if (!isOpen) {
                menu.classList.add('show');
                menu.style.display = 'block';
                menu.style.opacity = '1';
                showOverlay();
            }
        }
    }
    
    function closeAllDropdowns() {
        // Navigation Dropdowns
        document.querySelectorAll('.main-nav .has-dropdown').forEach(dropdown => {
            dropdown.classList.remove('open');
            const menu = dropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.style.display = '';
                menu.style.opacity = '';
            }
        });
        
        // User Dropdown
        const userMenu = document.getElementById('userDropdownMenu');
        if (userMenu) {
            userMenu.classList.remove('show');
            userMenu.style.display = '';
            userMenu.style.opacity = '';
        }
        
        hideOverlay();
        
        console.log('🧹 Alle Dropdowns geschlossen');
    }
    
    function showOverlay() {
        const overlay = document.getElementById('mobile-dropdown-overlay');
        if (overlay) {
            overlay.style.display = 'block';
        }
    }
    
    function hideOverlay() {
        const overlay = document.getElementById('mobile-dropdown-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    // ESC-Taste
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
    
    // Klick außerhalb
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.main-nav') && !e.target.closest('.user-dropdown-container')) {
            closeAllDropdowns();
        }
    });
    
})();

// Legacy Support
function toggleUserDropdown() {
    const menu = document.getElementById('userDropdownMenu');
    if (menu) {
        menu.classList.toggle('show');
    }
}

// Notification Center Toggle
document.addEventListener('DOMContentLoaded', function() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationToggle && notificationDropdown) {
        notificationToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            
            // Schließe User-Dropdown wenn offen
            const userMenu = document.getElementById('userDropdownMenu');
            if (userMenu && userMenu.classList.contains('show')) {
                userMenu.classList.remove('show');
            }
        });
        
        // Schließe bei Klick außerhalb
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.notification-center')) {
                notificationDropdown.classList.remove('show');
            }
        });
    }
});
</script>

<!-- Mobile Bottom Navigation Bar -->
<?php if ($isMobile): ?>
<nav class="mobile-bottom-nav">
    <a href="index.php" class="mobile-nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    
    <?php if (hasPermission('markers_view')): ?>
    <a href="markers.php" class="mobile-nav-item <?= basename($_SERVER['PHP_SELF']) == 'markers.php' ? 'active' : '' ?>">
        <i class="fas fa-qrcode"></i>
        <span>Marker</span>
    </a>
    <?php endif; ?>
    
    <?php if (hasPermission('markers_create')): ?>
    <a href="qr_scanner.php" class="mobile-nav-item mobile-nav-primary <?= basename($_SERVER['PHP_SELF']) == 'qr_scanner.php' ? 'active' : '' ?>">
        <i class="fas fa-camera"></i>
        <span>Scanner</span>
    </a>
    <?php endif; ?>
    
    <a href="#" class="mobile-nav-item" id="mobileNotificationBtn">
        <i class="fas fa-bell"></i>
        <?php if ($notificationCount > 0): ?>
            <span class="mobile-notification-badge"><?= $notificationCount ?></span>
        <?php endif; ?>
        <span>Meldungen</span>
    </a>
    
    <a href="profile.php" class="mobile-nav-item <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
        <i class="fas fa-user"></i>
        <span>Profil</span>
    </a>
</nav>

<script>
// Mobile Bottom Navigation - Notification Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileNotificationBtn = document.getElementById('mobileNotificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (mobileNotificationBtn && notificationDropdown) {
        mobileNotificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle Notification Dropdown
            notificationDropdown.classList.toggle('show');
            notificationDropdown.classList.add('mobile-positioned');
        });
    }
});
</script>
<?php endif; ?>