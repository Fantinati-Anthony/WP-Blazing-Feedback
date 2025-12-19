/**
 * Blazing Feedback - Module d'annotation avec DOM Anchoring
 *
 * Système de positionnement des pins basé sur l'ancrage DOM
 * comme ProjectHuddle/SureFeedback
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Annotation avec DOM Anchoring
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
            clickHandler: null,        // Référence au gestionnaire de clics global
            repositionTimeout: null,   // Timeout pour le repositionnement debounced
            // Nouveau: Mode inspecteur d'éléments
            inspectorMode: false,      // Mode inspecteur actif
            hoveredElement: null,      // Élément actuellement survolé
            selectedElement: null,     // Élément sélectionné
            selectedElementData: null, // Données de l'élément sélectionné
            highlightEl: null,         // Élément de surbrillance
            selectedOutlineEl: null,   // Élément contour de sélection
            inspectorHint: null,       // Hint du mode inspecteur
            pinsVisible: true,         // Visibilité des pins
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
            repositionDebounce: 100,   // ms pour debounce du repositionnement
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
            this.setupPinsContainer();
            this.bindEvents();

            console.log('[Blazing Feedback] Module Annotation avec DOM Anchoring initialisé');
        },

        /**
         * Configurer le conteneur des pins
         * @returns {void}
         */
        setupPinsContainer: function() {
            if (!this.elements.pinsContainer) return;

            // Déplacer le conteneur au niveau du body
            document.body.appendChild(this.elements.pinsContainer);

            // Style du conteneur - couvre toute la page
            this.elements.pinsContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 999990;
                overflow: visible;
            `;
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

            // Nouveaux événements pour l'inspecteur d'éléments
            document.addEventListener('blazing-feedback:start-inspector', this.startInspector.bind(this));
            document.addEventListener('blazing-feedback:stop-inspector', this.stopInspector.bind(this));
            document.addEventListener('blazing-feedback:clear-selection', this.clearSelection.bind(this));
            document.addEventListener('blazing-feedback:toggle-pins', this.togglePinsVisibility.bind(this));

            // Redimensionnement - repositionner tous les pins
            window.addEventListener('resize', this.debouncedReposition.bind(this));
            window.addEventListener('scroll', this.debouncedReposition.bind(this), { passive: true });

            // Observer les changements du DOM
            this.observeDOMChanges();

            // Observer les changements de classe du body (pour la sidebar)
            this.observeBodyClassChanges();

            // Créer le gestionnaire de clics global
            this.state.clickHandler = this.handleGlobalClick.bind(this);

            // Annuler avec Echap
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.state.isActive) {
                    this.deactivate();
                }
            });
        },

        /**
         * Observer les changements du DOM pour repositionner les pins
         * @returns {void}
         */
        observeDOMChanges: function() {
            if (typeof MutationObserver === 'undefined') return;

            const observer = new MutationObserver((mutations) => {
                // Ignorer les mutations liées au widget lui-même
                const shouldReposition = mutations.some(mutation => {
                    return !mutation.target.closest('.wpvfh-widget') &&
                           !mutation.target.closest('.wpvfh-pins-container');
                });

                if (shouldReposition) {
                    this.debouncedReposition();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class'],
            });
        },

        /**
         * Observer les changements de classe du body pour la sidebar
         * Repositionne le highlight quand la sidebar s'ouvre/ferme
         * @returns {void}
         */
        observeBodyClassChanges: function() {
            if (typeof MutationObserver === 'undefined') return;

            const self = this;
            let lastPanelActive = document.body.classList.contains('wpvfh-panel-active');

            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const currentPanelActive = document.body.classList.contains('wpvfh-panel-active');

                        // Si l'état de la sidebar a changé
                        if (currentPanelActive !== lastPanelActive) {
                            lastPanelActive = currentPanelActive;

                            // Attendre que la transition se termine
                            setTimeout(() => {
                                // Repositionner le highlight de l'inspecteur si actif
                                if (self.state.inspectorMode && self.state.hoveredElement) {
                                    self.updateHighlight(self.state.hoveredElement);
                                }

                                // Repositionner l'outline de sélection si présent
                                if (self._updateOutlinePosition) {
                                    self._updateOutlinePosition();
                                }

                                // Repositionner tous les pins
                                self.repositionAllPins();
                            }, 350); // Après la transition de 0.3s
                        }
                    }
                });
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class'],
            });
        },

        /**
         * Repositionnement debounced pour éviter les appels trop fréquents
         * @returns {void}
         */
        debouncedReposition: function() {
            if (this.state.repositionTimeout) {
                clearTimeout(this.state.repositionTimeout);
            }

            this.state.repositionTimeout = setTimeout(() => {
                this.repositionAllPins();
            }, this.config.repositionDebounce);
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

            // Ajouter les gestionnaires de clics globaux
            document.addEventListener('click', this.state.clickHandler, true);
            document.addEventListener('mousedown', this.preventInteraction, true);
            document.addEventListener('mouseup', this.preventInteraction, true);
            document.addEventListener('touchstart', this.state.clickHandler, true);
            document.addEventListener('touchend', this.preventInteraction, true);

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

            // Retirer les gestionnaires de clics globaux
            document.removeEventListener('click', this.state.clickHandler, true);
            document.removeEventListener('mousedown', this.preventInteraction, true);
            document.removeEventListener('mouseup', this.preventInteraction, true);
            document.removeEventListener('touchstart', this.state.clickHandler, true);
            document.removeEventListener('touchend', this.preventInteraction, true);

            // Émettre l'événement
            this.emitEvent('annotation-deactivated');

            console.log('[Blazing Feedback] Mode annotation désactivé');
        },

        /**
         * Empêcher les interactions pendant le mode annotation
         * @param {Event} event - Événement
         * @returns {void}
         */
        preventInteraction: function(event) {
            if (event.target.closest('.wpvfh-widget') ||
                event.target.closest('.wpvfh-pin') ||
                event.target.classList.contains('wpvfh-hint-close')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
        },

        /**
         * Gérer le clic global - DOM Anchoring
         * @param {MouseEvent|TouchEvent} event - Événement de clic
         * @returns {void}
         */
        handleGlobalClick: function(event) {
            if (!this.state.isActive) return;

            // Ignorer si clic sur le widget
            if (event.target.closest('.wpvfh-widget') ||
                event.target.closest('.wpvfh-pin')) {
                return;
            }

            // Ignorer si clic sur le bouton annuler
            if (event.target.classList.contains('wpvfh-hint-close')) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                this.deactivate();
                return;
            }

            // Bloquer l'événement
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            // Obtenir les coordonnées du clic
            let clientX, clientY;
            if (event.type === 'touchstart' && event.touches && event.touches.length > 0) {
                clientX = event.touches[0].clientX;
                clientY = event.touches[0].clientY;
            } else {
                clientX = event.clientX;
                clientY = event.clientY;
            }

            console.log('[Blazing Feedback] Clic capturé à:', clientX, clientY);

            // Masquer temporairement l'overlay pour trouver l'élément sous le clic
            if (this.elements.overlay) {
                this.elements.overlay.style.visibility = 'hidden';
            }

            // Obtenir l'élément cible (anchor element)
            const targetElement = document.elementFromPoint(clientX, clientY);

            // Restaurer l'overlay
            if (this.elements.overlay) {
                this.elements.overlay.style.visibility = '';
            }

            if (!targetElement) {
                console.warn('[Blazing Feedback] Aucun élément trouvé sous le clic');
                return;
            }

            // Calculer la position DOM Anchoring
            const anchorData = this.calculateAnchorPosition(targetElement, clientX, clientY);

            // Stocker les données de position
            this.state.clickPosition = anchorData;

            console.log('[Blazing Feedback] DOM Anchor:', anchorData);

            // Créer le pin temporaire
            this.createTemporaryPin(clientX, clientY);

            // Émettre l'événement avec les données d'ancrage
            this.emitEvent('pin-placed', anchorData);

            // Désactiver le mode annotation
            this.deactivate();
        },

        /**
         * Calculer la position d'ancrage DOM
         * @param {HTMLElement} element - Élément cible
         * @param {number} clientX - Position X du clic (viewport)
         * @param {number} clientY - Position Y du clic (viewport)
         * @returns {Object} Données d'ancrage
         */
        calculateAnchorPosition: function(element, clientX, clientY) {
            // Obtenir le rectangle de l'élément
            const rect = element.getBoundingClientRect();

            // Position du clic relative à l'élément (en pourcentage)
            const elementOffsetX = ((clientX - rect.left) / rect.width) * 100;
            const elementOffsetY = ((clientY - rect.top) / rect.height) * 100;

            // Position absolue sur la page (fallback)
            const scrollX = window.scrollX || window.pageXOffset;
            const scrollY = window.scrollY || window.pageYOffset;
            const absoluteX = clientX + scrollX;
            const absoluteY = clientY + scrollY;

            // Position en pourcentage de la page (fallback)
            const pageWidth = document.documentElement.scrollWidth;
            const pageHeight = document.documentElement.scrollHeight;
            const percentX = (absoluteX / pageWidth) * 100;
            const percentY = (absoluteY / pageHeight) * 100;

            // Générer le sélecteur CSS robuste
            const selector = this.generateRobustSelector(element);

            return {
                // DOM Anchoring - données principales
                selector: selector,
                element_offset_x: elementOffsetX,
                element_offset_y: elementOffsetY,

                // Fallback - pourcentages de la page
                percentX: percentX,
                percentY: percentY,
                position_x: percentX,  // Pour compatibilité avec l'API
                position_y: percentY,

                // Métadonnées supplémentaires
                absoluteX: absoluteX,
                absoluteY: absoluteY,
                clientX: clientX,
                clientY: clientY,
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                scrollX: scrollX,
                scrollY: scrollY,

                // Info sur l'élément d'ancrage
                anchor_tag: element.tagName.toLowerCase(),
                anchor_rect: {
                    width: rect.width,
                    height: rect.height,
                },
            };
        },

        /**
         * Générer un sélecteur CSS robuste pour l'élément
         * @param {HTMLElement} element - Élément
         * @returns {string} Sélecteur CSS
         */
        generateRobustSelector: function(element) {
            if (!element || element === document.body || element === document.documentElement) {
                return 'body';
            }

            // 1. Priorité à l'ID unique
            if (element.id && document.querySelectorAll('#' + CSS.escape(element.id)).length === 1) {
                return '#' + CSS.escape(element.id);
            }

            // 2. Data attributes uniques
            const dataId = element.dataset.id || element.dataset.key || element.dataset.testid;
            if (dataId) {
                const dataSelector = `[data-id="${dataId}"], [data-key="${dataId}"], [data-testid="${dataId}"]`;
                try {
                    if (document.querySelectorAll(dataSelector.split(', ')[0]).length === 1) {
                        return dataSelector.split(', ')[0];
                    }
                } catch (e) {}
            }

            // 3. Construire un chemin de sélecteur
            const path = [];
            let current = element;
            let depth = 0;
            const maxDepth = 6;

            while (current && current !== document.body && depth < maxDepth) {
                let selector = current.tagName.toLowerCase();

                // Ajouter l'ID s'il existe
                if (current.id) {
                    selector = '#' + CSS.escape(current.id);
                    path.unshift(selector);
                    break; // On a un ID, on peut s'arrêter
                }

                // Ajouter des classes significatives (ignorer les classes dynamiques)
                if (current.className && typeof current.className === 'string') {
                    const classes = current.className.split(/\s+/)
                        .filter(c => {
                            return c &&
                                   !c.startsWith('wpvfh-') &&
                                   !c.startsWith('is-') &&
                                   !c.startsWith('has-') &&
                                   !c.match(/^(active|open|visible|hidden|hover|focus|selected)$/i) &&
                                   !c.match(/^[a-z]+-\d+$/i); // classes avec nombres dynamiques
                        })
                        .slice(0, 2);

                    if (classes.length) {
                        selector += '.' + classes.map(c => CSS.escape(c)).join('.');
                    }
                }

                // Ajouter nth-of-type pour unicité
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
                depth++;
            }

            const finalSelector = path.join(' > ');

            // Vérifier que le sélecteur retourne bien l'élément attendu
            try {
                const found = document.querySelector(finalSelector);
                if (found !== element) {
                    console.warn('[Blazing Feedback] Sélecteur imprécis:', finalSelector);
                }
            } catch (e) {
                console.warn('[Blazing Feedback] Sélecteur invalide:', finalSelector);
            }

            return finalSelector;
        },

        /**
         * Créer un pin temporaire (avant soumission)
         * @param {number} x - Position X en pixels (viewport)
         * @param {number} y - Position Y en pixels (viewport)
         * @returns {void}
         */
        createTemporaryPin: function(x, y) {
            // Supprimer l'ancien pin temporaire
            const oldPin = document.querySelector('.wpvfh-pin-temp');
            if (oldPin) {
                oldPin.remove();
            }

            // Créer le nouveau pin (position fixe pour le temporaire)
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
                z-index: 2147483647;
                cursor: pointer;
                animation: wpvfh-pin-appear 0.2s ease-out;
                pointer-events: auto;
            `;

            pin.innerHTML = '<span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-weight:bold;font-size:12px;">+</span>';

            document.body.appendChild(pin);
            this.state.currentPin = pin;

            console.log('[Blazing Feedback] Pin temporaire créé à:', x, y);
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

            // Filtrer les feedbacks qui ont une position (élément ciblé)
            const pinsWithPosition = pins.filter(pin => pin.selector || pin.position_x || pin.position_y);

            // Créer les nouveaux pins avec numérotation (seulement ceux avec position)
            pinsWithPosition.forEach((pinData, index) => {
                pinData._displayOrder = index + 1;
                this.createPin(pinData);
            });

            // Repositionner immédiatement
            setTimeout(() => this.repositionAllPins(), 100);
        },

        /**
         * Créer un pin permanent avec DOM Anchoring
         * Affiche un contour jaune autour de l'élément avec un pin numéroté en haut à droite
         * @param {Object} data - Données du pin
         * @returns {HTMLElement} Élément du pin (conteneur outline)
         */
        createPin: function(data) {
            // Numéro d'affichage
            const pinNumber = data._displayOrder || (this.state.pins.length + 1);

            // Créer le conteneur (outline + pin)
            const container = document.createElement('div');
            container.className = 'wpvfh-element-feedback-outline wpvfh-pin-saved';
            container.dataset.feedbackId = data.id;
            container.dataset.status = data.status || 'new';
            container.dataset.pinNumber = pinNumber;

            // Le pin numéroté en haut à droite
            const pin = document.createElement('span');
            pin.className = 'wpvfh-element-pin';
            pin.dataset.status = data.status || 'new';
            pin.textContent = pinNumber;
            pin.style.pointerEvents = 'auto';
            pin.style.cursor = 'pointer';

            container.appendChild(pin);

            // Événements sur le pin
            pin.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectPin(data.id);
            });

            pin.addEventListener('mouseenter', () => {
                container.classList.add('active');
            });

            pin.addEventListener('mouseleave', () => {
                container.classList.remove('active');
            });

            // Ajouter au body (pas au pinsContainer pour un meilleur positionnement)
            document.body.appendChild(container);

            // Stocker la référence avec toutes les données d'ancrage
            this.state.pins.push({
                element: container,
                pinElement: pin,
                data: data,
            });

            // Positionner selon l'ancrage DOM
            this.positionPinByAnchor(container, data);

            return container;
        },

        /**
         * Positionner un pin (outline + numéro) selon son ancrage DOM
         * @param {HTMLElement} container - Élément conteneur (outline)
         * @param {Object} data - Données du pin
         * @returns {void}
         */
        positionPinByAnchor: function(container, data) {
            let positioned = false;

            // 1. Essayer de positionner via le sélecteur DOM
            if (data.selector) {
                const anchorElement = this.findElement(data.selector);

                if (anchorElement) {
                    const rect = anchorElement.getBoundingClientRect();
                    const scrollX = window.scrollX || window.pageXOffset;
                    const scrollY = window.scrollY || window.pageYOffset;

                    // Positionner l'outline autour de l'élément
                    container.style.left = (rect.left + scrollX) + 'px';
                    container.style.top = (rect.top + scrollY) + 'px';
                    container.style.width = rect.width + 'px';
                    container.style.height = rect.height + 'px';
                    container.style.opacity = '1';

                    // Stocker la référence à l'élément d'ancrage
                    container._anchorElement = anchorElement;

                    positioned = true;

                    console.log('[Blazing Feedback] Pin #' + data.id + ' positionné via DOM anchor');
                }
            }

            // 2. Fallback: créer un outline factice basé sur les pourcentages
            if (!positioned) {
                const pageWidth = document.documentElement.scrollWidth;
                const pageHeight = document.documentElement.scrollHeight;

                const posX = data.position_x || data.percentX || 50;
                const posY = data.position_y || data.percentY || 50;

                const absoluteX = (posX / 100) * pageWidth;
                const absoluteY = (posY / 100) * pageHeight;

                // Pour les pins sans ancrage DOM, on crée un petit outline
                container.style.left = (absoluteX - 30) + 'px';
                container.style.top = (absoluteY - 30) + 'px';
                container.style.width = '60px';
                container.style.height = '60px';
                container.style.opacity = '0.7'; // Légèrement transparent si fallback

                console.log('[Blazing Feedback] Pin #' + data.id + ' positionné via fallback (%)', posX, posY);
            }
        },

        /**
         * Repositionner tous les pins selon leur ancrage DOM
         * @returns {void}
         */
        repositionAllPins: function() {
            this.state.pins.forEach(pin => {
                this.positionPinByAnchor(pin.element, pin.data);
            });
        },

        /**
         * Trouver un élément par son sélecteur
         * @param {string} selector - Sélecteur CSS
         * @returns {HTMLElement|null} Élément trouvé
         */
        findElement: function(selector) {
            if (!selector || selector === 'body') {
                return document.body;
            }

            try {
                return document.querySelector(selector);
            } catch (e) {
                console.warn('[Blazing Feedback] Sélecteur invalide:', selector);
                return null;
            }
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
            // Supprimer les outlines du body
            this.state.pins.forEach(pin => {
                if (pin.element && pin.element.parentNode) {
                    pin.element.remove();
                }
            });

            // Vider aussi le pinsContainer au cas où
            if (this.elements.pinsContainer) {
                this.elements.pinsContainer.innerHTML = '';
            }

            // Supprimer tous les outlines orphelins
            document.querySelectorAll('.wpvfh-element-feedback-outline').forEach(el => el.remove());

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
         * Scroller vers un pin avec highlight jaune
         * @param {number} feedbackId - ID du feedback
         * @returns {void}
         */
        scrollToPinWithHighlight: function(feedbackId) {
            const pin = this.state.pins.find(p => p.data.id === feedbackId);

            if (!pin) return;

            const rect = pin.element.getBoundingClientRect();
            const scrollY = window.scrollY || window.pageYOffset;

            window.scrollTo({
                top: scrollY + rect.top - (window.innerHeight / 2),
                behavior: 'smooth',
            });

            // Ajouter le highlight jaune après le scroll
            setTimeout(() => {
                // Retirer le highlight des autres pins
                this.state.pins.forEach(p => {
                    p.element.classList.remove('wpvfh-pin-highlighted');
                });

                // Ajouter le highlight au pin actuel
                pin.element.classList.add('wpvfh-pin-highlighted');

                // Retirer le highlight après 3 secondes
                setTimeout(() => {
                    pin.element.classList.remove('wpvfh-pin-highlighted');
                }, 3000);
            }, 500);
        },

        /**
         * Obtenir la position actuelle du pin
         * @returns {Object|null} Position du pin
         */
        getPosition: function() {
            return this.state.clickPosition;
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

        // =====================================================================
        // MODE INSPECTEUR D'ÉLÉMENTS (style DevTools)
        // =====================================================================

        /**
         * Démarrer le mode inspecteur d'éléments
         * @returns {void}
         */
        startInspector: function() {
            if (this.state.inspectorMode) return;

            this.state.inspectorMode = true;

            // Ajouter la classe au body
            document.body.classList.add('wpvfh-inspector-mode');

            // Créer l'élément de surbrillance
            this.createHighlightElement();

            // Créer le hint en haut
            this.createInspectorHint();

            // Ajouter les gestionnaires d'événements
            this._inspectorMoveHandler = this.handleInspectorMove.bind(this);
            this._inspectorClickHandler = this.handleInspectorClick.bind(this);

            document.addEventListener('mousemove', this._inspectorMoveHandler, true);
            document.addEventListener('click', this._inspectorClickHandler, true);

            // Annuler avec Echap
            this._inspectorEscHandler = (e) => {
                if (e.key === 'Escape') {
                    this.stopInspector();
                }
            };
            document.addEventListener('keydown', this._inspectorEscHandler);

            console.log('[Blazing Feedback] Mode inspecteur démarré');
            this.emitEvent('inspector-started');
        },

        /**
         * Arrêter le mode inspecteur d'éléments
         * @returns {void}
         */
        stopInspector: function() {
            if (!this.state.inspectorMode) return;

            this.state.inspectorMode = false;
            this.state.hoveredElement = null;

            // Retirer la classe du body
            document.body.classList.remove('wpvfh-inspector-mode');

            // Supprimer l'élément de surbrillance
            if (this.state.highlightEl) {
                this.state.highlightEl.remove();
                this.state.highlightEl = null;
            }

            // Supprimer le hint
            if (this.state.inspectorHint) {
                this.state.inspectorHint.remove();
                this.state.inspectorHint = null;
            }

            // Retirer les gestionnaires d'événements
            if (this._inspectorMoveHandler) {
                document.removeEventListener('mousemove', this._inspectorMoveHandler, true);
            }
            if (this._inspectorClickHandler) {
                document.removeEventListener('click', this._inspectorClickHandler, true);
            }
            if (this._inspectorEscHandler) {
                document.removeEventListener('keydown', this._inspectorEscHandler);
            }

            console.log('[Blazing Feedback] Mode inspecteur arrêté');
            this.emitEvent('inspector-stopped');
        },

        /**
         * Créer l'élément de surbrillance (highlight)
         * @returns {void}
         */
        createHighlightElement: function() {
            if (this.state.highlightEl) return;

            const highlight = document.createElement('div');
            highlight.className = 'wpvfh-element-highlight';
            highlight.style.display = 'none';
            highlight.innerHTML = '<span class="wpvfh-element-highlight-label"></span>';

            document.body.appendChild(highlight);
            this.state.highlightEl = highlight;
        },

        /**
         * Créer le hint du mode inspecteur
         * @returns {void}
         */
        createInspectorHint: function() {
            if (this.state.inspectorHint) return;

            const hint = document.createElement('div');
            hint.className = 'wpvfh-inspector-hint';
            hint.innerHTML = `
                <span class="wpvfh-hint-icon">🎯</span>
                <span class="wpvfh-hint-text">Sélectionnez un élément</span>
                <button type="button" class="wpvfh-hint-cancel">Annuler</button>
            `;

            // Bouton annuler
            hint.querySelector('.wpvfh-hint-cancel').addEventListener('click', (e) => {
                e.stopPropagation();
                this.stopInspector();
            });

            document.body.appendChild(hint);
            this.state.inspectorHint = hint;
        },

        /**
         * Gérer le mouvement de souris en mode inspecteur
         * @param {MouseEvent} event
         * @returns {void}
         */
        handleInspectorMove: function(event) {
            if (!this.state.inspectorMode) return;

            // Ignorer si sur le widget ou le hint
            if (event.target.closest('.wpvfh-widget') ||
                event.target.closest('.wpvfh-inspector-hint') ||
                event.target.closest('.wpvfh-element-highlight')) {
                this.hideHighlight();
                return;
            }

            const element = event.target;

            // Ignorer si même élément
            if (element === this.state.hoveredElement) return;

            this.state.hoveredElement = element;
            this.showHighlight(element);
        },

        /**
         * Afficher la surbrillance sur un élément
         * @param {HTMLElement} element
         * @returns {void}
         */
        showHighlight: function(element) {
            if (!this.state.highlightEl || !element) return;

            const rect = element.getBoundingClientRect();
            const highlight = this.state.highlightEl;

            // Positionner la surbrillance
            highlight.style.display = 'block';
            highlight.style.left = rect.left + 'px';
            highlight.style.top = rect.top + 'px';
            highlight.style.width = rect.width + 'px';
            highlight.style.height = rect.height + 'px';

            // Label avec tag et classes
            const label = highlight.querySelector('.wpvfh-element-highlight-label');
            if (label) {
                const tagName = element.tagName.toLowerCase();
                const id = element.id ? `#${element.id}` : '';
                const classes = element.className && typeof element.className === 'string'
                    ? '.' + element.className.split(/\s+/).filter(c => c && !c.startsWith('wpvfh-')).slice(0, 2).join('.')
                    : '';

                label.textContent = tagName + id + classes;

                // Déterminer si le label doit être en bas
                if (rect.top < 30) {
                    label.classList.add('bottom');
                } else {
                    label.classList.remove('bottom');
                }
            }
        },

        /**
         * Masquer la surbrillance
         * @returns {void}
         */
        hideHighlight: function() {
            if (this.state.highlightEl) {
                this.state.highlightEl.style.display = 'none';
            }
        },

        /**
         * Gérer le clic en mode inspecteur
         * @param {MouseEvent} event
         * @returns {void}
         */
        handleInspectorClick: function(event) {
            if (!this.state.inspectorMode) return;

            // Ignorer si sur le widget ou le hint
            if (event.target.closest('.wpvfh-widget') ||
                event.target.closest('.wpvfh-inspector-hint') ||
                event.target.closest('.wpvfh-element-highlight')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            const element = event.target;

            // Sélectionner l'élément
            this.selectElement(element, event.clientX, event.clientY);

            // Arrêter le mode inspecteur
            this.stopInspector();
        },

        /**
         * Sélectionner un élément
         * @param {HTMLElement} element
         * @param {number} clientX
         * @param {number} clientY
         * @returns {void}
         */
        selectElement: function(element, clientX, clientY) {
            // Effacer l'ancienne sélection
            this.clearSelection();

            this.state.selectedElement = element;

            // Calculer les données d'ancrage
            const anchorData = this.calculateAnchorPosition(element, clientX, clientY);
            this.state.selectedElementData = anchorData;

            // Créer le contour de sélection permanent
            this.createSelectedOutline(element);

            console.log('[Blazing Feedback] Élément sélectionné:', anchorData);

            // Émettre l'événement
            this.emitEvent('element-selected', {
                element: element,
                data: anchorData,
            });
        },

        /**
         * Créer le contour de sélection permanent
         * @param {HTMLElement} element
         * @returns {void}
         */
        createSelectedOutline: function(element) {
            // Supprimer l'ancien contour
            if (this.state.selectedOutlineEl) {
                this.state.selectedOutlineEl.remove();
            }

            const rect = element.getBoundingClientRect();
            const scrollX = window.scrollX || window.pageXOffset;
            const scrollY = window.scrollY || window.pageYOffset;

            const outline = document.createElement('div');
            outline.className = 'wpvfh-element-selected-outline';
            outline.style.left = (rect.left + scrollX) + 'px';
            outline.style.top = (rect.top + scrollY) + 'px';
            outline.style.width = rect.width + 'px';
            outline.style.height = rect.height + 'px';

            // Badge de check
            outline.innerHTML = '<span class="wpvfh-element-selected-badge">✓</span>';

            document.body.appendChild(outline);
            this.state.selectedOutlineEl = outline;

            // Observer le redimensionnement pour mettre à jour la position
            this._updateOutlinePosition = () => {
                if (this.state.selectedElement && this.state.selectedOutlineEl) {
                    const newRect = this.state.selectedElement.getBoundingClientRect();
                    const newScrollX = window.scrollX || window.pageXOffset;
                    const newScrollY = window.scrollY || window.pageYOffset;

                    this.state.selectedOutlineEl.style.left = (newRect.left + newScrollX) + 'px';
                    this.state.selectedOutlineEl.style.top = (newRect.top + newScrollY) + 'px';
                    this.state.selectedOutlineEl.style.width = newRect.width + 'px';
                    this.state.selectedOutlineEl.style.height = newRect.height + 'px';
                }
            };

            window.addEventListener('scroll', this._updateOutlinePosition, { passive: true });
            window.addEventListener('resize', this._updateOutlinePosition);
        },

        /**
         * Effacer la sélection d'élément
         * @returns {void}
         */
        clearSelection: function() {
            this.state.selectedElement = null;
            this.state.selectedElementData = null;

            if (this.state.selectedOutlineEl) {
                this.state.selectedOutlineEl.remove();
                this.state.selectedOutlineEl = null;
            }

            if (this._updateOutlinePosition) {
                window.removeEventListener('scroll', this._updateOutlinePosition);
                window.removeEventListener('resize', this._updateOutlinePosition);
            }

            this.emitEvent('selection-cleared');
        },

        /**
         * Obtenir les données de l'élément sélectionné
         * @returns {Object|null}
         */
        getSelectedElementData: function() {
            return this.state.selectedElementData;
        },

        /**
         * Vérifier si un élément est sélectionné
         * @returns {boolean}
         */
        hasSelection: function() {
            return this.state.selectedElement !== null;
        },

        /**
         * Basculer la visibilité des pins
         * @param {CustomEvent} event
         * @returns {void}
         */
        togglePinsVisibility: function(event) {
            const forceVisible = event?.detail?.visible;

            if (typeof forceVisible === 'boolean') {
                this.state.pinsVisible = forceVisible;
            } else {
                this.state.pinsVisible = !this.state.pinsVisible;
            }

            const visibility = this.state.pinsVisible ? 'visible' : 'hidden';

            // Masquer/afficher le conteneur
            if (this.elements.pinsContainer) {
                this.elements.pinsContainer.style.visibility = visibility;
            }

            // Masquer/afficher tous les outlines de feedback
            this.state.pins.forEach(pin => {
                if (pin.element) {
                    pin.element.style.visibility = visibility;
                }
            });

            // Masquer/afficher les contours de sélection temporaire
            if (this.state.selectedOutlineEl) {
                this.state.selectedOutlineEl.style.visibility = visibility;
            }

            this.emitEvent('pins-visibility-changed', { visible: this.state.pinsVisible });

            console.log('[Blazing Feedback] Pins visibilité:', this.state.pinsVisible);
        },

        /**
         * Renuméroter tous les pins selon un nouvel ordre
         * @param {Array} orderedIds - IDs des feedbacks dans le nouvel ordre
         * @returns {void}
         */
        renumberPins: function(orderedIds) {
            if (!orderedIds || !Array.isArray(orderedIds)) return;

            orderedIds.forEach((id, index) => {
                const pinData = this.state.pins.find(p => p.data.id === id);
                if (pinData) {
                    const newNumber = index + 1;
                    pinData.data._displayOrder = newNumber;

                    // Mettre à jour le numéro dans le DOM
                    if (pinData.pinElement) {
                        pinData.pinElement.textContent = newNumber;
                    }
                    if (pinData.element) {
                        pinData.element.dataset.pinNumber = newNumber;
                    }
                }
            });

            console.log('[Blazing Feedback] Pins renumérotés:', orderedIds);
            this.emitEvent('pins-renumbered', { order: orderedIds });
        },

        /**
         * Obtenir l'ordre actuel des pins
         * @returns {Array} IDs des feedbacks dans l'ordre actuel
         */
        getPinOrder: function() {
            return this.state.pins
                .sort((a, b) => (a.data._displayOrder || 0) - (b.data._displayOrder || 0))
                .map(p => p.data.id);
        },

        /**
         * Vérifier si les pins sont visibles
         * @returns {boolean}
         */
        arePinsVisible: function() {
            return this.state.pinsVisible;
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
