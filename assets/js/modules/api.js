/**
 * Module API - Blazing Feedback
 * Requêtes REST API
 * @package Blazing_Feedback
 */
(function(window) {
    'use strict';

    const API = {
        /**
         * Initialiser le module
         * @param {Object} widget
         */
        init: function(widget) {
            this.widget = widget;
        },

        /**
         * Effectuer une requête à l'API REST
         * @param {string} method
         * @param {string} endpoint
         * @param {Object} data
         * @returns {Promise<Object>}
         */
        request: async function(method, endpoint, data = null) {
            const url = this.widget.config.restUrl + endpoint;

            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.widget.config.restNonce,
                },
                credentials: 'same-origin',
            };

            if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                options.body = JSON.stringify(data);
            }

            try {
                const response = await fetch(url, options);
                const responseText = await response.text();

                let responseData;
                try {
                    responseData = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('[Blazing Feedback] Réponse non-JSON:', responseText.substring(0, 500));
                    throw new Error('La réponse du serveur n\'est pas valide.');
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
         * Charger les feedbacks existants pour cette page
         */
        loadExistingFeedbacks: async function() {
            try {
                const currentUrl = this.widget.config.currentUrl || window.location.href;
                const url = encodeURIComponent(currentUrl);
                // Toujours inclure les feedbacks resolved/rejected pour les afficher dans la liste
                const response = await this.request('GET', `feedbacks/by-url?url=${url}&include_resolved=true`);

                if (Array.isArray(response)) {
                    let pinIndex = 1;
                    response.forEach(feedback => {
                        const hasPosition = feedback.selector || feedback.position_x || feedback.position_y;
                        if (hasPosition) {
                            feedback._displayOrder = pinIndex++;
                        }
                    });

                    this.widget.state.currentFeedbacks = response;

                    if (response.length > 0) {
                        this.widget.modules.tools.emitEvent('load-pins', { pins: response });
                    }

                    this.updateFeedbackCounts(response.length);

                    // Mettre à jour les compteurs de filtres
                    if (this.widget.modules.filters) {
                        this.widget.modules.filters.updateFilterCounts();
                    }

                    // Mettre à jour la barre de progression
                    if (this.widget.modules.validation) {
                        this.widget.modules.validation.updateValidationSection();
                    }

                    // Rendre la liste des feedbacks
                    if (this.widget.modules.list) {
                        this.widget.modules.list.renderPinsList();
                    }
                }
            } catch (error) {
                console.error('[Blazing Feedback] Erreur chargement feedbacks:', error);
            }
        },

        /**
         * Mettre à jour les compteurs
         * @param {number} count
         */
        updateFeedbackCounts: function(count) {
            const el = this.widget.elements;
            
            if (el.pinsCount) {
                el.pinsCount.textContent = count > 0 ? count : '';
            }
            if (el.feedbackCount) {
                if (count > 0) {
                    el.feedbackCount.textContent = count;
                    el.feedbackCount.hidden = false;
                } else {
                    el.feedbackCount.hidden = true;
                }
            }
        }
    };

    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.api = API;

})(window);
