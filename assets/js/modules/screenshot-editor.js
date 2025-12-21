/**
 * Module Screenshot Editor - Blazing Feedback
 * Éditeur d'annotation pour les captures d'écran
 * @package Blazing_Feedback
 */
(function(window, document) {
    'use strict';

    const ScreenshotEditor = {
        canvas: null,
        ctx: null,
        isDrawing: false,
        currentTool: 'pen',
        currentColor: '#e74c3c',
        currentSize: 4,
        startX: 0,
        startY: 0,
        history: [],
        historyIndex: -1,
        originalImage: null,
        onSaveCallback: null,

        /**
         * Initialiser l'éditeur
         */
        init: function() {
            this.canvas = document.getElementById('wpvfh-editor-canvas');
            if (!this.canvas) return;

            this.ctx = this.canvas.getContext('2d');
            this.bindEvents();
        },

        /**
         * Lier les événements
         */
        bindEvents: function() {
            // Outils
            document.querySelectorAll('.wpvfh-editor-tool').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.wpvfh-editor-tool').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    this.currentTool = btn.dataset.tool;
                });
            });

            // Couleur
            const colorInput = document.getElementById('wpvfh-editor-color');
            if (colorInput) {
                colorInput.addEventListener('change', (e) => {
                    this.currentColor = e.target.value;
                });
            }

            // Taille
            const sizeSelect = document.getElementById('wpvfh-editor-size');
            if (sizeSelect) {
                sizeSelect.addEventListener('change', (e) => {
                    this.currentSize = parseInt(e.target.value);
                });
            }

            // Annuler
            const undoBtn = document.getElementById('wpvfh-editor-undo');
            if (undoBtn) {
                undoBtn.addEventListener('click', () => this.undo());
            }

            // Effacer tout
            const clearBtn = document.getElementById('wpvfh-editor-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => this.clearAll());
            }

            // Fermer
            const closeBtn = document.getElementById('wpvfh-editor-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.close());
            }

            // Annuler (bouton)
            const cancelBtn = document.getElementById('wpvfh-editor-cancel');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => this.close());
            }

            // Enregistrer
            const saveBtn = document.getElementById('wpvfh-editor-save');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => this.save());
            }

            // Overlay
            const modal = document.getElementById('wpvfh-screenshot-editor');
            if (modal) {
                modal.querySelector('.wpvfh-modal-overlay')?.addEventListener('click', () => this.close());
            }

            // Canvas events
            if (this.canvas) {
                this.canvas.addEventListener('mousedown', (e) => this.handleMouseDown(e));
                this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
                this.canvas.addEventListener('mouseup', (e) => this.handleMouseUp(e));
                this.canvas.addEventListener('mouseleave', (e) => this.handleMouseUp(e));

                // Touch support
                this.canvas.addEventListener('touchstart', (e) => this.handleTouchStart(e));
                this.canvas.addEventListener('touchmove', (e) => this.handleTouchMove(e));
                this.canvas.addEventListener('touchend', (e) => this.handleTouchEnd(e));
            }
        },

        /**
         * Ouvrir l'éditeur avec une image
         */
        open: function(imageDataUrl, callback) {
            this.onSaveCallback = callback;
            this.history = [];
            this.historyIndex = -1;

            const modal = document.getElementById('wpvfh-screenshot-editor');
            if (!modal) return;

            modal.hidden = false;

            // Charger l'image
            const img = new Image();
            img.onload = () => {
                this.originalImage = img;

                // Get container dimensions for responsive canvas sizing
                const container = document.querySelector('.wpvfh-editor-canvas-container');
                const containerWidth = container ? container.clientWidth - 32 : window.innerWidth - 60;
                const containerHeight = container ? container.clientHeight - 32 : window.innerHeight - 200;

                // Use full image resolution, scaled to fit container
                let displayWidth = img.width;
                let displayHeight = img.height;

                // Scale to fit container while maintaining aspect ratio
                const scaleX = containerWidth / displayWidth;
                const scaleY = containerHeight / displayHeight;
                const scale = Math.min(scaleX, scaleY, 1); // Don't upscale

                displayWidth = Math.floor(img.width * scale);
                displayHeight = Math.floor(img.height * scale);

                // Set canvas to display size (we'll draw at original resolution)
                this.canvas.width = img.width;
                this.canvas.height = img.height;

                // Set display size via CSS
                this.canvas.style.width = displayWidth + 'px';
                this.canvas.style.height = displayHeight + 'px';

                // Draw at full resolution
                this.ctx.drawImage(img, 0, 0, img.width, img.height);
                this.saveState();
            };
            img.src = imageDataUrl;
        },

        /**
         * Fermer l'éditeur
         */
        close: function() {
            const modal = document.getElementById('wpvfh-screenshot-editor');
            if (modal) {
                modal.hidden = true;
            }
            this.history = [];
            this.historyIndex = -1;
            this.originalImage = null;
        },

        /**
         * Enregistrer et fermer
         */
        save: function() {
            if (this.canvas) {
                const dataUrl = this.canvas.toDataURL('image/png');
                if (this.onSaveCallback) {
                    this.onSaveCallback(dataUrl);
                }
            }
            this.close();
        },

        /**
         * Obtenir les coordonnées de la souris sur le canvas
         */
        getMousePos: function(e) {
            const rect = this.canvas.getBoundingClientRect();
            const scaleX = this.canvas.width / rect.width;
            const scaleY = this.canvas.height / rect.height;
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top) * scaleY
            };
        },

        /**
         * Gérer le mousedown
         */
        handleMouseDown: function(e) {
            e.preventDefault();
            this.isDrawing = true;
            const pos = this.getMousePos(e);
            this.startX = pos.x;
            this.startY = pos.y;

            if (this.currentTool === 'pen') {
                this.ctx.beginPath();
                this.ctx.moveTo(pos.x, pos.y);
            } else if (this.currentTool === 'text') {
                this.addText(pos.x, pos.y);
                this.isDrawing = false;
            }
        },

        /**
         * Gérer le mousemove
         */
        handleMouseMove: function(e) {
            if (!this.isDrawing) return;
            e.preventDefault();

            const pos = this.getMousePos(e);

            if (this.currentTool === 'pen') {
                this.drawPen(pos.x, pos.y);
            } else {
                // Pour les formes, redessiner depuis l'état précédent
                this.restoreState();
                this.drawShape(pos.x, pos.y, true);
            }
        },

        /**
         * Gérer le mouseup
         */
        handleMouseUp: function(e) {
            if (!this.isDrawing) return;
            this.isDrawing = false;

            const pos = this.getMousePos(e);

            if (this.currentTool !== 'pen') {
                this.restoreState();
                this.drawShape(pos.x, pos.y, false);
            }

            this.saveState();
        },

        /**
         * Touch events
         */
        handleTouchStart: function(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            this.handleMouseDown(mouseEvent);
        },

        handleTouchMove: function(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            this.handleMouseMove(mouseEvent);
        },

        handleTouchEnd: function(e) {
            e.preventDefault();
            const mouseEvent = new MouseEvent('mouseup', {});
            this.handleMouseUp(mouseEvent);
        },

        /**
         * Dessiner au crayon
         */
        drawPen: function(x, y) {
            this.ctx.lineWidth = this.currentSize;
            this.ctx.lineCap = 'round';
            this.ctx.strokeStyle = this.currentColor;
            this.ctx.lineTo(x, y);
            this.ctx.stroke();
        },

        /**
         * Dessiner une forme
         */
        drawShape: function(endX, endY, preview) {
            this.ctx.strokeStyle = this.currentColor;
            this.ctx.lineWidth = this.currentSize;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';

            const width = endX - this.startX;
            const height = endY - this.startY;

            switch (this.currentTool) {
                case 'line':
                    this.ctx.beginPath();
                    this.ctx.moveTo(this.startX, this.startY);
                    this.ctx.lineTo(endX, endY);
                    this.ctx.stroke();
                    break;

                case 'arrow':
                    this.drawArrow(this.startX, this.startY, endX, endY);
                    break;

                case 'rect':
                    this.ctx.beginPath();
                    this.ctx.strokeRect(this.startX, this.startY, width, height);
                    break;

                case 'circle':
                    const centerX = this.startX + width / 2;
                    const centerY = this.startY + height / 2;
                    const radiusX = Math.abs(width / 2);
                    const radiusY = Math.abs(height / 2);
                    this.ctx.beginPath();
                    this.ctx.ellipse(centerX, centerY, radiusX, radiusY, 0, 0, 2 * Math.PI);
                    this.ctx.stroke();
                    break;
            }
        },

        /**
         * Dessiner une flèche
         */
        drawArrow: function(fromX, fromY, toX, toY) {
            const headLength = 15;
            const angle = Math.atan2(toY - fromY, toX - fromX);

            // Ligne
            this.ctx.beginPath();
            this.ctx.moveTo(fromX, fromY);
            this.ctx.lineTo(toX, toY);
            this.ctx.stroke();

            // Tête de flèche
            this.ctx.beginPath();
            this.ctx.moveTo(toX, toY);
            this.ctx.lineTo(
                toX - headLength * Math.cos(angle - Math.PI / 6),
                toY - headLength * Math.sin(angle - Math.PI / 6)
            );
            this.ctx.moveTo(toX, toY);
            this.ctx.lineTo(
                toX - headLength * Math.cos(angle + Math.PI / 6),
                toY - headLength * Math.sin(angle + Math.PI / 6)
            );
            this.ctx.stroke();
        },

        /**
         * Ajouter du texte
         */
        addText: function(x, y) {
            const text = prompt('Entrez le texte:');
            if (text) {
                this.ctx.font = `${this.currentSize * 4}px Arial`;
                this.ctx.fillStyle = this.currentColor;
                this.ctx.fillText(text, x, y);
                this.saveState();
            }
        },

        /**
         * Sauvegarder l'état actuel
         */
        saveState: function() {
            // Supprimer les états après l'index actuel (si on a fait undo)
            this.history = this.history.slice(0, this.historyIndex + 1);

            // Sauvegarder le nouvel état
            this.history.push(this.canvas.toDataURL());
            this.historyIndex = this.history.length - 1;

            // Limiter l'historique
            if (this.history.length > 20) {
                this.history.shift();
                this.historyIndex--;
            }
        },

        /**
         * Restaurer l'état précédent (pour preview)
         */
        restoreState: function() {
            if (this.historyIndex >= 0 && this.history[this.historyIndex]) {
                const img = new Image();
                img.src = this.history[this.historyIndex];
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.ctx.drawImage(img, 0, 0);
            }
        },

        /**
         * Annuler
         */
        undo: function() {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                const img = new Image();
                img.onload = () => {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.ctx.drawImage(img, 0, 0);
                };
                img.src = this.history[this.historyIndex];
            }
        },

        /**
         * Effacer tout et revenir à l'image originale
         */
        clearAll: function() {
            if (this.originalImage) {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.ctx.drawImage(this.originalImage, 0, 0, this.originalImage.width, this.originalImage.height);
                this.saveState();
            }
        }
    };

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ScreenshotEditor.init());
    } else {
        ScreenshotEditor.init();
    }

    // Exposer globalement
    window.BlazingScreenshotEditor = ScreenshotEditor;

})(window, document);
