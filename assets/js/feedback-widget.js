/**
 * Blazing Feedback - Widget Principal
 *
 * Contrôleur principal qui orchestre les modules
 * Screenshot et Annotation
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Widget Blazing Feedback
     * @namespace
     */
    const BlazingFeedback = {

        /**
         * Configuration depuis WordPress
         * @type {Object}
         */
        config: window.wpvfhData || {},

        /**
         * État du widget
         * @type {Object}
         */
        state: {
            isOpen: false,
            isSubmitting: false,
            feedbackMode: 'view',      // 'view' | 'create' | 'annotate'
            currentFeedbacks: [],
            screenshotData: null,
            pinPosition: null,
            currentFeedbackId: null,   // ID du feedback en cours de visualisation
            currentFilter: 'all',       // Filtre actif
            allPages: [],              // Liste de toutes les pages avec feedbacks
            attachments: [],           // Fichiers attachés au formulaire
            mentionUsers: [],          // Liste des utilisateurs pour mentions
            feedbackToDelete: null,    // ID du feedback à supprimer (modal)
        },

        /**
         * Éléments DOM
         * @type {Object}
         */
        elements: {},

        /**
         * Initialiser le widget
         * @returns {void}
         */
        init: function() {
            // Vérifier les permissions
            if (!this.config.canCreate && !this.config.canModerate) {
                console.log('[Blazing Feedback] Utilisateur sans permissions');
                return;
            }

            this.cacheElements();
            this.moveFixedElementsToBody();
            this.bindEvents();
            this.loadExistingFeedbacks();
            this.checkOpenFeedbackParam();

            console.log('[Blazing Feedback] Widget initialisé');
        },

        /**
         * Déplacer les éléments fixed vers body pour éviter les problèmes de stacking context
         * @returns {void}
         */
        moveFixedElementsToBody: function() {
            // Déplacer le panel vers body pour éviter les problèmes avec transform/filter des parents
            if (this.elements.panel && this.elements.panel.parentNode !== document.body) {
                document.body.appendChild(this.elements.panel);
                console.log('[Blazing Feedback] Panel déplacé vers body');
            }

            // Déplacer l'overlay vers body aussi
            if (this.elements.sidebarOverlay && this.elements.sidebarOverlay.parentNode !== document.body) {
                document.body.appendChild(this.elements.sidebarOverlay);
            }

            // Déplacer les modals vers body
            if (this.elements.confirmModal && this.elements.confirmModal.parentNode !== document.body) {
                document.body.appendChild(this.elements.confirmModal);
            }
            if (this.elements.validateModal && this.elements.validateModal.parentNode !== document.body) {
                document.body.appendChild(this.elements.validateModal);
            }
        },

        /**
         * Mettre en cache les éléments DOM
         * @returns {void}
         */
        cacheElements: function() {
            this.elements = {
                container: document.getElementById('wpvfh-container'),
                toggleBtn: document.getElementById('wpvfh-toggle-btn'),
                panel: document.getElementById('wpvfh-panel'),
                sidebarOverlay: document.getElementById('wpvfh-sidebar-overlay'),
                form: document.getElementById('wpvfh-form'),
                commentField: document.getElementById('wpvfh-comment'),
                screenshotPreview: document.getElementById('wpvfh-screenshot-preview'),
                screenshotData: document.getElementById('wpvfh-screenshot-data'),
                positionX: document.getElementById('wpvfh-position-x'),
                positionY: document.getElementById('wpvfh-position-y'),
                pinInfo: document.querySelector('.wpvfh-pin-info'),
                closeBtn: document.querySelector('.wpvfh-close-btn'),
                cancelBtn: document.querySelector('.wpvfh-cancel-btn'),
                submitBtn: document.querySelector('.wpvfh-submit-btn'),
                notifications: document.getElementById('wpvfh-notifications'),
                overlay: document.getElementById('wpvfh-annotation-overlay'),
                // Nouveaux boutons flottants
                addBtn: document.getElementById('wpvfh-add-btn'),
                visibilityBtn: document.getElementById('wpvfh-visibility-btn'),
                // Section ciblage d'élément
                selectElementBtn: document.getElementById('wpvfh-select-element-btn'),
                selectedElement: document.getElementById('wpvfh-selected-element'),
                clearSelectionBtn: document.querySelector('.wpvfh-clear-selection'),
                // Onglets et liste
                tabs: document.querySelectorAll('.wpvfh-tab'),
                tabNew: document.getElementById('wpvfh-tab-new'),
                tabNewBtn: document.getElementById('wpvfh-tab-new-btn'),
                tabList: document.getElementById('wpvfh-tab-list'),
                tabDetails: document.getElementById('wpvfh-tab-details'),
                tabDetailsBtn: document.getElementById('wpvfh-tab-details-btn'),
                tabPages: document.getElementById('wpvfh-tab-pages'),
                pinsList: document.getElementById('wpvfh-pins-list'),
                pinsCount: document.getElementById('wpvfh-pins-count'),
                emptyState: document.getElementById('wpvfh-empty-state'),
                addFeedbackBtn: document.querySelector('.wpvfh-add-feedback-btn'),
                feedbackCount: document.getElementById('wpvfh-feedback-count'),
                // Filtres
                filters: document.getElementById('wpvfh-filters'),
                filterButtons: document.querySelectorAll('.wpvfh-filter-btn'),
                // Pages
                pagesList: document.getElementById('wpvfh-pages-list'),
                pagesEmpty: document.getElementById('wpvfh-pages-empty'),
                pagesLoading: document.getElementById('wpvfh-pages-loading'),
                // Validation de page
                pageValidation: document.getElementById('wpvfh-page-validation'),
                validatePageBtn: document.getElementById('wpvfh-validate-page-btn'),
                validationStatus: document.getElementById('wpvfh-validation-status'),
                validationHint: document.getElementById('wpvfh-validation-hint'),
                // Suppression
                deleteSection: document.getElementById('wpvfh-delete-section'),
                deleteFeedbackBtn: document.getElementById('wpvfh-delete-feedback-btn'),
                // Modals
                confirmModal: document.getElementById('wpvfh-confirm-modal'),
                cancelDeleteBtn: document.getElementById('wpvfh-cancel-delete'),
                confirmDeleteBtn: document.getElementById('wpvfh-confirm-delete'),
                validateModal: document.getElementById('wpvfh-validate-modal'),
                cancelValidateBtn: document.getElementById('wpvfh-cancel-validate'),
                confirmValidateBtn: document.getElementById('wpvfh-confirm-validate'),
                // Mentions
                mentionDropdown: document.getElementById('wpvfh-mention-dropdown'),
                mentionList: document.getElementById('wpvfh-mention-list'),
                // Pièces jointes
                attachmentsInput: document.getElementById('wpvfh-attachments'),
                addAttachmentBtn: document.getElementById('wpvfh-add-attachment-btn'),
                attachmentsPreview: document.getElementById('wpvfh-attachments-preview'),
                // Invitations
                inviteSection: document.getElementById('wpvfh-invite-section'),
                participantsList: document.getElementById('wpvfh-participants-list'),
                inviteInput: document.getElementById('wpvfh-invite-input'),
                inviteBtn: document.getElementById('wpvfh-invite-btn'),
                userSuggestions: document.getElementById('wpvfh-user-suggestions'),
                // Éléments détails
                backToListBtn: document.getElementById('wpvfh-back-to-list'),
                detailId: document.getElementById('wpvfh-detail-id'),
                detailStatus: document.getElementById('wpvfh-detail-status'),
                detailAuthor: document.getElementById('wpvfh-detail-author'),
                detailDate: document.getElementById('wpvfh-detail-date'),
                detailComment: document.getElementById('wpvfh-detail-comment'),
                detailScreenshot: document.getElementById('wpvfh-detail-screenshot'),
                detailReplies: document.getElementById('wpvfh-detail-replies'),
                repliesList: document.getElementById('wpvfh-replies-list'),
                detailActions: document.getElementById('wpvfh-detail-actions'),
                statusSelect: document.getElementById('wpvfh-status-select'),
                replyInput: document.getElementById('wpvfh-reply-input'),
                sendReplyBtn: document.getElementById('wpvfh-send-reply'),
                // Éléments média
                mediaToolbar: document.querySelector('.wpvfh-media-toolbar'),
                toolButtons: document.querySelectorAll('.wpvfh-tool-btn'),
                voiceSection: document.getElementById('wpvfh-voice-section'),
                voiceRecordBtn: document.getElementById('wpvfh-voice-record'),
                voicePreview: document.getElementById('wpvfh-voice-preview'),
                transcriptPreview: document.getElementById('wpvfh-transcript-preview'),
                videoSection: document.getElementById('wpvfh-video-section'),
                videoRecordBtn: document.getElementById('wpvfh-video-record'),
                videoPreview: document.getElementById('wpvfh-video-preview'),
                audioData: document.getElementById('wpvfh-audio-data'),
                videoData: document.getElementById('wpvfh-video-data'),
                transcriptField: document.getElementById('wpvfh-transcript'),
                // Priorité
                tabPriority: document.getElementById('wpvfh-tab-priority'),
                priorityDropzones: document.querySelectorAll('.wpvfh-dropzone'),
                priorityLists: {
                    high: document.getElementById('wpvfh-priority-high-list'),
                    medium: document.getElementById('wpvfh-priority-medium-list'),
                    low: document.getElementById('wpvfh-priority-low-list'),
                    none: document.getElementById('wpvfh-priority-none-list'),
                },
                // Recherche
                searchBtn: document.getElementById('wpvfh-search-btn'),
                searchModal: document.getElementById('wpvfh-search-modal'),
                searchClose: document.getElementById('wpvfh-search-close'),
                searchForm: document.getElementById('wpvfh-search-form'),
                searchId: document.getElementById('wpvfh-search-id'),
                searchText: document.getElementById('wpvfh-search-text'),
                searchStatus: document.getElementById('wpvfh-search-status'),
                searchPriority: document.getElementById('wpvfh-search-priority'),
                searchAuthor: document.getElementById('wpvfh-search-author'),
                searchDateFrom: document.getElementById('wpvfh-search-date-from'),
                searchDateTo: document.getElementById('wpvfh-search-date-to'),
                searchSubmit: document.getElementById('wpvfh-search-submit'),
                searchReset: document.getElementById('wpvfh-search-reset'),
                searchResults: document.getElementById('wpvfh-search-results'),
                searchResultsList: document.getElementById('wpvfh-search-results-list'),
                searchCount: document.getElementById('wpvfh-search-count'),
            };
        },

        /**
         * Attacher les gestionnaires d'événements
         * @returns {void}
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

            // Bouton "Ajouter un feedback" dans l'onglet liste
            if (this.elements.addFeedbackBtn) {
                this.elements.addFeedbackBtn.addEventListener('click', () => {
                    this.switchTab('new');
                    this.startFeedbackMode();
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

            switch (tool) {
                case 'screenshot':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    this.captureScreenshot();
                    break;
                case 'voice':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    if (this.elements.voiceSection) this.elements.voiceSection.hidden = false;
                    break;
                case 'video':
                    event.target.closest('.wpvfh-tool-btn').classList.add('active');
                    if (this.elements.videoSection) this.elements.videoSection.hidden = false;
                    break;
            }
        },

        /**
         * Gérer l'enregistrement vocal
         */
        handleVoiceRecord: async function() {
            if (!window.BlazingVoiceRecorder) {
                this.showNotification('Enregistrement vocal non disponible', 'error');
                return;
            }

            const recorder = window.BlazingVoiceRecorder;

            if (recorder.state.isRecording) {
                recorder.stop();
                this.elements.voiceRecordBtn.classList.remove('recording');
                this.elements.voiceRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer';
                if (this.voiceTimer) clearInterval(this.voiceTimer);
            } else {
                const started = await recorder.start();
                if (started) {
                    this.elements.voiceRecordBtn.classList.add('recording');
                    this.elements.voiceRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVoiceTimer();
                } else {
                    this.showNotification('Impossible d\'accéder au microphone', 'error');
                }
            }
        },

        /**
         * Démarrer le timer d'enregistrement vocal
         */
        startVoiceTimer: function() {
            const timeDisplay = this.elements.voiceSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.voiceTimer = setInterval(() => {
                if (window.BlazingVoiceRecorder) {
                    const duration = window.BlazingVoiceRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingVoiceRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Gérer la fin de l'enregistrement vocal
         * @param {CustomEvent} event
         */
        handleVoiceComplete: async function(event) {
            const { audioUrl, transcript } = event.detail;

            if (this.elements.voicePreview) {
                const audio = this.elements.voicePreview.querySelector('audio');
                if (audio) audio.src = audioUrl;
                this.elements.voicePreview.hidden = false;
            }

            // Stocker les données audio en base64
            if (window.BlazingVoiceRecorder && this.elements.audioData) {
                const base64 = await window.BlazingVoiceRecorder.getAudioBase64();
                this.elements.audioData.value = base64 || '';
            }

            // Afficher la transcription
            if (transcript && this.elements.transcriptPreview) {
                const textEl = this.elements.transcriptPreview.querySelector('.wpvfh-transcript-text');
                if (textEl) textEl.textContent = transcript;
                this.elements.transcriptPreview.hidden = false;
                if (this.elements.transcriptField) {
                    this.elements.transcriptField.value = transcript;
                }
            }
        },

        /**
         * Gérer la mise à jour de la transcription
         * @param {CustomEvent} event
         */
        handleTranscriptionUpdate: function(event) {
            const { final, interim } = event.detail;
            if (this.elements.transcriptPreview) {
                const textEl = this.elements.transcriptPreview.querySelector('.wpvfh-transcript-text');
                if (textEl) {
                    textEl.textContent = final + (interim ? ' ' + interim : '');
                }
                this.elements.transcriptPreview.hidden = false;
            }
        },

        /**
         * Effacer l'enregistrement vocal
         */
        clearVoiceRecording: function() {
            if (window.BlazingVoiceRecorder) {
                window.BlazingVoiceRecorder.clear();
            }
            if (this.elements.voicePreview) {
                this.elements.voicePreview.hidden = true;
                const audio = this.elements.voicePreview.querySelector('audio');
                if (audio) audio.src = '';
            }
            if (this.elements.transcriptPreview) {
                this.elements.transcriptPreview.hidden = true;
            }
            if (this.elements.audioData) this.elements.audioData.value = '';
            if (this.elements.transcriptField) this.elements.transcriptField.value = '';
        },

        /**
         * Gérer l'enregistrement vidéo
         */
        handleVideoRecord: async function() {
            if (!window.BlazingScreenRecorder) {
                this.showNotification('Enregistrement d\'écran non disponible', 'error');
                return;
            }

            const recorder = window.BlazingScreenRecorder;

            if (recorder.state.isRecording) {
                recorder.stop();
                this.elements.videoRecordBtn.classList.remove('recording');
                this.elements.videoRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Enregistrer l\'écran';
                if (this.videoTimer) clearInterval(this.videoTimer);
            } else {
                const started = await recorder.start({ includeMicrophone: true });
                if (started) {
                    this.elements.videoRecordBtn.classList.add('recording');
                    this.elements.videoRecordBtn.querySelector('.wpvfh-record-text').textContent = 'Arrêter';
                    this.startVideoTimer();
                } else {
                    this.showNotification('Impossible d\'accéder à l\'écran', 'error');
                }
            }
        },

        /**
         * Démarrer le timer d'enregistrement vidéo
         */
        startVideoTimer: function() {
            const timeDisplay = this.elements.videoSection?.querySelector('.wpvfh-recorder-time');
            if (!timeDisplay) return;

            this.videoTimer = setInterval(() => {
                if (window.BlazingScreenRecorder) {
                    const duration = window.BlazingScreenRecorder.getCurrentDuration();
                    timeDisplay.textContent = window.BlazingScreenRecorder.formatDuration(duration);
                }
            }, 100);
        },

        /**
         * Gérer la fin de l'enregistrement vidéo
         * @param {CustomEvent} event
         */
        handleVideoComplete: async function(event) {
            const { videoUrl } = event.detail;

            if (this.elements.videoPreview) {
                const video = this.elements.videoPreview.querySelector('video');
                if (video) video.src = videoUrl;
                this.elements.videoPreview.hidden = false;
            }

            // Note: Les vidéos sont trop volumineuses pour base64
            // On stocke l'URL blob pour l'upload séparé
            this.state.videoBlob = event.detail.videoBlob;
        },

        /**
         * Effacer l'enregistrement vidéo
         */
        clearVideoRecording: function() {
            if (window.BlazingScreenRecorder) {
                window.BlazingScreenRecorder.clear();
            }
            if (this.elements.videoPreview) {
                this.elements.videoPreview.hidden = true;
                const video = this.elements.videoPreview.querySelector('video');
                if (video) video.src = '';
            }
            this.state.videoBlob = null;
        },

        /**
         * Gérer le toggle du widget (bouton principal "Feedback")
         * Ouvre le panel avec la liste des feedbacks
         * @param {Event} event - Événement de clic
         * @returns {void}
         */
        handleToggle: function(event) {
            event.preventDefault();

            if (this.state.isOpen) {
                this.closePanel();
            } else {
                // Ouvrir le panel avec l'onglet liste
                this.openPanel('list');
            }
        },

        /**
         * Gérer le clic sur le bouton "+" (ajouter)
         * Ouvre le panel avec le formulaire nouveau feedback
         * @param {Event} event
         * @returns {void}
         */
        handleAddClick: function(event) {
            event.preventDefault();

            // Ouvrir le panel avec l'onglet nouveau
            this.openPanel('new');

            // Focus sur le champ commentaire
            if (this.elements.commentField) {
                setTimeout(() => this.elements.commentField.focus(), 350);
            }
        },

        /**
         * Gérer le clic sur le bouton de visibilité
         * Affiche/masque les pins
         * @param {Event} event
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

            // Fermer temporairement le panel pour faciliter la sélection
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
            // Si aucune sélection n'a été faite, réouvrir le panel
            if (!window.BlazingAnnotation || !window.BlazingAnnotation.hasSelection()) {
                // Ne pas rouvrir automatiquement, l'utilisateur a annulé
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

            // Retirer la classe du body
            document.body.classList.remove('wpvfh-panel-active');

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

            // Nettoyer le formulaire
            this.resetForm();

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
        },

        /**
         * Afficher la liste des pins dans la sidebar avec drag-and-drop
         */
        renderPinsList: function() {
            if (!this.elements.pinsList) return;

            const feedbacks = this.getFilteredFeedbacks();

            // Mettre à jour le compteur
            if (this.elements.pinsCount) {
                this.elements.pinsCount.textContent = feedbacks.length > 0 ? `(${feedbacks.length})` : '';
            }

            // Afficher/masquer l'état vide
            if (this.elements.emptyState) {
                this.elements.emptyState.hidden = feedbacks.length > 0;
            }
            this.elements.pinsList.hidden = feedbacks.length === 0;

            if (feedbacks.length === 0) return;

            // Ajouter la classe sortable
            this.elements.pinsList.classList.add('sortable');

            // Générer le HTML des pins avec handle de drag et numéro
            const html = feedbacks.map((feedback, index) => {
                const statusLabels = {
                    new: this.config.i18n?.statusNew || 'Nouveau',
                    in_progress: this.config.i18n?.statusInProgress || 'En cours',
                    resolved: this.config.i18n?.statusResolved || 'Résolu',
                    rejected: this.config.i18n?.statusRejected || 'Rejeté',
                };

                const status = feedback.status || 'new';
                const date = feedback.date ? new Date(feedback.date).toLocaleDateString() : '';
                const pinNumber = index + 1;

                // Vérifier si l'utilisateur peut supprimer ce feedback
                const isCreator = feedback.author?.id === this.config.userId;
                const canDelete = isCreator || this.config.canManage;

                // Vérifier si un élément a été ciblé (position ou sélecteur)
                const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;

                return `
                    <div class="wpvfh-pin-item" data-feedback-id="${feedback.id}" data-pin-number="${pinNumber}">
                        ${hasPosition ? `
                        <div class="wpvfh-pin-marker status-${status}">
                            ${pinNumber}
                        </div>
                        ` : ''}
                        <div class="wpvfh-pin-content">
                            <div class="wpvfh-pin-header">
                                <span class="wpvfh-pin-id">#${feedback.id}</span>
                            </div>
                            <p class="wpvfh-pin-text">${this.escapeHtml(feedback.comment || feedback.content || '')}</p>
                            <div class="wpvfh-pin-meta">
                                <span class="wpvfh-pin-status status-${status}">${statusLabels[status]}</span>
                                ${date ? `<span class="wpvfh-pin-date">${date}</span>` : ''}
                            </div>
                        </div>
                        <div class="wpvfh-pin-actions">
                            ${hasPosition ? `
                            <button type="button" class="wpvfh-pin-action wpvfh-pin-goto" title="Aller au pin">
                                📍
                            </button>
                            ` : ''}
                            ${canDelete ? `
                            <button type="button" class="wpvfh-pin-action wpvfh-pin-delete" title="Supprimer" data-feedback-id="${feedback.id}">
                                🗑️
                            </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            this.elements.pinsList.innerHTML = html;

            // Ajouter les événements aux items
            this.elements.pinsList.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                // Clic pour voir les détails du feedback
                item.addEventListener('click', (e) => {
                    // Ne pas réagir si on clique sur une action
                    if (e.target.closest('.wpvfh-pin-action')) {
                        return;
                    }
                    const feedbackId = parseInt(item.dataset.feedbackId, 10);

                    // Trouver le feedback dans la liste
                    const feedback = this.state.currentFeedbacks.find(f => f.id === feedbackId);
                    if (feedback) {
                        // Afficher les détails du feedback
                        this.showFeedbackDetails(feedback);
                    }
                });

                // Clic sur le bouton "aller au pin" (📍)
                const gotoBtn = item.querySelector('.wpvfh-pin-goto');
                if (gotoBtn) {
                    gotoBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const feedbackId = parseInt(item.dataset.feedbackId, 10);
                        this.scrollToPin(feedbackId);
                    });
                }

                // Clic sur le bouton supprimer
                const deleteBtn = item.querySelector('.wpvfh-pin-delete');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const feedbackId = parseInt(deleteBtn.dataset.feedbackId, 10);
                        this.showDeleteModalForFeedback(feedbackId);
                    });
                }
            });
        },

        /**
         * Initialiser le drag-and-drop pour la liste
         */
        initDragAndDrop: function() {
            const list = this.elements.pinsList;
            if (!list) return;

            let draggedItem = null;

            list.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                // Début du drag
                item.addEventListener('dragstart', (e) => {
                    draggedItem = item;
                    item.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', item.dataset.feedbackId);
                });

                // Fin du drag
                item.addEventListener('dragend', () => {
                    item.classList.remove('dragging');
                    list.querySelectorAll('.wpvfh-pin-item').forEach(i => {
                        i.classList.remove('drag-over');
                    });
                    draggedItem = null;
                });

                // Survol pendant le drag
                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';

                    if (item !== draggedItem) {
                        item.classList.add('drag-over');
                    }
                });

                // Sortie du survol
                item.addEventListener('dragleave', () => {
                    item.classList.remove('drag-over');
                });

                // Drop
                item.addEventListener('drop', (e) => {
                    e.preventDefault();
                    item.classList.remove('drag-over');

                    if (draggedItem && item !== draggedItem) {
                        // Réorganiser dans le DOM
                        const allItems = [...list.querySelectorAll('.wpvfh-pin-item')];
                        const fromIndex = allItems.indexOf(draggedItem);
                        const toIndex = allItems.indexOf(item);

                        if (fromIndex < toIndex) {
                            item.parentNode.insertBefore(draggedItem, item.nextSibling);
                        } else {
                            item.parentNode.insertBefore(draggedItem, item);
                        }

                        // Mettre à jour l'ordre dans currentFeedbacks
                        this.updateFeedbackOrder();
                    }
                });
            });
        },

        /**
         * Mettre à jour l'ordre des feedbacks après réorganisation
         */
        updateFeedbackOrder: function() {
            const list = this.elements.pinsList;
            if (!list) return;

            // Récupérer les IDs dans le nouvel ordre
            const orderedIds = [...list.querySelectorAll('.wpvfh-pin-item')]
                .map(item => parseInt(item.dataset.feedbackId, 10));

            // Réorganiser currentFeedbacks
            const newOrder = orderedIds.map(id =>
                this.state.currentFeedbacks.find(f => f.id === id)
            ).filter(Boolean);

            this.state.currentFeedbacks = newOrder;

            // Mettre à jour les numéros dans la liste
            list.querySelectorAll('.wpvfh-pin-item').forEach((item, index) => {
                const newNumber = index + 1;
                item.dataset.pinNumber = newNumber;
                const marker = item.querySelector('.wpvfh-pin-marker');
                if (marker) {
                    marker.textContent = newNumber;
                }
                // Animation flash
                item.classList.add('reordered');
                setTimeout(() => item.classList.remove('reordered'), 500);
            });

            // Renuméroter les pins sur la page
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.renumberPins(orderedIds);
            }

            console.log('[Blazing Feedback] Ordre mis à jour:', orderedIds);
        },

        /**
         * Scroller vers un pin sur la page
         * @param {number} feedbackId
         */
        scrollToPin: function(feedbackId) {
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.scrollToPin(feedbackId);
            }
        },

        /**
         * Échapper le HTML
         * @param {string} str
         * @returns {string}
         */
        escapeHtml: function(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Gérer le placement d'un pin
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinPlaced: function(event) {
            const position = event.detail;

            // Stocker la position
            this.state.pinPosition = position;

            // Mettre à jour les champs cachés
            if (this.elements.positionX) {
                this.elements.positionX.value = position.percentX;
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = position.percentY;
            }

            // Afficher l'info du pin
            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = false;
            }

            // Capturer le screenshot si activé
            if (this.elements.screenshotToggle && this.elements.screenshotToggle.checked) {
                this.captureScreenshot();
            }

            // Ouvrir le panel
            this.state.feedbackMode = 'create';
            this.openPanel();
        },

        /**
         * Gérer la sélection d'un pin existant
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handlePinSelected: function(event) {
            const { feedbackId, pinData } = event.detail;

            if (!pinData) return;

            // Afficher les détails du feedback
            this.showFeedbackDetails(pinData);
        },

        /**
         * Capturer le screenshot
         * @returns {void}
         */
        captureScreenshot: async function() {
            if (!window.BlazingScreenshot || !window.BlazingScreenshot.isAvailable()) {
                console.warn('[Blazing Feedback] Screenshot non disponible');
                return;
            }

            try {
                this.showNotification(this.config.i18n?.loadingMessage || 'Capture en cours...', 'info');

                const dataUrl = await window.BlazingScreenshot.capture();

                // Redimensionner si nécessaire
                const resizedDataUrl = await window.BlazingScreenshot.resize(dataUrl, 1200, 900);

                this.state.screenshotData = resizedDataUrl;

                // Mettre à jour le champ caché
                if (this.elements.screenshotData) {
                    this.elements.screenshotData.value = resizedDataUrl;
                }

                // Afficher l'aperçu
                this.showScreenshotPreview(resizedDataUrl);

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);
                this.showNotification('Erreur lors de la capture', 'error');
            }
        },

        /**
         * Afficher l'aperçu du screenshot
         * @param {string} dataUrl - Image en base64
         * @returns {void}
         */
        showScreenshotPreview: function(dataUrl) {
            if (!this.elements.screenshotPreview) return;

            const img = this.elements.screenshotPreview.querySelector('img');
            if (img) {
                img.src = dataUrl;
            }

            this.elements.screenshotPreview.hidden = false;
        },

        /**
         * Effacer le screenshot
         * @returns {void}
         */
        clearScreenshot: function() {
            this.state.screenshotData = null;

            if (this.elements.screenshotData) {
                this.elements.screenshotData.value = '';
            }

            if (this.elements.screenshotPreview) {
                this.elements.screenshotPreview.hidden = true;
                const img = this.elements.screenshotPreview.querySelector('img');
                if (img) {
                    img.src = '';
                }
            }
        },

        /**
         * Gérer le succès de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureSuccess: function(event) {
            // Capture gérée dans captureScreenshot
        },

        /**
         * Gérer l'erreur de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureError: function(event) {
            console.warn('[Blazing Feedback] Erreur de capture:', event.detail.error);
        },

        /**
         * Gérer l'annulation
         * @returns {void}
         */
        handleCancel: function() {
            // Supprimer le pin temporaire
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.removeTemporaryPin();
            }

            this.closePanel();
        },

        /**
         * Gérer la soumission du formulaire
         * @param {Event} event - Événement de soumission
         * @returns {void}
         */
        handleSubmit: async function(event) {
            event.preventDefault();

            if (this.state.isSubmitting) return;

            // Validation
            const comment = this.elements.commentField?.value?.trim();
            if (!comment) {
                this.showNotification(this.config.i18n?.errorMessage || 'Veuillez entrer un commentaire', 'error');
                this.elements.commentField?.focus();
                return;
            }

            this.state.isSubmitting = true;
            this.setSubmitState(true);

            try {
                // Collecter les métadonnées système complètes
                const metadata = window.BlazingScreenshot ? window.BlazingScreenshot.getMetadata() : {};

                // Capture d'écran automatique si pas déjà fournie
                let screenshotData = this.state.screenshotData || null;
                if (!screenshotData && window.BlazingScreenshot && window.BlazingScreenshot.isAvailable()) {
                    try {
                        console.log('[Blazing Feedback] Capture d\'écran automatique...');
                        screenshotData = await window.BlazingScreenshot.capture();
                        console.log('[Blazing Feedback] Screenshot automatique capturé');
                    } catch (screenshotError) {
                        console.warn('[Blazing Feedback] Erreur capture auto:', screenshotError);
                        // Continuer sans screenshot
                    }
                }

                // Préparer les données avec toutes les infos système
                const feedbackData = {
                    comment: comment,
                    url: this.config.currentUrl || window.location.href,
                    position_x: this.state.pinPosition?.position_x || this.elements.positionX?.value || null,
                    position_y: this.state.pinPosition?.position_y || this.elements.positionY?.value || null,
                    screenshot_data: screenshotData,
                    // Dimensions écran
                    screen_width: metadata.screenWidth,
                    screen_height: metadata.screenHeight,
                    viewport_width: metadata.viewportWidth,
                    viewport_height: metadata.viewportHeight,
                    device_pixel_ratio: metadata.devicePixelRatio,
                    color_depth: metadata.colorDepth,
                    orientation: metadata.orientation,
                    // Navigateur & OS
                    browser: metadata.browser,
                    browser_version: metadata.browserVersion,
                    os: metadata.os,
                    os_version: metadata.osVersion,
                    device: metadata.device,
                    platform: metadata.platform,
                    user_agent: metadata.userAgent,
                    // Langue & locale
                    language: metadata.language,
                    languages: metadata.languages,
                    timezone: metadata.timezone,
                    timezone_offset: metadata.timezoneOffset,
                    local_time: metadata.localTime,
                    // Capacités
                    cookies_enabled: metadata.cookiesEnabled,
                    online: metadata.onLine,
                    touch_support: metadata.touchSupport ? JSON.stringify(metadata.touchSupport) : null,
                    max_touch_points: metadata.maxTouchPoints,
                    // Hardware (si disponible)
                    device_memory: metadata.deviceMemory,
                    hardware_concurrency: metadata.hardwareConcurrency,
                    // Connexion (si disponible)
                    connection_type: metadata.connectionType ? JSON.stringify(metadata.connectionType) : null,
                    // DOM Anchoring data
                    selector: this.state.pinPosition?.selector || null,
                    element_offset_x: this.state.pinPosition?.element_offset_x || null,
                    element_offset_y: this.state.pinPosition?.element_offset_y || null,
                    scroll_x: this.state.pinPosition?.scrollX || metadata.scrollX,
                    scroll_y: this.state.pinPosition?.scrollY || metadata.scrollY,
                    // Référent
                    referrer: metadata.referrer,
                };

                console.log('[Blazing Feedback] Envoi du feedback:', feedbackData);

                // Envoyer à l'API
                const response = await this.apiRequest('POST', 'feedbacks', feedbackData);
                console.log('[Blazing Feedback] Réponse création:', response);

                if (response.id) {
                    // Succès
                    this.showNotification(this.config.i18n?.successMessage || 'Feedback envoyé avec succès !', 'success');

                    // Supprimer le pin temporaire et créer le permanent
                    if (window.BlazingAnnotation) {
                        window.BlazingAnnotation.removeTemporaryPin();
                        window.BlazingAnnotation.createPin(response);
                    }

                    // Fermer le panel
                    this.closePanel();

                    // Ajouter à la liste locale
                    this.state.currentFeedbacks.push(response);

                    // Mettre à jour les compteurs
                    const count = this.state.currentFeedbacks.length;
                    if (this.elements.pinsCount) {
                        this.elements.pinsCount.textContent = count > 0 ? count : '';
                    }
                    if (this.elements.feedbackCount) {
                        this.elements.feedbackCount.textContent = count;
                        this.elements.feedbackCount.hidden = count === 0;
                    }

                    // Émettre l'événement
                    this.emitEvent('feedback-created', response);
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de soumission:', error);
                this.showNotification(error.message || this.config.i18n?.errorMessage || 'Erreur lors de l\'envoi', 'error');
            } finally {
                this.state.isSubmitting = false;
                this.setSubmitState(false);
            }
        },

        /**
         * Réinitialiser le formulaire
         * @returns {void}
         */
        resetForm: function() {
            if (this.elements.form) {
                this.elements.form.reset();
            }

            this.state.pinPosition = null;
            this.clearScreenshot();

            if (this.elements.pinInfo) {
                this.elements.pinInfo.hidden = true;
            }

            if (this.elements.positionX) {
                this.elements.positionX.value = '';
            }
            if (this.elements.positionY) {
                this.elements.positionY.value = '';
            }

            // Supprimer le pin temporaire
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.removeTemporaryPin();
            }

            // Effacer la sélection d'élément
            this.emitEvent('clear-selection');

            // Réinitialiser l'affichage de la section ciblage
            if (this.elements.selectElementBtn) {
                this.elements.selectElementBtn.hidden = false;
            }
            if (this.elements.selectedElement) {
                this.elements.selectedElement.hidden = true;
            }
        },

        /**
         * Définir l'état du bouton de soumission
         * @param {boolean} isLoading - En cours de chargement
         * @returns {void}
         */
        setSubmitState: function(isLoading) {
            if (!this.elements.submitBtn) return;

            this.elements.submitBtn.disabled = isLoading;

            if (isLoading) {
                this.elements.submitBtn.innerHTML = '<span class="wpvfh-spinner"></span> ' + (this.config.i18n?.loadingMessage || 'Envoi...');
            } else {
                this.elements.submitBtn.innerHTML = '<span class="wpvfh-btn-emoji">📤</span> ' + (this.config.i18n?.submitButton || 'Envoyer');
            }
        },

        /**
         * Charger les feedbacks existants pour cette page
         * @returns {void}
         */
        loadExistingFeedbacks: async function() {
            try {
                const currentUrl = this.config.currentUrl || window.location.href;
                console.log('[Blazing Feedback] Chargement des feedbacks pour URL:', currentUrl);

                const url = encodeURIComponent(currentUrl);
                const response = await this.apiRequest('GET', `feedbacks/by-url?url=${url}`);

                console.log('[Blazing Feedback] Réponse API:', response);

                if (Array.isArray(response)) {
                    this.state.currentFeedbacks = response;
                    console.log('[Blazing Feedback] Feedbacks chargés:', response.length);

                    // Afficher les pins
                    if (response.length > 0) {
                        this.emitEvent('load-pins', { pins: response });
                    }

                    // Mettre à jour le compteur dans l'onglet Liste
                    if (this.elements.pinsCount) {
                        this.elements.pinsCount.textContent = response.length > 0 ? response.length : '';
                    }

                    // Mettre à jour le badge compteur sur le bouton principal
                    if (this.elements.feedbackCount) {
                        if (response.length > 0) {
                            this.elements.feedbackCount.textContent = response.length;
                            this.elements.feedbackCount.hidden = false;
                        } else {
                            this.elements.feedbackCount.hidden = true;
                        }
                    }

                    // Masquer l'onglet Priorité si aucun feedback
                    this.updatePriorityTabVisibility(response.length);
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement feedbacks:', error);
            }
        },

        /**
         * Afficher les détails d'un feedback dans la sidebar
         * @param {Object} feedback - Données du feedback
         * @returns {void}
         */
        showFeedbackDetails: function(feedback) {
            // Stocker l'ID du feedback courant
            this.state.currentFeedbackId = feedback.id;

            // Labels des statuts
            const statusLabels = {
                new: this.config.i18n?.statusNew || 'Nouveau',
                in_progress: this.config.i18n?.statusInProgress || 'En cours',
                resolved: this.config.i18n?.statusResolved || 'Résolu',
                rejected: this.config.i18n?.statusRejected || 'Rejeté',
            };

            const statusIcons = {
                new: '🆕',
                in_progress: '⏳',
                resolved: '✅',
                rejected: '❌',
            };

            const status = feedback.status || 'new';

            // Remplir les éléments
            if (this.elements.detailId) {
                this.elements.detailId.textContent = `#${feedback.id}`;
            }

            if (this.elements.detailStatus) {
                this.elements.detailStatus.innerHTML = `
                    <span class="wpvfh-status-badge status-${status}">
                        ${statusIcons[status] || ''} ${statusLabels[status] || status}
                    </span>
                `;
            }

            if (this.elements.detailAuthor) {
                this.elements.detailAuthor.innerHTML = `
                    <span>👤</span>
                    <span>${this.escapeHtml(feedback.author?.name || 'Anonyme')}</span>
                `;
            }

            if (this.elements.detailDate) {
                const date = feedback.date ? new Date(feedback.date).toLocaleString() : '';
                this.elements.detailDate.innerHTML = `
                    <span>📅</span>
                    <span>${date}</span>
                `;
            }

            if (this.elements.detailComment) {
                this.elements.detailComment.textContent = feedback.comment || feedback.content || '';
            }

            // Screenshot
            if (this.elements.detailScreenshot) {
                if (feedback.screenshot_url) {
                    const img = this.elements.detailScreenshot.querySelector('img');
                    if (img) img.src = feedback.screenshot_url;
                    this.elements.detailScreenshot.hidden = false;
                } else {
                    this.elements.detailScreenshot.hidden = true;
                }
            }

            // Réponses
            if (this.elements.detailReplies && this.elements.repliesList) {
                if (feedback.replies && feedback.replies.length > 0) {
                    this.elements.repliesList.innerHTML = feedback.replies.map(reply => `
                        <div class="wpvfh-reply-item">
                            <div class="wpvfh-reply-meta">
                                ${this.escapeHtml(reply.author?.name || 'Anonyme')} -
                                ${new Date(reply.date).toLocaleString()}
                            </div>
                            <div class="wpvfh-reply-content">${this.escapeHtml(reply.content)}</div>
                        </div>
                    `).join('');
                    this.elements.detailReplies.hidden = false;
                } else {
                    this.elements.detailReplies.hidden = true;
                }
            }

            // Actions modérateur
            if (this.elements.detailActions) {
                this.elements.detailActions.hidden = !this.config.canModerate;
            }

            // Sélecteur de statut
            if (this.elements.statusSelect) {
                this.elements.statusSelect.value = status;
            }

            // Vider le champ de réponse
            if (this.elements.replyInput) {
                this.elements.replyInput.value = '';
            }

            // Section suppression - visible pour le créateur ou un admin
            if (this.elements.deleteSection) {
                const isCreator = feedback.author?.id === this.config.userId;
                const canDelete = isCreator || this.config.canManage;
                this.elements.deleteSection.hidden = !canDelete;
            }

            // Ouvrir le panel et basculer sur l'onglet détails
            this.openPanel('details');
        },

        /**
         * Mettre à jour le statut d'un feedback
         * @param {number} feedbackId - ID du feedback
         * @param {string} status - Nouveau statut
         * @returns {void}
         */
        updateFeedbackStatus: async function(feedbackId, status) {
            try {
                await this.apiRequest('PUT', `feedbacks/${feedbackId}/status`, { status });

                // Mettre à jour le pin
                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.updatePin(feedbackId, { status });
                }

                // Mettre à jour l'affichage du statut dans la sidebar
                if (this.elements.detailStatus) {
                    const statusLabels = {
                        new: this.config.i18n?.statusNew || 'Nouveau',
                        in_progress: this.config.i18n?.statusInProgress || 'En cours',
                        resolved: this.config.i18n?.statusResolved || 'Résolu',
                        rejected: this.config.i18n?.statusRejected || 'Rejeté',
                    };
                    const statusIcons = {
                        new: '🆕',
                        in_progress: '⏳',
                        resolved: '✅',
                        rejected: '❌',
                    };
                    this.elements.detailStatus.innerHTML = `
                        <span class="wpvfh-status-badge status-${status}">
                            ${statusIcons[status] || ''} ${statusLabels[status] || status}
                        </span>
                    `;
                }

                this.showNotification('Statut mis à jour', 'success');

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de mise à jour:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
            }
        },

        /**
         * Ajouter une réponse à un feedback
         * @param {number} feedbackId - ID du feedback
         * @param {string} content - Contenu de la réponse
         * @returns {void}
         */
        addReply: async function(feedbackId, content) {
            try {
                await this.apiRequest('POST', `feedbacks/${feedbackId}/replies`, { content });
                this.showNotification('Réponse ajoutée', 'success');

                // Vider le champ de réponse
                if (this.elements.replyInput) {
                    this.elements.replyInput.value = '';
                }

                // Recharger les détails du feedback
                const updatedFeedback = await this.apiRequest('GET', `feedbacks/${feedbackId}`);
                this.showFeedbackDetails(updatedFeedback);

            } catch (error) {
                console.error('[Blazing Feedback] Erreur d\'ajout de réponse:', error);
                this.showNotification('Erreur lors de l\'ajout de la réponse', 'error');
            }
        },

        /**
         * Effectuer une requête à l'API REST
         * @param {string} method - Méthode HTTP
         * @param {string} endpoint - Endpoint
         * @param {Object} data - Données (optionnel)
         * @returns {Promise<Object>} Réponse
         */
        apiRequest: async function(method, endpoint, data = null) {
            const url = this.config.restUrl + endpoint;

            console.log('[Blazing Feedback] API Request:', method, url);

            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.config.restNonce,
                },
                credentials: 'same-origin',
            };

            if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                options.body = JSON.stringify(data);
                console.log('[Blazing Feedback] Request data:', data);
            }

            try {
                const response = await fetch(url, options);

                console.log('[Blazing Feedback] Response status:', response.status);

                // Lire le texte de la réponse d'abord
                const responseText = await response.text();

                // Vérifier si c'est du JSON valide
                let responseData;
                try {
                    responseData = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('[Blazing Feedback] Réponse non-JSON:', responseText.substring(0, 500));
                    throw new Error('La réponse du serveur n\'est pas valide. Vérifiez que les permaliens WordPress sont activés.');
                }

                if (!response.ok) {
                    throw new Error(responseData.message || `Erreur HTTP ${response.status}`);
                }

                return responseData;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur API:', error);
                throw error;
            }
        },

        /**
         * Afficher une notification
         * @param {string} message - Message
         * @param {string} type - Type (success, error, info, warning)
         * @returns {void}
         */
        showNotification: function(message, type = 'info') {
            if (!this.elements.notifications) return;

            const notification = document.createElement('div');
            notification.className = `wpvfh-notification wpvfh-notification-${type}`;
            notification.textContent = message;

            this.elements.notifications.appendChild(notification);

            // Animation d'entrée
            requestAnimationFrame(() => {
                notification.classList.add('wpvfh-notification-show');
            });

            // Supprimer après 4 secondes
            setTimeout(() => {
                notification.classList.remove('wpvfh-notification-show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        },

        /**
         * Échapper le HTML
         * @param {string} text - Texte à échapper
         * @returns {string} Texte échappé
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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

        // ===========================================
        // FILTRES
        // ===========================================

        /**
         * Gérer le clic sur un filtre
         * @param {string} status - Statut à filtrer
         */
        handleFilterClick: function(status) {
            this.state.currentFilter = status;

            // Mettre à jour les boutons
            if (this.elements.filterButtons) {
                this.elements.filterButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.status === status);
                });
            }

            // Filtrer et afficher la liste
            this.renderPinsList();
        },

        /**
         * Obtenir les feedbacks filtrés
         * @returns {Array} Feedbacks filtrés
         */
        getFilteredFeedbacks: function() {
            if (this.state.currentFilter === 'all') {
                return this.state.currentFeedbacks;
            }
            return this.state.currentFeedbacks.filter(f => f.status === this.state.currentFilter);
        },

        /**
         * Mettre à jour les compteurs des filtres
         */
        updateFilterCounts: function() {
            const feedbacks = this.state.currentFeedbacks || [];

            const counts = {
                all: feedbacks.length,
                new: feedbacks.filter(f => f.status === 'new').length,
                in_progress: feedbacks.filter(f => f.status === 'in_progress').length,
                resolved: feedbacks.filter(f => f.status === 'resolved').length,
                rejected: feedbacks.filter(f => f.status === 'rejected').length,
            };

            // Mettre à jour les badges
            const allCount = document.getElementById('wpvfh-filter-all-count');
            const newCount = document.getElementById('wpvfh-filter-new-count');
            const progressCount = document.getElementById('wpvfh-filter-progress-count');
            const resolvedCount = document.getElementById('wpvfh-filter-resolved-count');
            const rejectedCount = document.getElementById('wpvfh-filter-rejected-count');

            if (allCount) allCount.textContent = counts.all;
            if (newCount) newCount.textContent = counts.new;
            if (progressCount) progressCount.textContent = counts.in_progress;
            if (resolvedCount) resolvedCount.textContent = counts.resolved;
            if (rejectedCount) rejectedCount.textContent = counts.rejected;
        },

        // ===========================================
        // VALIDATION DE PAGE
        // ===========================================

        /**
         * Mettre à jour la section de validation de page
         */
        updateValidationSection: function() {
            if (!this.elements.pageValidation) return;

            const feedbacks = this.state.currentFeedbacks || [];

            // Vérifier si tous les feedbacks sont traités
            const totalCount = feedbacks.length;
            const resolvedCount = feedbacks.filter(f => f.status === 'resolved' || f.status === 'rejected').length;
            const allResolved = totalCount > 0 && resolvedCount === totalCount;

            // Afficher la section seulement s'il y a des feedbacks
            this.elements.pageValidation.hidden = totalCount === 0;

            if (totalCount === 0) return;

            // Mettre à jour l'icône et le texte selon l'état
            const statusIcon = this.elements.validationStatus?.querySelector('.wpvfh-validation-icon');
            const statusText = this.elements.validationStatus?.querySelector('.wpvfh-validation-text');

            if (allResolved) {
                if (statusIcon) statusIcon.textContent = '✅';
                if (statusText) statusText.textContent = 'Tous les points ont été traités';
                this.elements.pageValidation.classList.remove('pending');
                this.elements.pageValidation.classList.add('ready');
            } else {
                if (statusIcon) statusIcon.textContent = '⏳';
                if (statusText) statusText.textContent = `${resolvedCount}/${totalCount} points traités`;
                this.elements.pageValidation.classList.remove('ready');
                this.elements.pageValidation.classList.add('pending');
            }

            // Activer/désactiver le bouton (admin peut toujours valider)
            if (this.elements.validatePageBtn) {
                const canValidate = allResolved || this.config.canManage;
                this.elements.validatePageBtn.disabled = !canValidate;
            }

            // Mettre à jour l'indice
            if (this.elements.validationHint) {
                if (!allResolved) {
                    this.elements.validationHint.textContent = 'Tous les points doivent être résolus ou rejetés avant validation.';
                } else {
                    this.elements.validationHint.textContent = '';
                }
            }
        },

        /**
         * Afficher le modal de validation de page
         */
        showValidateModal: function() {
            if (this.elements.validateModal) {
                this.elements.validateModal.hidden = false;
            }
        },

        /**
         * Confirmer la validation de la page
         */
        confirmValidatePage: async function() {
            try {
                const currentUrl = this.config.currentUrl || window.location.href;
                await this.apiRequest('POST', 'pages/validate', { url: currentUrl });

                this.showNotification('Page validée avec succès !', 'success');

                // Fermer le modal
                if (this.elements.validateModal) {
                    this.elements.validateModal.hidden = true;
                }

                // Mettre à jour l'affichage
                this.elements.pageValidation.classList.remove('ready', 'pending');
                this.elements.pageValidation.classList.add('validated');

                const statusIcon = this.elements.validationStatus?.querySelector('.wpvfh-validation-icon');
                const statusText = this.elements.validationStatus?.querySelector('.wpvfh-validation-text');
                if (statusIcon) statusIcon.textContent = '🎉';
                if (statusText) statusText.textContent = 'Page validée';

                if (this.elements.validatePageBtn) {
                    this.elements.validatePageBtn.hidden = true;
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur validation:', error);
                this.showNotification('Erreur lors de la validation', 'error');
            }
        },

        // ===========================================
        // SUPPRESSION
        // ===========================================

        /**
         * Afficher le modal de suppression
         */
        showDeleteModal: function() {
            if (!this.state.currentFeedbackId) return;

            this.state.feedbackToDelete = this.state.currentFeedbackId;
            if (this.elements.confirmModal) {
                this.elements.confirmModal.hidden = false;
            }
        },

        /**
         * Afficher le modal de suppression pour un feedback spécifique
         * @param {number} feedbackId - ID du feedback
         */
        showDeleteModalForFeedback: function(feedbackId) {
            if (!feedbackId) return;

            this.state.feedbackToDelete = feedbackId;
            if (this.elements.confirmModal) {
                this.elements.confirmModal.hidden = false;
            }
        },

        /**
         * Masquer le modal de suppression
         */
        hideDeleteModal: function() {
            this.state.feedbackToDelete = null;
            if (this.elements.confirmModal) {
                this.elements.confirmModal.hidden = true;
            }
        },

        /**
         * Confirmer la suppression du feedback
         */
        confirmDeleteFeedback: async function() {
            const feedbackId = this.state.feedbackToDelete;
            if (!feedbackId) return;

            try {
                await this.apiRequest('DELETE', `feedbacks/${feedbackId}`);

                this.showNotification('Feedback supprimé', 'success');

                // Supprimer de la liste locale
                this.state.currentFeedbacks = this.state.currentFeedbacks.filter(f => f.id !== feedbackId);

                // Supprimer le pin sur la page
                if (window.BlazingAnnotation) {
                    window.BlazingAnnotation.removePin(feedbackId);
                }

                // Fermer le modal
                this.hideDeleteModal();

                // Retourner à la liste
                this.switchTab('list');

                // Mettre à jour les compteurs
                this.updateFilterCounts();
                if (this.elements.pinsCount) {
                    this.elements.pinsCount.textContent = this.state.currentFeedbacks.length > 0
                        ? this.state.currentFeedbacks.length : '';
                }
                if (this.elements.feedbackCount) {
                    const count = this.state.currentFeedbacks.length;
                    this.elements.feedbackCount.textContent = count;
                    this.elements.feedbackCount.hidden = count === 0;
                }

            } catch (error) {
                console.error('[Blazing Feedback] Erreur suppression:', error);
                this.showNotification('Erreur lors de la suppression', 'error');
            }
        },

        // ===========================================
        // PAGES
        // ===========================================

        /**
         * Charger la liste de toutes les pages avec feedbacks
         */
        loadAllPages: async function() {
            if (this.elements.pagesLoading) this.elements.pagesLoading.hidden = false;
            if (this.elements.pagesList) this.elements.pagesList.hidden = true;
            if (this.elements.pagesEmpty) this.elements.pagesEmpty.hidden = true;

            try {
                const response = await this.apiRequest('GET', 'pages');
                this.state.allPages = Array.isArray(response) ? response : [];

                this.renderPagesList();

            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement pages:', error);
                this.showNotification('Erreur lors du chargement des pages', 'error');
            } finally {
                if (this.elements.pagesLoading) this.elements.pagesLoading.hidden = true;
            }
        },

        /**
         * Afficher la liste des pages
         */
        renderPagesList: function() {
            if (!this.elements.pagesList) return;

            const pages = this.state.allPages || [];
            const currentUrl = this.config.currentUrl || window.location.href;

            // Afficher/masquer l'état vide
            if (this.elements.pagesEmpty) {
                this.elements.pagesEmpty.hidden = pages.length > 0;
            }
            this.elements.pagesList.hidden = pages.length === 0;

            if (pages.length === 0) return;

            // Générer le HTML
            const html = pages.map(page => {
                const isCurrent = page.url === currentUrl;
                const title = page.title || this.extractPageTitle(page.url);
                const shortUrl = this.shortenUrl(page.url);

                return `
                    <div class="wpvfh-page-item ${isCurrent ? 'current' : ''}" data-url="${this.escapeHtml(page.url)}">
                        <span class="wpvfh-page-icon">${page.validated ? '✅' : '📄'}</span>
                        <div class="wpvfh-page-info">
                            <div class="wpvfh-page-title">${this.escapeHtml(title)}</div>
                            <div class="wpvfh-page-url">${this.escapeHtml(shortUrl)}</div>
                        </div>
                        <span class="wpvfh-page-badge ${page.validated ? 'validated' : 'has-feedbacks'}">
                            ${page.validated ? '✓' : page.count || 0}
                        </span>
                    </div>
                `;
            }).join('');

            this.elements.pagesList.innerHTML = html;

            // Ajouter les événements
            this.elements.pagesList.querySelectorAll('.wpvfh-page-item').forEach(item => {
                item.addEventListener('click', () => {
                    const url = item.dataset.url;
                    if (url && url !== currentUrl) {
                        window.location.href = url;
                    } else {
                        // Page courante, aller à la liste
                        this.switchTab('list');
                    }
                });
            });
        },

        /**
         * Extraire un titre de l'URL
         * @param {string} url - URL
         * @returns {string} Titre
         */
        extractPageTitle: function(url) {
            try {
                const urlObj = new URL(url);
                let path = urlObj.pathname;

                // Retirer les slashes de début/fin
                path = path.replace(/^\/|\/$/g, '');

                if (!path || path === '') return 'Accueil';

                // Prendre le dernier segment et nettoyer
                const segments = path.split('/');
                let title = segments[segments.length - 1];

                // Retirer les extensions
                title = title.replace(/\.(html?|php|aspx?)$/i, '');

                // Remplacer les tirets/underscores par des espaces
                title = title.replace(/[-_]/g, ' ');

                // Capitaliser la première lettre
                return title.charAt(0).toUpperCase() + title.slice(1);
            } catch (e) {
                return url;
            }
        },

        /**
         * Raccourcir une URL
         * @param {string} url - URL complète
         * @returns {string} URL raccourcie
         */
        shortenUrl: function(url) {
            try {
                const urlObj = new URL(url);
                return urlObj.pathname || '/';
            } catch (e) {
                return url;
            }
        },

        // ===========================================
        // PIÈCES JOINTES
        // ===========================================

        /**
         * Gérer la sélection de fichiers
         * @param {FileList} files - Fichiers sélectionnés
         */
        handleAttachmentSelect: function(files) {
            const maxFiles = 5;
            const maxSize = 10 * 1024 * 1024; // 10 Mo

            for (const file of files) {
                // Vérifier le nombre maximum
                if (this.state.attachments.length >= maxFiles) {
                    this.showNotification(`Maximum ${maxFiles} fichiers autorisés`, 'warning');
                    break;
                }

                // Vérifier la taille
                if (file.size > maxSize) {
                    this.showNotification(`"${file.name}" dépasse la limite de 10 Mo`, 'warning');
                    continue;
                }

                // Ajouter à la liste
                this.state.attachments.push(file);
            }

            // Mettre à jour l'aperçu
            this.renderAttachmentsPreview();

            // Réinitialiser l'input
            if (this.elements.attachmentsInput) {
                this.elements.attachmentsInput.value = '';
            }
        },

        /**
         * Afficher l'aperçu des pièces jointes
         */
        renderAttachmentsPreview: function() {
            if (!this.elements.attachmentsPreview) return;

            if (this.state.attachments.length === 0) {
                this.elements.attachmentsPreview.innerHTML = '';
                return;
            }

            const html = this.state.attachments.map((file, index) => {
                const icon = this.getFileIcon(file.type);
                const size = this.formatFileSize(file.size);

                return `
                    <div class="wpvfh-attachment-preview-item" data-index="${index}">
                        <span class="wpvfh-file-icon">${icon}</span>
                        <span class="wpvfh-file-name">${this.escapeHtml(file.name)}</span>
                        <span class="wpvfh-file-size">${size}</span>
                        <button type="button" class="wpvfh-remove-attachment" data-index="${index}">&times;</button>
                    </div>
                `;
            }).join('');

            this.elements.attachmentsPreview.innerHTML = html;

            // Ajouter les événements de suppression
            this.elements.attachmentsPreview.querySelectorAll('.wpvfh-remove-attachment').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(btn.dataset.index, 10);
                    this.state.attachments.splice(index, 1);
                    this.renderAttachmentsPreview();
                });
            });
        },

        /**
         * Obtenir l'icône d'un fichier selon son type
         * @param {string} mimeType - Type MIME
         * @returns {string} Emoji
         */
        getFileIcon: function(mimeType) {
            if (mimeType.startsWith('image/')) return '🖼️';
            if (mimeType === 'application/pdf') return '📕';
            if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊';
            return '📎';
        },

        /**
         * Formater la taille d'un fichier
         * @param {number} bytes - Taille en bytes
         * @returns {string} Taille formatée
         */
        formatFileSize: function(bytes) {
            if (bytes < 1024) return bytes + ' o';
            if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' Ko';
            return (bytes / (1024 * 1024)).toFixed(1) + ' Mo';
        },

        // ===========================================
        // MENTIONS @
        // ===========================================

        /**
         * Gérer l'input pour les mentions
         * @param {Event} e - Événement input
         */
        handleMentionInput: function(e) {
            const textarea = e.target;
            const text = textarea.value;
            const cursorPos = textarea.selectionStart;

            // Trouver si on est en train d'écrire une mention
            const textBeforeCursor = text.substring(0, cursorPos);
            const mentionMatch = textBeforeCursor.match(/@(\w*)$/);

            if (mentionMatch) {
                const searchTerm = mentionMatch[1];
                this.showMentionDropdown(searchTerm, textarea);
            } else {
                this.hideMentionDropdown();
            }
        },

        /**
         * Gérer les touches pour les mentions
         * @param {KeyboardEvent} e - Événement keydown
         */
        handleMentionKeydown: function(e) {
            if (!this.elements.mentionDropdown || this.elements.mentionDropdown.hidden) {
                return;
            }

            const items = this.elements.mentionList?.querySelectorAll('.wpvfh-mention-item');
            const activeItem = this.elements.mentionList?.querySelector('.wpvfh-mention-item.active');
            let activeIndex = -1;

            if (items) {
                items.forEach((item, i) => {
                    if (item === activeItem) activeIndex = i;
                });
            }

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (items && items.length > 0) {
                        const nextIndex = (activeIndex + 1) % items.length;
                        items.forEach((item, i) => item.classList.toggle('active', i === nextIndex));
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (items && items.length > 0) {
                        const prevIndex = activeIndex <= 0 ? items.length - 1 : activeIndex - 1;
                        items.forEach((item, i) => item.classList.toggle('active', i === prevIndex));
                    }
                    break;
                case 'Enter':
                case 'Tab':
                    if (activeItem) {
                        e.preventDefault();
                        this.insertMention(activeItem.dataset.username);
                    }
                    break;
                case 'Escape':
                    this.hideMentionDropdown();
                    break;
            }
        },

        /**
         * Afficher le dropdown des mentions
         * @param {string} searchTerm - Terme de recherche
         * @param {HTMLElement} textarea - Textarea source
         */
        showMentionDropdown: async function(searchTerm, textarea) {
            // Charger les utilisateurs si pas encore fait
            if (this.state.mentionUsers.length === 0) {
                await this.loadMentionUsers();
            }

            // Filtrer les utilisateurs
            const filtered = this.state.mentionUsers.filter(user =>
                user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                user.username.toLowerCase().includes(searchTerm.toLowerCase())
            ).slice(0, 6);

            if (filtered.length === 0) {
                this.hideMentionDropdown();
                return;
            }

            // Générer le HTML
            const html = filtered.map((user, i) => `
                <div class="wpvfh-mention-item ${i === 0 ? 'active' : ''}" data-username="${this.escapeHtml(user.username)}">
                    <div class="wpvfh-mention-avatar">${user.name.charAt(0).toUpperCase()}</div>
                    <div class="wpvfh-mention-info">
                        <div class="wpvfh-mention-name">${this.escapeHtml(user.name)}</div>
                        <div class="wpvfh-mention-email">@${this.escapeHtml(user.username)}</div>
                    </div>
                </div>
            `).join('');

            if (this.elements.mentionList) {
                this.elements.mentionList.innerHTML = html;
            }

            // Positionner le dropdown
            if (this.elements.mentionDropdown) {
                const rect = textarea.getBoundingClientRect();
                this.elements.mentionDropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                this.elements.mentionDropdown.style.left = rect.left + 'px';
                this.elements.mentionDropdown.hidden = false;
            }

            // Ajouter les événements de clic
            this.elements.mentionList?.querySelectorAll('.wpvfh-mention-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.insertMention(item.dataset.username);
                });
            });
        },

        /**
         * Masquer le dropdown des mentions
         */
        hideMentionDropdown: function() {
            if (this.elements.mentionDropdown) {
                this.elements.mentionDropdown.hidden = true;
            }
        },

        /**
         * Insérer une mention dans le textarea
         * @param {string} username - Nom d'utilisateur
         */
        insertMention: function(username) {
            const textarea = this.elements.commentField;
            if (!textarea) return;

            const text = textarea.value;
            const cursorPos = textarea.selectionStart;

            // Trouver le début de la mention
            const textBeforeCursor = text.substring(0, cursorPos);
            const mentionStart = textBeforeCursor.lastIndexOf('@');

            if (mentionStart >= 0) {
                // Remplacer @xxx par @username
                const newText = text.substring(0, mentionStart) + '@' + username + ' ' + text.substring(cursorPos);
                textarea.value = newText;

                // Positionner le curseur après la mention
                const newCursorPos = mentionStart + username.length + 2;
                textarea.setSelectionRange(newCursorPos, newCursorPos);
                textarea.focus();
            }

            this.hideMentionDropdown();
        },

        /**
         * Charger la liste des utilisateurs pour les mentions
         */
        loadMentionUsers: async function() {
            try {
                const response = await this.apiRequest('GET', 'users');
                this.state.mentionUsers = Array.isArray(response) ? response : [];
            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement utilisateurs:', error);
                this.state.mentionUsers = [];
            }
        },

        // ===========================================
        // PRIORITÉ
        // ===========================================

        /**
         * Mettre à jour la visibilité de l'onglet Priorité
         */
        updatePriorityTabVisibility: function(feedbackCount) {
            const priorityTabBtn = document.querySelector('.wpvfh-tab[data-tab="priority"]');
            if (priorityTabBtn) {
                priorityTabBtn.style.display = feedbackCount > 0 ? '' : 'none';
            }
        },

        /**
         * Rendre les listes de priorité
         */
        renderPriorityLists: function() {
            const lists = this.elements.priorityLists;
            if (!lists || !lists.none) return;

            // Vider les listes
            Object.values(lists).forEach(list => {
                if (list) list.innerHTML = '';
            });

            // Grouper les feedbacks par priorité (ordre: none, high, medium, low)
            const feedbacksByPriority = {
                none: [],
                high: [],
                medium: [],
                low: []
            };

            this.state.currentFeedbacks.forEach(feedback => {
                const priority = feedback.priority || 'none';
                if (feedbacksByPriority[priority]) {
                    feedbacksByPriority[priority].push(feedback);
                } else {
                    feedbacksByPriority.none.push(feedback);
                }
            });

            // Trier par ordre dans chaque groupe
            Object.keys(feedbacksByPriority).forEach(priority => {
                feedbacksByPriority[priority].sort((a, b) => (a.priority_order || 0) - (b.priority_order || 0));
            });

            // Rendre les feedbacks dans chaque liste et gérer la visibilité des sections
            Object.keys(feedbacksByPriority).forEach(priority => {
                const list = lists[priority];
                if (!list) return;

                const section = list.closest('.wpvfh-priority-section');
                const hasFeedbacks = feedbacksByPriority[priority].length > 0;

                // Masquer la section si vide
                if (section) {
                    section.style.display = hasFeedbacks ? '' : 'none';
                }

                feedbacksByPriority[priority].forEach(feedback => {
                    const item = this.createPriorityItem(feedback);
                    list.appendChild(item);
                });
            });

            // Initialiser le drag-drop
            this.initPriorityDragDrop();
        },

        /**
         * Créer un élément de priorité
         */
        createPriorityItem: function(feedback) {
            const item = document.createElement('div');
            item.className = 'wpvfh-pin-item';
            item.draggable = true;
            item.dataset.feedbackId = feedback.id;

            const statusClass = `status-${feedback.status || 'new'}`;
            const statusLabels = {
                'new': 'Nouveau',
                'in_progress': 'En cours',
                'resolved': 'Résolu',
                'rejected': 'Rejeté'
            };

            item.innerHTML = `
                <span class="wpvfh-pin-marker ${statusClass}"></span>
                <div class="wpvfh-pin-content">
                    <div class="wpvfh-pin-header">
                        <span class="wpvfh-pin-id">#${feedback.id}</span>
                    </div>
                    <div class="wpvfh-pin-text">${this.escapeHtml(feedback.content || feedback.comment || 'Sans commentaire')}</div>
                    <div class="wpvfh-pin-meta">
                        <span class="wpvfh-pin-status ${statusClass}">${statusLabels[feedback.status] || 'Nouveau'}</span>
                    </div>
                </div>
            `;

            // Clic pour voir les détails
            item.addEventListener('click', (e) => {
                if (e.target.closest('.wpvfh-drag-handle')) return;
                this.showFeedbackDetails(feedback);
            });

            return item;
        },

        /**
         * Initialiser le drag-drop pour la priorité
         */
        initPriorityDragDrop: function() {
            const lists = this.elements.priorityLists;
            const dropzones = this.elements.priorityDropzones;
            let draggedItem = null;
            let draggedFeedbackId = null;

            // Gestionnaires pour les items
            Object.values(lists).forEach(list => {
                if (!list) return;

                list.querySelectorAll('.wpvfh-pin-item').forEach(item => {
                    item.addEventListener('dragstart', (e) => {
                        draggedItem = item;
                        draggedFeedbackId = item.dataset.feedbackId;
                        item.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', draggedFeedbackId);
                    });

                    item.addEventListener('dragend', () => {
                        if (draggedItem) {
                            draggedItem.classList.remove('dragging');
                        }
                        draggedItem = null;
                        draggedFeedbackId = null;
                        // Retirer les classes drag-over
                        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
                    });
                });
            });

            // Gestionnaires pour les zones de dépôt (sticky)
            dropzones.forEach(zone => {
                zone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    zone.classList.add('drag-over');
                });

                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('drag-over');
                });

                zone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    zone.classList.remove('drag-over');
                    const priority = zone.dataset.priority;
                    if (draggedFeedbackId && priority) {
                        this.updateFeedbackPriority(draggedFeedbackId, priority);
                    }
                });
            });

            // Gestionnaires pour les listes (réordonner dans la même priorité)
            Object.entries(lists).forEach(([priority, list]) => {
                if (!list) return;

                list.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    list.classList.add('drag-over');

                    // Trouver la position d'insertion
                    const afterElement = this.getDragAfterElement(list, e.clientY);
                    if (draggedItem) {
                        if (afterElement) {
                            list.insertBefore(draggedItem, afterElement);
                        } else {
                            list.appendChild(draggedItem);
                        }
                    }
                });

                list.addEventListener('dragleave', (e) => {
                    if (!list.contains(e.relatedTarget)) {
                        list.classList.remove('drag-over');
                    }
                });

                list.addEventListener('drop', (e) => {
                    e.preventDefault();
                    list.classList.remove('drag-over');
                    if (draggedFeedbackId) {
                        this.updateFeedbackPriority(draggedFeedbackId, priority);
                        this.savePriorityOrder(priority, list);
                    }
                });
            });
        },

        /**
         * Trouver l'élément après lequel insérer
         */
        getDragAfterElement: function(container, y) {
            const draggableElements = [...container.querySelectorAll('.wpvfh-pin-item:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        },

        /**
         * Mettre à jour la priorité d'un feedback
         */
        updateFeedbackPriority: async function(feedbackId, priority) {
            try {
                await this.apiRequest('POST', `feedbacks/${feedbackId}/priority`, {
                    priority: priority
                });

                // Mettre à jour localement
                const feedback = this.state.currentFeedbacks.find(f => f.id == feedbackId);
                if (feedback) {
                    feedback.priority = priority;
                }

                this.showNotification('Priorité mise à jour', 'success');
            } catch (error) {
                console.error('[Blazing Feedback] Erreur mise à jour priorité:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
                // Re-rendre les listes en cas d'erreur
                this.renderPriorityLists();
            }
        },

        /**
         * Sauvegarder l'ordre dans une liste de priorité (local uniquement)
         */
        savePriorityOrder: function(priority, list) {
            // L'ordre est géré localement pour le réarrangement visuel
            // La priorité est sauvegardée via updateFeedbackPriority
        },

        /**
         * Ouvrir la modal de recherche
         */
        openSearchModal: function() {
            if (this.elements.searchModal) {
                this.elements.searchModal.hidden = false;
                this.elements.searchModal.classList.add('active');
            }
        },

        /**
         * Fermer la modal de recherche
         */
        closeSearchModal: function() {
            if (this.elements.searchModal) {
                this.elements.searchModal.hidden = true;
                this.elements.searchModal.classList.remove('active');
            }
        },

        /**
         * Réinitialiser la recherche
         */
        resetSearch: function() {
            if (this.elements.searchId) this.elements.searchId.value = '';
            if (this.elements.searchText) this.elements.searchText.value = '';
            if (this.elements.searchStatus) this.elements.searchStatus.value = '';
            if (this.elements.searchPriority) this.elements.searchPriority.value = '';
            if (this.elements.searchAuthor) this.elements.searchAuthor.value = '';
            if (this.elements.searchDateFrom) this.elements.searchDateFrom.value = '';
            if (this.elements.searchDateTo) this.elements.searchDateTo.value = '';

            if (this.elements.searchResults) {
                this.elements.searchResults.classList.remove('active');
            }
            if (this.elements.searchResultsList) {
                this.elements.searchResultsList.innerHTML = '';
            }
        },

        /**
         * Effectuer la recherche
         */
        performSearch: async function() {
            const criteria = {
                id: this.elements.searchId ? this.elements.searchId.value.trim() : '',
                text: this.elements.searchText ? this.elements.searchText.value.trim() : '',
                status: this.elements.searchStatus ? this.elements.searchStatus.value : '',
                priority: this.elements.searchPriority ? this.elements.searchPriority.value : '',
                author: this.elements.searchAuthor ? this.elements.searchAuthor.value.trim() : '',
                dateFrom: this.elements.searchDateFrom ? this.elements.searchDateFrom.value : '',
                dateTo: this.elements.searchDateTo ? this.elements.searchDateTo.value : '',
            };

            // Filtrer tous les feedbacks (localement)
            let results = [];

            // Recherche sur toutes les pages
            try {
                const response = await this.apiRequest('GET', 'feedback/search', criteria);
                results = response.feedbacks || [];
            } catch (error) {
                // Si l'API search n'existe pas, filtrer localement
                results = this.filterFeedbacksLocally(criteria);
            }

            this.displaySearchResults(results);
        },

        /**
         * Filtrer les feedbacks localement
         */
        filterFeedbacksLocally: function(criteria) {
            let results = [...this.state.feedbacks];

            // Filtrer par ID
            if (criteria.id) {
                const searchId = parseInt(criteria.id, 10);
                results = results.filter(f => f.id === searchId);
            }

            // Filtrer par texte
            if (criteria.text) {
                const searchText = criteria.text.toLowerCase();
                results = results.filter(f =>
                    (f.comment && f.comment.toLowerCase().includes(searchText)) ||
                    (f.transcript && f.transcript.toLowerCase().includes(searchText))
                );
            }

            // Filtrer par statut
            if (criteria.status) {
                results = results.filter(f => f.status === criteria.status);
            }

            // Filtrer par priorité
            if (criteria.priority) {
                results = results.filter(f => f.priority === criteria.priority);
            }

            // Filtrer par auteur
            if (criteria.author) {
                const searchAuthor = criteria.author.toLowerCase();
                results = results.filter(f =>
                    f.author_name && f.author_name.toLowerCase().includes(searchAuthor)
                );
            }

            // Filtrer par date
            if (criteria.dateFrom) {
                const fromDate = new Date(criteria.dateFrom);
                results = results.filter(f => new Date(f.created_at) >= fromDate);
            }
            if (criteria.dateTo) {
                const toDate = new Date(criteria.dateTo);
                toDate.setHours(23, 59, 59, 999);
                results = results.filter(f => new Date(f.created_at) <= toDate);
            }

            return results;
        },

        /**
         * Afficher les résultats de recherche
         */
        displaySearchResults: function(results) {
            if (!this.elements.searchResults || !this.elements.searchResultsList) return;

            this.elements.searchResults.classList.add('active');

            if (this.elements.searchCount) {
                this.elements.searchCount.textContent = results.length;
            }

            if (results.length === 0) {
                this.elements.searchResultsList.innerHTML = `
                    <div class="wpvfh-search-no-results">
                        Aucun feedback trouvé
                    </div>
                `;
                return;
            }

            const statusColors = {
                'open': '#e74c3c',
                'in-progress': '#f39c12',
                'resolved': '#27ae60',
                'closed': '#95a5a6'
            };

            this.elements.searchResultsList.innerHTML = results.map(feedback => {
                const statusColor = statusColors[feedback.status] || '#95a5a6';
                const text = feedback.comment || feedback.transcript || 'Sans contenu';
                const date = new Date(feedback.created_at).toLocaleDateString('fr-FR');
                const pageUrl = feedback.page_url || '';
                const pageTitle = feedback.page_title || pageUrl;

                return `
                    <div class="wpvfh-search-result-item"
                         data-feedback-id="${feedback.id}"
                         data-page-url="${this.escapeHtml(pageUrl)}">
                        <div class="wpvfh-search-result-header">
                            <span class="wpvfh-search-result-id">#${feedback.id}</span>
                            <span class="wpvfh-search-result-status" style="background: ${statusColor}"></span>
                        </div>
                        <div class="wpvfh-search-result-text">${this.escapeHtml(text)}</div>
                        <div class="wpvfh-search-result-meta">
                            <span>${feedback.author_name || 'Anonyme'}</span>
                            <span>${date}</span>
                            ${pageTitle ? `<span title="${this.escapeHtml(pageUrl)}">${this.escapeHtml(pageTitle.substring(0, 30))}${pageTitle.length > 30 ? '...' : ''}</span>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            // Ajouter les événements de clic
            this.elements.searchResultsList.querySelectorAll('.wpvfh-search-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    const feedbackId = parseInt(item.dataset.feedbackId, 10);
                    const pageUrl = item.dataset.pageUrl;
                    this.goToFeedback(feedbackId, pageUrl);
                });
            });
        },

        /**
         * Naviguer vers un feedback (changer de page si nécessaire)
         */
        goToFeedback: function(feedbackId, pageUrl) {
            // Fermer la modal de recherche
            this.closeSearchModal();

            // Vérifier si on est sur la bonne page
            const currentUrl = window.location.href.split('?')[0].split('#')[0];
            const targetUrl = pageUrl ? pageUrl.split('?')[0].split('#')[0] : '';

            if (targetUrl && currentUrl !== targetUrl) {
                // Naviguer vers la page avec le feedback ID en paramètre
                const separator = pageUrl.includes('?') ? '&' : '?';
                window.location.href = pageUrl + separator + 'wpvfh_open=' + feedbackId;
            } else {
                // On est sur la bonne page, ouvrir les détails directement
                this.showFeedbackDetails(feedbackId);

                // S'assurer que le panneau est ouvert
                if (this.elements.panel && !this.elements.panel.classList.contains('active')) {
                    this.openPanel();
                }
            }
        },

        /**
         * Vérifier si un feedback doit être ouvert au chargement
         */
        checkOpenFeedbackParam: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const feedbackId = urlParams.get('wpvfh_open');

            if (feedbackId) {
                // Attendre que les feedbacks soient chargés
                setTimeout(() => {
                    this.showFeedbackDetails(parseInt(feedbackId, 10));
                    if (this.elements.panel && !this.elements.panel.classList.contains('active')) {
                        this.openPanel();
                    }

                    // Nettoyer l'URL
                    const cleanUrl = window.location.href.replace(/[?&]wpvfh_open=\d+/, '');
                    window.history.replaceState({}, '', cleanUrl);
                }, 500);
            }
        },
    };

    // Exposer le widget globalement
    window.BlazingFeedback = BlazingFeedback;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingFeedback.init());
    } else {
        BlazingFeedback.init();
    }

})(window, document);
