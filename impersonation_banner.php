<?php
// impersonation_banner.php - Zeigt Banner wenn Admin als anderer User angemeldet ist
// Include this in header.php

if (isset($_SESSION['impersonation_active']) && $_SESSION['impersonation_active']):
?>
<div style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); color: white; padding: 15px; text-align: center; position: fixed; top: 0; left: 0; right: 0; z-index: 10001; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-secret" style="font-size: 20px;"></i>
            <span style="font-weight: bold;">
                Admin-Modus: Sie sind als "<?= e($_SESSION['username']) ?>" angemeldet
            </span>
            <span style="background: rgba(0,0,0,0.2); padding: 5px 10px; border-radius: 5px; font-size: 0.9em;">
                Original-Admin: <?= e($_SESSION['impersonation_original_username']) ?>
            </span>
        </div>
        <a href="stop_impersonation.php" style="background: white; color: #ef4444; padding: 8px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 5px;">
            <i class="fas fa-sign-out-alt"></i> Admin-Modus beenden
        </a>
    </div>
</div>
<div style="height: 60px;"></div><!-- Spacer -->
<?php endif; ?>
