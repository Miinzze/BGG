/**
 * PASSWORT-STÄRKE-ANZEIGE IN ECHTZEIT
 * Inkl. Hinweis auf Passwort-Wiederverwendung
 */

function initPasswordStrengthMeter() {
    // Suche nach verschiedenen Passwort-Input-Feldern
    const newPasswordInput = document.getElementById('new_password') || document.getElementById('password');
    if (!newPasswordInput) return;
    
    // Strength-Meter HTML erstellen
    const strengthMeterHTML = `
        <div id="password-strength-meter" style="margin-top: 10px;">
            <div style="display: flex; gap: 4px; margin-bottom: 8px;">
                <div class="strength-bar" data-level="1"></div>
                <div class="strength-bar" data-level="2"></div>
                <div class="strength-bar" data-level="3"></div>
                <div class="strength-bar" data-level="4"></div>
            </div>
            <div id="strength-text" style="font-size: 14px; font-weight: 600;"></div>
            <div id="strength-requirements" style="margin-top: 10px; font-size: 13px;"></div>
        </div>
        
        <style>
            .strength-bar {
                height: 8px;
                flex: 1;
                background: #e0e0e0;
                border-radius: 4px;
                transition: background 0.3s;
            }
            
            .strength-bar.active-weak {
                background: #f44336;
            }
            
            .strength-bar.active-medium {
                background: #ff9800;
            }
            
            .strength-bar.active-strong {
                background: #4caf50;
            }
            
            #strength-text.weak {
                color: #f44336;
            }
            
            #strength-text.medium {
                color: #ff9800;
            }
            
            #strength-text.strong {
                color: #4caf50;
            }
            
            .requirement {
                padding: 4px 0;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .requirement.met {
                color: #4caf50;
            }
            
            .requirement.met i {
                color: #4caf50;
            }
            
            .requirement.unmet {
                color: #999;
            }
            
            .requirement.unmet i {
                color: #999;
            }
        </style>
    `;
    
    // Meter nach Input-Feld einfügen (nach dem Parent-Element Small-Tag wenn vorhanden)
    const parentFormGroup = newPasswordInput.closest('.form-group');
    if (parentFormGroup) {
        // Füge nach dem letzten Element in der form-group ein
        parentFormGroup.insertAdjacentHTML('beforeend', strengthMeterHTML);
    } else {
        // Fallback: Nach dem Input-Feld
        newPasswordInput.insertAdjacentHTML('afterend', strengthMeterHTML);
    }
    
    // Event-Listener für Echtzeit-Updates
    newPasswordInput.addEventListener('input', function() {
        updatePasswordStrength(this.value);
    });
    
    // Initial leeren State anzeigen
    updatePasswordStrength('');
}

function updatePasswordStrength(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Score berechnen
    let score = 0;
    if (requirements.length) score++;
    if (requirements.uppercase) score++;
    if (requirements.lowercase) score++;
    if (requirements.number) score++;
    if (requirements.special) score += 2;
    if (password.length >= 12) score++;
    
    // Stärke bestimmen
    let strength, strengthText, strengthClass;
    if (password.length === 0) {
        strength = 0;
        strengthText = '';
        strengthClass = '';
    } else if (score <= 3) {
        strength = 1;
        strengthText = '🔴 Schwach - Nicht sicher genug';
        strengthClass = 'weak';
    } else if (score <= 5) {
        strength = 2;
        strengthText = '🟡 Mittel - Akzeptabel';
        strengthClass = 'medium';
    } else {
        strength = 3;
        strengthText = '🟢 Stark - Sehr sicher';
        strengthClass = 'strong';
    }
    
    // Strength-Bars aktualisieren
    const bars = document.querySelectorAll('.strength-bar');
    bars.forEach((bar, index) => {
        bar.classList.remove('active-weak', 'active-medium', 'active-strong');
        if (index < strength) {
            if (strengthClass === 'weak') {
                bar.classList.add('active-weak');
            } else if (strengthClass === 'medium') {
                bar.classList.add('active-medium');
            } else {
                bar.classList.add('active-strong');
            }
        }
    });
    
    // Text aktualisieren
    const strengthTextEl = document.getElementById('strength-text');
    if (strengthTextEl) {
        strengthTextEl.textContent = strengthText;
        strengthTextEl.className = strengthClass;
    }
    
    // Requirements-Liste aktualisieren
    const requirementsHTML = `
        <div class="requirement ${requirements.length ? 'met' : 'unmet'}">
            <i class="fas ${requirements.length ? 'fa-check-circle' : 'fa-circle'}"></i>
            <span>Mindestens 8 Zeichen</span>
        </div>
        <div class="requirement ${requirements.uppercase ? 'met' : 'unmet'}">
            <i class="fas ${requirements.uppercase ? 'fa-check-circle' : 'fa-circle'}"></i>
            <span>Mindestens 1 Großbuchstabe</span>
        </div>
        <div class="requirement ${requirements.lowercase ? 'met' : 'unmet'}">
            <i class="fas ${requirements.lowercase ? 'fa-check-circle' : 'fa-circle'}"></i>
            <span>Mindestens 1 Kleinbuchstabe</span>
        </div>
        <div class="requirement ${requirements.number ? 'met' : 'unmet'}">
            <i class="fas ${requirements.number ? 'fa-check-circle' : 'fa-circle'}"></i>
            <span>Mindestens 1 Zahl</span>
        </div>
        <div class="requirement ${requirements.special ? 'met' : 'unmet'}">
            <i class="fas ${requirements.special ? 'fa-check-circle' : 'fa-circle'}"></i>
            <span>Sonderzeichen (Bonus für höhere Sicherheit)</span>
        </div>
    `;
    
    const requirementsEl = document.getElementById('strength-requirements');
    if (requirementsEl) {
        requirementsEl.innerHTML = requirementsHTML;
    }
}

// Auto-Init beim Laden
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPasswordStrengthMeter);
} else {
    initPasswordStrengthMeter();
}