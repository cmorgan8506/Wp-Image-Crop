<?php
if( !class_exists( 'Wpic_Admin' ) ){
	
	class Wpic_Admin{
		
		public function __construct(){
			add_action( 'admin_menu', array( $this, 'wpic_add_page' ) );
			add_action( 'admin_init', array( $this, 'wpic_register' ) );
			if($_GET['page'] == 'wpic')
				add_action('admin_print_scripts', array( $this, 'wpic_admin_scripts' ) );
			add_action('admin_print_styles', array( $this, 'wpic_admin_styles' ) );
			add_action( 'wp_ajax_nopriv_wpic_handle_domain', array( $this, 'wpic_handle_domain' ) );
			add_action( 'wp_ajax_wpic_handle_domain', array($this, 'wpic_handle_domain' ) );

		}
		
		public function wpic_add_page(){
			add_options_page( 'WP Image Crop', 'WP Image Crop', 'manage_options', 'wpic', array( $this, 'wpic_options_page' ) );
		}
		
		public function wpic_options_page(){
			?>
			<div>
			<h2>WP Image Crop</h2>
			The follow settings will help you configure Wp Image Crop to work more effectively.
			<form action="options.php" method="post">
			<?php settings_fields('wpic_options'); ?>
			<?php do_settings_sections('wpic'); ?>

			<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
			</form></div>
			<?php
		}
		
		
		public function wpic_register(){
			register_setting( 'wpic_options', 'wpic_options', array( $this, 'wpic_options_validate' ) );
			add_settings_section( 'wpic_main', 'Main Settings', array( $this, 'wpic_section_text' ), 'wpic' );
			add_settings_field( 'wpic_cdn_string', 'Add your cdn url', array( $this, 'wpic_setting_cdn' ), 'wpic', 'wpic_main' );
			add_settings_field( 'wpic_default_img', 'Current Default Image', array( $this, 'wpic_setting_default_img' ), 'wpic', 'wpic_main' );
			add_settings_field( 'wpic_default', 'Add/Change default image url', array( $this, 'wpic_setting_default' ), 'wpic', 'wpic_main' );
			add_settings_field( 'wpic_domain', 'Add/Remove trusted domains', array( $this, 'wpic_setting_domains' ), 'wpic', 'wpic_main' );
			
		}
		
		public function wpic_options_validate($input){
			$options = get_option('wpic_options');
			$options['cdn_url'] = $input['cdn_url'];
			$options['default_image'] = trim($input['default_image']);
			if($input['trusted_domains'] != '')
				$options['trusted_domains'] = $input['trusted_domains'];

			
			if(!preg_match("#((http)://)#ie", $options['cdn_url'])) {
				$options['cdn_url'] = '';
			}
			
			if(!preg_match("#^.+(.jpg|.JPG|.gif|.GIF|.png|.PNG|.jpeg|.JPEG)$#", $options['default_image'])) {
				$options['default_img'] = '';
			}
			return $options;
		}
		
		public function wpic_section_text(){
			echo "Use this page to configue your preferences";
		}
		
		public function wpic_setting_cdn(){
			$options = get_option('wpic_options');

			echo "<input id='wpic_cdn_string' name='wpic_options[cdn_url]' 
			size='40' type='text' value='{$options['cdn_url']}' />";
		}
		
		public function wpic_setting_default(){
			$options = get_option('wpic_options');
			echo "<input id='wpic_default' type='text' size='27' name='wpic_options[default_image]' 
				 value='{$options['default_image']}' />
			      <input id='upload_image_button' type='button' value='Upload Image' />";
		}
		
		public function wpic_setting_default_img(){
			$options = get_option('wpic_options');
			echo "<img style='max-width:200px;' src='{$options['default_image']}' />";
		}
		
		public function wpic_setting_domains(){
			$options = get_option('wpic_options');
			$domains = $options['trusted_domains'];

			echo "<input id='wpic_domains' type='text' size='27' value='' />";
			echo "<input class='wpic_domains_button' value='add' type='button' />";
		    
		    if($domains != NULL){
				echo "<ul id='domains'>";
				foreach($domains as $domain){
					if($domain != ' ')
						echo "<li>" . $domain . " <input type='button' class='wpic_domains_button'value='remove' /></li>";
				}
				echo "</ul>";
			}
		}
		
		public function wpic_handle_domain(){
			$domain = $_POST['domain'];
		
			if($domain != ''){
				$options = get_option('wpic_options');
				$options['trusted_domains'] = (array)$options['trusted_domains'];
				$options['trusted_domains'] = array_values($options['trusted_domains']);

				if($_POST['method'] == 'add'){
					if(!in_array($domain, $options['trusted_domains']))
						$options['trusted_domains'][] = $domain;
					else
						die(false);
				}else{
					foreach($options['trusted_domains'] as $key => $value){
					if(trim($value) == trim($domain))
						$index = $key;
					}
					unset($options['trusted_domains'][$index]);
				}
				
				if(update_option('wpic_options', $options) == true) die(true);
				else die(false);
			}
			die(false);
		}
		
		function wpic_admin_scripts() {
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_register_script('wpic-upload', WP_PLUGIN_URL.'/wp-image-crop/wpic-script.js', array('jquery','media-upload','thickbox'));
			wp_enqueue_script('wpic-upload');
		}

		function wpic_admin_styles() {
			wp_enqueue_style('thickbox');
		}
		
	} // class ends
	
	$admin = new Wpic_Admin;	
}


