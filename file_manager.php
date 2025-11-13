<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$pageTitle = 'Dateiverwaltung - Marker Dokumente & Wartungsprotokolle';

// Alle Marker mit ihren Dokumenten UND Wartungs-PDFs laden
// WICHTIG: Zeigt auch Dokumente von endgültig gelöschten Markern!
$query = "
    SELECT 
        marker_name,
        marker_id,
        SUM(document_count) as document_count,
        SUM(total_size) as total_size,
        MAX(last_upload) as last_upload
    FROM (
        -- Normale Dokumente (auch von gelöschten Markern)
        (SELECT 
            md.marker_name,
            md.marker_id,
            COUNT(md.id) as document_count,
            SUM(md.file_size) as total_size,
            MAX(md.uploaded_at) as last_upload
        FROM marker_documents md
        GROUP BY md.marker_name, md.marker_id)
        
        UNION ALL
        
        -- Wartungs-PDFs (auch von gelöschten Markern)
        (SELECT 
            IF(m.name IS NOT NULL, m.name, CONCAT('Marker ', mh.marker_id)) as marker_name,
            mh.marker_id,
            COUNT(mh.id) as document_count,
            SUM(10485760) as total_size,
            MAX(mh.maintenance_date) as last_upload
        FROM maintenance_history mh
        LEFT JOIN markers m ON mh.marker_id = m.id
        WHERE mh.pdf_report_path IS NOT NULL 
          AND mh.pdf_report_path != ''
          AND mh.status = 'completed'
        GROUP BY IF(m.name IS NOT NULL, m.name, CONCAT('Marker ', mh.marker_id)), mh.marker_id)
    ) as combined_docs
    GROUP BY marker_name, marker_id
    HAVING document_count > 0
    ORDER BY marker_name ASC
";

$stmt = $pdo->query($query);
$markerFolders = $stmt->fetchAll();

function formatBytes($bytes, $precision = 2) {
    if ($bytes == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function getFileIcon($mimeType, $ext) {
    // Bilder
    if (strpos($mimeType, 'image/') === 0) return 'fa-image';
    
    // Videos
    if (strpos($mimeType, 'video/') === 0) return 'fa-video';
    
    // PDFs
    if ($mimeType === 'application/pdf' || $ext === 'pdf') return 'fa-file-pdf';
    
    // Word
    if (in_array($ext, ['doc', 'docx']) || strpos($mimeType, 'word') !== false) return 'fa-file-word';
    
    // Excel
    if (in_array($ext, ['xls', 'xlsx']) || strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false) return 'fa-file-excel';
    
    // PowerPoint
    if (in_array($ext, ['ppt', 'pptx']) || strpos($mimeType, 'powerpoint') !== false || strpos($mimeType, 'presentation') !== false) return 'fa-file-powerpoint';
    
    // Archive
    if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) return 'fa-file-zipper';
    
    // Text
    if (strpos($mimeType, 'text/') === 0 || $ext === 'txt') return 'fa-file-lines';
    
    return 'fa-file';
}

function getDocumentTypeLabel($type) {
    $types = [
        'manual' => 'Anleitung',
        'protocol' => 'Protokoll',
        'photo' => 'Foto',
        'video' => 'Video',
        'certificate' => 'Zertifikat',
        'other' => 'Sonstiges'
    ];
    return $types[$type] ?? 'Sonstiges';
}

function isImageFile($mimeType) {
    return strpos($mimeType, 'image/') === 0;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .folder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .folder-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .folder-card:hover {
            border-color: #667eea;
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        
        .folder-icon {
            font-size: 64px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .folder-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            word-break: break-word;
        }
        
        .folder-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }
        
        .folder-stat {
            text-align: center;
        }
        
        .folder-stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }
        
        .folder-stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 3px;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-dialog {
            background-color: white;
            margin: 3% auto;
            width: 90%;
            max-width: 1200px;
            border-radius: 12px;
            animation: slideDown 0.3s;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            padding: 25px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px 12px 0 0;
            color: white;
        }
        
        .modal-header h2 {
            margin: 0;
            color: white;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 32px;
            cursor: pointer;
            color: white;
            opacity: 0.9;
            transition: opacity 0.2s;
            line-height: 1;
            padding: 0;
            width: 40px;
            height: 40px;
        }
        
        .modal-close:hover {
            opacity: 1;
        }
        
        .modal-body {
            padding: 25px;
            overflow-y: auto;
            flex: 1;
        }
        
        .document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .document-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .document-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .document-preview {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        
        .document-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .document-preview i {
            font-size: 64px;
            color: #667eea;
        }
        
        .document-info {
            padding: 15px;
        }
        
        .document-title {
            font-weight: 600;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: #2c3e50;
        }
        
        .document-meta {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .document-type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 10px;
            background: #e9ecef;
            color: #495057;
        }
        
        .document-actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        .stats-overview {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-box {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            color: white;
        }
        
        .stat-box i {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .no-documents {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-documents i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-folder"></i> Dateiverwaltung - Marker Dokumente & Wartungsprotokolle</h1>
                <p>Alle hochgeladenen Dokumente und Wartungs-PDFs nach Markern sortiert</p>
            </div>
            
            <!-- Statistiken -->
            <div class="stats-overview">
                <div class="stats-grid">
                    <div class="stat-box">
                        <i class="fas fa-folder"></i>
                        <div class="stat-value"><?= count($markerFolders) ?></div>
                        <div class="stat-label">Marker mit Dateien</div>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-file"></i>
                        <div class="stat-value"><?= array_sum(array_column($markerFolders, 'document_count')) ?></div>
                        <div class="stat-label">Dokumente & Protokolle</div>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-hdd"></i>
                        <div class="stat-value"><?= formatBytes(array_sum(array_column($markerFolders, 'total_size'))) ?></div>
                        <div class="stat-label">Speicherplatz</div>
                    </div>
                </div>
            </div>
            
            <!-- Suche -->
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Marker durchsuchen...">
            </div>
            
            <!-- Ordner-Ansicht -->
            <?php if (empty($markerFolders)): ?>
                <div class="no-documents">
                    <i class="fas fa-folder-open"></i>
                    <h3>Keine Dokumente vorhanden</h3>
                    <p>Es wurden noch keine Dokumente zu Markern hochgeladen.</p>
                </div>
            <?php else: ?>
                <div class="folder-grid" id="folderGrid">
                    <?php foreach ($markerFolders as $folder): ?>
                        <div class="folder-card" 
                             data-marker-name="<?= htmlspecialchars($folder['marker_name']) ?>"
                             onclick="openMarkerFolder('<?= htmlspecialchars($folder['marker_name'], ENT_QUOTES) ?>', <?= $folder['marker_id'] ?>)">
                            <div class="folder-icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div class="folder-name"><?= htmlspecialchars($folder['marker_name']) ?></div>
                            <div class="folder-stats">
                                <div class="folder-stat">
                                    <div class="folder-stat-value"><?= $folder['document_count'] ?></div>
                                    <div class="folder-stat-label">Dateien</div>
                                </div>
                                <div class="folder-stat">
                                    <div class="folder-stat-value"><?= formatBytes($folder['total_size']) ?></div>
                                    <div class="folder-stat-label">Größe</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal für Ordner-Inhalt -->
    <div id="folderModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-folder-open"></i> Lade...</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalContent">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #667eea;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Suche
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const folders = document.querySelectorAll('.folder-card');
        
        folders.forEach(folder => {
            const markerName = folder.getAttribute('data-marker-name').toLowerCase();
            if (markerName.includes(searchTerm)) {
                folder.style.display = 'block';
            } else {
                folder.style.display = 'none';
            }
        });
    });
    
    function openMarkerFolder(markerName, markerId) {
        const modal = document.getElementById('folderModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        
        modal.style.display = 'block';
        modalTitle.innerHTML = '<i class="fas fa-folder-open"></i> ' + markerName;
        
        // Lade Dokumente via AJAX - mit marker_id für bessere Identifikation
        fetch('ajax_get_marker_documents.php?marker_name=' + encodeURIComponent(markerName) + '&marker_id=' + markerId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDocuments(data.documents, markerName, markerId);
                } else {
                    modalContent.innerHTML = '<div class="no-documents"><i class="fas fa-exclamation-circle"></i><p>Fehler: ' + (data.error || 'Unbekannter Fehler') + '</p></div>';
                }
            })
            .catch(error => {
                modalContent.innerHTML = '<div class="no-documents"><i class="fas fa-exclamation-circle"></i><p>Fehler beim Laden: ' + error + '</p></div>';
            });
    }
    
    function displayDocuments(documents, markerName, markerId) {
        const modalContent = document.getElementById('modalContent');
        
        if (documents.length === 0) {
            modalContent.innerHTML = '<div class="no-documents"><i class="fas fa-folder-open"></i><p>Keine Dokumente in diesem Ordner</p></div>';
            return;
        }
        
        let html = '<div class="document-grid">';
        
        documents.forEach(doc => {
            const ext = doc.file_name ? doc.file_name.split('.').pop().toLowerCase() : '';
            const isImage = doc.mime_type && doc.mime_type.startsWith('image/');
            const fileIcon = getFileIcon(doc.mime_type, ext);
            const isMaintenance = doc.source_type === 'maintenance';
            
            html += `
                <div class="document-card">
                    <div class="document-preview">
                        ${isImage ? 
                            '<img src="' + doc.document_path + '" alt="Vorschau">' :
                            '<i class="fas ' + fileIcon + '"></i>'
                        }
                    </div>
                    <div class="document-info">
                        ${isMaintenance ? 
                            '<div class="document-type-badge" style="background: #28a745; color: white;"><i class="fas fa-clipboard-check"></i> Wartungsprotokoll</div>' :
                            '<div class="document-type-badge">' + getDocumentTypeLabel(doc.document_type) + '</div>'
                        }
                        <div class="document-title">${doc.document_name}</div>
                        ${doc.file_name ? '<div class="document-meta"><i class="fas fa-file"></i> ' + doc.file_name + '</div>' : ''}
                        <div class="document-meta"><i class="fas fa-weight"></i> ${formatBytes(doc.file_size)}</div>
                        <div class="document-meta"><i class="fas fa-calendar"></i> ${formatDate(doc.uploaded_at)}</div>
                        ${doc.uploader ? '<div class="document-meta"><i class="fas fa-user"></i> ' + doc.uploader + '</div>' : ''}
                        <div class="document-actions">
                            <a href="${doc.document_path}" target="_blank" class="btn btn-sm btn-primary" style="flex: 1;">
                                <i class="fas fa-eye"></i> Ansehen
                            </a>
                            <a href="${doc.document_path}" download class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
                
        modalContent.innerHTML = html;
    }
    
    function getFileIcon(mimeType, ext) {
        if (!mimeType) mimeType = '';
        if (!ext) ext = '';
        
        if (mimeType.startsWith('image/')) return 'fa-image';
        if (mimeType.startsWith('video/')) return 'fa-video';
        if (mimeType === 'application/pdf' || ext === 'pdf') return 'fa-file-pdf';
        if (['doc', 'docx'].includes(ext) || mimeType.includes('word')) return 'fa-file-word';
        if (['xls', 'xlsx'].includes(ext) || mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fa-file-excel';
        if (['ppt', 'pptx'].includes(ext) || mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'fa-file-powerpoint';
        if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return 'fa-file-zipper';
        if (mimeType.startsWith('text/') || ext === 'txt') return 'fa-file-lines';
        
        return 'fa-file';
    }
    
    function getDocumentTypeLabel(type) {
        const types = {
            'manual': 'Anleitung',
            'protocol': 'Protokoll',
            'photo': 'Foto',
            'video': 'Video',
            'certificate': 'Zertifikat',
            'other': 'Sonstiges'
        };
        return types[type] || 'Sonstiges';
    }
    
    function formatBytes(bytes) {
        if (bytes === 0 || !bytes) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        while (bytes >= 1024 && i < units.length - 1) {
            bytes /= 1024;
            i++;
        }
        return Math.round(bytes * 100) / 100 + ' ' + units[i];
    }
    
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('de-DE') + ' ' + date.toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'});
    }
    
    function closeModal() {
        document.getElementById('folderModal').style.display = 'none';
    }
    
    // Modal schließen bei Klick außerhalb
    window.onclick = function(event) {
        const modal = document.getElementById('folderModal');
        if (event.target === modal) {
            closeModal();
        }
    }
    
    // ESC-Taste zum Schließen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>