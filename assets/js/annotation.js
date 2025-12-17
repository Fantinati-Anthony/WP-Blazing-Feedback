/**
 * Blazing Feedback - Module d'annotation
 *
 * Gère le placement des pins/marqueurs sur la page
 * et leur repositionnement dynamique
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Annotation
     * @namespace
     */
    const BlazingAnnotation = {

        /**
         * État du module
         * @type {Object}
         */
        state: {
            isActive: false,           // Mode annotation actif
            currentPin: null,          // Pin en cours de placement
            pins: [],                  // Liste des pins affichés
            selectedPin: null,         // Pin sélectionné
            clickPosition: null,       // Position du dernier clic
        },

        /**
         * Configuration
         * @type {Object}
         */
        config: {
            pinSize: 28,
            pinColor: '#e74c3c',
            pinActiveColor: '#c0392b',
            animationDuration: 200,
        },

        /**
         * Éléments DOM
         * @type {Object}
         */
        elements: {
            overlay: null,
            pinsContainer: null,
            hint: null,
        },

        /**
         * Initialiser le module
         * @returns {void}
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();

            console.log('[Blazing Feedback] Module Annotation initialisé');
        },

        /**
         * Mettre en cache les éléments DOM
         * @returns {void}
         */
        cacheElements: function() {
            this.elements.overlay = document.getElementById('wpvfh-annotation-overlay');
            this.elements.pinsContainer = document.getElementById('wpvfh-pins-container');
        },

        /**
         * Attacher les gestionnaires d'événements
         * @returns {void}
         */
        bindEvents: function() {
            // Écouter les événements personnalisés
            document.addEventListener('blazing-feedback:start-annotation', this.activate.bind(this));
            document.addEventListener('blazing-feedback:stop-annotation', this.deactivate.bind(this));
            document.addEventListener('blazing-feedback:load-pins', this.loadPins.bind(this));
            document.addEventListener('blazing-feedback:clear-pins', this.clearPins.bind(this));

            // Redimensionnement de la fenêtre
            window.addEventListener('resize', this.handleResize.bind(this));

            // Clic sur l'overlay - capture les clics pour placer les pins
            if (this.elements.overlay) {
                this.elements.overlay.addEventListener('click', this.handleOverlayClick.bind(this));
            }

            // Annuler avec Echap
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.state.isActive) {
                    this.deactivate();
                }
            });
        },

        /**
         * Activer le mode annotation
         * @returns {void}
         */
        activate: function() {
            if (this.state.isActive) return;

            this.state.isActive = true;

            // Afficher l'overlay
            if (this.elements.overlay) {
                this.elements.overlay.hidden = false;
                this.elements.overlay.setAttribute('aria-hidden', 'false');
            }

            // Ajouter la classe au body
            document.body.classList.add('wpvfh-annotation-mode');

            // Changer le curseur
            document.body.style.cursor = 'crosshair';

            // Émettre l'événement
            this.emitEvent('annotation-activated');

            console.log('[Blazing Feedback] Mode annotation activé');
        },

        /**
         * Désactiver le mode annotation
         * @returns {void}
         */
        deactivate: function() {
            if (!this.state.isActive) return;

            this.state.isActive = false;
            this.state.currentPin = null;

            // Masquer l'overlay
            if (this.elements.overlay) {
                this.elements.overlay.hidden = true;
                this.elements.overlay.setAttribute('aria-hidden', 'true');
            }

            // Retirer la classe du body
            document.body.classList.remove('wpvfh-annotation-mode');

            // Restaurer le curseur
            document.body.style.cursor = '';

            // Émettre l'événement
            this.emitEvent('annotation-deactivated');

            console.log('[Blazing Feedback] Mode annotation désactivé');
        },

        /**
         * Gérer le clic sur l'overlay
         * @param {MouseEvent} event - Événement de clic
         * @returns {void}
         */
        handleOverlayClick: function(event) {
            event.preventDefault();
            event.stopPropagation();

            // Ignorer si clic sur le bouton annuler
            if (event.target.classList.contains('wpvfh-hint-close')) {
                this.deactivate();
                return;
            }

            // Calculer la position relative
            const scrollX = window.scrollX || window.pageXOffset;
            const scrollY = window.scrollY || window.pageYOffset;

            const pageWidth = document.documentElement.scrollWidth;
            const pageHeight = document.documentElement.scrollHeight;

            // Position absolue sur la page
            const absoluteX = event.clientX + scrollX;
            const absoluteY = event.clientY + scrollY;

            // Position en pourcentage
            const percentX = (absoluteX / pageWidth) * 100;
            const percentY = (absoluteY / pageHeight) * 100;

            // Stocker la position
            this.state.clickPosition = {
                absoluteX,
                absoluteY,
                percentX,
                percentY,
                clientX: event.clientX,
                clientY: event.clientY,
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                scrollX,
                scrollY,
            };

            // Masquer temporairement l'overlay pour trouver l'élément sous le clic
            this.elements.overlay.style.display = 'none';

            // Obtenir l'élément sous le clic
            const elementUnder = document.elementFromPoint(event.clientX, event.clientY);

            // Restaurer l'overlay
            this.elements.overlay.style.display = '';

            // Générer un sélecteur CSS pour l'élément
            const selector = this.generateSelector(elementUnder);
            this.state.clickPosition.selector = selector;
            this.state.clickPosition.element = elementUnder;

            // Créer le pin temporaire
            this.createTemporaryPin(event.clientX, event.clientY);

            // Émettre l'événement avec les données
            this.emitEvent('pin-placed', this.state.clickPosition);

            // Désactiver le mode annotation
            this.deactivate();
        },

        /**
         * Créer un pin temporaire (avant soumission)
         * @param {number} x - Position X en pixels
         * @param {number} y - Position Y en pixels
         * @returns {void}
         */
        createTemporaryPin: function(x, y) {
            // Supprimer l'ancien pin temporaire
            const oldPin = document.querySelector('.wpvfh-pin-temp');
            if (oldPin) {
                oldPin.remove();
            }

            // Créer le nouveau pin
            const pin = document.createElement('div');
            pin.className = 'wpvfh-pin wpvfh-pin-temp';
            pin.style.cssText = `
                position: fixed;
                left: ${x}px;
                top: ${y}px;
                width: ${this.config.pinSize}px;
                height: ${this.config.pinSize}px;
                background: ${this.config.pinColor};
                border: 3px solid #fff;
                border-radius: 50%;
                transform: translate(-50%, -50%);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                z-index: 999999;
                cursor: pointer;
                animation: wpvfh-pin-appear 0.2s ease-out;
            `;

            pin.innerHTML = '<span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-weight:bold;font-size:12px;">+</span>';

            document.body.appendChild(pin);

            this.state.currentPin = pin;
        },

        /**
         * Supprimer le pin temporaire
         * @returns {void}
         */
        removeTemporaryPin: function() {
            const tempPin = document.querySelector('.wpvfh-pin-temp');
            if (tempPin) {
                tempPin.remove();
            }
            this.state.currentPin = null;
            this.state.clickPosition = null;
        },

        /**
         * Obtenir la position actuelle du pin
         * @returns {Object|null} Position du pin
         */
        getPosition: function() {
            return this.state.clickPosition;
        },

        /**
         * Charger et afficher les pins existants
         * @param {CustomEvent} event - Événement avec les données des pins
         * @returns {void}
         */
        loadPins: function(event) {
            const { pins } = event.detail || {};

            if (!pins || !Array.isArray(pins)) {
                return;
            }

            // Effacer les pins existants
            this.clearPins();

            // Créer les nouveaux pins
            pins.forEach(pinData => this.createPin(pinData));
        },

        /**
         * Créer un pin permanent
         * @param {Object} data - Données du pin
         * @returns {HTMLElement} Élément du pin
         */
        createPin: function(data) {
            const pin = document.createElement('div');
            pin.className = 'wpvfh-pin wpvfh-pin-saved';
            pin.dataset.feedbackId = data.id;
            pin.dataset.status = data.status || 'new';

            // Couleur selon le statut
            const statusColors = {
                new: '#3498db',
                in_progress: '#f39c12',
                resolved: '#27ae60',
                rejected: '#e74c3c',
            };
            const color = statusColors[data.status] || statusColors.new;

            // Positionner le pin
            pin.style.cssText = `
                position: absolute;
                left: ${data.position_x}%;
                top: ${data.position_y}%;
                width: ${this.config.pinSize}px;
                height: ${this.config.pinSize}px;
                background: ${color};
                border: 3px solid #fff;
                border-radius: 50%;
                transform: translate(-50%, -50%);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                cursor: pointer;
                transition: transform 0.2s ease;
            `;

            // Numéro ou icône selon le statut
            const icons = {
                new: '!',
                in_progress: '⏳',
                resolved: '✓',
                rejected: '✗',
            };
            pin.innerHTML = `<span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-weight:bold;font-size:12px;">${icons[data.status] || '!'}</span>`;

            // Événements
            pin.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectPin(data.id);
            });

            pin.addEventListener('mouseenter', () => {
                pin.style.transform = 'translate(-50%, -50%) scale(1.2)';
            });

            pin.addEventListener('mouseleave', () => {
                pin.style.transform = 'translate(-50%, -50%)';
            });

            // Ajouter au conteneur
            if (this.elements.pinsContainer) {
                this.elements.pinsContainer.appendChild(pin);
            }

            // Stocker la référence
            this.state.pins.push({
                element: pin,
                data: data,
            });

            return pin;
        },

        /**
         * Sélectionner un pin
         * @param {number} feedbackId - ID du feedback
         * @returns {void}
         */
        selectPin: function(feedbackId) {
            // Désélectionner l'ancien
            if (this.state.selectedPin) {
                const oldPin = this.state.pins.find(p => p.data.id === this.state.selectedPin);
                if (oldPin) {
                    oldPin.element.classList.remove('wpvfh-pin-selected');
                }
            }

            // Sélectionner le nouveau
            this.state.selectedPin = feedbackId;
            const pin = this.state.pins.find(p => p.data.id === feedbackId);
            if (pin) {
                pin.element.classList.add('wpvfh-pin-selected');
            }

            // Émettre l'événement
            this.emitEvent('pin-selected', { feedbackId, pinData: pin?.data });
        },

        /**
         * Effacer tous les pins
         * @returns {void}
         */
        clearPins: function() {
            if (this.elements.pinsContainer) {
                this.elements.pinsContainer.innerHTML = '';
            }
            this.state.pins = [];
            this.state.selectedPin = null;
        },

        /**
         * Mettre à jour un pin existant
         * @param {number} feedbackId - ID du feedback
         * @param {Object} newData - Nouvelles données
         * @returns {void}
         */
        updatePin: function(feedbackId, newData) {
            const pinIndex = this.state.pins.findIndex(p => p.data.id === feedbackId);

            if (pinIndex === -1) return;

            const pin = this.state.pins[pinIndex];

            // Mettre à jour les données
            Object.assign(pin.data, newData);

            // Mettre à jour l'apparence si le statut a changé
            if (newData.status) {
                pin.element.dataset.status = newData.status;

                const statusColors = {
                    new: '#3498db',
                    in_progress: '#f39c12',
                    resolved: '#27ae60',
                    rejected: '#e74c3c',
                };
                pin.element.style.background = statusColors[newData.status] || statusColors.new;

                const icons = {
                    new: '!',
                    in_progress: '⏳',
                    resolved: '✓',
                    rejected: '✗',
                };
                pin.element.querySelector('span').textContent = icons[newData.status] || '!';
            }
        },

        /**
         * Supprimer un pin
         * @param {number} feedbackId - ID du feedback
         * @returns {void}
         */
        removePin: function(feedbackId) {
            const pinIndex = this.state.pins.findIndex(p => p.data.id === feedbackId);

            if (pinIndex === -1) return;

            const pin = this.state.pins[pinIndex];
            pin.element.remove();
            this.state.pins.splice(pinIndex, 1);

            if (this.state.selectedPin === feedbackId) {
                this.state.selectedPin = null;
            }
        },

        /**
         * Générer un sélecteur CSS pour un élément
         * @param {HTMLElement} element - Élément
         * @returns {string} Sélecteur CSS
         */
        generateSelector: function(element) {
            if (!element || element === document.body || element === document.documentElement) {
                return 'body';
            }

            // ID unique
            if (element.id) {
                return '#' + CSS.escape(element.id);
            }

            // Construire un sélecteur basé sur le chemin
            const path = [];
            let current = element;

            while (current && current !== document.body) {
                let selector = current.tagName.toLowerCase();

                // Ajouter des classes significatives
                if (current.className && typeof current.className === 'string') {
                    const classes = current.className.split(/\s+/)
                        .filter(c => c && !c.startsWith('wpvfh-'))
                        .slice(0, 2);
                    if (classes.length) {
                        selector += '.' + classes.map(c => CSS.escape(c)).join('.');
                    }
                }

                // Ajouter un index si nécessaire
                if (current.parentElement) {
                    const siblings = Array.from(current.parentElement.children)
                        .filter(el => el.tagName === current.tagName);
                    if (siblings.length > 1) {
                        const index = siblings.indexOf(current) + 1;
                        selector += `:nth-of-type(${index})`;
                    }
                }

                path.unshift(selector);
                current = current.parentElement;

                // Limiter la profondeur
                if (path.length >= 5) break;
            }

            return path.join(' > ');
        },

        /**
         * Trouver un élément par son sélecteur
         * @param {string} selector - Sélecteur CSS
         * @returns {HTMLElement|null} Élément trouvé
         */
        findElement: function(selector) {
            try {
                return document.querySelector(selector);
            } catch (e) {
                return null;
            }
        },

        /**
         * Repositionner les pins lors du redimensionnement
         * @returns {void}
         */
        handleResize: function() {
            // Les pins en pourcentage se repositionnent automatiquement
            // Mais on peut déclencher une vérification de repositionnement intelligent

            this.state.pins.forEach(pin => {
                if (pin.data.selector) {
                    const element = this.findElement(pin.data.selector);
                    if (element) {
                        // Recalculer la position si l'élément existe toujours
                        const rect = element.getBoundingClientRect();
                        const scrollX = window.scrollX || window.pageXOffset;
                        const scrollY = window.scrollY || window.pageYOffset;

                        const pageWidth = document.documentElement.scrollWidth;
                        const pageHeight = document.documentElement.scrollHeight;

                        // Nouvelle position en pourcentage
                        const newPercentX = ((rect.left + scrollX) / pageWidth) * 100;
                        const newPercentY = ((rect.top + scrollY) / pageHeight) * 100;

                        // Mettre à jour la position visuelle
                        pin.element.style.left = newPercentX + '%';
                        pin.element.style.top = newPercentY + '%';
                    }
                }
            });
        },

        /**
         * Scroller vers un pin
         * @param {number} feedbackId - ID du feedback
         * @returns {void}
         */
        scrollToPin: function(feedbackId) {
            const pin = this.state.pins.find(p => p.data.id === feedbackId);

            if (!pin) return;

            const rect = pin.element.getBoundingClientRect();
            const scrollY = window.scrollY || window.pageYOffset;

            window.scrollTo({
                top: scrollY + rect.top - (window.innerHeight / 2),
                behavior: 'smooth',
            });

            // Sélectionner le pin après le scroll
            setTimeout(() => this.selectPin(feedbackId), 500);
        },

        /**
         * Vérifier si le mode annotation est actif
         * @returns {boolean}
         */
        isActive: function() {
            return this.state.isActive;
        },

        /**
         * Émettre un événement personnalisé
         * @param {string} name - Nom de l'événement
         * @param {Object} detail - Détails
         * @returns {void}
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        },
    };

    // Exposer le module globalement
    window.BlazingAnnotation = BlazingAnnotation;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingAnnotation.init());
    } else {
        BlazingAnnotation.init();
    }

})(window, document);
