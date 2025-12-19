/**
 * Module Pages - Blazing Feedback
 * Gestion de la liste des pages avec feedbacks
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const Pages = {
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Charger la liste de toutes les pages avec feedbacks
         */
        loadAllPages: async function() {
            const el = this.widget.elements;

            if (el.pagesLoading) el.pagesLoading.hidden = false;
            if (el.pagesList) el.pagesList.hidden = true;
            if (el.pagesEmpty) el.pagesEmpty.hidden = true;

            try {
                const response = await this.widget.modules.api.request('GET', 'pages');
                this.widget.state.allPages = Array.isArray(response) ? response : [];

                this.updatePagesStats();
                this.renderPagesList();

            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement pages:', error);
                this.widget.modules.notifications.show('Erreur lors du chargement des pages', 'error');
            } finally {
                if (el.pagesLoading) el.pagesLoading.hidden = true;
            }
        },

        /**
         * Mettre à jour les stats des pages
         */
        updatePagesStats: function() {
            const pages = this.widget.state.allPages || [];

            // Calculer les stats
            const totalPages = pages.length;
            const totalFeedbacks = pages.reduce((sum, p) => sum + (p.count || 0), 0);
            const validatedPages = pages.filter(p => p.validated).length;
            const pendingPages = totalPages - validatedPages;

            // Mettre à jour les compteurs
            const totalEl = document.getElementById('wpvfh-pages-total-count');
            const feedbacksEl = document.getElementById('wpvfh-pages-feedbacks-count');
            const validatedEl = document.getElementById('wpvfh-pages-validated-count');
            const pendingEl = document.getElementById('wpvfh-pages-pending-count');

            if (totalEl) totalEl.textContent = totalPages;
            if (feedbacksEl) feedbacksEl.textContent = totalFeedbacks;
            if (validatedEl) validatedEl.textContent = validatedPages;
            if (pendingEl) pendingEl.textContent = pendingPages;
        },

        /**
         * Afficher la liste des pages
         */
        renderPagesList: function() {
            const el = this.widget.elements;
            const tools = this.widget.modules.tools;

            if (!el.pagesList) return;

            const pages = this.widget.state.allPages || [];
            const currentUrl = this.widget.config.currentUrl || window.location.href;

            // Afficher/masquer l'état vide
            if (el.pagesEmpty) {
                el.pagesEmpty.hidden = pages.length > 0;
            }
            el.pagesList.hidden = pages.length === 0;

            if (pages.length === 0) return;

            // Générer le HTML
            const html = pages.map(page => {
                const isCurrent = page.url === currentUrl;
                const title = page.title || this.extractPageTitle(page.url);
                const shortUrl = this.shortenUrl(page.url);

                return `
                    <div class="wpvfh-page-item ${isCurrent ? 'current' : ''}" data-url="${tools.escapeHtml(page.url)}">
                        <span class="wpvfh-page-icon">${page.validated ? '✅' : '📄'}</span>
                        <div class="wpvfh-page-info">
                            <div class="wpvfh-page-title">${tools.escapeHtml(title)}</div>
                            <div class="wpvfh-page-url">${tools.escapeHtml(shortUrl)}</div>
                        </div>
                        <span class="wpvfh-page-badge ${page.validated ? 'validated' : 'has-feedbacks'}">
                            ${page.validated ? '✓' : page.count || 0}
                        </span>
                    </div>
                `;
            }).join('');

            el.pagesList.innerHTML = html;

            // Ajouter les événements
            el.pagesList.querySelectorAll('.wpvfh-page-item').forEach(item => {
                item.addEventListener('click', () => {
                    const url = item.dataset.url;
                    if (url && url !== currentUrl) {
                        window.location.href = url;
                    } else {
                        // Page courante, aller à la liste
                        this.widget.modules.panel.switchTab('list');
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

                // Retirer l'extension
                title = title.replace(/\.[^.]+$/, '');

                // Remplacer les tirets/underscores par des espaces
                title = title.replace(/[-_]/g, ' ');

                // Capitaliser
                title = title.charAt(0).toUpperCase() + title.slice(1);

                return title;
            } catch (e) {
                return 'Page';
            }
        },

        /**
         * Raccourcir une URL pour l'affichage
         * @param {string} url - URL
         * @returns {string} URL raccourcie
         */
        shortenUrl: function(url) {
            try {
                const urlObj = new URL(url);
                let path = urlObj.pathname;

                // Limiter la longueur
                if (path.length > 40) {
                    path = '...' + path.slice(-37);
                }

                return path || '/';
            } catch (e) {
                return url.length > 40 ? '...' + url.slice(-37) : url;
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.pages = Pages;

})(window);
