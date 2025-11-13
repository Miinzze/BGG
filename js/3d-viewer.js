// 3D Model Viewer & AR Configuration
class Model3DViewer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.model = null;
        this.controls = null;
        this.animationId = null;
        this.isAR = false;
    }

    async init(modelUrl) {
        if (!this.container) {
            console.error('Container not found');
            return;
        }

        // Three.js Setup
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0xf0f0f0);

        // Camera
        this.camera = new THREE.PerspectiveCamera(
            45,
            this.container.clientWidth / this.container.clientHeight,
            0.1,
            1000
        );
        this.camera.position.set(0, 1, 5);

        // Renderer
        this.renderer = new THREE.WebGLRenderer({ 
            antialias: true,
            alpha: true 
        });
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.shadowMap.enabled = true;
        this.container.appendChild(this.renderer.domElement);

        // Lights
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(10, 10, 5);
        directionalLight.castShadow = true;
        this.scene.add(directionalLight);

        // Controls - OrbitControls für 360° Ansicht
        if (typeof THREE.OrbitControls !== 'undefined') {
            this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
            this.controls.enableDamping = true;
            this.controls.dampingFactor = 0.05;
            this.controls.autoRotate = true;
            this.controls.autoRotateSpeed = 2.0;
        }

        // Load Model
        if (modelUrl) {
            await this.loadModel(modelUrl);
        }

        // Start Animation
        this.animate();

        // Handle Resize
        window.addEventListener('resize', () => this.onWindowResize());
    }

    async loadModel(url) {
        const fileExtension = url.split('.').pop().toLowerCase();
        
        try {
            let loader;
            
            if (fileExtension === 'glb' || fileExtension === 'gltf') {
                loader = new THREE.GLTFLoader();
                const gltf = await new Promise((resolve, reject) => {
                    loader.load(url, resolve, undefined, reject);
                });
                this.model = gltf.scene;
            } else if (fileExtension === 'obj') {
                loader = new THREE.OBJLoader();
                this.model = await new Promise((resolve, reject) => {
                    loader.load(url, resolve, undefined, reject);
                });
            }

            if (this.model) {
                // Center and scale model
                const box = new THREE.Box3().setFromObject(this.model);
                const center = box.getCenter(new THREE.Vector3());
                const size = box.getSize(new THREE.Vector3());
                const maxDim = Math.max(size.x, size.y, size.z);
                const scale = 2 / maxDim;
                
                this.model.scale.setScalar(scale);
                this.model.position.sub(center.multiplyScalar(scale));
                
                this.scene.add(this.model);
            }
        } catch (error) {
            console.error('Error loading 3D model:', error);
            this.showError('Fehler beim Laden des 3D-Modells');
        }
    }

    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        
        if (this.controls) {
            this.controls.update();
        }
        
        this.renderer.render(this.scene, this.camera);
    }

    onWindowResize() {
        if (!this.container) return;
        
        this.camera.aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
    }

    dispose() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        if (this.renderer && this.container) {
            this.container.removeChild(this.renderer.domElement);
        }
        if (this.controls) {
            this.controls.dispose();
        }
    }

    showError(message) {
        if (this.container) {
            this.container.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #e74c3c;">
                    <div style="text-align: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p>${message}</p>
                    </div>
                </div>
            `;
        }
    }

    // AR-Konfiguration
    updateARConfig(config) {
        if (this.model) {
            if (config.scale) {
                this.model.scale.setScalar(config.scale);
            }
            if (config.rotation) {
                this.model.rotation.x = THREE.MathUtils.degToRad(config.rotation.x || 0);
                this.model.rotation.y = THREE.MathUtils.degToRad(config.rotation.y || 0);
                this.model.rotation.z = THREE.MathUtils.degToRad(config.rotation.z || 0);
            }
            if (config.position) {
                this.model.position.set(
                    config.position.x || 0,
                    config.position.y || 0,
                    config.position.z || 0
                );
            }
        }
    }

    // Change model color/material
    changeMaterial(color) {
        if (this.model) {
            this.model.traverse((child) => {
                if (child.isMesh) {
                    child.material = new THREE.MeshStandardMaterial({
                        color: new THREE.Color(color),
                        metalness: 0.5,
                        roughness: 0.5
                    });
                }
            });
        }
    }

    // Toggle auto-rotation
    toggleAutoRotate() {
        if (this.controls) {
            this.controls.autoRotate = !this.controls.autoRotate;
            return this.controls.autoRotate;
        }
        return false;
    }

    // Reset camera position
    resetCamera() {
        if (this.camera && this.controls) {
            this.camera.position.set(0, 1, 5);
            this.controls.target.set(0, 0, 0);
            this.controls.update();
        }
    }
}

// Mobile 3D Capture Function
class Mobile3DCapture {
    constructor() {
        this.images = [];
        this.maxImages = 20;
        this.currentAngle = 0;
    }

    async captureImage(videoElement) {
        const canvas = document.createElement('canvas');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoElement, 0, 0);
        
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                resolve(blob);
            }, 'image/jpeg', 0.9);
        });
    }

    async addImage(imageBlob) {
        if (this.images.length >= this.maxImages) {
            return { success: false, message: 'Maximale Anzahl an Bildern erreicht' };
        }
        
        this.images.push({
            blob: imageBlob,
            angle: this.currentAngle
        });
        
        this.currentAngle += (360 / this.maxImages);
        
        return {
            success: true,
            count: this.images.length,
            progress: (this.images.length / this.maxImages) * 100
        };
    }

    async process3DModel(markerId) {
        if (this.images.length < 8) {
            return { success: false, message: 'Mindestens 8 Bilder benötigt' };
        }

        const formData = new FormData();
        formData.append('marker_id', markerId);
        formData.append('model_name', 'Mobile 3D Capture - ' + new Date().toLocaleString('de-DE'));
        
        this.images.forEach((img, index) => {
            formData.append('images[]', img.blob, `capture_${index}.jpg`);
            formData.append('angles[]', img.angle);
        });

        try {
            const response = await fetch('process_3d_reconstruction.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            return result;
        } catch (error) {
            return { success: false, message: 'Fehler bei der Verarbeitung: ' + error.message };
        }
    }

    reset() {
        this.images = [];
        this.currentAngle = 0;
    }

    getProgress() {
        return {
            count: this.images.length,
            max: this.maxImages,
            percentage: (this.images.length / this.maxImages) * 100
        };
    }
}

// AR Quick View (WebXR)
class ARQuickView {
    constructor() {
        this.isSupported = 'xr' in navigator;
    }

    async checkSupport() {
        if (!this.isSupported) {
            return false;
        }
        
        try {
            const supported = await navigator.xr.isSessionSupported('immersive-ar');
            return supported;
        } catch (e) {
            return false;
        }
    }

    async launchAR(modelUrl) {
        if (!await this.checkSupport()) {
            // Fallback to model-viewer or AR.js
            this.launchFallbackAR(modelUrl);
            return;
        }

        // Launch WebXR AR session
        // Implementation würde hier erfolgen
        alert('AR wird gestartet...');
    }

    launchFallbackAR(modelUrl) {
        // Fallback für Geräte ohne WebXR
        // Nutze model-viewer mit AR-Button
        const viewer = document.createElement('model-viewer');
        viewer.src = modelUrl;
        viewer.ar = true;
        viewer.setAttribute('ar-modes', 'webxr scene-viewer quick-look');
        viewer.setAttribute('camera-controls', '');
        viewer.setAttribute('auto-rotate', '');
        
        // Simuliere AR-Button Click
        if (viewer.canActivateAR) {
            viewer.activateAR();
        }
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Model3DViewer, Mobile3DCapture, ARQuickView };
}