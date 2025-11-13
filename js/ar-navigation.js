/**
 * AR-Navigation System für Marker - KORRIGIERTE VERSION
 * Zeigt einen 3D-Pfeil mit Entfernungsangabe zum Ziel
 */

class ARNavigation {
    constructor() {
        this.targetLat = null;
        this.targetLng = null;
        this.targetName = '';
        this.currentPosition = null;
        this.heading = 0;
        this.watchId = null;
        this.compassWatchId = null;
        this.isActive = false;
        this.arOverlay = null;
        this.canvas = null;
        this.ctx = null;
        this.debugMode = true; // Aktiviere Debug-Ausgaben
    }

    /**
     * Starte AR-Navigation zu einem Marker
     */
    startNavigation(lat, lng, name) {
        console.log('[AR-Nav] Starte Navigation zu:', name, lat, lng);
        
        if (!lat || !lng) {
            this.showError('Keine GPS-Koordinaten für diesen Marker verfügbar');
            return;
        }

        this.targetLat = parseFloat(lat);
        this.targetLng = parseFloat(lng);
        this.targetName = name;

        // Prüfe ob Geolocation verfügbar ist
        if (!navigator.geolocation) {
            this.showError('GPS wird von Ihrem Gerät nicht unterstützt');
            return;
        }

        // Erstelle AR-Overlay
        this.createAROverlay();
        this.isActive = true;

        // Starte GPS-Tracking
        this.startLocationTracking();
        
        // Starte Kompass (falls verfügbar)
        this.startCompassTracking();
    }

    /**
     * Erstelle das AR-Overlay
     */
    createAROverlay() {
        console.log('[AR-Nav] Erstelle AR-Overlay');
        
        // Erstelle Overlay-Container
        this.arOverlay = document.createElement('div');
        this.arOverlay.id = 'ar-navigation-overlay';
        this.arOverlay.innerHTML = `
            <div class="ar-header">
                <div class="ar-target-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="ar-target-name">${this.targetName}</span>
                </div>
                <button class="ar-close-btn" onclick="arNav.stopNavigation()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ar-canvas-container">
                <canvas id="ar-canvas"></canvas>
                <div class="ar-distance-overlay">
                    <div class="ar-distance-main" id="ar-distance">
                        <span class="distance-value">--</span>
                        <span class="distance-unit">m</span>
                    </div>
                    <div class="ar-compass" id="ar-compass">
                        <i class="fas fa-compass"></i>
                        <span id="ar-bearing">--°</span>
                    </div>
                    <div class="ar-accuracy" id="ar-accuracy">
                        GPS: <span>--</span>m Genauigkeit
                    </div>
                </div>
            </div>
            <div class="ar-instructions">
                <p id="ar-instruction-text">
                    <i class="fas fa-location-arrow"></i> 
                    Warte auf GPS-Position...
                </p>
            </div>
        `;
        
        document.body.appendChild(this.arOverlay);
        console.log('[AR-Nav] Overlay hinzugefügt');

        // Warte kurz, damit das DOM aktualisiert wird
        setTimeout(() => {
            // Canvas initialisieren
            this.canvas = document.getElementById('ar-canvas');
            if (!this.canvas) {
                console.error('[AR-Nav] Canvas nicht gefunden!');
                this.showError('Canvas konnte nicht initialisiert werden');
                return;
            }
            
            this.ctx = this.canvas.getContext('2d');
            if (!this.ctx) {
                console.error('[AR-Nav] Canvas Context konnte nicht erstellt werden!');
                this.showError('Canvas Context Fehler');
                return;
            }
            
            console.log('[AR-Nav] Canvas erfolgreich initialisiert:', this.canvas.width, 'x', this.canvas.height);
            
            // Canvas-Größe anpassen
            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());

            // Animation starten
            this.animate();
            
            // Zeichne initialen Zustand (zeigt, dass Canvas funktioniert)
            this.drawInitialState();
        }, 100);
    }

    /**
     * Zeichne initialen Zustand (Platzhalter bis GPS-Daten verfügbar sind)
     */
    drawInitialState() {
        if (!this.ctx || !this.canvas) return;
        
        const ctx = this.ctx;
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        
        // Lösche Canvas
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Zeichne Kompass-Ring
        this.drawCompassRing(ctx, centerX, centerY);
        
        // Zeichne Platzhalter-Text
        ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
        ctx.font = 'bold 20px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Warte auf GPS...', centerX, centerY);
        
        console.log('[AR-Nav] Initialer Zustand gezeichnet');
    }

    /**
     * Canvas-Größe anpassen
     */
    resizeCanvas() {
        if (!this.canvas) return;
        
        const container = this.canvas.parentElement;
        const oldWidth = this.canvas.width;
        const oldHeight = this.canvas.height;
        
        this.canvas.width = container.clientWidth;
        this.canvas.height = container.clientHeight;
        
        console.log('[AR-Nav] Canvas resized:', oldWidth, 'x', oldHeight, '->', this.canvas.width, 'x', this.canvas.height);
    }

    /**
     * Starte GPS-Tracking
     */
    startLocationTracking() {
        console.log('[AR-Nav] Starte GPS-Tracking');
        
        const options = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                console.log('[AR-Nav] GPS-Position erhalten:', position.coords.latitude, position.coords.longitude);
                
                this.currentPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    heading: position.coords.heading
                };
                
                this.updateDisplay();
            },
            (error) => {
                console.error('[AR-Nav] GPS-Fehler:', error);
                this.showError(this.getGeolocationErrorMessage(error));
            },
            options
        );
    }

    /**
     * Starte Kompass-Tracking (für Geräte mit Kompass)
     */
    startCompassTracking() {
        console.log('[AR-Nav] Starte Kompass-Tracking');
        
        if (window.DeviceOrientationEvent) {
            // iOS 13+ erfordert Berechtigung
            if (typeof DeviceOrientationEvent.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission()
                    .then(permissionState => {
                        if (permissionState === 'granted') {
                            console.log('[AR-Nav] Kompass-Berechtigung erteilt');
                            window.addEventListener('deviceorientation', (e) => this.handleOrientation(e));
                        } else {
                            console.log('[AR-Nav] Kompass-Berechtigung verweigert');
                        }
                    })
                    .catch(err => {
                        console.error('[AR-Nav] Kompass-Berechtigung Fehler:', err);
                    });
            } else {
                // Nicht-iOS oder ältere iOS-Versionen
                window.addEventListener('deviceorientation', (e) => this.handleOrientation(e));
            }
        } else {
            console.log('[AR-Nav] DeviceOrientation nicht verfügbar');
        }
    }

    /**
     * Handle Device Orientation
     */
    handleOrientation(event) {
        if (event.alpha !== null) {
            // Alpha ist die Kompass-Richtung
            this.heading = event.alpha;
        } else if (event.webkitCompassHeading !== undefined) {
            // Webkit (iOS)
            this.heading = event.webkitCompassHeading;
        }
    }

    /**
     * Berechne Distanz zwischen zwei GPS-Koordinaten (Haversine-Formel)
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Erdradius in Metern
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c; // Distanz in Metern
    }

    /**
     * Berechne Peilung (Bearing) zum Ziel
     */
    calculateBearing(lat1, lon1, lat2, lon2) {
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const y = Math.sin(Δλ) * Math.cos(φ2);
        const x = Math.cos(φ1) * Math.sin(φ2) -
                  Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
        
        let bearing = Math.atan2(y, x) * 180 / Math.PI;
        
        // Normalisiere auf 0-360
        return (bearing + 360) % 360;
    }

    /**
     * Aktualisiere Anzeige
     */
    updateDisplay() {
        if (!this.currentPosition) {
            console.log('[AR-Nav] updateDisplay: Keine currentPosition');
            return;
        }

        const distance = this.calculateDistance(
            this.currentPosition.lat,
            this.currentPosition.lng,
            this.targetLat,
            this.targetLng
        );

        const bearing = this.calculateBearing(
            this.currentPosition.lat,
            this.currentPosition.lng,
            this.targetLat,
            this.targetLng
        );

        console.log('[AR-Nav] Distanz:', distance.toFixed(2), 'm, Bearing:', bearing.toFixed(2), '°');

        // Aktualisiere UI
        this.updateDistanceDisplay(distance);
        this.updateBearingDisplay(bearing);
        this.updateAccuracyDisplay(this.currentPosition.accuracy);
        this.updateInstructions(distance);
    }

    /**
     * Aktualisiere Entfernungsanzeige
     */
    updateDistanceDisplay(distance) {
        const distanceElement = document.querySelector('#ar-distance .distance-value');
        const unitElement = document.querySelector('#ar-distance .distance-unit');
        
        if (!distanceElement || !unitElement) {
            console.error('[AR-Nav] Distanz-Elemente nicht gefunden');
            return;
        }
        
        if (distance < 1000) {
            distanceElement.textContent = Math.round(distance);
            unitElement.textContent = 'm';
        } else {
            distanceElement.textContent = (distance / 1000).toFixed(1);
            unitElement.textContent = 'km';
        }

        // Ändere Farbe basierend auf Entfernung
        const distanceMain = document.getElementById('ar-distance');
        if (distance < 10) {
            distanceMain.style.backgroundColor = '#28a745'; // Grün - sehr nah
        } else if (distance < 50) {
            distanceMain.style.backgroundColor = '#17a2b8'; // Blau - nah
        } else if (distance < 200) {
            distanceMain.style.backgroundColor = '#ffc107'; // Gelb - mittel
        } else {
            distanceMain.style.backgroundColor = '#e63312'; // Rot - weit
        }
    }

    /**
     * Aktualisiere Peilungsanzeige
     */
    updateBearingDisplay(bearing) {
        const bearingElement = document.getElementById('ar-bearing');
        if (bearingElement) {
            bearingElement.textContent = Math.round(bearing) + '°';
        }
    }

    /**
     * Aktualisiere GPS-Genauigkeitsanzeige
     */
    updateAccuracyDisplay(accuracy) {
        const accuracyElement = document.querySelector('#ar-accuracy span');
        if (accuracyElement) {
            accuracyElement.textContent = Math.round(accuracy);
        }
    }

    /**
     * Aktualisiere Anweisungen
     */
    updateInstructions(distance) {
        const instructionText = document.getElementById('ar-instruction-text');
        if (!instructionText) return;
        
        if (distance < 5) {
            instructionText.innerHTML = '<i class="fas fa-check-circle"></i> Sie haben Ihr Ziel erreicht!';
            instructionText.style.color = '#28a745';
            
            // Optional: Vibration bei Ankunft (falls unterstützt)
            if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200]);
            }
        } else if (distance < 20) {
            instructionText.innerHTML = '<i class="fas fa-map-marker-alt"></i> Ziel ist in Ihrer Nähe';
            instructionText.style.color = '#17a2b8';
        } else if (distance < 100) {
            instructionText.innerHTML = '<i class="fas fa-location-arrow"></i> Folgen Sie dem Pfeil';
            instructionText.style.color = '#ffc107';
        } else {
            instructionText.innerHTML = '<i class="fas fa-location-arrow"></i> Folgen Sie dem Pfeil zu Ihrem Ziel';
            instructionText.style.color = '#e63312';
        }
    }

    /**
     * Zeichne 3D-Pfeil auf Canvas
     */
    drawArrow() {
        if (!this.ctx || !this.canvas) {
            console.log('[AR-Nav] drawArrow: Canvas/Context nicht verfügbar');
            return;
        }
        
        if (!this.currentPosition) {
            console.log('[AR-Nav] drawArrow: Keine currentPosition');
            return;
        }

        const canvas = this.canvas;
        const ctx = this.ctx;
        
        // Canvas löschen
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Berechne Richtung zum Ziel
        const bearing = this.calculateBearing(
            this.currentPosition.lat,
            this.currentPosition.lng,
            this.targetLat,
            this.targetLng
        );

        // Berechne relativen Winkel (Bearing minus Device Heading)
        let relativeAngle = bearing - this.heading;
        if (relativeAngle < 0) relativeAngle += 360;
        if (relativeAngle > 360) relativeAngle -= 360;

        // Canvas-Zentrum
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;

        // Zeichne Kompass-Ring
        this.drawCompassRing(ctx, centerX, centerY);

        // Zeichne 3D-Pfeil
        this.draw3DArrow(ctx, centerX, centerY, relativeAngle);

        // Zeichne Himmelsrichtungen
        this.drawCardinalDirections(ctx, centerX, centerY);
    }

    /**
     * Zeichne Kompass-Ring
     */
    drawCompassRing(ctx, centerX, centerY) {
        const radius = Math.min(centerX, centerY) * 0.7;

        // Äußerer Ring
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Innerer Ring
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius * 0.9, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Grad-Markierungen
        for (let i = 0; i < 360; i += 30) {
            const angle = (i - 90) * Math.PI / 180;
            const startRadius = i % 90 === 0 ? radius * 0.85 : radius * 0.9;
            const endRadius = radius;

            const x1 = centerX + startRadius * Math.cos(angle);
            const y1 = centerY + startRadius * Math.sin(angle);
            const x2 = centerX + endRadius * Math.cos(angle);
            const y2 = centerY + endRadius * Math.sin(angle);

            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.lineWidth = i % 90 === 0 ? 3 : 1;
            ctx.stroke();
        }
    }


    /**
     * Zeichne Kompassnadel die zum Ziel zeigt
     */
    drawCompassNeedle(ctx, centerX, centerY, angle) {
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate((angle - 90) * Math.PI / 180);

        // Nadel-Schatten
        ctx.shadowColor = 'rgba(0, 0, 0, 0.6)';
        ctx.shadowBlur = 20;
        ctx.shadowOffsetX = 4;
        ctx.shadowOffsetY = 4;

        // Norden (rot) - zeigt zum Ziel
        ctx.beginPath();
        ctx.moveTo(0, -120);
        ctx.lineTo(-15, 25);
        ctx.lineTo(0, 12);
        ctx.lineTo(15, 25);
        ctx.closePath();
        
        const northGradient = ctx.createLinearGradient(0, -120, 0, 25);
        northGradient.addColorStop(0, '#ff3333');
        northGradient.addColorStop(0.5, '#ff6666');
        northGradient.addColorStop(1, '#cc0000');
        ctx.fillStyle = northGradient;
        ctx.fill();

        // Kontur für Norden
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.6)';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        // Süden (weiß/grau)
        ctx.beginPath();
        ctx.moveTo(0, 120);
        ctx.lineTo(-15, -25);
        ctx.lineTo(0, -12);
        ctx.lineTo(15, -25);
        ctx.closePath();
        
        const southGradient = ctx.createLinearGradient(0, -25, 0, 120);
        southGradient.addColorStop(0, '#f0f0f0');
        southGradient.addColorStop(0.5, '#c0c0c0');
        southGradient.addColorStop(1, '#909090');
        ctx.fillStyle = southGradient;
        ctx.fill();
        
        // Kontur für Süden
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.4)';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        // Zentrum-Kreis (äußerer Ring)
        ctx.shadowColor = 'transparent';
        ctx.beginPath();
        ctx.arc(0, 0, 22, 0, 2 * Math.PI);
        ctx.fillStyle = '#ffffff';
        ctx.fill();
        ctx.strokeStyle = '#333333';
        ctx.lineWidth = 2.5;
        ctx.stroke();

        // Innerer Kreis (rot)
        ctx.beginPath();
        ctx.arc(0, 0, 10, 0, 2 * Math.PI);
        const centerGradient = ctx.createRadialGradient(0, 0, 0, 0, 0, 10);
        centerGradient.addColorStop(0, '#ff8888');
        centerGradient.addColorStop(0.6, '#ff4444');
        centerGradient.addColorStop(1, '#cc0000');
        ctx.fillStyle = centerGradient;
        ctx.fill();

        // Glanz-Effekt auf Nadel (Norden)
        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.beginPath();
        ctx.moveTo(-8, -100);
        ctx.lineTo(-10, -30);
        ctx.lineTo(-5, -30);
        ctx.lineTo(-4, -100);
        ctx.closePath();
        ctx.fill();

        // Buchstaben N, E, S, W am Rand
        ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
        ctx.shadowBlur = 3;
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#ffffff';
        ctx.fillText('N', 0, -140);
        ctx.fillText('S', 0, 140);
        ctx.rotate(Math.PI / 2);
        ctx.fillText('E', 0, -140);
        ctx.rotate(Math.PI);
        ctx.fillText('W', 0, -140);

        ctx.restore();
    }

    /**
     * Zeichne 3D-Pfeil
     */
    draw3DArrow(ctx, centerX, centerY, angle) {
        // Zeichne zuerst die Kompassnadel im Hintergrund
        this.drawCompassNeedle(ctx, centerX, centerY, angle);
        const distance = this.calculateDistance(
            this.currentPosition.lat,
            this.currentPosition.lng,
            this.targetLat,
            this.targetLng
        );

        // Pfeilgröße basierend auf Entfernung
        let arrowLength = 80;
        if (distance < 50) arrowLength = 100;
        if (distance < 20) arrowLength = 120;

        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate((angle - 90) * Math.PI / 180);

        // Schatten für 3D-Effekt
        ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
        ctx.shadowBlur = 15;
        ctx.shadowOffsetX = 5;
        ctx.shadowOffsetY = 5;

        // Pfeil-Körper (Rechteck mit Gradient)
        const gradient = ctx.createLinearGradient(0, -15, 0, 15);
        gradient.addColorStop(0, '#ff6b6b');
        gradient.addColorStop(0.5, '#e63312');
        gradient.addColorStop(1, '#cc2a0a');

        ctx.fillStyle = gradient;
        ctx.fillRect(-10, -arrowLength / 2, 20, arrowLength * 0.6);

        // Pfeil-Spitze (Dreieck)
        ctx.beginPath();
        ctx.moveTo(0, -arrowLength / 2 - 40); // Spitze
        ctx.lineTo(-25, -arrowLength / 2 + 20); // Links
        ctx.lineTo(25, -arrowLength / 2 + 20); // Rechts
        ctx.closePath();
        
        const tipGradient = ctx.createLinearGradient(0, -arrowLength / 2 - 40, 0, -arrowLength / 2 + 20);
        tipGradient.addColorStop(0, '#ff8888');
        tipGradient.addColorStop(1, '#e63312');
        ctx.fillStyle = tipGradient;
        ctx.fill();

        // Glanz-Effekt
        ctx.shadowColor = 'transparent';
        ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.beginPath();
        ctx.moveTo(0, -arrowLength / 2 - 30);
        ctx.lineTo(-15, -arrowLength / 2 + 10);
        ctx.lineTo(-8, -arrowLength / 2 + 10);
        ctx.lineTo(-5, -arrowLength / 2 - 30);
        ctx.closePath();
        ctx.fill();

        // Kontur für bessere Sichtbarkeit
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(0, -arrowLength / 2 - 40);
        ctx.lineTo(-25, -arrowLength / 2 + 20);
        ctx.lineTo(-10, -arrowLength / 2 + 20);
        ctx.lineTo(-10, arrowLength * 0.1);
        ctx.lineTo(10, arrowLength * 0.1);
        ctx.lineTo(10, -arrowLength / 2 + 20);
        ctx.lineTo(25, -arrowLength / 2 + 20);
        ctx.closePath();
        ctx.stroke();

        ctx.restore();

        // Pulsierender Effekt bei naher Entfernung
        if (distance < 50) {
            const pulse = Math.sin(Date.now() / 200) * 0.1 + 0.9;
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.globalAlpha = 0.3 * pulse;
            ctx.beginPath();
            ctx.arc(0, 0, 150 * pulse, 0, 2 * Math.PI);
            ctx.fillStyle = '#e63312';
            ctx.fill();
            ctx.restore();
        }
    }

    /**
     * Zeichne Himmelsrichtungen
     */
    drawCardinalDirections(ctx, centerX, centerY) {
        const radius = Math.min(centerX, centerY) * 0.7;
        const directions = [
            { angle: 0, label: 'N', color: '#ff4444' },
            { angle: 90, label: 'O', color: '#ffffff' },
            { angle: 180, label: 'S', color: '#ffffff' },
            { angle: 270, label: 'W', color: '#ffffff' }
        ];

        ctx.font = 'bold 18px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        directions.forEach(dir => {
            const angle = (dir.angle - this.heading - 90) * Math.PI / 180;
            const x = centerX + radius * 1.15 * Math.cos(angle);
            const y = centerY + radius * 1.15 * Math.sin(angle);

            // Schatten für Text
            ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
            ctx.shadowBlur = 5;
            
            ctx.fillStyle = dir.color;
            ctx.fillText(dir.label, x, y);
        });
        
        ctx.shadowColor = 'transparent';
    }

    /**
     * Animations-Loop
     */
    animate() {
        if (!this.isActive) {
            console.log('[AR-Nav] Animation gestoppt (nicht aktiv)');
            return;
        }

        this.drawArrow();
        requestAnimationFrame(() => this.animate());
    }

    /**
     * Stoppe Navigation
     */
    stopNavigation() {
        console.log('[AR-Nav] Stoppe Navigation');
        this.isActive = false;

        // Stoppe GPS-Tracking
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }

        // Entferne Event Listener
        window.removeEventListener('deviceorientation', this.handleOrientation);

        // Entferne Overlay
        if (this.arOverlay) {
            this.arOverlay.remove();
            this.arOverlay = null;
        }

        this.currentPosition = null;
        this.heading = 0;
        this.canvas = null;
        this.ctx = null;
    }

    /**
     * Zeige Fehlermeldung
     */
    showError(message) {
        console.error('[AR-Nav] Fehler:', message);
        alert('AR-Navigation Fehler:\n' + message);
    }

    /**
     * Geolocation-Fehlermeldung
     */
    getGeolocationErrorMessage(error) {
        switch (error.code) {
            case error.PERMISSION_DENIED:
                return 'GPS-Zugriff verweigert. Bitte erlauben Sie den Standortzugriff in den Einstellungen.';
            case error.POSITION_UNAVAILABLE:
                return 'GPS-Position nicht verfügbar. Bitte prüfen Sie Ihre GPS-Einstellungen.';
            case error.TIMEOUT:
                return 'GPS-Anfrage hat zu lange gedauert. Bitte versuchen Sie es erneut.';
            default:
                return 'Ein unbekannter GPS-Fehler ist aufgetreten.';
        }
    }
}

// Globale Instanz erstellen
const arNav = new ARNavigation();