/**
 * Blazing Feedback - Module de capture d'écran
 *
 * Utilise html2canvas pour capturer le viewport de la page
 * Gère les cas d'erreur avec fallback gracieux
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

(function(window, document) {
    'use strict';

    /**
     * Module Screenshot
     * @namespace
     */
    const BlazingScreenshot = {

        /**
         * Options de configuration par défaut pour html2canvas
         * @type {Object}
         */
        defaultOptions: {
            useCORS: true,              // Tenter de charger les images cross-origin
            allowTaint: false,          // Ne pas permettre les images qui salissent le canvas
            backgroundColor: '#ffffff', // Fond blanc par défaut
            scale: 1,                   // Échelle 1:1 pour performance
            logging: false,             // Pas de logs en production
            imageTimeout: 5000,         // Timeout pour le chargement des images
            removeContainer: true,      // Nettoyer après capture
            foreignObjectRendering: false, // Désactivé pour compatibilité
        },

        /**
         * État du module
         * @type {Object}
         */
        state: {
            isCapturing: false,
            lastCapture: null,
            lastCaptureTime: null,
        },

        /**
         * Initialiser le module
         * @returns {void}
         */
        init: function() {
            // Vérifier la disponibilité de html2canvas
            if (typeof html2canvas === 'undefined') {
                console.warn('[Blazing Feedback] html2canvas non disponible, captures désactivées');
                return;
            }

            // Écouter l'événement de demande de capture
            document.addEventListener('blazing-feedback:capture-request', this.handleCaptureRequest.bind(this));

            console.log('[Blazing Feedback] Module Screenshot initialisé');
        },

        /**
         * Capturer le viewport actuel
         * @param {Object} options - Options personnalisées
         * @returns {Promise<string>} Data URL de l'image
         */
        capture: async function(options = {}) {
            // Empêcher les captures simultanées
            if (this.state.isCapturing) {
                return Promise.reject(new Error('Capture déjà en cours'));
            }

            this.state.isCapturing = true;

            try {
                // Masquer temporairement le widget de feedback
                this.hideWidget();

                // Fusionner les options
                const mergedOptions = { ...this.defaultOptions, ...options };

                // Ajouter les dimensions du viewport
                mergedOptions.width = window.innerWidth;
                mergedOptions.height = window.innerHeight;
                mergedOptions.windowWidth = window.innerWidth;
                mergedOptions.windowHeight = window.innerHeight;

                // Ignorer certains éléments
                mergedOptions.ignoreElements = (element) => {
                    // Ignorer le conteneur du plugin
                    if (element.id && element.id.startsWith('wpvfh-')) {
                        return true;
                    }
                    // Ignorer les éléments marqués
                    if (element.hasAttribute('data-blazing-ignore')) {
                        return true;
                    }
                    return false;
                };

                // Capturer avec html2canvas
                const canvas = await html2canvas(document.body, mergedOptions);

                // Convertir en data URL
                const dataUrl = canvas.toDataURL('image/png', 0.9);

                // Stocker la capture
                this.state.lastCapture = dataUrl;
                this.state.lastCaptureTime = Date.now();

                // Émettre l'événement de succès
                this.emitEvent('capture-success', { dataUrl });

                return dataUrl;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture:', error);

                // Émettre l'événement d'erreur
                this.emitEvent('capture-error', { error: error.message });

                // Tenter le fallback
                return this.fallbackCapture();

            } finally {
                // Réafficher le widget
                this.showWidget();
                this.state.isCapturing = false;
            }
        },

        /**
         * Capture de fallback (génère une image placeholder)
         * @returns {Promise<string>} Data URL de l'image placeholder
         */
        fallbackCapture: function() {
            return new Promise((resolve) => {
                const canvas = document.createElement('canvas');
                canvas.width = 800;
                canvas.height = 600;

                const ctx = canvas.getContext('2d');

                // Fond gris clair
                ctx.fillStyle = '#f5f5f5';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Bordure
                ctx.strokeStyle = '#ddd';
                ctx.lineWidth = 2;
                ctx.strokeRect(1, 1, canvas.width - 2, canvas.height - 2);

                // Icône et texte
                ctx.fillStyle = '#999';
                ctx.font = '48px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('📷', canvas.width / 2, canvas.height / 2 - 30);

                ctx.font = '16px Arial';
                ctx.fillText('Capture non disponible', canvas.width / 2, canvas.height / 2 + 20);

                // URL de la page
                ctx.font = '12px monospace';
                ctx.fillStyle = '#666';
                ctx.fillText(window.location.href, canvas.width / 2, canvas.height / 2 + 50);

                resolve(canvas.toDataURL('image/png'));
            });
        },

        /**
         * Capturer une zone spécifique
         * @param {HTMLElement} element - Élément à capturer
         * @param {Object} options - Options personnalisées
         * @returns {Promise<string>} Data URL de l'image
         */
        captureElement: async function(element, options = {}) {
            if (!element) {
                return Promise.reject(new Error('Élément non spécifié'));
            }

            this.state.isCapturing = true;

            try {
                this.hideWidget();

                const mergedOptions = { ...this.defaultOptions, ...options };

                const canvas = await html2canvas(element, mergedOptions);
                const dataUrl = canvas.toDataURL('image/png', 0.9);

                this.emitEvent('element-capture-success', { dataUrl, element });

                return dataUrl;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur de capture élément:', error);
                this.emitEvent('element-capture-error', { error: error.message });
                throw error;

            } finally {
                this.showWidget();
                this.state.isCapturing = false;
            }
        },

        /**
         * Obtenir les métadonnées de l'environnement
         * @returns {Object} Métadonnées
         */
        getMetadata: function() {
            const ua = navigator.userAgent;

            return {
                // Dimensions écran
                screenWidth: window.screen.width,
                screenHeight: window.screen.height,
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                scrollX: window.scrollX || window.pageXOffset,
                scrollY: window.scrollY || window.pageYOffset,
                devicePixelRatio: window.devicePixelRatio || 1,
                colorDepth: window.screen.colorDepth || null,
                orientation: window.screen.orientation?.type || null,

                // Navigateur
                browser: this.detectBrowser(ua),
                browserVersion: this.detectBrowserVersion(ua),
                os: this.detectOS(ua),
                osVersion: this.detectOSVersion(ua),
                device: this.detectDevice(),
                userAgent: ua,
                platform: navigator.platform || null,
                language: navigator.language || navigator.userLanguage || null,
                languages: navigator.languages ? navigator.languages.join(', ') : null,

                // Capacités
                cookiesEnabled: navigator.cookieEnabled,
                javaEnabled: navigator.javaEnabled ? navigator.javaEnabled() : false,
                touchSupport: this.detectTouchSupport(),
                maxTouchPoints: navigator.maxTouchPoints || 0,
                onLine: navigator.onLine,
                doNotTrack: navigator.doNotTrack || null,

                // Connexion réseau (si disponible)
                connectionType: this.getConnectionInfo(),

                // Mémoire (si disponible)
                deviceMemory: navigator.deviceMemory || null,
                hardwareConcurrency: navigator.hardwareConcurrency || null,

                // Timezone
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                timezoneOffset: new Date().getTimezoneOffset(),

                // Page
                url: window.location.href,
                title: document.title,
                referrer: document.referrer || null,
                timestamp: new Date().toISOString(),
                localTime: new Date().toLocaleString(),
            };
        },

        /**
         * Détecter la version du navigateur
         * @param {string} ua - User agent
         * @returns {string} Version du navigateur
         */
        detectBrowserVersion: function(ua) {
            let match;
            if (ua.includes('Firefox')) {
                match = ua.match(/Firefox\/(\d+\.\d+)/);
            } else if (ua.includes('Edg/')) {
                match = ua.match(/Edg\/(\d+\.\d+)/);
            } else if (ua.includes('Chrome')) {
                match = ua.match(/Chrome\/(\d+\.\d+)/);
            } else if (ua.includes('Safari') && !ua.includes('Chrome')) {
                match = ua.match(/Version\/(\d+\.\d+)/);
            } else if (ua.includes('OPR')) {
                match = ua.match(/OPR\/(\d+\.\d+)/);
            }
            return match ? match[1] : 'Inconnue';
        },

        /**
         * Détecter la version de l'OS
         * @param {string} ua - User agent
         * @returns {string} Version de l'OS
         */
        detectOSVersion: function(ua) {
            let match;
            if (ua.includes('Windows NT')) {
                match = ua.match(/Windows NT (\d+\.\d+)/);
                if (match) {
                    const versions = { '10.0': '10/11', '6.3': '8.1', '6.2': '8', '6.1': '7' };
                    return versions[match[1]] || match[1];
                }
            } else if (ua.includes('Mac OS X')) {
                match = ua.match(/Mac OS X (\d+[._]\d+[._]?\d*)/);
                return match ? match[1].replace(/_/g, '.') : 'Inconnue';
            } else if (ua.includes('Android')) {
                match = ua.match(/Android (\d+\.?\d*\.?\d*)/);
                return match ? match[1] : 'Inconnue';
            } else if (ua.includes('iPhone') || ua.includes('iPad')) {
                match = ua.match(/OS (\d+[._]\d+[._]?\d*)/);
                return match ? match[1].replace(/_/g, '.') : 'Inconnue';
            }
            return 'Inconnue';
        },

        /**
         * Détecter le support tactile
         * @returns {Object} Infos tactile
         */
        detectTouchSupport: function() {
            return {
                touchEvents: 'ontouchstart' in window,
                touchPoints: navigator.maxTouchPoints > 0,
                pointerEvents: 'PointerEvent' in window,
            };
        },

        /**
         * Obtenir les infos de connexion
         * @returns {Object|null} Infos connexion
         */
        getConnectionInfo: function() {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (!connection) return null;

            return {
                effectiveType: connection.effectiveType || null,
                downlink: connection.downlink || null,
                rtt: connection.rtt || null,
                saveData: connection.saveData || false,
            };
        },

        /**
         * Détecter le navigateur
         * @param {string} ua - User agent
         * @returns {string} Nom du navigateur
         */
        detectBrowser: function(ua) {
            if (ua.includes('Firefox')) {
                const match = ua.match(/Firefox\/(\d+)/);
                return 'Firefox ' + (match ? match[1] : '');
            }
            if (ua.includes('Edg/')) {
                const match = ua.match(/Edg\/(\d+)/);
                return 'Edge ' + (match ? match[1] : '');
            }
            if (ua.includes('Chrome')) {
                const match = ua.match(/Chrome\/(\d+)/);
                return 'Chrome ' + (match ? match[1] : '');
            }
            if (ua.includes('Safari') && !ua.includes('Chrome')) {
                const match = ua.match(/Version\/(\d+)/);
                return 'Safari ' + (match ? match[1] : '');
            }
            if (ua.includes('Opera') || ua.includes('OPR')) {
                return 'Opera';
            }
            return 'Inconnu';
        },

        /**
         * Détecter le système d'exploitation
         * @param {string} ua - User agent
         * @returns {string} Nom de l'OS
         */
        detectOS: function(ua) {
            if (ua.includes('Windows NT 10')) return 'Windows 10/11';
            if (ua.includes('Windows NT 6.3')) return 'Windows 8.1';
            if (ua.includes('Windows NT 6.2')) return 'Windows 8';
            if (ua.includes('Windows NT 6.1')) return 'Windows 7';
            if (ua.includes('Windows')) return 'Windows';
            if (ua.includes('Mac OS X')) {
                const match = ua.match(/Mac OS X (\d+[._]\d+)/);
                return 'macOS ' + (match ? match[1].replace('_', '.') : '');
            }
            if (ua.includes('Android')) {
                const match = ua.match(/Android (\d+\.?\d*)/);
                return 'Android ' + (match ? match[1] : '');
            }
            if (ua.includes('iOS') || ua.includes('iPhone') || ua.includes('iPad')) {
                const match = ua.match(/OS (\d+[._]\d+)/);
                return 'iOS ' + (match ? match[1].replace('_', '.') : '');
            }
            if (ua.includes('Linux')) return 'Linux';
            return 'Inconnu';
        },

        /**
         * Détecter le type d'appareil
         * @returns {string} Type d'appareil
         */
        detectDevice: function() {
            const width = window.innerWidth;

            // Vérifier si c'est un appareil tactile
            const isTouch = 'ontouchstart' in window ||
                           navigator.maxTouchPoints > 0 ||
                           navigator.msMaxTouchPoints > 0;

            if (width <= 480) {
                return 'Mobile';
            }
            if (width <= 1024) {
                return isTouch ? 'Tablet' : 'Desktop';
            }
            return 'Desktop';
        },

        /**
         * Masquer le widget de feedback
         * @returns {void}
         */
        hideWidget: function() {
            const container = document.getElementById('wpvfh-container');
            if (container) {
                container.style.visibility = 'hidden';
            }
        },

        /**
         * Afficher le widget de feedback
         * @returns {void}
         */
        showWidget: function() {
            const container = document.getElementById('wpvfh-container');
            if (container) {
                container.style.visibility = 'visible';
            }
        },

        /**
         * Gérer la demande de capture
         * @param {CustomEvent} event - Événement
         * @returns {void}
         */
        handleCaptureRequest: async function(event) {
            const { callback, options } = event.detail || {};

            try {
                const dataUrl = await this.capture(options);
                const metadata = this.getMetadata();

                if (typeof callback === 'function') {
                    callback(null, { dataUrl, metadata });
                }
            } catch (error) {
                if (typeof callback === 'function') {
                    callback(error, null);
                }
            }
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

        /**
         * Obtenir la dernière capture
         * @returns {Object|null} Données de la dernière capture
         */
        getLastCapture: function() {
            if (!this.state.lastCapture) {
                return null;
            }

            return {
                dataUrl: this.state.lastCapture,
                capturedAt: this.state.lastCaptureTime,
            };
        },

        /**
         * Effacer la dernière capture
         * @returns {void}
         */
        clearLastCapture: function() {
            this.state.lastCapture = null;
            this.state.lastCaptureTime = null;
        },

        /**
         * Vérifier si les captures sont disponibles
         * @returns {boolean}
         */
        isAvailable: function() {
            return typeof html2canvas !== 'undefined';
        },

        /**
         * Capturer la page complète avec défilement
         * @param {Object} options - Options personnalisées
         * @returns {Promise<string>} Data URL de l'image complète
         */
        captureFullPage: async function(options = {}) {
            // Empêcher les captures simultanées
            if (this.state.isCapturing) {
                return Promise.reject(new Error('Capture déjà en cours'));
            }

            this.state.isCapturing = true;

            // Configuration
            const config = {
                scrollDelay: options.scrollDelay || 300,      // Délai après chaque scroll (ms)
                overlap: options.overlap || 50,                // Chevauchement entre captures (px)
                maxHeight: options.maxHeight || 30000,         // Hauteur max pour éviter les problèmes mémoire
                quality: options.quality || 0.92,
                ...options
            };

            // Sauvegarder l'état initial
            const originalScrollX = window.scrollX || window.pageXOffset;
            const originalScrollY = window.scrollY || window.pageYOffset;
            const originalOverflow = document.body.style.overflow;
            const originalHtmlOverflow = document.documentElement.style.overflow;

            try {
                // Masquer le widget
                this.hideWidget();

                // Émettre événement de début
                this.emitEvent('fullpage-capture-start', {});

                // Dimensions
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                const pageWidth = Math.max(
                    document.body.scrollWidth,
                    document.documentElement.scrollWidth,
                    document.body.offsetWidth,
                    document.documentElement.offsetWidth,
                    viewportWidth
                );
                const pageHeight = Math.min(
                    Math.max(
                        document.body.scrollHeight,
                        document.documentElement.scrollHeight,
                        document.body.offsetHeight,
                        document.documentElement.offsetHeight
                    ),
                    config.maxHeight
                );

                console.log(`[Blazing Feedback] Capture pleine page: ${pageWidth}x${pageHeight}`);

                // Calculer le nombre de captures nécessaires
                const stepHeight = viewportHeight - config.overlap;
                const numCaptures = Math.ceil(pageHeight / stepHeight);
                const captures = [];

                // Options html2canvas pour chaque capture
                const captureOptions = {
                    ...this.defaultOptions,
                    width: viewportWidth,
                    height: viewportHeight,
                    windowWidth: viewportWidth,
                    windowHeight: viewportHeight,
                    x: 0,
                    y: 0,
                    scrollX: 0,
                    scrollY: 0,
                    ignoreElements: (element) => {
                        if (element.id && element.id.startsWith('wpvfh-')) return true;
                        if (element.hasAttribute && element.hasAttribute('data-blazing-ignore')) return true;
                        // Ignorer les éléments fixed/sticky pour éviter les duplications
                        const style = window.getComputedStyle(element);
                        if (style.position === 'fixed' || style.position === 'sticky') {
                            element.setAttribute('data-blazing-was-fixed', style.position);
                            return true;
                        }
                        return false;
                    }
                };

                // Capturer chaque section
                for (let i = 0; i < numCaptures; i++) {
                    const scrollY = i * stepHeight;
                    const isLastCapture = (i === numCaptures - 1);

                    // Calculer la hauteur réelle pour cette capture
                    let captureHeight = viewportHeight;
                    if (isLastCapture) {
                        const remainingHeight = pageHeight - scrollY;
                        captureHeight = Math.min(remainingHeight, viewportHeight);
                    }

                    // Scroller à la position
                    window.scrollTo(0, scrollY);

                    // Attendre le rendu (images lazy-loaded, animations, etc.)
                    await this.waitForRender(config.scrollDelay);

                    // Émettre progression
                    this.emitEvent('fullpage-capture-progress', {
                        current: i + 1,
                        total: numCaptures,
                        percent: Math.round(((i + 1) / numCaptures) * 100)
                    });

                    // Capturer cette section
                    const canvas = await html2canvas(document.body, {
                        ...captureOptions,
                        height: captureHeight
                    });

                    captures.push({
                        canvas: canvas,
                        y: scrollY,
                        height: captureHeight
                    });

                    console.log(`[Blazing Feedback] Capture ${i + 1}/${numCaptures} à Y=${scrollY}`);
                }

                // Créer le canvas final et concaténer les captures
                const finalCanvas = document.createElement('canvas');
                finalCanvas.width = pageWidth;
                finalCanvas.height = pageHeight;
                const ctx = finalCanvas.getContext('2d');

                // Fond blanc
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);

                // Dessiner chaque capture à sa position
                for (let i = 0; i < captures.length; i++) {
                    const capture = captures[i];
                    const yPos = i * stepHeight;

                    // Pour la dernière capture, ajuster la position si nécessaire
                    if (i === captures.length - 1) {
                        const remainingSpace = pageHeight - yPos;
                        if (capture.height < viewportHeight) {
                            // Dessiner uniquement la partie nécessaire
                            ctx.drawImage(
                                capture.canvas,
                                0, 0, capture.canvas.width, capture.height,
                                0, yPos, pageWidth, capture.height
                            );
                        } else {
                            ctx.drawImage(capture.canvas, 0, yPos);
                        }
                    } else {
                        ctx.drawImage(capture.canvas, 0, yPos);
                    }
                }

                // Capturer les éléments fixed séparément et les ajouter en overlay
                await this.captureFixedElements(ctx, pageWidth, pageHeight);

                // Convertir en data URL
                const dataUrl = finalCanvas.toDataURL('image/png', config.quality);

                // Stocker la capture
                this.state.lastCapture = dataUrl;
                this.state.lastCaptureTime = Date.now();

                // Émettre succès
                this.emitEvent('fullpage-capture-success', {
                    dataUrl,
                    width: pageWidth,
                    height: pageHeight,
                    captures: numCaptures
                });

                console.log(`[Blazing Feedback] Capture pleine page terminée: ${this.formatSize(this.getSize(dataUrl))}`);

                return dataUrl;

            } catch (error) {
                console.error('[Blazing Feedback] Erreur capture pleine page:', error);
                this.emitEvent('fullpage-capture-error', { error: error.message });
                throw error;

            } finally {
                // Restaurer l'état initial
                window.scrollTo(originalScrollX, originalScrollY);
                document.body.style.overflow = originalOverflow;
                document.documentElement.style.overflow = originalHtmlOverflow;

                // Restaurer les éléments fixed
                document.querySelectorAll('[data-blazing-was-fixed]').forEach(el => {
                    el.removeAttribute('data-blazing-was-fixed');
                });

                this.showWidget();
                this.state.isCapturing = false;
            }
        },

        /**
         * Attendre le rendu de la page
         * @param {number} delay - Délai en ms
         * @returns {Promise<void>}
         */
        waitForRender: function(delay) {
            return new Promise(resolve => {
                // Utiliser requestAnimationFrame pour s'assurer que le rendu est fait
                requestAnimationFrame(() => {
                    setTimeout(resolve, delay);
                });
            });
        },

        /**
         * Capturer et ajouter les éléments fixed en overlay
         * @param {CanvasRenderingContext2D} ctx - Contexte du canvas
         * @param {number} pageWidth - Largeur de la page
         * @param {number} pageHeight - Hauteur de la page
         */
        captureFixedElements: async function(ctx, pageWidth, pageHeight) {
            // Cette fonction peut être étendue pour capturer les headers/footers fixed
            // et les dessiner en position appropriée sur l'image finale
            // Pour l'instant, on les ignore car ils créent des duplications
        },

        /**
         * Redimensionner une image base64
         * @param {string} dataUrl - Image en base64
         * @param {number} maxWidth - Largeur maximale
         * @param {number} maxHeight - Hauteur maximale
         * @returns {Promise<string>} Image redimensionnée
         */
        resize: function(dataUrl, maxWidth = 1200, maxHeight = 900) {
            return new Promise((resolve, reject) => {
                const img = new Image();

                img.onload = function() {
                    let { width, height } = img;

                    // Calculer les nouvelles dimensions
                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }
                    if (height > maxHeight) {
                        width = (width * maxHeight) / height;
                        height = maxHeight;
                    }

                    // Créer le canvas redimensionné
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    resolve(canvas.toDataURL('image/png', 0.85));
                };

                img.onerror = function() {
                    reject(new Error('Erreur de chargement de l\'image'));
                };

                img.src = dataUrl;
            });
        },

        /**
         * Calculer la taille d'une image base64 en octets
         * @param {string} dataUrl - Image en base64
         * @returns {number} Taille en octets
         */
        getSize: function(dataUrl) {
            // Retirer le préfixe data:...;base64,
            const base64 = dataUrl.split(',')[1] || '';
            // Calculer la taille (base64 = ~4/3 de la taille réelle)
            return Math.round((base64.length * 3) / 4);
        },

        /**
         * Formater la taille en unités lisibles
         * @param {number} bytes - Taille en octets
         * @returns {string} Taille formatée
         */
        formatSize: function(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        },
    };

    // Exposer le module globalement
    window.BlazingScreenshot = BlazingScreenshot;

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BlazingScreenshot.init());
    } else {
        BlazingScreenshot.init();
    }

})(window, document);
