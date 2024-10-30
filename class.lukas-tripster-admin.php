<?php
class LukasTripsterSettingsPage
{
		/**
		 * Holds the values to be used in the fields callbacks
		 */
		private $options;

		/**
		 * Start up
		 */
		public function __construct()
		{
				add_action( 'admin_menu', array( $this, 'lukas_tripster_plugin_setting_page' ) );
				add_action( 'admin_init', array( $this, 'page_init' ) );
				
				add_filter( 'plugin_action_links_'.plugin_basename( LUKAS_TRIPSTER_PLUGIN_DIR . 'lukas-tripster-use-api.php'), array( $this, 'admin_plugin_settings_link' ) );
        add_filter( 'all_plugins', array( $this, 'modify_plugin_description' ) );

				$this->options = get_option( 'lukas_tripster_settings' );
		}

		/**
		 * Add options page
		 */
		public function lukas_tripster_plugin_setting_page()
		{
				// This page will be under "Settings"
				add_options_page(
						'Settings Admin',
						esc_html__('Tripster', 'lukas-tripster'),
						'manage_options',
						'lukas-tripster-setting-admin',
						array( $this, 'create_admin_page' )
			 );
		}

		public static function admin_plugin_settings_link( $links ) {

				$args = array( 'page' => 'lukas-tripster-setting-admin' );
				$url = add_query_arg( $args, class_exists( 'Jetpack' ) ? admin_url( 'admin.php' ) : admin_url( 'options-general.php' ) );
				$settings_link = '<a href="'.esc_url( $url ).'">'.esc_html__('Settings', 'lukas-tripster').'</a>';
				array_unshift( $links, $settings_link );

				return $links;
		}

		/**
		 * Options page callback
		 */
		public function create_admin_page()
		{
				// Set class property
				?>
				<div class="wrap">
						<h1><?php esc_html_e('Tripster API plugin Settings', 'lukas-tripster'); ?></h1>
						<form method="post" action="options.php">
						<?php
								// This prints out all hidden setting fields
								settings_fields( 'lukas_tripster_settings_group' );
								do_settings_sections( 'lukas-tripster-setting-admin' );
								submit_button();
						?>
						</form>
				</div>
				<?php
		}

		/**
		 * Register and add settings
		 */
		public function page_init()
		{
				
				register_setting(
						'lukas_tripster_settings_group', // Option group
						'lukas_tripster_settings', // Option name
						array( $this, 'sanitize' ) // Sanitize
			 );
				
				add_settings_section(
						'lukas_tripster_setting_color_section', // ID
						esc_html__('Color settings', 'lukas-tripster'), // Title
						array( $this, 'print_color_section_info' ), // Callback
						'lukas-tripster-setting-admin' // Page
			 );
				
				add_settings_section(
						'lukas_tripster_setting_template_section', // ID
						esc_html__('Template settings', 'lukas-tripster'), // Title
						array( $this, 'print_template_section_info' ), // Callback
						'lukas-tripster-setting-admin' // Page
			 );

				add_settings_section(
						'lukas_tripster_setting_partner_section', // ID
						esc_html__('Partner info', 'lukas-tripster'), // Title
						array( $this, 'print_partner_section_info' ), // Callback
						'lukas-tripster-setting-admin' // Page
			 );

				add_settings_field(
						'lukas_tripster_text_color', // ID
						esc_html__('Text color', 'lukas-tripster'), // Title
						array( $this, 'lukas_tripster_field_callback' ), // Callback
						'lukas-tripster-setting-admin', // Page
						'lukas_tripster_setting_color_section', // Section
						array( 
							'field_id' => 'lukas_tripster_text_color',
							'field_class' => 'color-picker-hex',
					 )
			 );
				
				add_settings_field(
						'lukas_tripster_bg_color', // ID
						esc_html__('Background color', 'lukas-tripster'), // Title
						array( $this, 'lukas_tripster_field_callback' ), // Callback
						'lukas-tripster-setting-admin', // Page
						'lukas_tripster_setting_color_section', // Section
						array( 
							'field_id' => 'lukas_tripster_bg_color',
							'field_class' => 'color-picker-hex',
					 )
			 );
				
				add_settings_field(
						'lukas_tripster_main_border_color', // ID
						esc_html__('Main border color', 'lukas-tripster'), // Title
						array( $this, 'lukas_tripster_field_callback' ), // Callback
						'lukas-tripster-setting-admin', // Page
						'lukas_tripster_setting_color_section', // Section
						array( 
							'field_id' => 'lukas_tripster_main_border_color',
							'field_class' => 'color-picker-hex',
					 )
			 );
				
				add_settings_field(
						'lukas_tripster_title_color', // ID
						esc_html__('Title color', 'lukas-tripster'), // Title
						array( $this, 'lukas_tripster_field_callback' ), // Callback
						'lukas-tripster-setting-admin', // Page
						'lukas_tripster_setting_color_section', // Section
						array( 
							'field_id' => 'lukas_tripster_title_color',
							'field_class' => 'color-picker-hex',
					 )
			 );
				
				add_settings_field(
						'lukas_tripster_link_color', // ID
						esc_html__('Links color', 'lukas-tripster'), // Title
						array( $this, 'lukas_tripster_field_callback' ), // Callback
						'lukas-tripster-setting-admin', // Page
						'lukas_tripster_setting_color_section', // Section
						array( 
							'field_id' => 'lukas_tripster_link_color',
							'field_class' => 'color-picker-hex',
					 )
			 );

				add_settings_field(
						'lukas_tripster_price_color', // ID
						esc_html__('Price color', 'lukas-tripster'), // Title
						array( $this, 'lukas_tripster_field_callback' ), // Callback
						'lukas-tripster-setting-admin', // Page
						'lukas_tripster_setting_color_section', // Section
						array( 
							'field_id' => 'lukas_tripster_price_color',
							'field_class' => 'color-picker-hex',
					 )
			 );

				add_settings_field(
						'lukas_tripster_partner_id',
						esc_html__('Partner ID', 'lukas-tripster'),
						array( $this, 'lukas_tripster_field_callback' ),
						'lukas-tripster-setting-admin',
						'lukas_tripster_setting_partner_section',
						array( 
							'field_id' => 'lukas_tripster_partner_id',
					 )
			 );
				
				add_settings_field(
						'lukas_tripster_min_width_isnumeric',
						esc_html__('Minimum block width (in px)', 'lukas-tripster'),
						array( $this, 'lukas_tripster_field_callback' ),
						'lukas-tripster-setting-admin',
						'lukas_tripster_setting_template_section',
						array( 
							'field_id' => 'lukas_tripster_min_width_isnumeric',
					 )
			 );
				
				add_settings_field(
						'lukas_tripster_font_size_isnumeric',
						esc_html__('Font size (in em)', 'lukas-tripster'),
						array( $this, 'lukas_tripster_field_callback' ),
						'lukas-tripster-setting-admin',
						'lukas_tripster_setting_template_section',
						array( 
							'field_id' => 'lukas_tripster_font_size_isnumeric',
					 )
			 );
				
				add_settings_field(
						'lukas_tripster_block_num_isnumeric',
						esc_html__('Number of block by default', 'lukas-tripster'),
						array( $this, 'lukas_tripster_field_callback' ),
						'lukas-tripster-setting-admin',
						'lukas_tripster_setting_template_section',
						array( 
							'field_id' => 'lukas_tripster_block_num_isnumeric',
					 )
			 );
				
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'lukas_tripster_js', plugins_url( 'jquery.custom.js', __FILE__ ) );
				wp_enqueue_style( 'wp-color-picker' );

		}

		/**
		 * Function that will check if value is a valid HEX color.
		 */
		public function check_color( $value ) {

				if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with #
						return true;
				}

				return false;
		}

		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys
		 */
		public function sanitize( $fields )
		{
				$valid_fields = array();

				foreach( $fields as $key => $val ) {
					
					$valid_fields[$key] = sanitize_text_field( $val );
					
					if( 'lukas_tripster_block_num_isnumeric' == $key && (3 < $val || 0 >= $val) ) {
							
							add_settings_error( 'lukas_tripster_settings', $key.'_error', 'Max columns number is 3 or more than 0', 'error' ); // $setting, $code, $message, $type

							// Get the previous valid value
							$valid_fields[$key] = $this->options[$key];
					}
					
					if( preg_match( '/_isnumeric/', $key ) ) {
					
						if( FALSE === is_numeric($val)) {
							// Set the error message
							add_settings_error( 'lukas_tripster_settings', $key.'_error', sprintf( esc_html__( 'Insert a valid numeric value for %s', 'lukas-tripster' ), $key ), 'error' ); // $setting, $code, $message, $type

							// Get the previous valid value
							$valid_fields[$key] = $this->options[$key];
						
						} else {

								$valid_fields[$key] = $val;

						}
					
					}
					
					if( preg_match( '/color/', $key ) ) {
						
						// Validate Title Color
						$val = trim( $val );
						$val = strip_tags( stripslashes( $val ) );

						// Check if is a valid hex color
						if( FALSE === $this->check_color( $val ) ) {

								// Set the error message
								add_settings_error( 'lukas_tripster_settings', $key.'_error', 'Insert a valid color for '.$key, 'error' ); // $setting, $code, $message, $type

								// Get the previous valid value
								$valid_fields[$key] = $this->options[$key];

						} else {

								$valid_fields[$key] = $val;

						}
					}
					
				}
				
				return apply_filters( 'sanitize', $valid_fields, $fields);
		}

		/**
		 * Print the Partner section text
		 */
		public function print_partner_section_info()
		{
				print esc_html__('Enter partner settings below:', 'lukas-tripster');
		}
		
		/**
		 * Print the Color section text
		 */
		public function print_color_section_info()
		{
				print esc_html__('Enter color settings below:', 'lukas-tripster');
		}

		/**
		 * Print the Template section text
		 */
		public function print_template_section_info()
		{
				print esc_html__('Enter template settings below:', 'lukas-tripster');
		}

		/**
		 * Get the settings option array and print one of its values
		 */
		public function lukas_tripster_field_callback($args)
		{
				$fid		= $args['field_id'];
				$attr		= preg_replace('/_/', '-', $fid);
				$class  = '';
				if( isset( $args['field_class'] ) )	{
					if( is_array( $args['field_class'] ) ) {
						$args['field_class'] = implode(' ', $args['field_class']);
					}
					$class 	=  esc_attr( $args['field_class'] );
				}
				
				printf(
						'<input type="text" id="%s" class="%s" name="lukas_tripster_settings[%s]" value="%s" />',
						$attr,
						$class,
						$fid,
						isset( $this->options[$fid] ) ? esc_attr( $this->options[$fid]) : ''
				);
		}
		
		public static function modify_plugin_description( $all_plugins ) {
			if ( isset( $all_plugins['lukas-tripster-use-api/lukas-tripster-use-api.php'] ) ) {
					$all_plugins['lukas-tripster-use-api/lukas-tripster-use-api.php']['Description'] = esc_html__( "Show list of excursion's using API tripster.ru", 'lukas-tripster' );
			}
			
			return $all_plugins;
		}

}

if( is_admin() )
		$lukas_tripster_settings_page = new LukasTripsterSettingsPage();
