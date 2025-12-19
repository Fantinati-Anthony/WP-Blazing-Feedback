# Blazing Feedback - Modules JavaScript

## Architecture Modulaire

Le widget Blazing Feedback est désormais organisé en **19 modules JavaScript fonctionnels** et un **orchestrateur léger** (229 lignes).

### Fichiers

- **feedback-widget.js** (229 lignes) : Orchestrateur principal qui charge et initialise tous les modules
- **modules/** (19 fichiers, 2556 lignes) : Modules fonctionnels indépendants

### Liste des Modules

| Module | Lignes | Description |
|--------|--------|-------------|
| **tools.js** | 119 | Utilitaires DOM (escapeHtml, formatFileSize, etc.) |
| **notifications.js** | 48 | Affichage des notifications toast |
| **core.js** | 229 | Configuration, état, cache DOM, thème |
| **api.js** | 120 | Requêtes REST API WordPress |
| **labels.js** | 171 | Gestion des labels (type, statut, priorité) |
| **tags.js** | 186 | Gestion des tags (création, suppression) |
| **filters.js** | 66 | Filtrage des feedbacks par statut |
| **screenshot.js** | 77 | Capture d'écran |
| **media.js** | 144 | Enregistrement audio/vidéo |
| **attachments.js** | 85 | Gestion des pièces jointes |
| **mentions.js** | 131 | Mentions @utilisateur |
| **validation.js** | 103 | Validation de page |
| **form.js** | 189 | Formulaire de feedback (soumission, validation) |
| **list.js** | 75 | Affichage de la liste des feedbacks |
| **details.js** | 149 | Vue détaillée d'un feedback |
| **panel.js** | 148 | Gestion du panneau latéral (ouverture/fermeture) |
| **search.js** | 150 | Recherche de feedbacks |
| **events.js** | 338 | Gestion de tous les événements |
| **participants.js** | 28 | Gestion des participants (extension future) |

### Pattern de Module

Chaque module suit ce pattern :

```javascript
(function(window) {
    'use strict';

    const ModuleName = {
        /**
         * Initialiser le module
         * @param {Object} widget - Instance BlazingFeedback
         */
        init: function(widget) {
            this.widget = widget;
        },

        // Autres méthodes du module
        methodName: function() {
            // Utiliser this.widget pour accéder au widget principal
            this.widget.state.xxx
            this.widget.elements.xxx
            this.widget.modules.xxx.method()
        }
    };

    // Export du module
    if (!window.FeedbackWidget) window.FeedbackWidget = { modules: {} };
    if (!window.FeedbackWidget.modules) window.FeedbackWidget.modules = {};
    window.FeedbackWidget.modules.moduleName = ModuleName;

})(window);
```

### Ordre d'Initialisation

Les modules sont initialisés dans cet ordre par l'orchestrateur :

1. **tools** - Utilisé par tous les modules
2. **notifications** - Utilisé par tous les modules
3. **core** - Configuration, état, éléments DOM
4. **api** - Requêtes serveur
5. **labels** - Utilisé par list, details
6. **tags** - Utilisé par form, details
7. **filters** - Utilisé par list
8. **screenshot, media, attachments, mentions, validation** - Fonctionnalités
9. **form, list, details, panel, search** - Vues principales
10. **events** - Attache tous les listeners
11. **participants** - Extensions futures

### Communication entre Modules

Les modules communiquent via :

1. **`this.widget.state`** : État partagé
2. **`this.widget.elements`** : Éléments DOM
3. **`this.widget.modules.xxx.method()`** : Appels inter-modules
4. **`this.widget.modules.tools.emitEvent()`** : Événements custom

### Exemple d'Utilisation

```javascript
// Dans un module
this.widget.modules.notifications.show('Message', 'success');
this.widget.modules.api.request('GET', 'feedbacks/123');
this.widget.modules.panel.openPanel('details');
```

### Avantages de cette Architecture

✅ **Modularité** : Chaque module a une responsabilité unique
✅ **Maintenabilité** : Code organisé et facile à modifier
✅ **Testabilité** : Modules < 500 lignes, faciles à tester
✅ **Réutilisabilité** : Modules indépendants réutilisables
✅ **Performance** : Chargement progressif possible
✅ **Lisibilité** : Code structuré et bien documenté

### Migration depuis l'Ancien Système

L'ancien fichier monolithique (4268 lignes) a été conservé dans `feedback-widget.js.backup` pour référence.

Le nouveau système maintient la même API publique via l'orchestrateur :

```javascript
// Ces méthodes restent disponibles
window.BlazingFeedback.openPanel('list');
window.BlazingFeedback.showNotification('Message', 'success');
window.BlazingFeedback.apiRequest('GET', 'feedbacks');
```

### Statistiques

- **Ancien système** : 1 fichier de 4268 lignes
- **Nouveau système** : 20 fichiers (1 orchestrateur + 19 modules)
- **Total lignes** : 2785 lignes
- **Réduction fichier principal** : 94.6% (4268 → 229 lignes)
- **Modules les plus gros** : events.js (338), core.js (229), form.js (189)
- **Modules les plus petits** : participants.js (28), notifications.js (48)

### Développement Futur

Pour ajouter une nouvelle fonctionnalité :

1. Créer un nouveau module dans `modules/`
2. Suivre le pattern de module
3. Ajouter le module à la liste d'initialisation dans `feedback-widget.js`
4. Utiliser `this.widget.modules.xxx` pour communiquer

---

**Version** : 1.0.0
**Date** : 2025-12-19
**Architecture** : Modules JavaScript fonctionnels
