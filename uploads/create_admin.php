<?php
// create_admin.php - Führen Sie diese Datei einmalig aus, um den Admin-Benutzer zu erstellen
// Danach löschen Sie diese Datei aus Sicherheitsgründen!

require_once 'config.php';

// Passwort hashen
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Generierter Hash: " . $hashedPassword . "<br><br>";

try {
    // Zuerst prüfen ob Admin bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        // Admin existiert, Passwort aktualisieren
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hashedPassword]);
        echo "✅ Admin-Passwort wurde aktualisiert!<br>";
    } else {
        // Admin erstellen
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $hashedPassword, 'admin']);
        echo "✅ Admin-Benutzer wurde erstellt!<br>";
    }
    
    echo "<br><strong>Login-Daten:</strong><br>";
    echo "Benutzername: admin<br>";
    echo "Passwort: admin123<br><br>";
    echo "⚠️ <strong>WICHTIG:</strong> Löschen Sie diese Datei (create_admin.php) jetzt aus Sicherheitsgründen!<br><br>";
    echo "<a href='login.php' style='display: inline-block; padding: 10px 20px; background: #e63312; color: white; text-decoration: none; border-radius: 5px;'>Zum Login</a>";
    
} catch (PDOException $e) {
    echo "❌ Fehler: " . $e->getMessage();
}
?>