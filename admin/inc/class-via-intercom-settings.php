<?php
/**
 * Foundation Settings Option Page for via-wpsetup
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Via_Foundation_Settings {
	private $options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	public function add_plugin_page() {
		add_options_page(
			'Foundations',
			'Foundations',
			'manage_options',
			'foundations',
			array( $this, 'create_admin_page' )
		);
	}

	public function create_admin_page() {
		$this->options = get_option( 'via_foundations' ); 
		?>
		<div class="wrap">
			<h1>Foundations Settings</h1>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'via_foundations_option_group' );
					do_settings_sections( 'foundations' );
					submit_button();
				?>
			</form>
		</div>
		<?php 
	}

	public function page_init() {
		register_setting(
			'via_foundations_option_group',
			'via_foundations',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'via_foundations_intercom_section',
			'Intercom Settings',
			null,
			'foundations'
		);

		add_settings_field(
			'intercom_secret',
			'Intercom Secret',
			array( $this, 'intercom_secret_callback' ),
			'foundations',
			'via_foundations_intercom_section'
		);

		add_settings_section(
			'via_foundations_usersnap_section',
			'Usersnap Settings',
			null,
			'foundations'
		);

		add_settings_field(
			'usersnap_enabled',
			'Usersnap Enabled',
			array( $this, 'usersnap_enabled_callback' ),
			'foundations',
			'via_foundations_usersnap_section'
		);

		add_settings_field(
			'usersnap_apikey',
			'Usersnap Space API Key',
			array( $this, 'usersnap_apikey_callback' ),
			'foundations',
			'via_foundations_usersnap_section'
		);	
	}

	public function sanitize( $input ) {
		$new_input = array();

		// Intercom
		if ( isset( $input['intercom_secret'] ) ) {
			$new_input['intercom_secret'] = sanitize_text_field( $input['intercom_secret'] );
		}
		// Usersnap enabled (checkbox: not present when unchecked)
		$new_input['usersnap_enabled'] = isset( $input['usersnap_enabled'] ) ? 1 : 0;

		// Usersnap API key
		if ( isset( $input['usersnap_apikey'] ) ) {
			$new_input['usersnap_apikey'] = sanitize_text_field( $input['usersnap_apikey'] );
		}
		return $new_input;
	}

	public function intercom_secret_callback() {
		printf(
			'<input class="regular-text" type="password" name="via_foundations[intercom_secret]" id="intercom_secret" value="%s" autocomplete="new-password">',
			isset( $this->options['intercom_secret'] ) ? esc_attr( $this->options['intercom_secret'] ) : ''
		);
	}

	public function usersnap_enabled_callback() {
		$checked = isset( $this->options['usersnap_enabled'] ) && $this->options['usersnap_enabled'] ? 'checked' : '';
		printf(
			'<input type="checkbox" name="via_foundations[usersnap_enabled]" id="usersnap_enabled" value="1" %s>',
			$checked
		);
	}

	public function usersnap_apikey_callback() {
		printf(
			'<input class="regular-text" type="text" name="via_foundations[usersnap_apikey]" id="usersnap_apikey" value="%s">',
			isset( $this->options['usersnap_apikey'] ) ? esc_attr( $this->options['usersnap_apikey'] ) : ''
		);
	}

	public static function output_intercom_script() {
        $current_user = wp_get_current_user();
        $options = get_option( 'via_foundations' );
        $intercom_secret = isset($options['intercom_secret']) ? $options['intercom_secret'] : '';
        $user_hash = hash_hmac( 'sha256', $current_user->data->ID, $intercom_secret );
		if ( empty( $intercom_secret ) ) {
			return; // Do not output script if secret is not set
		}
        ?>
        <script>
            window.intercomSettings = {
                api_base: "https://api-iam.intercom.io",
                app_id: "apevxw0z",
                user_id: <?php echo json_encode($current_user->data->ID); ?>,
                name: <?php echo json_encode($current_user->data->display_name); ?>,
                email: <?php echo json_encode($current_user->data->user_email); ?>,
                created_at: <?php echo strtotime($current_user->data->user_registered); ?>,
                user_hash: <?php echo json_encode($user_hash); ?>
            };
        </script>
        <script>
            (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic("reattach_activator");ic("update",w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement("script");s.type="text/javascript";s.async=true;s.src="https://widget.intercom.io/widget/apevxw0z";var x=d.getElementsByTagName("script")[0];x.parentNode.insertBefore(s,x);};if(document.readyState==="complete"){l();}else if(w.attachEvent){w.attachEvent("onload",l);}else{w.addEventListener("load",l,false);}}})();
        </script>
        <?php
    }
}

// Only load the settings page in WP admin
if ( is_admin() ) {
	new Via_Foundation_Settings();
}