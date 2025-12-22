=== Blazing Feedback ===
Contributors: blazingfeedback
Donate link: https://github.com/your-repo/blazing-feedback
Tags: feedback, visual feedback, annotations, bug reporting, screenshot, collaboration
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin de feedback visuel autonome pour WordPress. Annotations, captures d'écran, gestion de statuts. Alternative open-source à ProjectHuddle, Feedbucket et Marker.io.

== Description ==

**Blazing Feedback** transforme la collecte de retours utilisateurs en une expérience fluide et visuelle. Vos clients et équipes peuvent placer des marqueurs directement sur vos pages, ajouter des commentaires, et capturer automatiquement le contexte technique.

= Fonctionnalités principales =

* **Feedback visuel en direct** - Bouton flottant non intrusif pour démarrer une session de feedback
* **Annotations cliquables** - Placez des pins précis sur n'importe quel élément de la page
* **Capture d'écran automatique** - Génère une capture du viewport avec html2canvas
* **Métadonnées complètes** - Navigateur, OS, résolution, position de scroll automatiquement capturés
* **Gestion de statuts** - Nouveau, En cours, Résolu, Rejeté
* **Système de réponses** - Discussions en fil sur chaque feedback
* **Dashboard admin** - Vue d'ensemble, filtres, statistiques
* **REST API complète** - Intégration avec vos outils existants
* **100% autonome** - Aucune dépendance externe, aucun SaaS

= Cas d'usage =

* Recueillir les retours clients sur un site en développement
* Faciliter la QA et le signalement de bugs
* Collaborer avec des équipes distantes
* Gérer les demandes de modifications visuelles

= Rôles et permissions =

* **Feedback Client** - Peut créer et voir ses propres feedbacks
* **Feedback Member** - Peut modérer, répondre et changer les statuts
* **Feedback Admin** - Accès complet incluant les paramètres

= Sécurité =

* Nonces sur toutes les actions
* Vérification des capacités utilisateur
* Échappement et sanitisation des données
* Aucun accès public non contrôlé

= Extensibilité =

Blazing Feedback est conçu pour être étendu :

* Hooks et filtres sur toutes les actions principales
* REST API documentée
* Templates personnalisables
* Architecture modulaire

== Installation ==

1. Téléchargez le plugin et décompressez-le dans `/wp-content/plugins/`
2. Activez le plugin via le menu 'Extensions' dans WordPress
3. Accédez à 'Feedbacks' dans le menu admin pour configurer
4. Les utilisateurs avec les bonnes permissions verront le bouton de feedback sur le site

= Installation manuelle =

1. Téléchargez le fichier .zip du plugin
2. Allez dans Extensions > Ajouter > Téléverser une extension
3. Choisissez le fichier .zip et cliquez sur 'Installer maintenant'
4. Activez le plugin

== Frequently Asked Questions ==

= Le plugin fonctionne-t-il avec mon thème ? =

Oui, Blazing Feedback est conçu pour fonctionner avec tous les thèmes WordPress. Le widget est injecté de manière non intrusive et utilise des styles encapsulés.

= Les visiteurs non connectés peuvent-ils laisser des feedbacks ? =

Par défaut, non. Seuls les utilisateurs connectés avec les bonnes capacités peuvent créer des feedbacks. Vous pouvez activer les feedbacks anonymes dans les paramètres, mais attention au spam potentiel.

= Les screenshots fonctionnent-ils avec tous les sites ? =

La capture utilise html2canvas qui a certaines limitations avec les éléments cross-origin (images, iframes externes). Un fallback génère une image placeholder en cas d'échec.

= Puis-je personnaliser l'apparence du widget ? =

Oui, via CSS personnalisé. Le plugin utilise des variables CSS que vous pouvez surcharger. Les templates sont également personnalisables.

= Le plugin a-t-il un impact sur les performances ? =

Les assets JS/CSS ne sont chargés que pour les utilisateurs autorisés. La capture d'écran n'est déclenchée qu'à la demande. L'impact est minimal.

= Comment exporter les feedbacks ? =

Les administrateurs peuvent exporter les feedbacks via le dashboard. L'export est disponible en format CSV.

= Le plugin est-il compatible avec le multisite ? =

Oui, le plugin peut être activé par site ou globalement sur un réseau multisite.

== Screenshots ==

1. Bouton flottant de feedback sur le frontend
2. Panneau de création de feedback
3. Pin placé sur la page avec capture d'écran
4. Dashboard admin avec statistiques
5. Détail d'un feedback avec réponses
6. Page de paramètres

== Changelog ==

= 2.1.0 =
* Nouvelle fonctionnalité : Capture pleine page avec défilement automatique
* Menu dropdown sur le bouton capture avec choix viewport/page entière
* Concaténation automatique des captures pour pages longues
* Notifications de progression pendant la capture
* Gestion des éléments fixed/sticky pour éviter les duplications

= 2.0.0 =
* Refonte majeure avec module Blazing Minds (CPPICAVAL™)
* Gestion de clients, portfolios et projets
* Cycle d'apprentissage ICAVAL (Information → Clarification → Action → Valeur → Apprentissage)
* Intégration IA pour génération de clarifications
* Nouveau design des cartes feedback
* Architecture modulaire JavaScript (19 modules)
* Amélioration des performances

= 1.0.0 =
* Version initiale
* Widget de feedback flottant
* Système d'annotations avec pins
* Capture d'écran avec html2canvas
* Custom Post Type pour les feedbacks
* REST API complète
* Dashboard admin
* Système de rôles et permissions
* Support des réponses/threads
* Gestion des statuts
* Notifications par email
* Compatible mobile et tablet

== Upgrade Notice ==

= 1.0.0 =
Version initiale de Blazing Feedback. Aucune mise à jour requise.

== Privacy Policy ==

Blazing Feedback ne collecte aucune donnée personnelle en dehors de celles nécessaires au fonctionnement du plugin :

* Commentaires de feedback
* Captures d'écran (stockées localement sur votre serveur)
* Métadonnées techniques (navigateur, résolution)

Aucune donnée n'est envoyée à des serveurs tiers. Tout reste sur votre installation WordPress.

== Credits ==

* [html2canvas](https://html2canvas.hertzen.com/) - Capture d'écran côté client (MIT License)
* Icônes et design inspirés des meilleures pratiques UX

== Support ==

Pour obtenir de l'aide :

* [Documentation](https://github.com/your-repo/blazing-feedback/wiki)
* [GitHub Issues](https://github.com/your-repo/blazing-feedback/issues)
* [Forum WordPress](https://wordpress.org/support/plugin/blazing-feedback/)
