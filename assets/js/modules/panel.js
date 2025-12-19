/**
 * Toggle, panel open/close, tabs
 * 
 * Reference file for feedback-widget.js lines 940-1350
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
handleToggle * - handleAddClick * - handleVisibilityToggle * - openPanel * - closePanel * - switchTab
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=940, limit=411
 */

         * @returns {void}
         */
        handleVisibilityToggle: function(event) {
            event.preventDefault();

            const btn = this.elements.visibilityBtn;
            const isVisible = btn.dataset.visible === 'true';

            // Inverser la visibilité
            const newVisible = !isVisible;
            btn.dataset.visible = newVisible.toString();

            // Mettre à jour les icônes
            const iconVisible = btn.querySelector('.wpvfh-icon-visible');
            const iconHidden = btn.querySelector('.wpvfh-icon-hidden');

            if (iconVisible) iconVisible.hidden = !newVisible;
            if (iconHidden) iconHidden.hidden = newVisible;

            // Émettre l'événement pour masquer/afficher les pins
            this.emitEvent('toggle-pins', { visible: newVisible });

            // Notification
            this.showNotification(
                newVisible ? 'Points affichés' : 'Points masqués',
                'info'
            );
        },

        /**
         * Gérer le clic sur "Cibler un élément"
         * Démarre le mode inspecteur DevTools
         * @param {Event} event
         * @returns {void}
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

            this.state.isOpen = true;

            // Ajouter la classe au body pour pousser le contenu
            document.body.classList.add('wpvfh-panel-active');

            // Ajouter la classe de position du panel (gauche/droite)
            const panelPosition = this.elements.container?.dataset?.panelPosition || 'right';
            if (panelPosition === 'left') {
                document.body.classList.add('wpvfh-panel-left');
            }

            if (this.elements.panel) {
                // Retirer l'attribut hidden
                this.elements.panel.removeAttribute('hidden');
                this.elements.panel.hidden = false;
                this.elements.panel.setAttribute('aria-hidden', 'false');

                // Forcer un reflow avant d'ajouter la classe d'animation
                void this.elements.panel.offsetHeight;

                // Ajouter la classe pour l'animation d'ouverture
                this.elements.panel.classList.add('wpvfh-panel-open');

                console.log('[Blazing Feedback] Panel classes:', this.elements.panel.className);
            }

            // Afficher l'overlay (visible seulement sur mobile en mode push)
            if (this.elements.sidebarOverlay) {
                this.elements.sidebarOverlay.classList.add('wpvfh-overlay-visible');
            }

            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.setAttribute('aria-expanded', 'true');
            }

            // S'assurer de basculer sur le bon onglet
            this.switchTab(tab);

            // Focus sur le champ de commentaire si onglet nouveau
            if (tab === 'new' && this.elements.commentField) {
                setTimeout(() => this.elements.commentField.focus(), 350);
            }

            console.log('[Blazing Feedback] Panel ouvert, onglet:', tab);

            // Repositionner les pins après l'animation du margin
            setTimeout(() => {
                if (window.BlazingAnnotation && window.BlazingAnnotation.repositionAllPins) {
                    window.BlazingAnnotation.repositionAllPins();
                }
            }, 350);

            this.emitEvent('panel-opened');
        },

        /**
         * Fermer le panel de feedback (sidebar)
         * @returns {void}
         */
        closePanel: function() {
            this.state.isOpen = false;
            this.state.feedbackMode = 'view';

            // Retirer les classes du body
            document.body.classList.remove('wpvfh-panel-active');
            document.body.classList.remove('wpvfh-panel-left');

            if (this.elements.panel) {
                this.elements.panel.classList.remove('wpvfh-panel-open');
                // Masquer après l'animation
                setTimeout(() => {
                    if (!this.state.isOpen) {
                        this.elements.panel.hidden = true;
                        this.elements.panel.setAttribute('hidden', '');
                        this.elements.panel.setAttribute('aria-hidden', 'true');
                        this.elements.panel.style.pointerEvents = '';
                    }
                }, 300);
            }

            // Masquer l'overlay
            if (this.elements.sidebarOverlay) {
                this.elements.sidebarOverlay.classList.remove('wpvfh-overlay-visible');
            }

            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.setAttribute('aria-expanded', 'false');
            }

            // Nettoyer le formulaire seulement si on n'est pas en mode sélection d'élément
            if (!this.state.isSelectingElement) {
                this.resetForm();
            }

            console.log('[Blazing Feedback] Panel fermé');

            // Repositionner les pins après l'animation du margin
            setTimeout(() => {
                if (window.BlazingAnnotation && window.BlazingAnnotation.repositionAllPins) {
                    window.BlazingAnnotation.repositionAllPins();
                }
            }, 350);

            this.emitEvent('panel-closed');
        },

        /**
         * Changer d'onglet
         * @param {string} tabName - Nom de l'onglet ('new', 'list', 'pages' ou 'details')
         */
        switchTab: function(tabName) {
            // Mettre à jour les boutons d'onglet
            if (this.elements.tabs && this.elements.tabs.length > 0) {
                this.elements.tabs.forEach(tab => {
                    // L'onglet "Nouveau" n'est visible QUE quand on crée un feedback
                    if (tab.dataset.tab === 'new') {
                        tab.hidden = (tabName !== 'new');
                    }
                    // L'onglet "Détails" n'est visible QUE si un feedback est sélectionné
                    if (tab.dataset.tab === 'details') {
                        const showDetailsTab = (tabName === 'details' && this.state.currentFeedbackId);
                        tab.hidden = !showDetailsTab;
                    }
                    tab.classList.toggle('active', tab.dataset.tab === tabName);
                });
            }

            // Afficher/masquer les contenus
            if (this.elements.tabNew) {
                this.elements.tabNew.classList.toggle('active', tabName === 'new');
            }
            if (this.elements.tabList) {
                this.elements.tabList.classList.toggle('active', tabName === 'list');
            }
            if (this.elements.tabDetails) {
                this.elements.tabDetails.classList.toggle('active', tabName === 'details');
            }
            if (this.elements.tabPages) {
                this.elements.tabPages.classList.toggle('active', tabName === 'pages');
            }
            if (this.elements.tabPriority) {
                this.elements.tabPriority.classList.toggle('active', tabName === 'priority');
            }
            if (this.elements.tabMetadata) {
                this.elements.tabMetadata.classList.toggle('active', tabName === 'metadata');
            }

            // Si on va sur la liste, charger les feedbacks
            if (tabName === 'list') {
                this.renderPinsList();
                this.updateFilterCounts();
                this.updateValidationSection();
                // Réinitialiser le feedback courant
                this.state.currentFeedbackId = null;
            }

            // Si on va sur les pages, charger la liste des pages
            if (tabName === 'pages') {
                this.loadAllPages();
            }

            // Si on va sur la priorité, charger les feedbacks par priorité
            if (tabName === 'priority') {
                this.renderPriorityLists();
            }

            // Si on va sur les métadatas, charger les listes
            if (tabName === 'metadata') {
                this.renderMetadataLists();
            }
        },

        /**
         * Afficher la liste des pins dans la sidebar avec drag-and-drop
         */
        renderPinsList: function() {
            if (!this.elements.pinsList) return;

            const feedbacks = this.getFilteredFeedbacks();