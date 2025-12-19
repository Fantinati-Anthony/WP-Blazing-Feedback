/**
 * Blazing Feedback - Widget Principal
 *
 * Contrôleur principal qui orchestre les modules
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    // Créer le namespace global pour les modules
    if (!window.FeedbackWidget) {
        window.FeedbackWidget = {
            modules: {}
        };
    }

    /**
     * Widget Blazing Feedback - Orchestrateur
     * Charge et initialise tous les modules
     * @namespace
     */
    const BlazingFeedback = {

        /**
         * Configuration depuis WordPress
         * @type {Object}
         */
        config: window.wpvfhData || {},

        /**
         * État du widget (géré par le module core)
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
            isSelectingElement: false, // Mode sélection d'élément
            savedFormData: null,       // Données sauvegardées du formulaire
            videoBlob: null,           // Blob vidéo pour upload
        },

        /**
         * Éléments DOM (géré par le module core)
         * @type {Object}
         */
        elements: {},

        /**
         * Timers
         */
        voiceTimer: null,
        videoTimer: null,

        /**
         * Initialiser le widget et tous les modules
         * @returns {void}
         */
        init: function() {
            // Vérifier les permissions
            if (!this.config.canCreate && !this.config.canModerate) {
                console.log('[Blazing Feedback] Utilisateur sans permissions');
                return;
            }

            // Charger les modules dans l'ordre
            this.loadModules();

            console.log('[Blazing Feedback] Widget initialisé avec modules');
        },

        /**
         * Charger et initialiser tous les modules
         * @returns {void}
         */
        loadModules: function() {
            const modules = window.FeedbackWidget.modules;

            // 1. Module Core - Configuration, état, éléments DOM
            if (modules.core && modules.core.init) {
                modules.core.init(this);
            }

            // 2. Module Events - Attacher les gestionnaires d'événements
            if (modules.events && modules.events.init) {
                modules.events.init(this);
            }

            // 3. Module API - Charger les feedbacks existants
            if (modules.api && modules.api.loadExistingFeedbacks) {
                modules.api.loadExistingFeedbacks(this);
            }

            // 4. Vérifier si un feedback doit être ouvert au chargement
            if (modules.search && modules.search.checkOpenFeedbackParam) {
                modules.search.checkOpenFeedbackParam(this);
            }

            console.log('[Blazing Feedback] Modules chargés:', Object.keys(modules));
        },

        // ===========================================
        // DÉLÉGATION AUX MODULES
        // Les méthodes suivantes délèguent aux modules appropriés
        // ===========================================

        /**
         * Émettre un événement personnalisé (module core)
         */
        emitEvent: function(name, detail = {}) {
            const modules = window.FeedbackWidget.modules;
            if (modules.core && modules.core.emitEvent) {
                return modules.core.emitEvent(name, detail);
            }
        },

        /**
         * Échapper le HTML (module tools)
         */
        escapeHtml: function(text) {
            const modules = window.FeedbackWidget.modules;
            if (modules.tools && modules.tools.escapeHtml) {
                return modules.tools.escapeHtml(text);
            }
            // Fallback
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Afficher une notification (module notifications)
         */
        showNotification: function(message, type = 'info') {
            const modules = window.FeedbackWidget.modules;
            if (modules.notifications && modules.notifications.show) {
                return modules.notifications.show(this, message, type);
            }
        },

        /**
         * Ouvrir le panel (module panel)
         */
        openPanel: function(tab = 'new') {
            const modules = window.FeedbackWidget.modules;
            if (modules.panel && modules.panel.open) {
                return modules.panel.open(this, tab);
            }
        },

        /**
         * Fermer le panel (module panel)
         */
        closePanel: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.panel && modules.panel.close) {
                return modules.panel.close(this);
            }
        },

        /**
         * Changer d'onglet (module panel)
         */
        switchTab: function(tabName) {
            const modules = window.FeedbackWidget.modules;
            if (modules.panel && modules.panel.switchTab) {
                return modules.panel.switchTab(this, tabName);
            }
        },

        /**
         * Afficher les détails d'un feedback (module details)
         */
        showFeedbackDetails: function(feedback) {
            const modules = window.FeedbackWidget.modules;
            if (modules.details && modules.details.show) {
                return modules.details.show(this, feedback);
            }
        },

        /**
         * Requête API (module api)
         */
        apiRequest: async function(method, endpoint, data = null) {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.request) {
                return modules.api.request(this, method, endpoint, data);
            }
        },

        /**
         * Charger les feedbacks existants (module api)
         */
        loadExistingFeedbacks: async function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.loadExistingFeedbacks) {
                return modules.api.loadExistingFeedbacks(this);
            }
        },

        /**
         * Afficher la liste des pins (module list)
         */
        renderPinsList: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.list && modules.list.render) {
                return modules.list.render(this);
            }
        },

        /**
         * Obtenir les feedbacks filtrés (module filters)
         */
        getFilteredFeedbacks: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.filters && modules.filters.getFiltered) {
                return modules.filters.getFiltered(this);
            }
            return this.state.currentFeedbacks;
        },

        /**
         * Mettre à jour les compteurs de filtres (module filters)
         */
        updateFilterCounts: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.filters && modules.filters.updateCounts) {
                return modules.filters.updateCounts(this);
            }
        },

        /**
         * Réinitialiser le formulaire (module form)
         */
        resetForm: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.form && modules.form.reset) {
                return modules.form.reset(this);
            }
        },

        /**
         * Capturer le screenshot (module screenshot)
         */
        captureScreenshot: async function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.screenshot && modules.screenshot.capture) {
                return modules.screenshot.capture(this);
            }
        },

        /**
         * Effacer le screenshot (module screenshot)
         */
        clearScreenshot: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.screenshot && modules.screenshot.clear) {
                return modules.screenshot.clear(this);
            }
        },

        /**
         * Obtenir le label d'un statut (module labels)
         */
        getStatusLabel: function(statusId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getStatusLabel) {
                return modules.labels.getStatusLabel(this, statusId);
            }
            return statusId;
        },

        /**
         * Obtenir l'emoji d'un statut (module labels)
         */
        getStatusEmoji: function(statusId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getStatusEmoji) {
                return modules.labels.getStatusEmoji(this, statusId);
            }
            return '';
        },

        /**
         * Obtenir la couleur d'un statut (module labels)
         */
        getStatusColor: function(statusId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getStatusColor) {
                return modules.labels.getStatusColor(this, statusId);
            }
            return '#6c757d';
        },

        /**
         * Obtenir la config d'un type (module labels)
         */
        getTypeConfig: function(typeId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getTypeConfig) {
                return modules.labels.getTypeConfig(this, typeId);
            }
            return null;
        },

        /**
         * Obtenir le label d'un type (module labels)
         */
        getTypeLabel: function(typeId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getTypeLabel) {
                return modules.labels.getTypeLabel(this, typeId);
            }
            return typeId;
        },

        /**
         * Obtenir l'emoji d'un type (module labels)
         */
        getTypeEmoji: function(typeId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getTypeEmoji) {
                return modules.labels.getTypeEmoji(this, typeId);
            }
            return '';
        },

        /**
         * Obtenir la config d'une priorité (module labels)
         */
        getPriorityConfig: function(priorityId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getPriorityConfig) {
                return modules.labels.getPriorityConfig(this, priorityId);
            }
            return null;
        },

        /**
         * Obtenir le label d'une priorité (module labels)
         */
        getPriorityLabel: function(priorityId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getPriorityLabel) {
                return modules.labels.getPriorityLabel(this, priorityId);
            }
            return priorityId;
        },

        /**
         * Obtenir l'emoji d'une priorité (module labels)
         */
        getPriorityEmoji: function(priorityId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getPriorityEmoji) {
                return modules.labels.getPriorityEmoji(this, priorityId);
            }
            return '';
        },

        /**
         * Obtenir la couleur d'une priorité (module labels)
         */
        getPriorityColor: function(priorityId) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.getPriorityColor) {
                return modules.labels.getPriorityColor(this, priorityId);
            }
            return '#6c757d';
        },

        /**
         * Générer le HTML des labels d'un feedback (module labels)
         */
        generateFeedbackLabelsHtml: function(feedback) {
            const modules = window.FeedbackWidget.modules;
            if (modules.labels && modules.labels.generateHtml) {
                return modules.labels.generateHtml(this, feedback);
            }
            return '';
        },

        /**
         * Scroller vers un pin (module list)
         */
        scrollToPin: function(feedbackId) {
            if (window.BlazingAnnotation) {
                window.BlazingAnnotation.scrollToPin(feedbackId);
            }
        },

        /**
         * Mettre à jour l'ordre des feedbacks (module list)
         */
        updateFeedbackOrder: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.list && modules.list.updateOrder) {
                return modules.list.updateOrder(this);
            }
        },

        /**
         * Mettre à jour le statut d'un feedback (module api)
         */
        updateFeedbackStatus: async function(feedbackId, status) {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.updateStatus) {
                return modules.api.updateStatus(this, feedbackId, status);
            }
        },

        /**
         * Mettre à jour une meta d'un feedback (module api)
         */
        updateFeedbackMeta: async function(feedbackId, metaKey, metaValue) {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.updateMeta) {
                return modules.api.updateMeta(this, feedbackId, metaKey, metaValue);
            }
        },

        /**
         * Mettre à jour la priorité d'un feedback (module api)
         */
        updateFeedbackPriority: async function(feedbackId, priority) {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.updatePriority) {
                return modules.api.updatePriority(this, feedbackId, priority);
            }
        },

        /**
         * Ajouter une réponse (module api)
         */
        addReply: async function(feedbackId, content) {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.addReply) {
                return modules.api.addReply(this, feedbackId, content);
            }
        },

        /**
         * Mettre à jour les labels dans la vue détails (module details)
         */
        updateDetailLabels: function(feedback) {
            const modules = window.FeedbackWidget.modules;
            if (modules.details && modules.details.updateLabels) {
                return modules.details.updateLabels(this, feedback);
            }
        },

        /**
         * Section validation de page (module validation)
         */
        updateValidationSection: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.validation && modules.validation.updateSection) {
                return modules.validation.updateSection(this);
            }
        },

        /**
         * Charger toutes les pages (module api)
         */
        loadAllPages: async function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.loadAllPages) {
                return modules.api.loadAllPages(this);
            }
        },

        /**
         * Rendre les listes de priorité (module api)
         */
        renderPriorityLists: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.renderPriorityLists) {
                return modules.api.renderPriorityLists(this);
            }
        },

        /**
         * Rendre les listes de métadonnées (module api)
         */
        renderMetadataLists: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.renderMetadataLists) {
                return modules.api.renderMetadataLists(this);
            }
        },

        /**
         * Mettre à jour la visibilité de l'onglet priorité (module api)
         */
        updatePriorityTabVisibility: function(feedbackCount) {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.updatePriorityTabVisibility) {
                return modules.api.updatePriorityTabVisibility(this, feedbackCount);
            }
        },

        /**
         * Changer de sous-onglet métadonnées (module api)
         */
        switchMetadataSubtab: function(subtabName) {
            const modules = window.FeedbackWidget.modules;
            if (modules.api && modules.api.switchMetadataSubtab) {
                return modules.api.switchMetadataSubtab(this, subtabName);
            }
        },

        /**
         * Obtenir la couleur d'un tag prédéfini (module tags)
         */
        getPredefinedTagColor: function(tagName) {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.getPredefinedColor) {
                return modules.tags.getPredefinedColor(this, tagName);
            }
            return null;
        },

        /**
         * Ajouter un tag au formulaire (module tags)
         */
        addFormTag: function(newTag, color) {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.addFormTag) {
                return modules.tags.addFormTag(this, newTag, color);
            }
        },

        /**
         * Retirer un tag du formulaire (module tags)
         */
        removeFormTag: function(tagToRemove) {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.removeFormTag) {
                return modules.tags.removeFormTag(this, tagToRemove);
            }
        },

        /**
         * Retirer le dernier tag du formulaire (module tags)
         */
        removeLastFormTag: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.removeLastFormTag) {
                return modules.tags.removeLastFormTag(this);
            }
        },

        /**
         * Effacer tous les tags du formulaire (module tags)
         */
        clearFormTags: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.clearFormTags) {
                return modules.tags.clearFormTags(this);
            }
        },

        /**
         * Ajouter un tag dans la vue détails (module tags)
         */
        addTag: function(newTag) {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.add) {
                return modules.tags.add(this, newTag);
            }
        },

        /**
         * Retirer un tag dans la vue détails (module tags)
         */
        removeTag: function(tagToRemove) {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.remove) {
                return modules.tags.remove(this, tagToRemove);
            }
        },

        /**
         * Retirer le dernier tag dans la vue détails (module tags)
         */
        removeLastTag: function() {
            const modules = window.FeedbackWidget.modules;
            if (modules.tags && modules.tags.removeLast) {
                return modules.tags.removeLast(this);
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
