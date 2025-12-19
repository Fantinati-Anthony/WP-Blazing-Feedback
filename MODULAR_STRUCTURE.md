# Structure Modulaire - Blazing Feedback

## Vue d'ensemble

Le fichier `feedback-widget.js` a été scindé en modules pour améliorer la maintenabilité et réduire la complexité.

### Statistiques
- **Ancien fichier** : 4268 lignes (~46000 tokens)
- **Nouveau fichier principal** : 616 lignes (~3000 tokens) ✅
- **Module core.js** : 307 lignes ✅
- **Réduction** : ~86% du code déplacé vers les modules

## Architecture

### Fichier Principal (`feedback-widget.js`)
Le fichier principal est maintenant un **orchestrateur léger** qui :
- Crée le namespace global `window.FeedbackWidget.modules`
- Définit l'état et la configuration
- Charge tous les modules dans l'ordre
- Délègue toutes les fonctionnalités aux modules via des méthodes proxy

### Modules

Tous les modules sont dans `/assets/js/modules/` et exportent leurs fonctions via :
```javascript
window.FeedbackWidget.modules.moduleName = ModuleObject;
```

#### Modules Créés

1. **core.js** ✅ (307 lignes)
   - `init(widget)` - Initialisation
   - `cacheElements(widget)` - Cache des éléments DOM
   - `applyThemeColors(widget)` - Application du thème
   - `moveFixedElementsToBody(widget)` - Déplacement des éléments fixed
   - `emitEvent(name, detail)` - Émission d'événements personnalisés

#### Modules À Compléter

Les modules suivants existent mais doivent être mis à jour avec le code extrait :

2. **events.js** - Gestionnaires d'événements
   - `init(widget)` - Attacher tous les event listeners
   - Tous les `handle*` methods (handleToggle, handleAddClick, etc.)
   - Événements pour pins, screenshot, sélection, etc.

3. **panel.js** - Gestion du panneau latéral
   - `open(widget, tab)` - Ouvrir le panel
   - `close(widget)` - Fermer le panel
   - `switchTab(widget, tabName)` - Changer d'onglet

4. **form.js** - Formulaire de feedback
   - `submit(widget, event)` - Soumission du formulaire
   - `reset(widget)` - Réinitialisation
   - `setSubmitState(widget, isLoading)` - État du bouton

5. **api.js** - Requêtes API REST
   - `request(widget, method, endpoint, data)` - Requête générique
   - `loadExistingFeedbacks(widget)` - Charger les feedbacks
   - `updateStatus(widget, feedbackId, status)` - Mettre à jour le statut
   - `updateMeta(widget, feedbackId, key, value)` - Mettre à jour les metas
   - `updatePriority(widget, feedbackId, priority)` - Mettre à jour la priorité
   - `addReply(widget, feedbackId, content)` - Ajouter une réponse
   - `loadAllPages(widget)` - Charger toutes les pages
   - `renderPriorityLists(widget)` - Rendre les listes de priorité
   - `renderMetadataLists(widget)` - Rendre les listes de métadonnées

6. **list.js** - Liste des feedbacks
   - `render(widget)` - Afficher la liste
   - `initDragAndDrop(widget)` - Initialiser le drag & drop
   - `updateOrder(widget)` - Mettre à jour l'ordre

7. **details.js** - Détails d'un feedback
   - `show(widget, feedback)` - Afficher les détails
   - `updateLabels(widget, feedback)` - Mettre à jour les labels

8. **filters.js** - Filtrage des feedbacks
   - `handleClick(widget, status)` - Clic sur un filtre
   - `getFiltered(widget)` - Obtenir les feedbacks filtrés
   - `updateCounts(widget)` - Mettre à jour les compteurs

9. **labels.js** - Gestion des labels (type, priorité, statut)
   - `getStatusLabel(widget, statusId)` - Label du statut
   - `getStatusEmoji(widget, statusId)` - Emoji du statut
   - `getStatusColor(widget, statusId)` - Couleur du statut
   - `getTypeConfig(widget, typeId)` - Config du type
   - `getPriorityConfig(widget, priorityId)` - Config de la priorité
   - `generateHtml(widget, feedback)` - Générer le HTML des labels

10. **screenshot.js** - Capture d'écran
    - `capture(widget)` - Capturer l'écran
    - `clear(widget)` - Effacer le screenshot
    - Gestion voix/vidéo

11. **tags.js** - Gestion des tags
    - `add(widget, tag)` - Ajouter un tag (vue détails)
    - `remove(widget, tag)` - Retirer un tag (vue détails)
    - `addFormTag(widget, tag, color)` - Ajouter au formulaire
    - `removeFormTag(widget, tag)` - Retirer du formulaire
    - `getPredefinedColor(widget, tagName)` - Couleur prédéfinie

12. **notifications.js** - Notifications toast
    - `show(widget, message, type)` - Afficher une notification

13. **search.js** - Recherche de feedbacks
    - `openModal(widget)` - Ouvrir la modal de recherche
    - `closeModal(widget)` - Fermer la modal
    - `perform(widget)` - Effectuer la recherche
    - `checkOpenFeedbackParam(widget)` - Vérifier paramètre URL

14. **validation.js** - Validation de page
    - `updateSection(widget)` - Mettre à jour la section
    - `showModal(widget)` - Afficher la modal
    - `confirm(widget)` - Confirmer la validation

15. **attachments.js** - Pièces jointes
    - `handleSelect(widget, files)` - Sélection de fichiers
    - `renderPreview(widget)` - Afficher l'aperçu

16. **mentions.js** - Mentions @user
    - `handleInput(widget, event)` - Input @
    - `handleKeydown(widget, event)` - Navigation au clavier
    - `insert(widget, username)` - Insérer une mention

17. **participants.js** - Participants/invitations
    - Gestion des participants

18. **selection.js** - Sélection d'éléments DOM
    - `handleSelect(widget, event)` - Sélectionner un élément
    - `handleClear(widget, event)` - Effacer la sélection

19. **tools.js** - Utilitaires DOM
    - `escapeHtml(text)` - Échapper le HTML
    - `shortenUrl(url)` - Raccourcir une URL
    - `extractPageTitle(url)` - Extraire le titre

20. **pins.js** - Déjà externe (window.BlazingAnnotation)

## Comment Compléter les Modules

### Processus pour chaque module :

1. **Extraire le code** de l'ancien fichier (disponible dans git) :
   ```bash
   git show HEAD:assets/js/feedback-widget.js > /tmp/old-widget.js
   ```

2. **Identifier les fonctions** à extraire (voir la liste ci-dessus)

3. **Créer la structure du module** :
   ```javascript
   (function(window, document) {
       'use strict';

       const ModuleName = {
           init: function(widget) {
               // Initialisation si nécessaire
           },

           methodName: function(widget, ...params) {
               // Code extrait avec widget. au lieu de this.
           },
       };

       if (!window.FeedbackWidget) {
           window.FeedbackWidget = { modules: {} };
       }
       window.FeedbackWidget.modules.moduleName = ModuleName;
   })(window, document);
   ```

4. **Adapter le code** :
   - Remplacer `this.` par `widget.`
   - Utiliser les modules via `window.FeedbackWidget.modules.*`
   - Exemple : `this.showNotification(...)` devient `widget.showNotification(...)`

5. **Tester** que le module s'exporte correctement

## Ordre de Chargement

Les modules sont chargés dans cet ordre (défini dans `feedback-widget.js::loadModules()`) :

1. `core.init()` - Cache des éléments, thème
2. `events.init()` - Attache les événements
3. `api.loadExistingFeedbacks()` - Charge les feedbacks
4. `search.checkOpenFeedbackParam()` - Vérifie l'URL

## Vérification

Pour vérifier que les modules sont bien chargés :
```javascript
console.log(Object.keys(window.FeedbackWidget.modules));
// Devrait afficher : ['core', 'events', 'panel', 'api', ...]
```

## Bénéfices

- **Maintenabilité** : Code organisé par responsabilité
- **Taille** : Chaque module < 500 lignes (objectif < 3000 tokens)
- **Testabilité** : Modules indépendants et testables
- **Clarté** : Structure claire et documentée
- **Performance** : Pas d'impact, chargement synchrone

## Prochaines Étapes

1. Compléter tous les modules listés ci-dessus
2. Tester l'intégration complète
3. Vérifier que toutes les fonctionnalités fonctionnent
4. Mettre à jour les tests unitaires si nécessaire
