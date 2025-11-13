<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

header('Content-Type: application/json');

$markerName = $_GET['marker_name'] ?? '';
$markerId = intval($_GET['marker_id'] ?? 0);

if (empty($markerName) && $markerId === 0) {
    echo json_encode(['success' => false, 'error' => 'Kein Marker angegeben']);
    exit;
}

try {
    // Wenn marker_id vorhanden ist, danach filtern, sonst nach Name
    if ($markerId > 0) {
        // Mit marker_id filtern (funktioniert auch bei endgültig gelöschten Markern)
        $query = "
            SELECT 
                md.id,
                md.marker_id,
                md.marker_name,
                md.document_name,
                md.document_type,
                md.document_path,
                md.file_name,
                md.mime_type,
                md.file_size,
                md.uploaded_at,
                u.username as uploader,
                md.uploaded_by,
                md.public_description as description,
                'document' as source_type
            FROM marker_documents md
            LEFT JOIN users u ON md.uploaded_by = u.id
            WHERE md.marker_id = ?
            
            UNION ALL
            
            -- Wartungs-PDFs laden (auch von endgültig gelöschten Markern)
            SELECT 
                mh.id,
                mh.marker_id,
                COALESCE(
                    (SELECT marker_name FROM marker_documents WHERE marker_id = mh.marker_id LIMIT 1),
                    m.name,
                    CONCAT('Gelöschter Marker (ID: ', mh.marker_id, ')')
                ) as marker_name,
                CONCAT('Wartungsprotokoll: ', COALESCE(mc.name, 'Unbekannt')) as document_name,
                'protocol' as document_type,
                mh.pdf_report_path as document_path,
                SUBSTRING_INDEX(mh.pdf_report_path, '/', -1) as file_name,
                'application/pdf' as mime_type,
                10485760 as file_size,
                mh.maintenance_date as uploaded_at,
                u.username as uploader,
                mh.performed_by as uploaded_by,
                NULL as description,
                'maintenance' as source_type
            FROM maintenance_history mh
            LEFT JOIN markers m ON mh.marker_id = m.id
            LEFT JOIN maintenance_checklists mc ON mh.checklist_id = mc.id
            LEFT JOIN users u ON mh.performed_by = u.id
            WHERE mh.marker_id = ?
              AND mh.pdf_report_path IS NOT NULL 
              AND mh.pdf_report_path != ''
              AND mh.status = 'completed'
            
            ORDER BY uploaded_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$markerId, $markerId]);
    } else {
        // Fallback: Mit marker_name filtern (für alte Kompatibilität)
        $query = "
            SELECT 
                md.id,
                md.marker_id,
                md.marker_name,
                md.document_name,
                md.document_type,
                md.document_path,
                md.file_name,
                md.mime_type,
                md.file_size,
                md.uploaded_at,
                u.username as uploader,
                md.uploaded_by,
                md.public_description as description,
                'document' as source_type
            FROM marker_documents md
            LEFT JOIN users u ON md.uploaded_by = u.id
            WHERE md.marker_name = ?
            
            UNION ALL
            
            -- Wartungs-PDFs laden
            SELECT 
                mh.id,
                mh.marker_id,
                COALESCE(m.name, 'Gelöschter Marker') as marker_name,
                CONCAT('Wartungsprotokoll: ', COALESCE(mc.name, 'Unbekannt')) as document_name,
                'protocol' as document_type,
                mh.pdf_report_path as document_path,
                SUBSTRING_INDEX(mh.pdf_report_path, '/', -1) as file_name,
                'application/pdf' as mime_type,
                10485760 as file_size,
                mh.maintenance_date as uploaded_at,
                u.username as uploader,
                mh.performed_by as uploaded_by,
                NULL as description,
                'maintenance' as source_type
            FROM maintenance_history mh
            LEFT JOIN markers m ON mh.marker_id = m.id
            LEFT JOIN maintenance_checklists mc ON mh.checklist_id = mc.id
            LEFT JOIN users u ON mh.performed_by = u.id
            WHERE COALESCE(m.name, 'Gelöschter Marker') = ?
              AND mh.pdf_report_path IS NOT NULL 
              AND mh.pdf_report_path != ''
              AND mh.status = 'completed'
            
            ORDER BY uploaded_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$markerName, $markerName]);
    }
    
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'documents' => $documents
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}