<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// CSRF-Token validieren
validateCSRF();

$bugId = intval($_GET['id'] ?? 0);

if ($bugId <= 0) {
    $_SESSION['message'] = 'Ungültige Ticket-ID';
    $_SESSION['messageType'] = 'danger';
    header('Location: my_bug_tickets.php');
    exit;
}

try {
    // Ticket laden und prüfen ob es dem Benutzer gehört
    $stmt = $pdo->prepare("
        SELECT br.*, u.email as user_email 
        FROM bug_reports br
        JOIN users u ON u.id = ?
        WHERE br.id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $bugId]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        $_SESSION['message'] = 'Ticket nicht gefunden';
        $_SESSION['messageType'] = 'danger';
        header('Location: my_bug_tickets.php');
        exit;
    }
    
    // Prüfen ob das Ticket dem Benutzer gehört
    if ($ticket['email'] !== $ticket['user_email']) {
        $_SESSION['message'] = 'Sie können nur Ihre eigenen Tickets löschen';
        $_SESSION['messageType'] = 'danger';
        header('Location: my_bug_tickets.php');
        exit;
    }
    
    // Warnung bei Tickets in Bearbeitung
    if ($ticket['status'] === 'in_bearbeitung' && !isset($_GET['confirm'])) {
        ?>
        <!DOCTYPE html>
        <html lang="de">
        <head>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/ar-navigation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ticket löschen - Bestätigung</title>
            <link rel="stylesheet" href="<?= minify_css('css/style.css') ?>">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        </head>
        <body>
            <?php include 'header.php'; ?>
            
            <div class="main-container">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h1><i class="fas fa-exclamation-triangle"></i> Ticket löschen</h1>
                    </div>
                    
                    <div class="alert alert-warning" style="padding: 30px;">
                        <h2 style="margin-top: 0;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Achtung: Ticket wird gerade bearbeitet!
                        </h2>
                        
                        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
                            <h3><?= e($ticket['title']) ?></h3>
                            <p style="color: #666; margin: 10px 0;">
                                <strong>Status:</strong> <span class="badge badge-warning">In Bearbeitung</span><br>
                                <strong>Priorität:</strong> <?= ucfirst($ticket['priority']) ?><br>
                                <strong>Erstellt:</strong> <?= formatDateTime($ticket['created_at']) ?>
                            </p>
                            
                            <?php if ($ticket['notes']): ?>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px;">
                                <strong><i class="fas fa-comment"></i> Admin-Notizen:</strong>
                                <p style="margin: 10px 0 0 0;"><?= nl2br(e($ticket['notes'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.6;">
                            <strong>Dieses Ticket wird gerade von einem Administrator bearbeitet.</strong>
                            <br><br>
                            Möchten Sie es wirklich löschen? Der Administrator kann dann nicht mehr auf das Ticket zugreifen.
                            <br><br>
                            <em>Hinweis: Normalerweise sollten Sie Tickets in Bearbeitung nicht löschen.</em>
                        </p>
                        
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <a href="delete_bug.php?id=<?= $bugId ?>&confirm=1&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                               class="btn btn-danger btn-large">
                                <i class="fas fa-trash"></i> Ja, trotzdem löschen
                            </a>
                            <a href="my_bug_tickets.php" class="btn btn-secondary btn-large">
                                <i class="fas fa-arrow-left"></i> Abbrechen
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'footer.php'; ?>
        </body>
        </html>
        <?php
        exit;
    }
    
    // Screenshot löschen falls vorhanden
    if (!empty($ticket['screenshot_path']) && file_exists($ticket['screenshot_path'])) {
        @unlink($ticket['screenshot_path']);
    }
    
    // Ticket-Kommentare löschen (falls vorhanden)
    $stmt = $pdo->prepare("DELETE FROM bug_comments WHERE bug_id = ?");
    $stmt->execute([$bugId]);
    
    // Ticket endgültig löschen
    $stmt = $pdo->prepare("DELETE FROM bug_reports WHERE id = ?");
    $stmt->execute([$bugId]);
    
    // Aktivitätslog
    logActivity('bug_deleted', "Bug-Ticket #$bugId '{$ticket['title']}' gelöscht");
    
    $_SESSION['message'] = 'Ticket erfolgreich gelöscht';
    $_SESSION['messageType'] = 'success';
    
} catch (Exception $e) {
    error_log('Delete Bug Error: ' . $e->getMessage());
    $_SESSION['message'] = 'Fehler beim Löschen: ' . e($e->getMessage());
    $_SESSION['messageType'] = 'danger';
}

header('Location: my_bug_tickets.php');
exit;