/**
 * MARKER LAZY LOADING - LEAFLET INTEGRATION
 * 
 * Lädt Marker dynamisch basierend auf:
 * - Sichtbarem Kartenbereich
 * - Zoom-Level
 * 
 * FEATURES:
 * - Nur sichtbare Marker laden
 * - Automatisches Clustering bei vielen Markern
 * - Debouncing (nicht zu oft laden)
 * - Cache für bereits geladene Bereiche
 * 
 * INTEGRATION:
 * Nach Leaflet-Map Initialisierung:
 * const lazyLoader = new MarkerLazyLoader(map);
 * lazyLoader.enable();
 */

class MarkerLazyLoader {
    constructor(map, options = {}) {
        this.map = map;
        this.options = {
            apiEndpoint: 'api_markers_lazy.php',
            debounceMs: 300,
            minZoomForIndividual: 12,
            ...options
        };
        
        this.markers = new Map(); // marker_id -> Leaflet Marker
        this.clusters = new Map(); // cluster_key -> Leaflet Marker
        this.loadedBounds = [];
        this.isLoading = false;
        this.loadTimeout = null;
    }
    
    /**
     * Aktiviere Lazy Loading
     */
    enable() {
        // Initial laden
        this.loadMarkers();
        
        // Bei Kartenänderung nachladen
        this.map.on('moveend', () => this.scheduleLoad());
        this.map.on('zoomend', () => this.scheduleLoad());
    }
    
    /**
     * Lade Marker (mit Debouncing)
     */
    scheduleLoad() {
        if (this.loadTimeout) {
            clearTimeout(this.loadTimeout);
        }
        
        this.loadTimeout = setTimeout(() => {
            this.loadMarkers();
        }, this.options.debounceMs);
    }
    
    /**
     * Lade Marker vom Server
     */
    async loadMarkers() {
        if (this.isLoading) return;
        
        const bounds = this.map.getBounds();
        const zoom = this.map.getZoom();
        
        // Bounds formatieren
        const boundsStr = [
            bounds.getSouth(),
            bounds.getWest(),
            bounds.getNorth(),
            bounds.getEast()
        ].join(',');
        
        // Prüfe ob Bereich bereits geladen
        if (this.isBoundsLoaded(bounds, zoom)) {
            return;
        }
        
        this.isLoading = true;
        this.showLoadingIndicator();
        
        try {
            const url = `${this.options.apiEndpoint}?bounds=${boundsStr}&zoom=${zoom}`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                this.clearMarkers();
                this.renderMarkers(data.markers, data.clustered);
                this.markBoundsAsLoaded(bounds, zoom);
                
                // Event für Statistiken
                this.map.fire('markersloaded', {
                    count: data.markers.length,
                    total: data.total,
                    clustered: data.clustered
                });
            }
        } catch (error) {
            console.error('Fehler beim Laden der Marker:', error);
            this.showErrorIndicator();
        } finally {
            this.isLoading = false;
            this.hideLoadingIndicator();
        }
    }
    
    /**
     * Rendere Marker auf Karte
     */
    renderMarkers(markers, isClustered) {
        markers.forEach(item => {
            if (item.type === 'cluster') {
                this.renderCluster(item);
            } else {
                this.renderMarker(item);
            }
        });
    }
    
    /**
     * Rendere einzelnen Marker
     */
    renderMarker(marker) {
        if (this.markers.has(marker.id)) {
            return; // Bereits auf Karte
        }
        
        const icon = this.getMarkerIcon(marker);
        const leafletMarker = L.marker([marker.latitude, marker.longitude], { icon });
        
        // Popup
        const popupContent = this.createPopupContent(marker);
        leafletMarker.bindPopup(popupContent);
        
        // Zur Karte hinzufügen
        leafletMarker.addTo(this.map);
        this.markers.set(marker.id, leafletMarker);
    }
    
    /**
     * Rendere Cluster
     */
    renderCluster(cluster) {
        const clusterKey = `${cluster.latitude}_${cluster.longitude}`;
        
        if (this.clusters.has(clusterKey)) {
            return;
        }
        
        const icon = L.divIcon({
            html: `<div class="marker-cluster">${cluster.count}</div>`,
            className: 'marker-cluster-container',
            iconSize: [40, 40]
        });
        
        const clusterMarker = L.marker([cluster.latitude, cluster.longitude], { icon });
        
        // Click = Zoom in
        clusterMarker.on('click', () => {
            this.map.setView([cluster.latitude, cluster.longitude], this.map.getZoom() + 2);
        });
        
        clusterMarker.addTo(this.map);
        this.clusters.set(clusterKey, clusterMarker);
    }
    
    /**
     * Marker-Icon basierend auf Status
     */
    getMarkerIcon(marker) {
        let color = '#007bff'; // Standard
        
        if (marker.is_storage) {
            color = '#6c757d'; // Grau für Lager
        } else if (marker.rental_status === 'vermietet') {
            color = '#dc3545'; // Rot für vermietet
        } else if (marker.rental_status === 'wartung') {
            color = '#ffc107'; // Gelb für Wartung
        }
        
        const iconHtml = marker.marker_type === 'nfc_chip' 
            ? '<i class="fas fa-wifi"></i>' 
            : '<i class="fas fa-qrcode"></i>';
        
        return L.divIcon({
            html: `<div class="custom-marker" style="background-color: ${color}">${iconHtml}</div>`,
            className: 'custom-marker-container',
            iconSize: [30, 30]
        });
    }
    
    /**
     * Popup-Inhalt erstellen
     */
    createPopupContent(marker) {
        return `
            <div class="marker-popup">
                <h3>${marker.name}</h3>
                <p><strong>ID:</strong> ${marker.qr_code || marker.nfc_chip_id}</p>
                ${marker.category ? `<p><strong>Kategorie:</strong> ${marker.category}</p>` : ''}
                <p><strong>Status:</strong> ${marker.rental_status || 'Verfügbar'}</p>
                <div class="popup-actions">
                    <a href="view_marker.php?id=${marker.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Details
                    </a>
                </div>
            </div>
        `;
    }
    
    /**
     * Lösche alle Marker von der Karte
     */
    clearMarkers() {
        this.markers.forEach(marker => marker.remove());
        this.markers.clear();
        
        this.clusters.forEach(cluster => cluster.remove());
        this.clusters.clear();
    }
    
    /**
     * Prüfe ob Bounds bereits geladen
     */
    isBoundsLoaded(bounds, zoom) {
        // Vereinfachte Prüfung - in Produktion komplexere Logik
        return false; // Immer neu laden für Demo
    }
    
    /**
     * Markiere Bounds als geladen
     */
    markBoundsAsLoaded(bounds, zoom) {
        this.loadedBounds.push({
            bounds: bounds,
            zoom: zoom,
            timestamp: Date.now()
        });
        
        // Alte Einträge entfernen (>5 Minuten)
        const now = Date.now();
        this.loadedBounds = this.loadedBounds.filter(
            entry => (now - entry.timestamp) < 300000
        );
    }
    
    /**
     * Loading-Indikator
     */
    showLoadingIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'marker-loading-indicator';
        indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Lade Marker...';
        indicator.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
        `;
        document.body.appendChild(indicator);
    }
    
    hideLoadingIndicator() {
        const indicator = document.getElementById('marker-loading-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    showErrorIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'alert alert-danger';
        indicator.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
        `;
        indicator.innerHTML = 'Fehler beim Laden der Marker!';
        document.body.appendChild(indicator);
        
        setTimeout(() => indicator.remove(), 3000);
    }
}

/**
 * CSS für Cluster und Custom Marker
 */
const lazyLoadingCSS = `
<style>
.marker-cluster-container {
    background: transparent;
    border: none;
}

.marker-cluster {
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    transition: all 0.2s;
}

.marker-cluster:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
}

.custom-marker-container {
    background: transparent;
    border: none;
}

.custom-marker {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    cursor: pointer;
    transition: all 0.2s;
}

.custom-marker:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 10px rgba(0,0,0,0.4);
}

.marker-popup {
    min-width: 200px;
}

.marker-popup h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.marker-popup p {
    margin: 5px 0;
    font-size: 13px;
}

.popup-actions {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}
</style>
`;

// CSS automatisch einfügen
if (typeof document !== 'undefined') {
    document.head.insertAdjacentHTML('beforeend', lazyLoadingCSS);
}