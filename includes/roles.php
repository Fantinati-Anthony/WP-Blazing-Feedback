<?php
/**
 * Gestion des rôles personnalisés du plugin
 *
 * Crée et gère les rôles : feedback_client, feedback_member, feedback_admin
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.0.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe de gestion des rôles
 *
 * @since 1.0.0
 */
class WPVFH_Roles {

    /**
     * Définition des rôles et leurs capacités
     *
     * @since 1.0.0
     * @var array
     */
    private static $roles = array(
        'feedback_client' => array(
            'display_name' => 'Feedback Client',
            'capabilities' => array(
                // Lecture de base WordPress
                'read'                       => true,

                // Capacités feedback CPT - Client (ses propres feedbacks)
                'edit_feedback'              => true,
                'read_feedback'              => true,
                'delete_feedback'            => true,
                'edit_feedbacks'             => true,
                'publish_feedbacks'          => true,

                // Pas d'accès aux feedbacks des autres
                'edit_others_feedbacks'      => false,
                'delete_others_feedbacks'    => false,
                'read_private_feedbacks'     => false,
                'edit_private_feedbacks'     => false,
                'delete_private_feedbacks'   => false,

                // Capacités personnalisées
                'moderate_feedback'          => false,
                'manage_feedback'            => false,
                'export_feedback'            => false,
            ),
        ),
        'feedback_member' => array(
            'display_name' => 'Feedback Member',
            'capabilities' => array(
                // Lecture de base WordPress
                'read'                       => true,

                // Capacités feedback CPT - Member (peut voir et modifier tous)
                'edit_feedback'              => true,
                'read_feedback'              => true,
                'delete_feedback'            => true,
                'edit_feedbacks'             => true,
                'publish_feedbacks'          => true,
                'edit_others_feedbacks'      => true,
                'read_private_feedbacks'     => true,
                'edit_private_feedbacks'     => true,

                // Pas de suppression des autres
                'delete_others_feedbacks'    => false,
                'delete_private_feedbacks'   => false,

                // Capacités personnalisées
                'moderate_feedback'          => true,
                'manage_feedback'            => false,
                'export_feedback'            => false,
            ),
        ),
        'feedback_admin' => array(
            'display_name' => 'Feedback Admin',
            'capabilities' => array(
                // Lecture de base WordPress
                'read'                       => true,

                // Toutes les capacités feedback CPT
                'edit_feedback'              => true,
                'read_feedback'              => true,
                'delete_feedback'            => true,
                'edit_feedbacks'             => true,
                'publish_feedbacks'          => true,
                'edit_others_feedbacks'      => true,
                'delete_others_feedbacks'    => true,
                'read_private_feedbacks'     => true,
                'edit_private_feedbacks'     => true,
                'delete_private_feedbacks'   => true,
                'edit_published_feedbacks'   => true,
                'delete_published_feedbacks' => true,

                // Capacités personnalisées
                'moderate_feedback'          => true,
                'manage_feedback'            => true,
                'export_feedback'            => true,
            ),
        ),
    );

    /**
     * Initialiser la gestion des rôles
     *
     * @since 1.0.0
     * @return void
     */
    public static function init() {
        // Ajouter les capacités aux rôles WordPress existants
        add_action( 'admin_init', array( __CLASS__, 'add_caps_to_admin' ) );
    }

    /**
     * Créer les rôles personnalisés
     *
     * Appelé lors de l'activation du plugin
     *
     * @since 1.0.0
     * @return void
     */
    public static function create_roles() {
        /**
         * Filtre les rôles à créer
         *
         * @since 1.0.0
         * @param array $roles Définition des rôles
         */
        $roles = apply_filters( 'wpvfh_roles', self::$roles );

        foreach ( $roles as $role_slug => $role_data ) {
            // Vérifier si le rôle existe déjà
            $existing_role = get_role( $role_slug );

            if ( $existing_role ) {
                // Mettre à jour les capacités si le rôle existe
                foreach ( $role_data['capabilities'] as $cap => $grant ) {
                    if ( $grant ) {
                        $existing_role->add_cap( $cap );
                    } else {
                        $existing_role->remove_cap( $cap );
                    }
                }
            } else {
                // Créer le nouveau rôle
                add_role(
                    $role_slug,
                    __( $role_data['display_name'], 'blazing-feedback' ),
                    $role_data['capabilities']
                );
            }
        }

        // Ajouter les capacités à l'administrateur
        self::add_caps_to_admin();
    }

    /**
     * Ajouter toutes les capacités feedback à l'administrateur
     *
     * @since 1.0.0
     * @return void
     */
    public static function add_caps_to_admin() {
        $admin_role = get_role( 'administrator' );

        if ( ! $admin_role ) {
            return;
        }

        // Ajouter toutes les capacités du plugin (CPT standard + personnalisées)
        $all_caps = array(
            // Capacités CPT WordPress standard
            'edit_feedback',
            'read_feedback',
            'delete_feedback',
            'edit_feedbacks',
            'edit_others_feedbacks',
            'publish_feedbacks',
            'read_private_feedbacks',
            'delete_feedbacks',
            'delete_private_feedbacks',
            'delete_published_feedbacks',
            'delete_others_feedbacks',
            'edit_private_feedbacks',
            'edit_published_feedbacks',
            // Capacités personnalisées
            'moderate_feedback',
            'manage_feedback',
            'export_feedback',
        );

        foreach ( $all_caps as $cap ) {
            $admin_role->add_cap( $cap );
        }

        // Ajouter également à l'éditeur les capacités de membre
        $editor_role = get_role( 'editor' );
        if ( $editor_role ) {
            $editor_caps = array(
                'edit_feedback',
                'read_feedback',
                'delete_feedback',
                'edit_feedbacks',
                'publish_feedbacks',
                'edit_others_feedbacks',
                'read_private_feedbacks',
                'edit_private_feedbacks',
                'moderate_feedback',
            );

            foreach ( $editor_caps as $cap ) {
                $editor_role->add_cap( $cap );
            }
        }

        // Ajouter à l'auteur les capacités de client
        $author_role = get_role( 'author' );
        if ( $author_role ) {
            $author_caps = array(
                'edit_feedback',
                'read_feedback',
                'delete_feedback',
                'edit_feedbacks',
                'publish_feedbacks',
            );

            foreach ( $author_caps as $cap ) {
                $author_role->add_cap( $cap );
            }
        }

        // Ajouter au contributeur les capacités de base
        $contributor_role = get_role( 'contributor' );
        if ( $contributor_role ) {
            $contributor_caps = array(
                'edit_feedbacks',
                'read_feedback',
            );

            foreach ( $contributor_caps as $cap ) {
                $contributor_role->add_cap( $cap );
            }
        }
    }

    /**
     * Supprimer les rôles personnalisés
     *
     * Appelé lors de la désinstallation du plugin
     *
     * @since 1.0.0
     * @return void
     */
    public static function remove_roles() {
        // Supprimer les rôles personnalisés
        foreach ( array_keys( self::$roles ) as $role_slug ) {
            remove_role( $role_slug );
        }

        // Retirer les capacités des rôles WordPress
        self::remove_caps_from_roles();
    }

    /**
     * Retirer les capacités des rôles WordPress existants
     *
     * @since 1.0.0
     * @return void
     */
    public static function remove_caps_from_roles() {
        $all_caps = array(
            // Capacités CPT WordPress standard
            'edit_feedback',
            'read_feedback',
            'delete_feedback',
            'edit_feedbacks',
            'edit_others_feedbacks',
            'publish_feedbacks',
            'read_private_feedbacks',
            'delete_feedbacks',
            'delete_private_feedbacks',
            'delete_published_feedbacks',
            'delete_others_feedbacks',
            'edit_private_feedbacks',
            'edit_published_feedbacks',
            // Capacités personnalisées
            'moderate_feedback',
            'manage_feedback',
            'export_feedback',
        );

        $wp_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

        foreach ( $wp_roles as $role_name ) {
            $role = get_role( $role_name );
            if ( $role ) {
                foreach ( $all_caps as $cap ) {
                    $role->remove_cap( $cap );
                }
            }
        }
    }

    /**
     * Obtenir la liste des rôles du plugin
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_roles() {
        return self::$roles;
    }

    /**
     * Vérifier si un utilisateur a un rôle de feedback
     *
     * @since 1.0.0
     * @param int|WP_User $user Utilisateur ou ID utilisateur
     * @return bool
     */
    public static function has_feedback_role( $user ) {
        if ( is_int( $user ) ) {
            $user = get_user_by( 'id', $user );
        }

        if ( ! $user instanceof WP_User ) {
            return false;
        }

        $feedback_roles = array_keys( self::$roles );
        return ! empty( array_intersect( $user->roles, $feedback_roles ) );
    }

    /**
     * Obtenir le niveau de rôle feedback d'un utilisateur
     *
     * @since 1.0.0
     * @param int|WP_User $user Utilisateur ou ID utilisateur
     * @return string|false Niveau de rôle (client, member, admin) ou false
     */
    public static function get_feedback_level( $user ) {
        if ( is_int( $user ) ) {
            $user = get_user_by( 'id', $user );
        }

        if ( ! $user instanceof WP_User ) {
            return false;
        }

        // Vérifier d'abord les capacités (pour les rôles WP standard)
        if ( user_can( $user, 'manage_feedback' ) ) {
            return 'admin';
        }

        if ( user_can( $user, 'moderate_feedback' ) ) {
            return 'member';
        }

        if ( user_can( $user, 'create_feedback' ) ) {
            return 'client';
        }

        return false;
    }

    /**
     * Attribuer un rôle feedback à un utilisateur
     *
     * @since 1.0.0
     * @param int    $user_id ID de l'utilisateur
     * @param string $role    Rôle à attribuer (feedback_client, feedback_member, feedback_admin)
     * @return bool
     */
    public static function assign_role( $user_id, $role ) {
        $user = get_user_by( 'id', $user_id );

        if ( ! $user ) {
            return false;
        }

        if ( ! array_key_exists( $role, self::$roles ) ) {
            return false;
        }

        // Retirer les anciens rôles feedback
        foreach ( array_keys( self::$roles ) as $feedback_role ) {
            $user->remove_role( $feedback_role );
        }

        // Ajouter le nouveau rôle
        $user->add_role( $role );

        /**
         * Action déclenchée après l'attribution d'un rôle feedback
         *
         * @since 1.0.0
         * @param int    $user_id ID de l'utilisateur
         * @param string $role    Rôle attribué
         */
        do_action( 'wpvfh_role_assigned', $user_id, $role );

        return true;
    }

    /**
     * Retirer tous les rôles feedback d'un utilisateur
     *
     * @since 1.0.0
     * @param int $user_id ID de l'utilisateur
     * @return bool
     */
    public static function remove_all_roles( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        if ( ! $user ) {
            return false;
        }

        foreach ( array_keys( self::$roles ) as $feedback_role ) {
            $user->remove_role( $feedback_role );
        }

        /**
         * Action déclenchée après le retrait des rôles feedback
         *
         * @since 1.0.0
         * @param int $user_id ID de l'utilisateur
         */
        do_action( 'wpvfh_roles_removed', $user_id );

        return true;
    }
}

// Initialiser la gestion des rôles
WPVFH_Roles::init();
