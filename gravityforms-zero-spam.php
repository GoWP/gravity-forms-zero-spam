<?php

/**
 * Plugin Name:       Gravity Forms Zero Spam
 * Plugin URI:        http://www.gowp.com/plugins/gravityforms-zero-spam
 * Description:       Enhance your Gravity Forms to include anti-spam measures originally based on the work of David Walsh's <a href="http://davidwalsh.name/wordpress-comment-spam">"Zero Spam"</a> technique.
 * Version:           1.0.3
 * Author:            GoWP
 * Author URI:        https://www.gowp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// my mother always said to use things as they're intended or not at all

	if ( ! defined( 'WPINC' ) ) {
		die;
	}

// clean up after ourselves

	register_deactivation_hook( __FILE__, array( 'GF_Zero_Spam', 'deactivate' ) );

// main plugin class

	class GF_Zero_Spam {
		public function __construct() { // instantiation (is that a word?)
			add_action( 'wp_footer', array( $this, 'add_key_field' ) ); // add key injection JS to the bottom of the page
			add_filter( 'gform_entry_is_spam', array( $this, 'check_key_field' ) ); // add our validation check to all forms
		}
		public function deactivate() { // plugin deactivation
			delete_option( 'gf_zero_spam_key' ); // remove the key
		}
		public function get_key() { // retrieve they key, generating if needed
			if ( ! $key = get_option( 'gf_zero_spam_key' ) ) {
				$key = wp_generate_password( 64, false, false );
				update_option( 'gf_zero_spam_key', $key, FALSE );
			}
			return $key;
		}
		public function add_key_field( $form ) { // inject the hidden field and key into the form at submission
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function($){
					var gforms = '.gform_wrapper form';
					$( document ).on( 'submit', gforms ,function() {
						$('<input>').attr( 'type', 'hidden' )
								.attr( 'name', 'gf_zero_spam_key' )
								.attr( 'value', '<?php echo $this->get_key(); ?>' )
								.appendTo( gforms );
						return true;
					});
				});
			</script>
			<?php
		}
		public function check_key_field( $is_spam ) { // check for the key during validation
			if ( ! isset( $_POST['gf_zero_spam_key'] ) || ( $_POST['gf_zero_spam_key'] != $this->get_key() ) ) {
				return true;
			}
			return false;
		}
	}

// Fire it up

	$gf_zero_spam = new GF_Zero_Spam;
