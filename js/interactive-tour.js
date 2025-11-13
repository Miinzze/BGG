/**
 * Interactive Tour System
 */
class InteractiveTour {
    constructor() {
        this.currentStep = 0;
        this.steps = [];
        this.overlay = null;
        this.tooltip = null;
        this.isActive = false;
    }
    
    defineSteps(steps) { this.steps = steps; }
    
    start() {
        if (this.isActive || this.steps.length === 0) return;
        this.isActive = true;
        this.currentStep = 0;
        this.createOverlay();
        this.showStep(0);
    }
    
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9998;display:none;';
        document.body.appendChild(this.overlay);
        
        this.tooltip = document.createElement('div');
        this.tooltip.style.cssText = 'position:fixed;background:white;border-radius:10px;padding:20px;max-width:400px;box-shadow:0 10px 30px rgba(0,0,0,0.3);z-index:9999;display:none;';
        document.body.appendChild(this.tooltip);
    }
    
    showStep(stepIndex) {
        if (stepIndex < 0 || stepIndex >= this.steps.length) { this.end(); return; }
        const step = this.steps[stepIndex];
        const element = document.querySelector(step.element);
        if (!element) { this.next(); return; }
        
        this.overlay.style.display = 'block';
        element.style.position = 'relative';
        element.style.zIndex = '10000';
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        const rect = element.getBoundingClientRect();
        this.tooltip.innerHTML = `
            <div style="margin-bottom:15px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <span style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:5px 15px;border-radius:20px;font-size:12px;font-weight:bold;">Schritt ${stepIndex+1} von ${this.steps.length}</span>
                    <button onclick="interactiveTour.skip()" style="background:none;border:none;font-size:20px;cursor:pointer;">×</button>
                </div>
                <h3 style="margin:0 0 10px 0;">${step.title}</h3>
                <p style="margin:0;color:#666;">${step.description}</p>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <button onclick="interactiveTour.skip()" style="padding:8px 20px;border:1px solid #dee2e6;background:white;border-radius:5px;cursor:pointer;">Überspringen</button>
                <div>
                    ${stepIndex>0?'<button onclick="interactiveTour.previous()" style="padding:8px 20px;border:1px solid #667eea;background:white;color:#667eea;border-radius:5px;cursor:pointer;margin-right:10px;">Zurück</button>':''}
                    <button onclick="interactiveTour.next()" style="padding:8px 20px;border:none;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border-radius:5px;cursor:pointer;">${stepIndex===this.steps.length-1?'Fertig':'Weiter'}</button>
                </div>
            </div>
        `;
        
        let top = rect.bottom + 20;
        let left = rect.left + (rect.width/2) - 200;
        if (left < 10) left = 10;
        if (left + 400 > window.innerWidth - 10) left = window.innerWidth - 410;
        if (top + 200 > window.innerHeight - 10) top = rect.top - 220;
        
        this.tooltip.style.top = top + 'px';
        this.tooltip.style.left = left + 'px';
        this.tooltip.style.display = 'block';
        this.currentStep = stepIndex;
    }
    
    next() { this.resetStyles(); this.currentStep < this.steps.length - 1 ? this.showStep(this.currentStep + 1) : this.end(); }
    previous() { this.resetStyles(); if (this.currentStep > 0) this.showStep(this.currentStep - 1); }
    skip() { if (confirm('Tour überspringen?')) this.end(false); }
    
    end(completed = true) {
        this.resetStyles();
        if (this.overlay) this.overlay.style.display = 'none';
        if (this.tooltip) this.tooltip.style.display = 'none';
        this.isActive = false;
        if (completed) fetch('mark_tour_completed.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ completed: true }) });
    }
    
    resetStyles() {
        if (this.currentStep >= 0 && this.currentStep < this.steps.length) {
            const element = document.querySelector(this.steps[this.currentStep].element);
            if (element) { element.style.position = ''; element.style.zIndex = ''; }
        }
    }
}

const interactiveTour = new InteractiveTour();

if (window.location.search.includes('show_tour=1')) {
    window.addEventListener('load', function() {
        setTimeout(() => {
            interactiveTour.defineSteps([
                { element: 'nav.navbar', title: '🧭 Navigation', description: 'Hier finden Sie alle wichtigen Bereiche des Systems.' },
                { element: '.dashboard-stats', title: '📊 Dashboard', description: 'Ihr Dashboard zeigt die wichtigsten Kennzahlen.' },
                { element: 'a[href="create_marker.php"]', title: '➕ Marker erstellen', description: 'Hier erstellen Sie neue Marker.' },
                { element: '.user-menu', title: '👤 Profil', description: 'Bearbeiten Sie Ihre Daten und Einstellungen.' }
            ]);
            interactiveTour.start();
            const url = new URL(window.location);
            url.searchParams.delete('show_tour');
            window.history.replaceState({}, '', url);
        }, 500);
    });
}
