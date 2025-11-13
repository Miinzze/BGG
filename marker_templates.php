<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requirePermission('marker_templates_manage');

$message = '';
$messageType = '';

// Template erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_template'])) {
    validateCSRF();
    
    $templateName = trim($_POST['template_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $isStorage = isset($_POST['is_storage']) ? 1 : 0;
    $rentalStatus = $_POST['rental_status'] ?? 'verfuegbar';
    $maintenanceInterval = intval($_POST['maintenance_interval'] ?? 6);
    $isMultiDevice = isset($_POST['is_multi_device']) ? 1 : 0;
    $isCustomerDevice = isset($_POST['is_customer_device']) ? 1 : 0;
    $isRepairDevice = isset($_POST['is_repair_device']) ? 1 : 0;
    $fuelUnit = $_POST['fuel_unit'] ?? 'percent';
    $fuelCapacity = !empty($_POST['fuel_capacity']) ? floatval($_POST['fuel_capacity']) : null;
    
    if (empty($templateName)) {
        $message = 'Bitte geben Sie einen Template-Namen ein';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO marker_templates 
                (template_name, description, category, is_storage, rental_status, maintenance_interval_months, 
                is_multi_device, is_customer_device, is_repair_device, fuel_unit, fuel_capacity, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $templateName, $description, $category, $isStorage, $rentalStatus, $maintenanceInterval,
                $isMultiDevice, $isCustomerDevice, $isRepairDevice, $fuelUnit, $fuelCapacity, $_SESSION['user_id']
            ]);
            
            $message = 'Template erfolgreich erstellt!';
            $messageType = 'success';
            logActivity('template_created', "Marker-Template '$templateName' erstellt");
        } catch (PDOException $e) {
            $message = 'Fehler beim Erstellen: ' . e($e->getMessage());
            $messageType = 'danger';
        }
    }
}

// Template löschen
if (isset($_GET['delete'])) {
    validateCSRF();
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM marker_templates WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Template erfolgreich gelöscht';
        $messageType = 'success';
        logActivity('template_deleted', "Marker-Template gelöscht (ID: $id)");
        header('Location: marker_templates.php');
        exit;
    } catch (PDOException $e) {
        $message = 'Fehler beim Löschen: ' . e($e->getMessage());
        $messageType = 'danger';
    }
}

// Templates laden
$stmt = $pdo->query("SELECT mt.*, u.username FROM marker_templates mt LEFT JOIN users u ON mt.created_by = u.id ORDER BY mt.created_at DESC");
$templates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marker Templates verwalten</title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .templates-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .template-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }
        
        .template-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .template-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .template-name {
            font-size: 18px;
            font-weight: bold;
            color: #212529;
        }
        
        .template-actions {
            display: flex;
            gap: 5px;
        }
        
        .template-description {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .template-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .detail-item i {
            color: #007bff;
            width: 16px;
            text-align: center;
        }
        
        .template-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }
        
        .template-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-storage {
            background: #e7f3ff;
            color: #004085;
        }
        
        .badge-customer {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-repair {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-multi {
            background: #d4edda;
            color: #155724;
        }
        
        .template-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="templates-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1><i class="fas fa-layer-group"></i> Marker Templates</h1>
                <p>Erstellen Sie Vorlagen für häufig verwendete Marker-Konfigurationen</p>
            </div>
            <button onclick="openModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Neues Template
            </button>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
        <?php endif; ?>
        
        <?php if (empty($templates)): ?>
            <div class="empty-state">
                <i class="fas fa-layer-group"></i>
                <h2>Keine Templates vorhanden</h2>
                <p>Erstellen Sie Ihr erstes Marker-Template, um die Arbeit zu beschleunigen!</p>
                <button onclick="openModal()" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Erstes Template erstellen
                </button>
            </div>
        <?php else: ?>
            <div class="template-grid">
                <?php foreach ($templates as $template): ?>
                <div class="template-card">
                    <div class="template-header">
                        <div class="template-name">
                            <i class="fas fa-clone"></i> <?= e($template['template_name']) ?>
                        </div>
                        <div class="template-actions">
                            <a href="create_marker.php?template_id=<?= $template['id'] ?>" class="btn btn-sm btn-success" title="Mit diesem Template erstellen">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a href="?delete=<?= $template['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Template wirklich löschen?')" 
                               title="Template löschen">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($template['description']): ?>
                    <div class="template-description">
                        <?= nl2br(e($template['description'])) ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="template-details">
                        <?php if ($template['category']): ?>
                        <div class="detail-item">
                            <i class="fas fa-tag"></i>
                            <span><?= e($template['category']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= $template['maintenance_interval_months'] ?> Monate</span>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-info-circle"></i>
                            <span><?= e(ucfirst($template['rental_status'])) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-gas-pump"></i>
                            <span><?= $template['fuel_unit'] === 'liter' ? 'Liter' : 'Prozent' ?></span>
                        </div>
                    </div>
                    
                    <div class="template-badges">
                        <?php if ($template['is_storage']): ?>
                            <span class="template-badge badge-storage">
                                <i class="fas fa-warehouse"></i> Lagergerät
                            </span>
                        <?php endif; ?>
                        <?php if ($template['is_customer_device']): ?>
                            <span class="template-badge badge-customer">
                                <i class="fas fa-user-tie"></i> Kundengerät
                            </span>
                        <?php endif; ?>
                        <?php if ($template['is_repair_device']): ?>
                            <span class="template-badge badge-repair">
                                <i class="fas fa-tools"></i> Reparaturgerät
                            </span>
                        <?php endif; ?>
                        <?php if ($template['is_multi_device']): ?>
                            <span class="template-badge badge-multi">
                                <i class="fas fa-layer-group"></i> Multi-Device
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="template-footer">
                        <i class="fas fa-user"></i> Erstellt von <?= e($template['username'] ?? 'Unbekannt') ?>
                        <br>
                        <i class="fas fa-clock"></i> <?= date('d.m.Y H:i', strtotime($template['created_at'])) ?> Uhr
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal für neues Template -->
    <div id="templateModal" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-plus"></i> Neues Template erstellen</h2>
            
            <form method="POST">
                <?= csrf_field() ?>
                
                <div class="form-section">
                    <h3>Grunddaten</h3>
                    <div class="form-group">
                        <label>Template-Name *</label>
                        <input type="text" name="template_name" class="form-control" required placeholder="z.B. Standard Generator">
                    </div>
                    <div class="form-group">
                        <label>Beschreibung</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional: Beschreiben Sie, wofür dieses Template verwendet wird"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Kategorie</label>
                        <select name="category" class="form-control">
                            <option value="">-- Keine --</option>
                            <option value="Generator">Generator</option>
                            <option value="Kompressor">Kompressor</option>
                            <option value="Pumpe">Pumpe</option>
                            <option value="Fahrzeug">Fahrzeug</option>
                            <option value="Werkzeug">Werkzeug</option>
                            <option value="Lager">Lager</option>
                            <option value="Sonstiges">Sonstiges</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Gerätetyp</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_storage">
                            Lagergerät
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_customer_device">
                            Kundengerät
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_repair_device">
                            Reparaturgerät
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_multi_device">
                            Multi-Device (mehrere Geräte an einem Standort)
                        </label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Einstellungen</h3>
                    <div class="form-group">
                        <label>Mietstatus</label>
                        <select name="rental_status" class="form-control">
                            <option value="verfuegbar">Verfügbar</option>
                            <option value="vermietet">Vermietet</option>
                            <option value="wartung">Wartung</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Wartungsintervall (Monate)</label>
                        <input type="number" name="maintenance_interval" class="form-control" value="6" min="1" max="120">
                    </div>
                    <div class="form-group">
                        <label>Kraftstoff-Einheit</label>
                        <select name="fuel_unit" class="form-control" onchange="toggleFuelCapacity(this)">
                            <option value="percent">Prozent (%)</option>
                            <option value="liter">Liter</option>
                        </select>
                    </div>
                    <div class="form-group" id="fuelCapacityGroup" style="display: none;">
                        <label>Tank-Kapazität (Liter)</label>
                        <input type="number" name="fuel_capacity" class="form-control" step="0.1" min="0" placeholder="z.B. 50">
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" name="create_template" class="btn btn-primary">
                        <i class="fas fa-save"></i> Template speichern
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        Abbrechen
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
    function openModal() {
        document.getElementById('templateModal').classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('templateModal').classList.remove('active');
    }
    
    function toggleFuelCapacity(select) {
        const capacityGroup = document.getElementById('fuelCapacityGroup');
        capacityGroup.style.display = select.value === 'liter' ? 'block' : 'none';
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Close modal on background click
    document.getElementById('templateModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html>