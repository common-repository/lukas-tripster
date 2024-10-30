<?php
/*
Plugin Name:  Lukas Tripster
Description:  Show list of excursion's using API tripster.ru
Version:      1.0
Author:       Konstantin Lukas
Author URI:   https://profiles.wordpress.org/servekon
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  lukas-tripster
Domain Path:  /languages
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'LUKAS_TRIPSTER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LUKAS_TRIPSTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LUKAS_TRIPSTER_MAX_NUMBER', 3 );

function lukas_tripster_load_plugin_textdomain() {
		load_plugin_textdomain( 'lukas-tripster', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'lukas_tripster_load_plugin_textdomain' );

require_once( LUKAS_TRIPSTER_PLUGIN_DIR . 'class.lukas-tripster-admin.php' );

function add_mce_button_lukas_tripster() {
		// check user permissions
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}
		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', 'add_tinymce_plugin_lukas_tripster' );
			add_filter( 'mce_buttons', 'register_mce_button_lukas_tripster' );
		}
}
add_action('init', 'add_mce_button_lukas_tripster');

function add_tinymce_plugin_lukas_tripster( $plugin_array ) {
		$plugin_array['lukasTripster'] = LUKAS_TRIPSTER_PLUGIN_URL .'/tinymce-but.js';
		return $plugin_array;
}

// Register new button in the editor
function register_mce_button_lukas_tripster( $buttons ) {
		array_push( $buttons, 'lukas_tripster' );
		return $buttons;
}

function lukas_tripster_tinymce_plugin_add_locale($locales) {
		$locales ['lukas-tripster'] = LUKAS_TRIPSTER_PLUGIN_DIR. 'lukas-tripster-tinymce-plugin-langs.php';
		return $locales;
}
add_filter('mce_external_languages', 'lukas_tripster_tinymce_plugin_add_locale');

function lukas_tripster_shortcode_callback( $atts ) {
	
		if( !function_exists( 'curl_init' ) ){
				return '
				<div class="notice notice-warning is-dismissible">'.
					esc_html__('PHP library cURL is not installed. Continue work isn\'t possible.', 'lukas-tripster').
				'</div>';
		}
		
		$options = get_option( 'lukas_tripster_settings' );
		
		$defaults = shortcode_atts( array(
				'city' => esc_html__( 'Moscow', 'lukas-tripster' ),
				'number' => $options['lukas_tripster_block_num_isnumeric'],
		), $atts, 'lukas_tripster' );
		
		$partner_id = $options['lukas_tripster_partner_id'];
		$city 			= esc_attr($defaults['city']);
		$number 			= (int)$defaults['number'];
		$out 				= '';
		
		if( LUKAS_TRIPSTER_MAX_NUMBER < $number ){
			$number = 3;
		}
		
		//~ Initiate curl
		$ch = curl_init();
		//~ Disable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//~ Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//~ Set the url
		curl_setopt($ch, CURLOPT_URL,'https://experience.tripster.ru/api/geo/cities/?name_ru='.$city);
		$tripsterCityJson = curl_exec($ch);
		$tripsterCityRes  = json_decode($tripsterCityJson);
		
		if(isset($tripsterCityRes->count) && $tripsterCityRes->count > 0){
				array_splice($tripsterCityRes->results, 1);
				foreach($tripsterCityRes->results as $tKey=>$tVal){
						$nameCity = (isset($tVal->experience_city->name_en)) ? $tVal->experience_city->name_en : $tVal->name;
						$nameCity = mb_ereg_replace(' ', '_', $nameCity);
						$nameCity = mb_ereg_replace('Nizhniy_Novgorod', 'Nizhny_Novgorod', $nameCity);
						
						curl_setopt($ch, CURLOPT_URL, 'https://experience.tripster.ru/partner/?city='.$nameCity.'&template=json&partner='.$partner_id.'&order=top');
						$tripsterTourJson = curl_exec($ch);
						$tripsterTourRes = json_decode($tripsterTourJson);
						
						if(isset($tripsterTourRes->experiences) && $tripsterTourRes->experiences){
							if( 3 > $number ){
								shuffle($tripsterTourRes->experiences);
							}
							
							array_splice($tripsterTourRes->experiences, $number);
							
							$urlSuff = '?utm_source='.$partner_id.'&exp_partner='.$partner_id.'&utm_campaign=affiliates&utm_medium=link&utm_content='.$nameCity;
							$out .= '<div class="lukas-tripster-wrapper">
							<div class="excursions-main-tbl">
									<div class="excursions-title alcenter"><a href="'.$tripsterTourRes->url.$urlSuff.'" target="_blank" rel="nofollow">'.esc_html__('Excursions', 'lukas-tripster').'. '.$tripsterTourRes->city.'</a></div>
							';
							foreach($tripsterTourRes->experiences as $teKey=>$teVal){
								$rating = round((float)$teVal->rating / 0.05, 2);
								$tripsterLink = esc_attr($teVal->url).$urlSuff;
								$lastClass = '';
								
								if( end($tripsterTourRes->experiences) === $teVal ){
									$lastClass = ' last-col';
								}
								
								$out .= '
								<div class="col-'.sizeof($tripsterTourRes->experiences).$lastClass.'">
									<table>
										<tr>
											<td><img src="'.esc_attr($teVal->thumb_photo_url).'" class="excursions-main-image" /></td>
											<td class="excursions-price alcenter">'.esc_html__('Price', 'lukas-tripster').' '.esc_attr($teVal->price_for).':<br /><span class="price-sum">'.esc_attr($teVal->minimal_full_price_local).' '.(($teVal->minimal_full_price != $teVal->minimal_full_price_local) ? esc_html__('rubl', 'lukas-tripster') : esc_html__('euro', 'lukas-tripster')).'</span></td>
										</tr>
										<tr>
											<td class="excursions-nowrap" style="text-align: justify">
												<a href="'.$tripsterLink.'" target="_blank" rel="nofollow">
													<img src="'.esc_attr($teVal->guide_avatar_url).'" class="excursions-avatar-image" align="left" />
													<span class="excursions-author-name">'.esc_attr($teVal->guide_first_name).'</span>
												</a><br />
												<a href="'.$tripsterLink.'" title="'.esc_attr($teVal->rating).' '.esc_html__('point from', 'lukas-tripster').' '.esc_attr($teVal->review_count).' '.esc_html__('reviews', 'lukas-tripster').'" class="excursions-rating" target="_blank" rel="nofollow"><i style="width: '.$rating.'%"></i></a>
												<a href="'.$tripsterLink.'" class="excursions-review-count" target="_blank" rel="nofollow">'.$teVal->review_count.'</a>
											</td>
										</tr>
										<tr>
											<td colspan="2" class="excursions-title"><a href="'.$tripsterLink.'" target="_blank" rel="nofollow">'.$teVal->title.'</a></td>
										</tr>
										<tr>
											<td colspan="2" class="excursions-description">'.esc_attr($teVal->tagline).'</td>
										</tr>
									</table>
								</div>
								';
							}
							$out .= '
								<div class="clearfix">&nbsp;</div>
							</div>
						</div>
							';
						}
				}
				curl_close($ch);
		}
		
		return $out;
}
add_shortcode( 'tripster', 'lukas_tripster_shortcode_callback' );

function lukas_tripster_get_css($options) {
		$out = '
			.lukas-tripster-wrapper {
				font-size: '.$options['lukas_tripster_font_size_isnumeric'].'em;
				line-height: '.floatval($options['lukas_tripster_font_size_isnumeric']+0.5).'em;
				color: '.$options['lukas_tripster_text_color'].';
			}
			.lukas-tripster-wrapper a{
				color: '.$options['lukas_tripster_link_color'].';
			}
			.lukas-tripster-wrapper .excursions-main-tbl{
				border: solid 1px '.$options['lukas_tripster_main_border_color'].';
				background-color: '.$options['lukas_tripster_bg_color'].';
			}
			
			.lukas-tripster-wrapper .col-1, .lukas-tripster-wrapper .col-2, .lukas-tripster-wrapper .col-3{
				min-width: '.$options['lukas_tripster_min_width_isnumeric'].'px;
			}
			
			.lukas-tripster-wrapper .excursions-rating {
				background: url('.LUKAS_TRIPSTER_PLUGIN_URL.'images/rating.png) 0 -14px no-repeat;
			}
			.lukas-tripster-wrapper .excursions-rating i {
				background: url('.LUKAS_TRIPSTER_PLUGIN_URL.'images/rating.png) 0 0 no-repeat;
			}
			.lukas-tripster-wrapper .excursions-review-count:after {
				background: url('.LUKAS_TRIPSTER_PLUGIN_URL.'images/icons.png) -1px -30px no-repeat;
			}
			.lukas-tripster-wrapper .excursions-title a{
				color: '.$options['lukas_tripster_title_color'].';
			}
			.lukas-tripster-wrapper .excursions-price{
				color: '.$options['lukas_tripster_price_color'].';
			}
			.lukas-tripster-wrapper .excursions-price .price-sum{
				color: '.$options['lukas_tripster_title_color'].';
			}
		';
		
		return $out;
}

function lukas_tripster_style_insert() {
		wp_enqueue_style(
				'lukas-tripster-style',
				LUKAS_TRIPSTER_PLUGIN_URL . '/css/lukas-tripster.css'
		);
		
		$options = get_option( 'lukas_tripster_settings' );
		$css = lukas_tripster_get_css( $options );
		
		wp_add_inline_style( 'lukas-tripster-style', $css );
}
add_action( 'wp_enqueue_scripts', 'lukas_tripster_style_insert' );
