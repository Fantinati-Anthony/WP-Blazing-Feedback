/**
 * Blazing Feedback - Widget Principal (Orchestrateur)
 * 
 * Orchestreur léger qui charge et initialise tous les modules
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
            currentFeedbackId: null,
            currentFilter: 'all',
            allPages: [],
            attachments: [],
            mentionUsers: [],
            feedbackToDelete: null,
            videoBlob: null,
            isSelectingElement: false,
            savedFormData: null,
        },

        /**
         * Éléments DOM (seront remplis par le module core)
         * @type {Object}
         */
        elements: {},

        /**
         * Modules chargés
         * @type {Object}
         */
        modules: {},

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

            // Charger les modules depuis window.FeedbackWidget.modules
            if (window.FeedbackWidget && window.FeedbackWidget.modules) {
                this.modules = window.FeedbackWidget.modules;
            } else {
                console.error('[Blazing Feedback] Modules non trouvés !');
                return;
            }

            // Initialiser tous les modules dans le bon ordre
            const moduleOrder = [
                'tools', 'notifications', 'core', 'api',
                'labels', 'tags', 'filters', 'screenshot',
                'media', 'attachments', 'mentions', 'validation',
                'form', 'list', 'pages', 'details', 'panel', 'search',
                'events', 'participants'
            ];

            moduleOrder.forEach(moduleName => {
                if (this.modules[moduleName] && this.modules[moduleName].init) {
                    this.modules[moduleName].init(this);
                    console.log(`[Blazing Feedback] Module ${moduleName} initialisé`);
                } else {
                    console.warn(`[Blazing Feedback] Module ${moduleName} non trouvé ou sans méthode init`);
                }
            });

            // Initialisation du core
            if (this.modules.core) {
                this.modules.core.cacheElements();
                this.modules.core.moveFixedElementsToBody();
                this.modules.core.applyThemeColors();
            }

            // Attacher les événements
            if (this.modules.events) {
                this.modules.events.bindEvents();
            }

            // Charger les feedbacks existants
            if (this.modules.api) {
                this.modules.api.loadExistingFeedbacks();
            }

            // Vérifier si un feedback doit être ouvert au chargement
            this.checkOpenFeedbackParam();

            console.log('[Blazing Feedback] Widget initialisé avec succès');
        },

        /**
         * Vérifier si un feedback doit être ouvert au chargement
         */
        checkOpenFeedbackParam: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const feedbackId = urlParams.get('wpvfh_open');

            if (feedbackId) {
                setTimeout(() => {
                    const id = parseInt(feedbackId, 10);
                    const feedback = this.state.currentFeedbacks.find(f => f.id === id);
                    if (feedback && this.modules.details) {
                        this.modules.details.showFeedbackDetails(feedback);
                    }

                    // Nettoyer l'URL
                    const cleanUrl = window.location.href.replace(/[?&]wpvfh_open=\d+/, '');
                    window.history.replaceState({}, '', cleanUrl);
                }, 500);
            }
        },

        // ========================================
        // API PUBLIQUE - Proxies vers les modules
        // ========================================

        /**
         * Afficher une notification
         * @param {string} message
         * @param {string} type
         */
        showNotification: function(message, type) {
            if (this.modules.notifications) {
                this.modules.notifications.show(message, type);
            }
        },

        /**
         * Ouvrir le panel
         * @param {string} tab
         */
        openPanel: function(tab) {
            if (this.modules.panel) {
                this.modules.panel.openPanel(tab);
            }
        },

        /**
         * Fermer le panel
         */
        closePanel: function() {
            if (this.modules.panel) {
                this.modules.panel.closePanel();
            }
        },

        /**
         * Changer d'onglet
         * @param {string} tabName
         */
        switchTab: function(tabName) {
            if (this.modules.panel) {
                this.modules.panel.switchTab(tabName);
            }
        },

        /**
         * Afficher les détails d'un feedback
         * @param {Object} feedback
         */
        showFeedbackDetails: function(feedback) {
            if (this.modules.details) {
                this.modules.details.showFeedbackDetails(feedback);
            }
        },

        /**
         * Capturer un screenshot
         */
        captureScreenshot: function() {
            if (this.modules.screenshot) {
                this.modules.screenshot.captureScreenshot();
            }
        },

        /**
         * Requête API
         * @param {string} method
         * @param {string} endpoint
         * @param {Object} data
         * @returns {Promise}
         */
        apiRequest: function(method, endpoint, data) {
            if (this.modules.api) {
                return this.modules.api.request(method, endpoint, data);
            }
            return Promise.reject(new Error('Module API non disponible'));
        }
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
