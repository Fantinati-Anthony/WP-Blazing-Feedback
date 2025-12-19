/**
 * Bindage des événements
 * 
 * Reference file for feedback-widget.js lines 331-700
 * See main file: assets/js/feedback-widget.js
 * 
 * Methods included:
 * - 
bindEvents
 * 
 * @package Blazing_Feedback
 */

/* 
 * To view this section, read feedback-widget.js with:
 * offset=331, limit=370
 */

        bindEvents: function() {
            // Bouton toggle (principal "Feedback" - ouvre la liste)
            if (this.elements.toggleBtn) {
                this.elements.toggleBtn.addEventListener('click', this.handleToggle.bind(this));
            }

            // Bouton ajouter (+) - ouvre le formulaire nouveau feedback
            if (this.elements.addBtn) {
                this.elements.addBtn.addEventListener('click', this.handleAddClick.bind(this));
            }

            // Bouton visibilité - afficher/masquer les pins
            if (this.elements.visibilityBtn) {
                this.elements.visibilityBtn.addEventListener('click', this.handleVisibilityToggle.bind(this));
            }

            // Bouton cibler un élément
            if (this.elements.selectElementBtn) {
                this.elements.selectElementBtn.addEventListener('click', this.handleSelectElement.bind(this));
            }

            // Bouton effacer la sélection (avec délégation d'événements)
            if (this.elements.clearSelectionBtn) {
                this.elements.clearSelectionBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleClearSelection(e);
                });
            }
            // Délégation d'événements pour le bouton clear dans selectedElement
            if (this.elements.selectedElement) {
                this.elements.selectedElement.addEventListener('click', (e) => {
                    if (e.target.classList.contains('wpvfh-clear-selection')) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.handleClearSelection(e);
                    }
                });
            }

            // Boutons du panel
            if (this.elements.closeBtn) {
                this.elements.closeBtn.addEventListener('click', this.closePanel.bind(this));
            }
            if (this.elements.cancelBtn) {
                this.elements.cancelBtn.addEventListener('click', this.handleCancel.bind(this));
            }

            // Soumission du formulaire
            if (this.elements.form) {
                this.elements.form.addEventListener('submit', this.handleSubmit.bind(this));
            }

            // Boutons de la barre d'outils média
            if (this.elements.toolButtons) {
                this.elements.toolButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => this.handleToolClick(e, btn.dataset.tool));
                });
            }

            // Bouton enregistrement vocal
            if (this.elements.voiceRecordBtn) {
                this.elements.voiceRecordBtn.addEventListener('click', this.handleVoiceRecord.bind(this));
            }

            // Bouton enregistrement vidéo
            if (this.elements.videoRecordBtn) {
                this.elements.videoRecordBtn.addEventListener('click', this.handleVideoRecord.bind(this));
            }

            // Boutons supprimer média
            document.querySelectorAll('.wpvfh-remove-media').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const parent = e.target.closest('.wpvfh-screenshot-preview, .wpvfh-audio-preview, .wpvfh-video-preview');
                    if (parent) {
                        if (parent.classList.contains('wpvfh-screenshot-preview')) this.clearScreenshot();
                        if (parent.classList.contains('wpvfh-audio-preview')) this.clearVoiceRecording();
                        if (parent.classList.contains('wpvfh-video-preview')) this.clearVideoRecording();
                    }
                });
            });

            // Événements des modules
            document.addEventListener('blazing-feedback:pin-placed', this.handlePinPlaced.bind(this));
            document.addEventListener('blazing-feedback:pin-selected', this.handlePinSelected.bind(this));
            document.addEventListener('blazing-feedback:capture-success', this.handleCaptureSuccess.bind(this));
            document.addEventListener('blazing-feedback:capture-error', this.handleCaptureError.bind(this));

            // Événements inspecteur d'éléments
            document.addEventListener('blazing-feedback:element-selected', this.handleElementSelected.bind(this));
            document.addEventListener('blazing-feedback:selection-cleared', this.handleSelectionCleared.bind(this));
            document.addEventListener('blazing-feedback:inspector-stopped', this.handleInspectorStopped.bind(this));

            // Événements enregistrement vocal
            document.addEventListener('blazing-feedback:voice-recording-complete', this.handleVoiceComplete.bind(this));
            document.addEventListener('blazing-feedback:voice-transcription-update', this.handleTranscriptionUpdate.bind(this));

            // Événements enregistrement vidéo
            document.addEventListener('blazing-feedback:screen-recording-complete', this.handleVideoComplete.bind(this));

            // Échap pour fermer
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.state.isOpen) {
                    this.closePanel();
                }
            });

            // Annuler dans l'overlay
            const hintClose = document.querySelector('.wpvfh-hint-close');
            if (hintClose) {
                hintClose.addEventListener('click', () => {
                    this.emitEvent('stop-annotation');
                    this.state.feedbackMode = 'view';
                });
            }

            // Sidebar overlay (fermer au clic)
            if (this.elements.sidebarOverlay) {
                this.elements.sidebarOverlay.addEventListener('click', this.closePanel.bind(this));
            }

            // Onglets
            if (this.elements.tabs) {
                this.elements.tabs.forEach(tab => {
                    tab.addEventListener('click', () => this.switchTab(tab.dataset.tab));
                });
            }

            // Sous-onglets métadatas
            if (this.elements.metadataSubtabs) {
                this.elements.metadataSubtabs.forEach(subtab => {
                    subtab.addEventListener('click', () => this.switchMetadataSubtab(subtab.dataset.subtab));
                });
            }

            // Bouton "Ajouter un feedback" dans l'onglet liste (même comportement que le bouton +)
            if (this.elements.addFeedbackBtn) {
                this.elements.addFeedbackBtn.addEventListener('click', () => {
                    this.switchTab('new');
                    // Focus sur le champ commentaire
                    if (this.elements.commentField) {
                        setTimeout(() => this.elements.commentField.focus(), 350);
                    }
                });
            }

            // Bouton retour dans les détails
            if (this.elements.backToListBtn) {
                this.elements.backToListBtn.addEventListener('click', () => {
                    this.switchTab('list');
                });
            }

            // Changement de statut
            if (this.elements.statusSelect) {
                this.elements.statusSelect.addEventListener('change', (e) => {
                    if (this.state.currentFeedbackId) {
                        this.updateFeedbackStatus(this.state.currentFeedbackId, e.target.value);
                    }
                });
            }

            // Changement de type (vue détails)
            if (this.elements.detailType) {
                this.elements.detailType.addEventListener('change', (e) => {
                    if (this.state.currentFeedbackId) {
                        this.updateFeedbackMeta(this.state.currentFeedbackId, 'feedback_type', e.target.value);
                    }
                });
            }

            // Changement de priorité (vue détails)
            if (this.elements.detailPrioritySelect) {
                this.elements.detailPrioritySelect.addEventListener('change', (e) => {
                    if (this.state.currentFeedbackId) {
                        this.updateFeedbackPriority(this.state.currentFeedbackId, e.target.value);
                    }
                });
            }

            // Tags dans le formulaire de création (virgule ou Entrée)
            if (this.elements.feedbackTagsInput) {
                this.elements.feedbackTagsInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        const newTag = e.target.value.replace(/,/g, '').trim();
                        if (newTag) {
                            this.addFormTag(newTag);
                            e.target.value = '';
                        }
                    }
                    // Supprimer le dernier tag avec Backspace si le champ est vide
                    if (e.key === 'Backspace' && e.target.value === '') {
                        this.removeLastFormTag();
                    }
                });
                // Gérer aussi la virgule tapée (pour les cas où keydown ne capture pas)
                this.elements.feedbackTagsInput.addEventListener('input', (e) => {
                    if (e.target.value.includes(',')) {
                        const parts = e.target.value.split(',');
                        parts.forEach((part, index) => {
                            const tag = part.trim();
                            if (tag && index < parts.length - 1) {
                                this.addFormTag(tag);
                            }
                        });
                        e.target.value = parts[parts.length - 1].trim();
                    }
                });
            }

            // Tags dans la vue détails (virgule ou Entrée)
            if (this.elements.detailTagsInput) {
                this.elements.detailTagsInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        const newTag = e.target.value.replace(/,/g, '').trim();
                        if (newTag && this.state.currentFeedbackId) {
                            this.addTag(newTag);
                            e.target.value = '';
                        }
                    }
                    // Supprimer le dernier tag avec Backspace si le champ est vide
                    if (e.key === 'Backspace' && e.target.value === '') {
                        this.removeLastTag();
                    }
                });
                // Gérer aussi la virgule tapée
                this.elements.detailTagsInput.addEventListener('input', (e) => {
                    if (e.target.value.includes(',')) {
                        const parts = e.target.value.split(',');
                        parts.forEach((part, index) => {
                            const tag = part.trim();
                            if (tag && index < parts.length - 1 && this.state.currentFeedbackId) {
                                this.addTag(tag);
                            }
                        });
                        e.target.value = parts[parts.length - 1].trim();
                    }
                });
            }

            // Tags prédéfinis (boutons cliquables)
            const predefinedTagBtns = document.querySelectorAll('.wpvfh-predefined-tag-btn');
            predefinedTagBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tagLabel = btn.getAttribute('data-tag');
                    const tagColor = btn.getAttribute('data-color');
                    if (tagLabel) {
                        this.addFormTag(tagLabel, tagColor);
                        btn.classList.add('selected');
                    }
                });
            });

            // Envoi de réponse
            if (this.elements.sendReplyBtn) {
                this.elements.sendReplyBtn.addEventListener('click', () => {
                    if (this.state.currentFeedbackId && this.elements.replyInput) {
                        const content = this.elements.replyInput.value.trim();
                        if (content) {
                            this.addReply(this.state.currentFeedbackId, content);
                        }
                    }
                });
            }

            // Filtres
            if (this.elements.filterButtons) {
                this.elements.filterButtons.forEach(btn => {
                    btn.addEventListener('click', () => this.handleFilterClick(btn.dataset.status));
                });
            }

            // Bouton supprimer feedback
            if (this.elements.deleteFeedbackBtn) {
                this.elements.deleteFeedbackBtn.addEventListener('click', () => this.showDeleteModal());
            }

            // Modal suppression
            if (this.elements.cancelDeleteBtn) {
                this.elements.cancelDeleteBtn.addEventListener('click', () => this.hideDeleteModal());
            }
            if (this.elements.confirmDeleteBtn) {
                this.elements.confirmDeleteBtn.addEventListener('click', () => this.confirmDeleteFeedback());
            }

            // Fermer modal en cliquant sur l'overlay
            document.querySelectorAll('.wpvfh-modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', () => {
                    if (this.elements.confirmModal) this.elements.confirmModal.hidden = true;
                    if (this.elements.validateModal) this.elements.validateModal.hidden = true;
                });
            });

            // Bouton valider la page
            if (this.elements.validatePageBtn) {
                this.elements.validatePageBtn.addEventListener('click', () => this.showValidateModal());
            }

            // Modal validation
            if (this.elements.cancelValidateBtn) {
                this.elements.cancelValidateBtn.addEventListener('click', () => {
                    if (this.elements.validateModal) this.elements.validateModal.hidden = true;
                });
            }
            if (this.elements.confirmValidateBtn) {
                this.elements.confirmValidateBtn.addEventListener('click', () => this.confirmValidatePage());
            }

            // Pièces jointes
            if (this.elements.addAttachmentBtn && this.elements.attachmentsInput) {
                this.elements.addAttachmentBtn.addEventListener('click', () => {
                    this.elements.attachmentsInput.click();
                });
                this.elements.attachmentsInput.addEventListener('change', (e) => {
                    this.handleAttachmentSelect(e.target.files);
                });
            }

            // Mentions @ dans le textarea
            if (this.elements.commentField) {
                this.elements.commentField.addEventListener('input', (e) => {
                    this.handleMentionInput(e);
                });
                this.elements.commentField.addEventListener('keydown', (e) => {
                    this.handleMentionKeydown(e);
                });
            }

            // Recherche
            if (this.elements.searchBtn) {
                this.elements.searchBtn.addEventListener('click', () => this.openSearchModal());
            }
            if (this.elements.searchClose) {
                this.elements.searchClose.addEventListener('click', () => this.closeSearchModal());
            }
            if (this.elements.searchModal) {
                this.elements.searchModal.addEventListener('click', (e) => {
                    if (e.target === this.elements.searchModal) {
                        this.closeSearchModal();
                    }
                });
            }
            if (this.elements.searchForm) {
                this.elements.searchForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.performSearch();
                });
            }
            if (this.elements.searchReset) {
                this.elements.searchReset.addEventListener('click', () => this.resetSearch());
            }
        },

        /**
         * Gérer le clic sur un outil média
         * @param {Event} event - Événement
         * @param {string} tool - Nom de l'outil
         */
        handleToolClick: function(event, tool) {
            event.preventDefault();

            // Désactiver tous les boutons
            this.elements.toolButtons.forEach(btn => btn.classList.remove('active'));

            // Masquer toutes les sections
            if (this.elements.voiceSection) this.elements.voiceSection.hidden = true;
            if (this.elements.videoSection) this.elements.videoSection.hidden = true;