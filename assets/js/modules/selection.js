/**
 * Sélection d'éléments, inspecteur
 * 
 * Reference file for feedback-widget.js lines 975-1170
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handleSelectElement * - handleClearSelection * - handleElementSelected * - handleSelectionCleared * - handleInspectorStopped * - startFeedbackMode
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=975, limit=196
 */

        handleSelectElement: function(event) {
            event.preventDefault();

            // Sauvegarder le contenu du formulaire avant de fermer le panel
            this.state.savedFormData = {
                comment: this.elements.commentInput?.value || '',
                type: this.elements.feedbackType?.value || '',
                priority: this.elements.feedbackPriority?.value || '',
                tags: this.elements.feedbackTags?.value || '',
            };

            // Fermer temporairement le panel pour faciliter la sélection
            // Mais marquer qu'on ne doit pas réinitialiser le formulaire
            this.state.isSelectingElement = true;
            if (this.state.isOpen) {
                this.closePanel();
            }

            // Démarrer le mode inspecteur
            this.emitEvent('start-inspector');
        },

        /**
         * Gérer le clic sur "Effacer la sélection"
         * @param {Event} event
         * @returns {void}
         */
        handleClearSelection: function(event) {
            event.preventDefault();

            // Effacer la sélection
            this.emitEvent('clear-selection');

            // Réinitialiser l'état local
            this.state.pinPosition = null;

            // Masquer l'indicateur de sélection (double méthode pour fiabilité)
            if (this.elements.selectedElement) {
                this.elements.selectedElement.hidden = true;
                this.elements.selectedElement.classList.add('wpvfh-hidden');
                this.elements.selectedElement.style.display = 'none';
            }

            // Afficher le bouton de sélection
            if (this.elements.selectElementBtn) {
                this.elements.selectElementBtn.hidden = false;
                this.elements.selectElementBtn.classList.remove('wpvfh-hidden');
                this.elements.selectElementBtn.style.display = '';
            }

            console.log('[Blazing Feedback] Sélection effacée');
        },

        /**
         * Gérer la sélection d'un élément (événement de l'inspecteur)
         * @param {CustomEvent} event
         * @returns {void}
         */
        handleElementSelected: function(event) {
            const { data } = event.detail;

            // Stocker les données de position
            this.state.pinPosition = data;

            // Mettre à jour les champs cachés
            if (this.elements.positionX) {
                this.elements.positionX.value = data.percentX || data.position_x || '';
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = data.percentY || data.position_y || '';
            }

            // Afficher l'indicateur de sélection (triple méthode pour fiabilité)
            if (this.elements.selectedElement) {
                this.elements.selectedElement.hidden = false;
                this.elements.selectedElement.classList.remove('wpvfh-hidden');
                this.elements.selectedElement.style.display = 'flex';

                // Afficher le sélecteur si disponible
                const textEl = this.elements.selectedElement.querySelector('.wpvfh-selected-text');
                if (textEl && data.selector) {
                    // Tronquer le sélecteur s'il est trop long
                    const shortSelector = data.selector.length > 40
                        ? data.selector.substring(0, 37) + '...'
                        : data.selector;
                    textEl.textContent = `Élément: ${data.anchor_tag}`;
                }
            }

            // Masquer le bouton de sélection
            if (this.elements.selectElementBtn) {
                this.elements.selectElementBtn.hidden = true;
                this.elements.selectElementBtn.classList.add('wpvfh-hidden');
                this.elements.selectElementBtn.style.display = 'none';
            }

            // Ouvrir le panel avec l'onglet nouveau si fermé
            if (!this.state.isOpen) {
                this.openPanel('new');
            }

            // Restaurer le contenu du formulaire sauvegardé
            if (this.state.savedFormData) {
                if (this.elements.commentInput && this.state.savedFormData.comment) {
                    this.elements.commentInput.value = this.state.savedFormData.comment;
                }
                if (this.elements.feedbackType && this.state.savedFormData.type) {
                    this.elements.feedbackType.value = this.state.savedFormData.type;
                }
                if (this.elements.feedbackPriority && this.state.savedFormData.priority) {
                    this.elements.feedbackPriority.value = this.state.savedFormData.priority;
                }
                if (this.elements.feedbackTags && this.state.savedFormData.tags) {
                    this.elements.feedbackTags.value = this.state.savedFormData.tags;
                }
                // Nettoyer les données sauvegardées
                this.state.savedFormData = null;
            }

            // Réinitialiser le flag de sélection d'élément
            this.state.isSelectingElement = false;

            console.log('[Blazing Feedback] Élément sélectionné:', data);
        },

        /**
         * Gérer l'effacement de la sélection
         * @param {CustomEvent} event
         * @returns {void}
         */
        handleSelectionCleared: function(event) {
            // Réafficher le bouton de sélection
            if (this.elements.selectElementBtn) {
                this.elements.selectElementBtn.hidden = false;
                this.elements.selectElementBtn.classList.remove('wpvfh-hidden');
                this.elements.selectElementBtn.style.display = '';
            }

            // Masquer l'indicateur (triple méthode pour fiabilité)
            if (this.elements.selectedElement) {
                this.elements.selectedElement.hidden = true;
                this.elements.selectedElement.classList.add('wpvfh-hidden');
                this.elements.selectedElement.style.display = 'none';
            }
        },

        /**
         * Gérer l'arrêt du mode inspecteur (annulation)
         * @param {CustomEvent} event
         * @returns {void}
         */
        handleInspectorStopped: function(event) {
            // Si on était en mode sélection d'élément et aucune sélection n'a été faite
            if (this.state.isSelectingElement) {
                // Réouvrir le panel et restaurer le contenu sauvegardé
                if (this.state.savedFormData) {
                    this.openPanel('new');
                    if (this.elements.commentInput && this.state.savedFormData.comment) {
                        this.elements.commentInput.value = this.state.savedFormData.comment;
                    }
                    if (this.elements.feedbackType && this.state.savedFormData.type) {
                        this.elements.feedbackType.value = this.state.savedFormData.type;
                    }
                    if (this.elements.feedbackPriority && this.state.savedFormData.priority) {
                        this.elements.feedbackPriority.value = this.state.savedFormData.priority;
                    }
                    if (this.elements.feedbackTags && this.state.savedFormData.tags) {
                        this.elements.feedbackTags.value = this.state.savedFormData.tags;
                    }
                    this.state.savedFormData = null;
                }
                this.state.isSelectingElement = false;
            }
        },

        /**
         * Démarrer le mode feedback (ancien mode annotation)
         * @returns {void}
         */
        startFeedbackMode: function() {
            this.state.feedbackMode = 'annotate';

            // Activer le mode annotation
            this.emitEvent('start-annotation');

            // Notification
            this.showNotification(this.config.i18n?.clickToPin || 'Cliquez pour placer un marqueur', 'info');
        },

        /**
         * Ouvrir le panel de feedback (sidebar)
         * @param {string} tab - Onglet à afficher ('new' ou 'list')
         * @returns {void}
         */
        openPanel: function(tab = 'new') {
            console.log('[Blazing Feedback] Ouverture du panel...');