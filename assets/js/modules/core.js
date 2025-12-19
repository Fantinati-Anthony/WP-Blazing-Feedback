/**
 * Blazing Feedback - Module Core
 *
 * Configuration, état, éléments DOM, initialisation, thème
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Core
     * Gère l'initialisation, le thème, le cache des éléments DOM
     */
    const CoreModule = {

        /**
         * Initialiser le module core
         * @param {Object} widget - Instance du widget principal
         */
        init: function(widget) {
            this.cacheElements(widget);
            this.moveFixedElementsToBody(widget);
            this.applyThemeColors(widget);

            console.log('[Blazing Feedback Core] Module initialisé');
        },

        /**
         * Appliquer les couleurs du thème selon le mode choisi
         * @param {Object} widget - Instance du widget
         */
        applyThemeColors: function(widget) {
            const themeMode = widget.config.themeMode || 'system';
            let colors;
            let logo;

            // Déterminer le mode effectif
            let effectiveMode = themeMode;
            if (themeMode === 'system') {
                // Utiliser la préférence système
                effectiveMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            // Sélectionner les couleurs selon le mode
            if (effectiveMode === 'dark') {
                colors = widget.config.colorsDark || {};
                logo = widget.config.panelLogos?.dark;
            } else {
                colors = widget.config.colorsLight || {};
                logo = widget.config.panelLogos?.light;
            }

            // Appliquer les variables CSS
            const root = document.documentElement;
            root.style.setProperty('--wpvfh-primary', colors.primary || '#FE5100');
            root.style.setProperty('--wpvfh-primary-hover', colors.primaryHover || '#E04800');
            root.style.setProperty('--wpvfh-secondary', colors.secondary || '#263e4b');
            root.style.setProperty('--wpvfh-success', colors.success || '#28a745');
            root.style.setProperty('--wpvfh-warning', colors.warning || '#ffc107');
            root.style.setProperty('--wpvfh-danger', colors.danger || '#dc3545');
            root.style.setProperty('--wpvfh-text', colors.text || '#263e4b');
            root.style.setProperty('--wpvfh-text-light', colors.textLight || '#5a7282');
            root.style.setProperty('--wpvfh-bg', colors.bg || '#ffffff');
            root.style.setProperty('--wpvfh-bg-light', colors.bgLight || '#f8f9fa');
            root.style.setProperty('--wpvfh-border', colors.border || '#e0e4e8');

            // Appliquer les variables CSS du footer
            root.style.setProperty('--wpvfh-footer-bg', colors.footerBg || '#f8f9fa');
            root.style.setProperty('--wpvfh-footer-border', colors.footerBorder || '#e9ecef');
            root.style.setProperty('--wpvfh-footer-btn-add-bg', colors.footerBtnAddBg || '#27ae60');
            root.style.setProperty('--wpvfh-footer-btn-add-text', colors.footerBtnAddText || '#ffffff');
            root.style.setProperty('--wpvfh-footer-btn-add-hover', colors.footerBtnAddHover || '#219a52');
            root.style.setProperty('--wpvfh-footer-btn-visibility-bg', colors.footerBtnVisibilityBg || '#3498db');
            root.style.setProperty('--wpvfh-footer-btn-visibility-text', colors.footerBtnVisibilityText || '#ffffff');
            root.style.setProperty('--wpvfh-footer-btn-visibility-hover', colors.footerBtnVisibilityHover || '#2980b9');

            // Mettre à jour le logo du panneau
            if (logo && widget.elements.panel) {
                const panelLogo = widget.elements.panel.querySelector('.wpvfh-panel-logo');
                if (panelLogo) {
                    panelLogo.src = logo;
                }
            }

            // Ajouter une classe pour le mode
            document.body.classList.remove('wpvfh-theme-light', 'wpvfh-theme-dark');
            document.body.classList.add('wpvfh-theme-' + effectiveMode);

            // Écouter les changements de préférence système si en mode système
            if (themeMode === 'system' && window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                    this.applyThemeColors(widget);
                });
            }

            console.log('[Blazing Feedback Core] Thème appliqué:', effectiveMode);
        },

        /**
         * Déplacer les éléments fixed vers body pour éviter les problèmes de stacking context
         * @param {Object} widget - Instance du widget
         */
        moveFixedElementsToBody: function(widget) {
            // Déplacer le bouton coin vers body
            if (widget.elements.toggleBtn && widget.elements.toggleBtn.parentNode !== document.body) {
                document.body.appendChild(widget.elements.toggleBtn);
                console.log('[Blazing Feedback Core] Bouton coin déplacé vers body');
            }

            // Déplacer le panel vers body pour éviter les problèmes avec transform/filter des parents
            if (widget.elements.panel && widget.elements.panel.parentNode !== document.body) {
                document.body.appendChild(widget.elements.panel);
                console.log('[Blazing Feedback Core] Panel déplacé vers body');
            }

            // Déplacer l'overlay vers body aussi
            if (widget.elements.sidebarOverlay && widget.elements.sidebarOverlay.parentNode !== document.body) {
                document.body.appendChild(widget.elements.sidebarOverlay);
            }

            // Déplacer les modals vers body
            if (widget.elements.confirmModal && widget.elements.confirmModal.parentNode !== document.body) {
                document.body.appendChild(widget.elements.confirmModal);
            }
            if (widget.elements.validateModal && widget.elements.validateModal.parentNode !== document.body) {
                document.body.appendChild(widget.elements.validateModal);
            }
        },

        /**
         * Mettre en cache les éléments DOM
         * @param {Object} widget - Instance du widget
         */
        cacheElements: function(widget) {
            widget.elements = {
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
                // Métadatas
                tabMetadata: document.getElementById('wpvfh-tab-metadata'),
                metadataSubtabs: document.querySelectorAll('.wpvfh-subtab'),
                metadataSubtabContents: document.querySelectorAll('.wpvfh-metadata-subtab-content'),
                metadataDropzones: document.querySelectorAll('.wpvfh-dropzone-metadata'),
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
                searchCount: document.getElementById('wpvfh-search-results-count'),
                // Champs Type, Priorité, Tags (formulaire création)
                feedbackType: document.getElementById('wpvfh-feedback-type'),
                feedbackPriority: document.getElementById('wpvfh-feedback-priority'),
                feedbackTags: document.getElementById('wpvfh-feedback-tags'),
                feedbackTagsContainer: document.getElementById('wpvfh-feedback-tags-container'),
                feedbackTagsInput: document.getElementById('wpvfh-feedback-tags-input'),
                // Champs Type, Priorité, Tags (vue détails)
                detailType: document.getElementById('wpvfh-detail-type'),
                detailPrioritySelect: document.getElementById('wpvfh-detail-priority-select'),
                detailTagsContainer: document.getElementById('wpvfh-detail-tags-container'),
                detailTagsInput: document.getElementById('wpvfh-detail-tags-input'),
                // Labels dans la vue détails
                detailLabels: document.getElementById('wpvfh-detail-labels'),
                detailTypeLabel: document.getElementById('wpvfh-detail-type-label'),
                detailPriorityLabel: document.getElementById('wpvfh-detail-priority-label'),
                commentInput: document.getElementById('wpvfh-comment'),
            };
        },

        /**
         * Émettre un événement personnalisé
         * @param {string} name - Nom de l'événement (sera préfixé par 'blazing-feedback:')
         * @param {Object} detail - Détails de l'événement
         */
        emitEvent: function(name, detail = {}) {
            const event = new CustomEvent('blazing-feedback:' + name, {
                bubbles: true,
                detail: detail,
            });
            document.dispatchEvent(event);
        },
    };

    // Exposer le module via le namespace global
    if (!window.FeedbackWidget) {
        window.FeedbackWidget = { modules: {} };
    }
    window.FeedbackWidget.modules.core = CoreModule;

})(window, document);
