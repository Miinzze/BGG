// ===================================================================
// HOCHPRÄZISE GPS-ERFASSUNG mit mehrfachen Messungen
// ===================================================================
// Version: 2.0 - Optimiert für maximale Genauigkeit
// Features:
// - Mehrfache Messungen (bis zu 30 Samples)
// - Gewichteter Durchschnitt basierend auf Genauigkeit
// - Echtzeit-Fortschrittsanzeige
// - Qualitätsfilterung (nur Messungen < 50m)
// - Zielgenauigkeit: < 5 Meter
// ===================================================================

class GPSHelper {
    constructor() {
        this.currentPosition = null;
        this.watchId = null;
        this.positions = [];
        this.isHighPrecisionMode = false;
    }
    
    // ===============================================================
    // STANDARD-MODUS: Schnelle einzelne Messung
    // ===============================================================
    getCurrentPosition(successCallback, errorCallback) {
        if (!navigator.geolocation) {
            if (errorCallback) errorCallback('Geolocation wird nicht unterstützt');
            return;
        }
        
        const options = {
            enableHighAccuracy: true,
            timeout: 30000, // 30 Sekunden (erhöht von 10s)
            maximumAge: 0
        };
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.currentPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    altitude: position.coords.altitude,
                    altitudeAccuracy: position.coords.altitudeAccuracy,
                    heading: position.coords.heading,
                    speed: position.coords.speed,
                    timestamp: position.timestamp
                };
                if (successCallback) successCallback(this.currentPosition);
            },
            (error) => {
                let message = 'GPS-Fehler: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message += 'Zugriff verweigert. Bitte erlauben Sie den Standortzugriff.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += 'Position nicht verfügbar. Bitte gehen Sie ins Freie.';
                        break;
                    case error.TIMEOUT:
                        message += 'Zeitüberschreitung. GPS-Signal zu schwach.';
                        break;
                    default:
                        message += 'Unbekannter Fehler.';
                }
                if (errorCallback) errorCallback(message);
            },
            options
        );
    }
    
    // ===============================================================
    // HOCHPRÄZISIONS-MODUS: Mehrfache Messungen für beste Genauigkeit
    // ===============================================================
    // successCallback: wird mit finaler Position aufgerufen
    // errorCallback: wird bei Fehler aufgerufen
    // progressCallback: wird bei jeder Messung mit Fortschritt aufgerufen
    getHighPrecisionPosition(successCallback, errorCallback, progressCallback) {
        if (!navigator.geolocation) {
            if (errorCallback) errorCallback('Geolocation wird nicht unterstützt');
            return;
        }
        
        this.isHighPrecisionMode = true;
        this.positions = [];
        
        const maxSamples = 30;          // 30 Messungen für höchste Genauigkeit
        const minSamples = 5;           // Mindestens 5 Messungen
        const maxTime = 60000;          // 60 Sekunden Maximum
        const targetAccuracy = 5;       // Ziel: unter 5 Meter
        const maxAccuracyThreshold = 50; // Nur Messungen < 50m speichern
        
        let sampleCount = 0;
        const startTime = Date.now();
        
        const options = {
            enableHighAccuracy: true,
            timeout: 60000,     // 60 Sekunden für hochpräzise Messung
            maximumAge: 0       // Keine zwischengespeicherten Positionen
        };
        
        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                sampleCount++;
                const accuracy = position.coords.accuracy;
                
                // Nur Messungen mit guter Qualität speichern
                if (accuracy <= maxAccuracyThreshold) {
                    this.positions.push({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: accuracy,
                        altitude: position.coords.altitude,
                        altitudeAccuracy: position.coords.altitudeAccuracy,
                        heading: position.coords.heading,
                        speed: position.coords.speed,
                        timestamp: position.timestamp
                    });
                }
                
                // Fortschritt melden
                if (progressCallback) {
                    const elapsed = Date.now() - startTime;
                    const bestAccuracy = this.positions.length > 0 
                        ? Math.min(...this.positions.map(p => p.accuracy))
                        : accuracy;
                    
                    const avgAccuracy = this.positions.length > 0
                        ? this.positions.reduce((sum, p) => sum + p.accuracy, 0) / this.positions.length
                        : accuracy;
                    
                    progressCallback({
                        samples: this.positions.length,
                        totalAttempts: sampleCount,
                        currentAccuracy: accuracy,
                        bestAccuracy: bestAccuracy,
                        avgAccuracy: avgAccuracy,
                        elapsed: elapsed,
                        progress: Math.min(100, (this.positions.length / maxSamples) * 100),
                        qualityPercent: this.positions.length > 0 ? (this.positions.length / sampleCount) * 100 : 0
                    });
                }
                
                // Stopp-Bedingungen
                const elapsed = Date.now() - startTime;
                const hasEnoughSamples = this.positions.length >= maxSamples;
                const hasGoodAccuracy = this.positions.length >= minSamples && accuracy < targetAccuracy;
                const timeExpired = elapsed >= maxTime;
                const hasMinimumData = this.positions.length >= minSamples && elapsed >= 15000; // Min 15s
                
                if (hasEnoughSamples || hasGoodAccuracy || timeExpired || hasMinimumData) {
                    this.stopWatching();
                    
                    if (this.positions.length === 0) {
                        if (errorCallback) {
                            errorCallback('Keine ausreichend genauen GPS-Daten erhalten. Bitte versuchen Sie es draußen mit freier Sicht zum Himmel.');
                        }
                        return;
                    }
                    
                    if (this.positions.length < minSamples) {
                        if (errorCallback) {
                            errorCallback(`Zu wenige Messungen (${this.positions.length}/${minSamples}). Bitte versuchen Sie es draußen.`);
                        }
                        return;
                    }
                    
                    // Beste Position berechnen
                    const result = this.calculateBestPosition();
                    this.currentPosition = result;
                    
                    if (successCallback) successCallback(result);
                }
            },
            (error) => {
                this.stopWatching();
                let message = 'GPS-Fehler: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message += 'Zugriff verweigert. Bitte erlauben Sie den Standortzugriff in den Einstellungen.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += 'Position nicht verfügbar. Bitte gehen Sie ins Freie mit freier Sicht zum Himmel.';
                        break;
                    case error.TIMEOUT:
                        message += 'Zeitüberschreitung. GPS-Signal zu schwach. Versuchen Sie es draußen.';
                        break;
                    default:
                        message += 'Unbekannter Fehler.';
                }
                if (errorCallback) errorCallback(message);
            },
            options
        );
    }
    
    // ===============================================================
    // Berechne beste Position aus mehreren Messungen
    // Verwendet gewichteten Durchschnitt basierend auf Genauigkeit
    // ===============================================================
    calculateBestPosition() {
        if (this.positions.length === 0) return null;
        
        // Gewichteter Durchschnitt basierend auf Genauigkeit
        // Je genauer die Messung, desto höher das Gewicht
        let totalWeightedLat = 0;
        let totalWeightedLng = 0;
        let totalWeightedAlt = 0;
        let totalWeight = 0;
        let bestAccuracy = Infinity;
        let bestPosition = null;
        
        this.positions.forEach(pos => {
            // Gewicht = 1 / (accuracy^2)
            // Bessere Genauigkeit = höheres Gewicht
            const weight = 1 / (pos.accuracy * pos.accuracy);
            
            totalWeightedLat += pos.lat * weight;
            totalWeightedLng += pos.lng * weight;
            if (pos.altitude !== null) {
                totalWeightedAlt += pos.altitude * weight;
            }
            totalWeight += weight;
            
            if (pos.accuracy < bestAccuracy) {
                bestAccuracy = pos.accuracy;
                bestPosition = pos;
            }
        });
        
        const avgLat = totalWeightedLat / totalWeight;
        const avgLng = totalWeightedLng / totalWeight;
        const avgAlt = totalWeightedAlt / totalWeight;
        
        // Berechne Standardabweichung für Genauigkeitsschätzung
        let sumSquaredDiff = 0;
        this.positions.forEach(pos => {
            const latDiff = pos.lat - avgLat;
            const lngDiff = pos.lng - avgLng;
            const distance = Math.sqrt(latDiff * latDiff + lngDiff * lngDiff) * 111000; // in Meter
            sumSquaredDiff += distance * distance;
        });
        const stdDev = Math.sqrt(sumSquaredDiff / this.positions.length);
        
        // Geschätzte Genauigkeit: Besser als beste Einzelmessung durch Durchschnittsbildung
        // Theoretisch verbessert sich die Genauigkeit mit Faktor 1/sqrt(n)
        const improvementFactor = 1 / Math.sqrt(this.positions.length);
        const estimatedAccuracy = Math.max(
            bestAccuracy * improvementFactor,
            stdDev,
            3 // Minimum 3 Meter (realistisch für GPS)
        );
        
        // Qualitätsscore (0-100)
        const qualityScore = Math.min(100, Math.round(
            (this.positions.length / 30) * 40 +           // 40% für Anzahl Messungen
            (Math.max(0, 50 - bestAccuracy) / 50) * 40 +  // 40% für beste Genauigkeit
            (Math.max(0, 30 - stdDev) / 30) * 20          // 20% für Konsistenz
        ));
        
        return {
            lat: avgLat,
            lng: avgLng,
            altitude: avgAlt || null,
            accuracy: estimatedAccuracy,
            samples: this.positions.length,
            bestAccuracy: bestAccuracy,
            avgAccuracy: this.positions.reduce((sum, p) => sum + p.accuracy, 0) / this.positions.length,
            stdDev: stdDev,
            qualityScore: qualityScore,
            method: 'weighted_average',
            timestamp: Date.now()
        };
    }
    
    // ===============================================================
    // Position kontinuierlich verfolgen (Standard-Modus)
    // ===============================================================
    watchPosition(updateCallback, errorCallback) {
        if (!navigator.geolocation) {
            if (errorCallback) errorCallback('Geolocation wird nicht unterstützt');
            return;
        }
        
        const options = {
            enableHighAccuracy: true,
            timeout: 30000,  // Erhöht von 5s auf 30s
            maximumAge: 0
        };
        
        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                this.currentPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    altitude: position.coords.altitude,
                    altitudeAccuracy: position.coords.altitudeAccuracy,
                    heading: position.coords.heading,
                    speed: position.coords.speed,
                    timestamp: position.timestamp
                };
                if (updateCallback) updateCallback(this.currentPosition);
            },
            (error) => {
                let message = 'GPS-Fehler: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message += 'Zugriff verweigert.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += 'Position nicht verfügbar.';
                        break;
                    case error.TIMEOUT:
                        message += 'Zeitüberschreitung.';
                        break;
                }
                if (errorCallback) errorCallback(message);
            },
            options
        );
    }
    
    // Position-Tracking stoppen
    stopWatching() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
    }
    
    // Position in Formular eintragen
    fillFormFields(latFieldId, lngFieldId) {
        if (this.currentPosition) {
            document.getElementById(latFieldId).value = this.currentPosition.lat;
            document.getElementById(lngFieldId).value = this.currentPosition.lng;
            return true;
        }
        return false;
    }
    
    // GPS-Status anzeigen
    showStatus(elementId, message, type = 'info') {
        const element = document.getElementById(elementId);
        if (element) {
            const colors = {
                'success': '#28a745',
                'error': '#dc3545',
                'info': '#007bff',
                'warning': '#ffc107'
            };
            
            element.innerHTML = `
                <div style="padding: 10px; background: ${colors[type]}20; border-left: 4px solid ${colors[type]}; border-radius: 5px; margin: 10px 0;">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    ${message}
                </div>
            `;
        }
    }
    
    // Genauigkeitsbewertung
    getAccuracyRating(accuracy) {
        if (accuracy <= 5) return { rating: 'Excellent', color: '#28a745', icon: 'fa-star' };
        if (accuracy <= 10) return { rating: 'Sehr gut', color: '#5cb85c', icon: 'fa-check-circle' };
        if (accuracy <= 20) return { rating: 'Gut', color: '#5bc0de', icon: 'fa-check' };
        if (accuracy <= 50) return { rating: 'Ausreichend', color: '#f0ad4e', icon: 'fa-exclamation-triangle' };
        return { rating: 'Schlecht', color: '#d9534f', icon: 'fa-times-circle' };
    }
}

// ===================================================================
// Kamera-Integration (unverändert)
// ===================================================================
class CameraHelper {
    constructor() {
        this.stream = null;
        this.videoElement = null;
    }
    
    async startCamera(videoElementId, errorCallback) {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Kamera-API wird nicht unterstützt');
            }
            
            this.videoElement = document.getElementById(videoElementId);
            if (!this.videoElement) {
                throw new Error('Video-Element nicht gefunden');
            }
            
            const constraints = {
                video: {
                    facingMode: 'environment',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            };
            
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.videoElement.srcObject = this.stream;
            this.videoElement.play();
            
            return true;
        } catch (error) {
            if (errorCallback) {
                errorCallback('Kamera-Fehler: ' + error.message);
            }
            return false;
        }
    }
    
    capturePhoto(canvasElementId) {
        if (!this.videoElement || !this.stream) {
            return null;
        }
        
        const canvas = document.getElementById(canvasElementId);
        if (!canvas) {
            return null;
        }
        
        canvas.width = this.videoElement.videoWidth;
        canvas.height = this.videoElement.videoHeight;
        
        const context = canvas.getContext('2d');
        context.drawImage(this.videoElement, 0, 0);
        
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                resolve(blob);
            }, 'image/jpeg', 0.85);
        });
    }
    
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        if (this.videoElement) {
            this.videoElement.srcObject = null;
        }
    }
    
    static isAvailable() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }
}

// File-Helper bleibt unverändert... (aus Platzgründen gekürzt, ist in der originalen Datei enthalten)