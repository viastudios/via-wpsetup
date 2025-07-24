<?php

class Github_Updater {

    private $file;

    private $plugin;

    private $basename;

    private $active;

    private $username;

    private $repository;

    private $authorize_token;

    private $github_response;

    public function __construct( $file ) {

        $this->file = $file;

        add_action( 'admin_init', array( $this, 'set_plugin_properties' ) );

        return $this;
    }

    public function set_plugin_properties() {
        $this->plugin   = get_plugin_data( $this->file );
        $this->basename = plugin_basename( $this->file );
        $this->active   = is_plugin_active( $this->basename );
    }

    public function set_username( $username ) {
        $this->username = $username;
    }

    public function set_repository( $repository ) {
        $this->repository = $repository;
    }

    public function authorize( $token ) {
        $this->authorize_token = $token;
    }

    private function get_repository_info() {
        if ( is_null( $this->github_response ) ) {
            $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository );
    
            if ( $this->authorize_token ) {
                $request_uri = add_query_arg( 'access_token', $this->authorize_token, $request_uri );
            }
    
            $response = wp_remote_get( $request_uri );
            if ( is_wp_error( $response ) ) {
                $this->github_response = array(); // prevent fatal error later
                return;
            }
    
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );
    
            if ( ! is_array( $data ) || empty( $data[0] ) ) {
                $this->github_response = array(); // prevent fatal error later
                return;
            }
    
            $release = $data[0];
    
            if ( $this->authorize_token ) {
                $release['zipball_url'] = add_query_arg( 'access_token', $this->authorize_token, $release['zipball_url'] );
            }
    
            $readme_uri = sprintf( 'https://raw.githubusercontent.com/%s/%s/master/README.md', $this->username, $this->repository );
            $readme_response = wp_remote_get( $readme_uri );
    
            if ( ! is_wp_error( $readme_response ) ) {
                $release['Readme'] = wp_remote_retrieve_body( $readme_response );
            } else {
                $release['Readme'] = 'README not found.';
            }
    
            $this->github_response = $release;
        }
    }
    
    public function initialize() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
        add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3);
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
    }

    public function modify_transient( $transient ) {

        if( property_exists( $transient, 'checked') ) { // Check if transient has a checked property

            if( $checked = $transient->checked ) { // Did Wordpress check for updates?

                $this->get_repository_info(); // Get the repo info

                if (
                    isset( $this->github_response['tag_name'] ) &&
                    isset( $checked[ $this->basename ] )
                ) {
                    $out_of_date = version_compare( $this->github_response['tag_name'], $checked[ $this->basename ], 'gt' );
                
                    if ( $out_of_date ) {
                        $new_files = $this->github_response['zipball_url'];
                        $slug = current( explode('/', $this->basename ) );
                
                        $plugin = array(
                            'url' => $this->plugin["PluginURI"],
                            'slug' => $slug,
                            'package' => $new_files,
                            'new_version' => $this->github_response['tag_name']
                        );
                
                        $transient->response[$this->basename] = (object) $plugin;
                    }
                }
            }
        }

        return $transient; // Return filtered transient
    }

    public function plugin_popup( $result, $action, $args ) {

        if( ! empty( $args->slug ) ) { // If there is a slug

            if( $args->slug == current( explode( '/' , $this->basename ) ) ) { // And it's our slug

                $this->get_repository_info(); // Get our repo info

                // We're going to parse the GitHub markdown release notes, include the parser
                require_once( plugin_dir_path( __FILE__ ) . "Parsedown.php" );

                // Set it to an array
                $plugin = array(
                    'name'              => $this->plugin["Name"],
                    'slug'              => $this->basename,
                    'requires'          => '4.0',
                    'tested'            => '4.9.4',
                    'added'             => '2018-02-20',
                    'version'           => $this->github_response['tag_name'],
                    'author'            => $this->plugin["AuthorName"],
                    'author_profile'    => $this->plugin["AuthorURI"],
                    'last_updated'      => $this->github_response['published_at'],
                    'homepage'          => $this->plugin["PluginURI"],
                    'short_description' => $this->plugin["Description"],
                    'sections'          => array(
                        'Description'   => class_exists( "Parsedown" )
                                            ? Parsedown::instance()->parse( $this->github_response['Readme'] )
                                            : $this->github_response['Readme'],
                        'Updates'       => class_exists( "Parsedown" )
                                            ? '<h1>' . $this->github_response['name'] . '</h1>' . Parsedown::instance()->parse( $this->github_response['body'] )
                                            : $this->github_response['body'],
                    ),
                    'download_link'     => $this->github_response['zipball_url']
                );

                return (object) $plugin; // Return the data
            }

        }
        return $result; // Otherwise return default
    }

    public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem; // Get global FS object

        $install_directory = plugin_dir_path( $this->file ); // Our plugin directory
        $wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir
        $result['destination'] = $install_directory; // Set the destination for the rest of the stack

        if ( $this->active ) { // If it was active
            activate_plugin( $this->basename ); // Reactivate
        }

        return $result;
    }
}
