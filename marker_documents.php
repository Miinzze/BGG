<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$marker_id = $_GET['marker_id'] ?? null;
if (!$marker_id) die('Keine Marker-ID');

$stmt = $pdo->prepare("SELECT * FROM markers WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$marker_id]);
$marker = $stmt->fetch();
if (!$marker) die('Gerät nicht gefunden');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    validateCSRF();
    try {
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Keine Datei');
        }
        
        $file = $_FILES['document'];
        
        // Erstelle sicheren Ordnernamen aus Marker-Name
        $markerFolderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $marker['name']);
        $markerFolderName = trim($markerFolderName, '_');
        
        $uploadDir = 'uploads/documents/' . $markerFolderName . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'doc_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Upload fehlgeschlagen');
        }
        
        $pdo->prepare("
            INSERT INTO marker_documents 
            (marker_id, marker_name, document_type, document_name, document_path, file_name, file_size, mime_type, uploaded_by, public_description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $marker_id,
            $marker['name'],
            $_POST['document_type'],
            $_POST['title'],
            $filepath,
            $file['name'],
            $file['size'],
            $file['type'],
            $_SESSION['user_id'],
            $_POST['description'] ?? null
        ]);
        
        $success = "Dokument hochgeladen!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    validateCSRF();
    try {
        $stmt = $pdo->prepare("SELECT * FROM marker_documents WHERE id = ?");
        $stmt->execute([$_POST['document_id']]);
        $doc = $stmt->fetch();
        
        if ($doc && file_exists($doc['document_path'])) unlink($doc['document_path']);
        $pdo->prepare("DELETE FROM marker_documents WHERE id = ?")->execute([$_POST['document_id']]);
        $success = "Dokument gelöscht!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Reguläre Dokumente laden
$documents = $pdo->prepare("
    SELECT d.*, u.username as uploader
    FROM marker_documents d
    LEFT JOIN users u ON d.uploaded_by = u.id
    WHERE d.marker_id = ?
    ORDER BY d.uploaded_at DESC
");
$documents->execute([$marker_id]);
$documents = $documents->fetchAll();

// Wartungs-PDFs laden (nur abgeschlossene Wartungen mit PDF)
$maintenancePdfs = $pdo->prepare("
    SELECT mh.id, mh.maintenance_date, mh.pdf_report_path, mh.notes,
           mc.name as checklist_name,
           u.username as performed_by_name
    FROM maintenance_history mh
    LEFT JOIN maintenance_checklists mc ON mh.checklist_id = mc.id
    LEFT JOIN users u ON mh.performed_by = u.id
    WHERE mh.marker_id = ?
    AND mh.status = 'completed'
    AND mh.pdf_report_path IS NOT NULL
    AND mh.pdf_report_path != ''
    ORDER BY mh.maintenance_date DESC
");
$maintenancePdfs->execute([$marker_id]);
$maintenancePdfs = $maintenancePdfs->fetchAll();

// Wartungsfotos extrahieren
$maintenancePhotos = [];
foreach ($maintenancePdfs as $pdf) {
    // Checklist-Daten sind optional (Spalte existiert möglicherweise nicht in allen DB-Versionen)
    if (isset($pdf['checklist_data']) && !empty($pdf['checklist_data'])) {
        $checklistData = json_decode($pdf['checklist_data'], true);
        if ($checklistData && is_array($checklistData)) {
            foreach ($checklistData as $itemId => $itemData) {
                if (isset($itemData['photo']) && file_exists($itemData['photo'])) {
                    $maintenancePhotos[] = [
                        'path' => $itemData['photo'],
                        'maintenance_id' => $pdf['id'],
                        'maintenance_date' => $pdf['maintenance_date'],
                        'checklist_name' => $pdf['checklist_name'],
                        'performed_by' => $pdf['performed_by_name']
                    ];
                }
            }
        }
    }
}

$totalDocuments = count($documents) + count($maintenancePdfs);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumente - <?= htmlspecialchars($marker['name']) ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .setting-group {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .setting-group h3 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .device-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .device-header h2 {
            margin: 0 0 8px 0;
            color: white;
            font-size: 22px;
        }
        .device-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        /* Tab Navigation */
        .tab-navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s;
        }
        .tab-btn:hover {
            color: var(--primary-color);
        }
        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        
        .document-card {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            align-items: start;
            transition: all 0.3s;
        }
        .document-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .document-card.maintenance-pdf {
            background: linear-gradient(to right, #fff5e6 0%, white 100%);
            border-left: 4px solid #ffc107;
        }
        .document-icon {
            width: 70px;
            height: 70px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .document-icon i {
            font-size: 32px;
            color: #6c757d;
        }
        .document-icon img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 8px;
        }
        .document-info {
            flex: 1;
        }
        .document-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .document-meta {
            font-size: 13px;
            color: #6c757d;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .document-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 8px 12px;
            font-size: 13px;
        }
        
        /* Foto-Galerie */
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .photo-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 10px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .photo-item:hover .photo-overlay {
            opacity: 1;
        }
        
        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.95);
            z-index: 9999;
            padding: 20px;
        }
        .lightbox.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lightbox-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }
        .lightbox-content img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 8px;
        }
        .lightbox-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
        }
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .lightbox-close:hover {
            background: #f8f9fa;
        }
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .lightbox-nav:hover {
            background: #f8f9fa;
        }
        .lightbox-nav.prev {
            left: 20px;
        }
        .lightbox-nav.next {
            right: 20px;
        }
        
        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-dialog {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .modal-header {
            padding: 20px 25px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 25px;
        }
        .close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #6c757d;
            line-height: 1;
        }
        .close:hover {
            color: #000;
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
    <div class="device-header">
        <h2><i class="fas fa-folder-open"></i> Dokumente & Fotos</h2>
        <p><i class="fas fa-box"></i> Gerät: <?= htmlspecialchars($marker['name']) ?> | 
           <i class="fas fa-file"></i> <?= $totalDocuments ?> Dokument<?= $totalDocuments != 1 ? 'e' : '' ?> | 
           <i class="fas fa-images"></i> <?= count($maintenancePhotos) ?> Foto<?= count($maintenancePhotos) != 1 ? 's' : '' ?>
        </p>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="switchTab('maintenance')">
            <i class="fas fa-tools"></i> Wartungsprotokolle (<?= count($maintenancePdfs) ?>)
        </button>
        <button class="tab-btn" onclick="switchTab('photos')">
            <i class="fas fa-images"></i> Foto-Galerie (<?= count($maintenancePhotos) ?>)
        </button>
        <button class="tab-btn" onclick="switchTab('documents')">
            <i class="fas fa-file-alt"></i> Andere Dokumente (<?= count($documents) ?>)
        </button>
    </div>

    <!-- Wartungsprotokolle Tab -->
    <div id="tab-maintenance" class="tab-content active">
        <div class="setting-group">
            <h3><i class="fas fa-clipboard-check"></i> Wartungsprotokolle</h3>
            
            <?php if (empty($maintenancePdfs)): ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-clipboard" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p style="color: #999; margin: 0;">Keine Wartungsprotokolle vorhanden</p>
                </div>
            <?php else: ?>
                <?php foreach ($maintenancePdfs as $pdf): ?>
                    <div class="document-card maintenance-pdf">
                        <div class="document-icon">
                            <i class="fas fa-file-pdf" style="color: #dc3545;"></i>
                        </div>
                        
                        <div class="document-info">
                            <h4 class="document-title">
                                <i class="fas fa-wrench"></i> 
                                Wartungsprotokoll
                                <?php if ($pdf['checklist_name']): ?>
                                    - <?= htmlspecialchars($pdf['checklist_name']) ?>
                                <?php endif; ?>
                            </h4>
                            
                            <div class="document-meta">
                                <?php if ($pdf['performed_by_name']): ?>
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($pdf['performed_by_name']) ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-calendar"></i> <?= date('d.m.Y', strtotime($pdf['maintenance_date'])) ?></span>
                                <?php if (file_exists($pdf['pdf_report_path'])): ?>
                                    <span><i class="fas fa-hdd"></i> <?= round(filesize($pdf['pdf_report_path']) / 1024, 1) ?> KB</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="document-actions">
                            <a href="<?= htmlspecialchars($pdf['pdf_report_path']) ?>" target="_blank" 
                               class="btn btn-sm btn-primary" title="Öffnen">
                                <i class="fas fa-eye"></i> Ansehen
                            </a>
                            <a href="<?= htmlspecialchars($pdf['pdf_report_path']) ?>" download 
                               class="btn btn-sm btn-success" title="Herunterladen">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Foto-Galerie Tab -->
    <div id="tab-photos" class="tab-content">
        <div class="setting-group">
            <h3><i class="fas fa-images"></i> Wartungsfotos Galerie</h3>
            <p style="color: #6c757d; margin-bottom: 20px;">
                Alle Fotos aus abgeschlossenen Wartungen
            </p>
            
            <?php if (empty($maintenancePhotos)): ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-camera" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p style="color: #999; margin: 0;">Keine Wartungsfotos vorhanden</p>
                </div>
            <?php else: ?>
                <div class="photo-gallery">
                    <?php foreach ($maintenancePhotos as $index => $photo): ?>
                        <div class="photo-item" onclick="openLightbox(<?= $index ?>)">
                            <img src="<?= htmlspecialchars($photo['path']) ?>" alt="Wartungsfoto">
                            <div class="photo-overlay">
                                <div><i class="fas fa-calendar"></i> <?= date('d.m.Y', strtotime($photo['maintenance_date'])) ?></div>
                                <?php if ($photo['checklist_name']): ?>
                                    <div><i class="fas fa-clipboard"></i> <?= htmlspecialchars(substr($photo['checklist_name'], 0, 30)) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Andere Dokumente Tab -->
    <div id="tab-documents" class="tab-content">
        <div class="setting-group">
            <h3><i class="fas fa-list"></i> Andere Dokumente</h3>
            
            <div style="margin-bottom: 20px;">
                <button onclick="openModal()" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Dokument hochladen
                </button>
            </div>
            
            <?php if (empty($documents)): ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-folder-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p style="color: #999; margin: 0;">Keine weiteren Dokumente vorhanden</p>
                </div>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <div class="document-card">
                        <div class="document-icon">
                            <?php 
                            $ext = strtolower(pathinfo($doc['document_path'], PATHINFO_EXTENSION));
                            $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                            ?>
                            <?php if (in_array($ext, $imageExts)): ?>
                                <img src="<?= htmlspecialchars($doc['document_path']) ?>" alt="Vorschau">
                            <?php elseif ($ext === 'pdf'): ?>
                                <i class="fas fa-file-pdf" style="color: #dc3545;"></i>
                            <?php elseif (in_array($ext, ['doc', 'docx'])): ?>
                                <i class="fas fa-file-word" style="color: #2b579a;"></i>
                            <?php elseif (in_array($ext, ['xls', 'xlsx'])): ?>
                                <i class="fas fa-file-excel" style="color: #217346;"></i>
                            <?php elseif (in_array($ext, ['mp4', 'avi', 'mov', 'wmv'])): ?>
                                <i class="fas fa-file-video" style="color: #6c757d;"></i>
                            <?php else: ?>
                                <i class="fas fa-file"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="document-info">
                            <h4 class="document-title">
                                <i class="fas fa-file-alt"></i> 
                                <?= htmlspecialchars($doc['document_name']) ?>
                            </h4>
                            
                            <?php if ($doc['public_description']): ?>
                                <p style="margin: 5px 0; color: #6c757d; font-size: 14px;">
                                    <?= htmlspecialchars($doc['public_description']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="document-meta">
                                <span><i class="fas fa-tag"></i> <?= htmlspecialchars($doc['document_type']) ?></span>
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($doc['uploader']) ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('d.m.Y H:i', strtotime($doc['uploaded_at'])) ?></span>
                                <span><i class="fas fa-hdd"></i> <?= round($doc['file_size'] / 1024, 1) ?> KB</span>
                            </div>
                        </div>
                        
                        <div class="document-actions">
                            <a href="<?= htmlspecialchars($doc['document_path']) ?>" target="_blank" 
                               class="btn btn-sm btn-primary" title="Öffnen">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= htmlspecialchars($doc['document_path']) ?>" download 
                               class="btn btn-sm btn-success" title="Herunterladen">
                                <i class="fas fa-download"></i>
                            </a>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Dokument wirklich löschen?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Löschen">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox für Foto-Galerie -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </button>
    <button class="lightbox-nav prev" onclick="navigateLightbox(-1)">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="lightbox-nav next" onclick="navigateLightbox(1)">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <div class="lightbox-content">
        <img id="lightboxImage" src="" alt="Foto">
        <div class="lightbox-info">
            <div id="lightboxInfo"></div>
        </div>
    </div>
</div>

<!-- Modal: Dokument hochladen -->
<div id="uploadModal" class="modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0;"><i class="fas fa-upload"></i> Dokument hochladen</h2>
                <button type="button" class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="upload">
                    
                    <div class="setting-group">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="document_type" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <i class="fas fa-tag"></i> Dokumenttyp *
                            </label>
                            <select id="document_type" name="document_type" required class="form-control" style="width: 100%; padding: 10px; border: 2px solid #dee2e6; border-radius: 6px;">
                                <option value="manual">Anleitung</option>
                                <option value="protocol">Protokoll</option>
                                <option value="photo">Foto</option>
                                <option value="video">Video</option>
                                <option value="certificate">Zertifikat</option>
                                <option value="other">Sonstiges</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="title" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <i class="fas fa-heading"></i> Titel *
                            </label>
                            <input type="text" id="title" name="title" required class="form-control" 
                                   style="width: 100%; padding: 10px; border: 2px solid #dee2e6; border-radius: 6px;"
                                   placeholder="z.B. Bedienungsanleitung">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <i class="fas fa-align-left"></i> Beschreibung
                            </label>
                            <textarea id="description" name="description" class="form-control" rows="3" 
                                      style="width: 100%; padding: 10px; border: 2px solid #dee2e6; border-radius: 6px;"
                                      placeholder="Optionale Beschreibung..."></textarea>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="document" style="display: block; margin-bottom: 8px; font-weight: 600;">
                                <i class="fas fa-file"></i> Datei *
                            </label>
                            <input type="file" id="document" name="document" required class="form-control"
                                   style="width: 100%; padding: 10px; border: 2px solid #dee2e6; border-radius: 6px;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.bmp,.webp,.mp4,.avi,.mov">
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Erlaubte Formate: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF, Videos, etc.
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-actions" style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-upload"></i> Hochladen
                        </button>
                        <button type="button" onclick="closeModal()" class="btn btn-secondary btn-large">
                            <i class="fas fa-times"></i> Abbrechen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Tab Switching
function switchTab(tabName) {
    // Alle Tabs ausblenden
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Gewählten Tab anzeigen
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}

// Modal
function openModal() {
    document.getElementById('uploadModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.id === 'uploadModal') {
        closeModal();
    }
}

// Lightbox für Foto-Galerie
const photos = <?= json_encode($maintenancePhotos) ?>;
let currentPhotoIndex = 0;

function openLightbox(index) {
    currentPhotoIndex = index;
    updateLightbox();
    document.getElementById('lightbox').classList.add('active');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
}

function navigateLightbox(direction) {
    currentPhotoIndex += direction;
    if (currentPhotoIndex < 0) currentPhotoIndex = photos.length - 1;
    if (currentPhotoIndex >= photos.length) currentPhotoIndex = 0;
    updateLightbox();
}

function updateLightbox() {
    const photo = photos[currentPhotoIndex];
    document.getElementById('lightboxImage').src = photo.path;
    
    const date = new Date(photo.maintenance_date);
    const formattedDate = date.toLocaleDateString('de-DE');
    
    document.getElementById('lightboxInfo').innerHTML = `
        <strong>Wartungsfoto ${currentPhotoIndex + 1} / ${photos.length}</strong><br>
        <i class="fas fa-calendar"></i> ${formattedDate} |
        <i class="fas fa-clipboard"></i> ${photo.checklist_name || 'Unbekannt'} |
        <i class="fas fa-user"></i> ${photo.performed_by || 'Unbekannt'}
    `;
}

// Keyboard Navigation für Lightbox
document.addEventListener('keydown', function(e) {
    if (document.getElementById('lightbox').classList.contains('active')) {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    }
});

// Lightbox schließen bei Klick außerhalb
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLightbox();
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>