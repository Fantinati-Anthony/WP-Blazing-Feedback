<?php
/**
 * GitHub Updater Class
 *
 * Permet les mises à jour automatiques du plugin depuis GitHub
 *
 * @package BlazingFeedback
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPVFH_GitHub_Updater
 *
 * Gère les mises à jour du plugin depuis GitHub
 */
class WPVFH_GitHub_Updater {

    /**
     * Slug du plugin
     *
     * @var string
     */
    private $slug;

    /**
     * Données du plugin
     *
     * @var array
     */
    private $plugin_data;

    /**
     * Nom d'utilisateur GitHub
     *
     * @var string
     */
    private $username;

    /**
     * Nom du repository GitHub
     *
     * @var string
     */
    private $repo;

    /**
     * Fichier principal du plugin
     *
     * @var string
     */
    private $plugin_file;

    /**
     * Réponse GitHub mise en cache
     *
     * @var object|null
     */
    private $github_response;

    /**
     * Token d'accès GitHub (optionnel, pour repos privés)
     *
     * @var string
     */
    private $access_token;

    /**
     * Constructeur
     *
     * @param string $plugin_file Chemin vers le fichier principal du plugin
     */
    public function __construct( $plugin_file ) {
        $this->plugin_file = $plugin_file;
        $this->slug        = plugin_basename( $plugin_file );

        // Configuration GitHub - À modifier selon votre repository
        $this->username     = 'Fantinati-Anthony';
        $this->repo         = 'Blazing-Feedback-WP';
        $this->access_token = ''; // Laisser vide pour les repos publics

        add_action( 'admin_init', array( $this, 'set_plugin_data' ) );
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );

        // Permettre la configuration via des constantes
        if ( defined( 'BLAZING_FEEDBACK_GITHUB_TOKEN' ) ) {
            $this->access_token = BLAZING_FEEDBACK_GITHUB_TOKEN;
        }
    }

    /**
     * Récupérer les données du plugin
     */
    public function set_plugin_data() {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $this->plugin_data = get_plugin_data( $this->plugin_file );
    }

    /**
     * Récupérer les informations de release depuis GitHub
     *
     * @return object|false
     */
    private function get_repository_info() {
        if ( ! empty( $this->github_response ) ) {
            return $this->github_response;
        }

        // Vérifier le cache
        $cached = get_transient( 'wpvfh_github_response' );
        if ( false !== $cached ) {
            $this->github_response = $cached;
            return $cached;
        }

        // Récupérer la dernière release
        $request_uri = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->username,
            $this->repo
        );

        $args = array(
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            ),
            'timeout' => 10,
        );

        // Ajouter le token si disponible
        if ( ! empty( $this->access_token ) ) {
            $args['headers']['Authorization'] = 'token ' . $this->access_token;
        }

        $response = wp_remote_get( $request_uri, $args );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            // Essayer de récupérer depuis les tags si pas de release
            return $this->get_repository_info_from_tags();
        }

        $response_body = json_decode( wp_remote_retrieve_body( $response ) );

        if ( empty( $response_body ) ) {
            return false;
        }

        $this->github_response = $response_body;

        // Mettre en cache pour 6 heures
        set_transient( 'wpvfh_github_response', $response_body, 6 * HOUR_IN_SECONDS );

        return $response_body;
    }

    /**
     * Récupérer les informations depuis les tags (fallback)
     *
     * @return object|false
     */
    private function get_repository_info_from_tags() {
        $request_uri = sprintf(
            'https://api.github.com/repos/%s/%s/tags',
            $this->username,
            $this->repo
        );

        $args = array(
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            ),
            'timeout' => 10,
        );

        if ( ! empty( $this->access_token ) ) {
            $args['headers']['Authorization'] = 'token ' . $this->access_token;
        }

        $response = wp_remote_get( $request_uri, $args );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $tags = json_decode( wp_remote_retrieve_body( $response ) );

        if ( empty( $tags ) || ! is_array( $tags ) ) {
            return false;
        }

        // Prendre le premier tag (le plus récent)
        $latest_tag = $tags[0];

        // Créer un objet similaire à une release
        $response_body = (object) array(
            'tag_name'     => $latest_tag->name,
            'name'         => $latest_tag->name,
            'body'         => '',
            'zipball_url'  => $latest_tag->zipball_url,
            'published_at' => '',
        );

        $this->github_response = $response_body;
        set_transient( 'wpvfh_github_response', $response_body, 6 * HOUR_IN_SECONDS );

        return $response_body;
    }

    /**
     * Vérifier si une mise à jour est disponible
     *
     * @param object $transient Transient des mises à jour
     * @return object
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        // Récupérer les données du plugin si pas encore fait
        if ( empty( $this->plugin_data ) ) {
            $this->set_plugin_data();
        }

        $github_info = $this->get_repository_info();

        if ( false === $github_info ) {
            return $transient;
        }

        // Nettoyer le numéro de version
        $github_version = ltrim( $github_info->tag_name, 'v' );
        $current_version = $this->plugin_data['Version'];

        // Comparer les versions
        if ( version_compare( $github_version, $current_version, '>' ) ) {
            $plugin = array(
                'slug'        => dirname( $this->slug ),
                'plugin'      => $this->slug,
                'new_version' => $github_version,
                'url'         => sprintf( 'https://github.com/%s/%s', $this->username, $this->repo ),
                'package'     => $this->get_download_url( $github_info ),
                'icons'       => array(),
                'banners'     => array(),
                'tested'      => '',
                'requires_php' => '7.4',
            );

            $transient->response[ $this->slug ] = (object) $plugin;
        }

        return $transient;
    }

    /**
     * Obtenir l'URL de téléchargement
     *
     * @param object $github_info Informations GitHub
     * @return string
     */
    private function get_download_url( $github_info ) {
        // Vérifier s'il y a un asset ZIP dans la release
        if ( ! empty( $github_info->assets ) && is_array( $github_info->assets ) ) {
            foreach ( $github_info->assets as $asset ) {
                if ( 'application/zip' === $asset->content_type ||
                     substr( $asset->name, -4 ) === '.zip' ) {
                    $url = $asset->browser_download_url;
                    if ( ! empty( $this->access_token ) ) {
                        $url = add_query_arg( 'access_token', $this->access_token, $url );
                    }
                    return $url;
                }
            }
        }

        // Fallback vers zipball_url
        $url = $github_info->zipball_url;
        if ( ! empty( $this->access_token ) ) {
            $url = add_query_arg( 'access_token', $this->access_token, $url );
        }

        return $url;
    }

    /**
     * Afficher les informations dans la popup de mise à jour
     *
     * @param false|object|array $result Résultat par défaut
     * @param string             $action Action demandée
     * @param object             $args   Arguments
     * @return false|object
     */
    public function plugin_popup( $result, $action, $args ) {
        if ( 'plugin_information' !== $action ) {
            return $result;
        }

        if ( empty( $args->slug ) || dirname( $this->slug ) !== $args->slug ) {
            return $result;
        }

        $github_info = $this->get_repository_info();

        if ( false === $github_info ) {
            return $result;
        }

        // Récupérer les données du plugin si pas encore fait
        if ( empty( $this->plugin_data ) ) {
            $this->set_plugin_data();
        }

        $plugin_info = array(
            'name'              => $this->plugin_data['Name'],
            'slug'              => dirname( $this->slug ),
            'version'           => ltrim( $github_info->tag_name, 'v' ),
            'author'            => $this->plugin_data['AuthorName'],
            'author_profile'    => $this->plugin_data['AuthorURI'],
            'homepage'          => $this->plugin_data['PluginURI'],
            'short_description' => $this->plugin_data['Description'],
            'sections'          => array(
                'description'  => $this->plugin_data['Description'],
                'changelog'    => $this->parse_changelog( $github_info->body ),
            ),
            'download_link'     => $this->get_download_url( $github_info ),
            'last_updated'      => ! empty( $github_info->published_at )
                ? date( 'Y-m-d', strtotime( $github_info->published_at ) )
                : '',
            'requires'          => '5.8',
            'requires_php'      => '7.4',
            'tested'            => '',
        );

        return (object) $plugin_info;
    }

    /**
     * Parser le changelog depuis le corps de la release
     *
     * @param string $body Corps de la release
     * @return string
     */
    private function parse_changelog( $body ) {
        if ( empty( $body ) ) {
            return '<p>' . __( 'Voir les notes de version sur GitHub.', 'blazing-feedback' ) . '</p>';
        }

        // Convertir le Markdown en HTML basique
        $changelog = esc_html( $body );
        $changelog = nl2br( $changelog );

        // Convertir les listes
        $changelog = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $changelog );
        $changelog = preg_replace( '/(<li>.*<\/li>)/s', '<ul>$1</ul>', $changelog );

        return $changelog;
    }

    /**
     * Actions après l'installation de la mise à jour
     *
     * @param bool  $response   Réponse de l'installation
     * @param array $hook_extra Données supplémentaires
     * @param array $result     Résultat de l'installation
     * @return array
     */
    public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;

        // Vérifier que c'est notre plugin
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->slug ) {
            return $result;
        }

        // Le dossier téléchargé depuis GitHub a un nom comme "user-repo-hash"
        // On doit le renommer avec le bon nom
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname( $this->slug );

        // Déplacer le contenu vers le bon dossier
        $wp_filesystem->move( $result['destination'], $plugin_folder );
        $result['destination'] = $plugin_folder;

        // Réactiver le plugin si nécessaire
        if ( is_plugin_active( $this->slug ) ) {
            activate_plugin( $this->slug );
        }

        return $result;
    }

    /**
     * Forcer la vérification des mises à jour
     */
    public static function force_update_check() {
        delete_transient( 'wpvfh_github_response' );
        delete_site_transient( 'update_plugins' );
    }
}
