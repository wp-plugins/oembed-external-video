<?php
/*
Plugin Name: oEmbed External Video
Plugin URI: http://www.parorrey.com/solutions/oembed-external-video/
Description: This plugin converts any external mp4 url into HTML5 video tag. This plugins is needed because WordPress oEmbed only converts urls from supported oEmbed providers.
Version: 0.1
Author: Ali Qureshi
Author URI: http://www.parorrey.com
License: GPLv3
*/

wp_embed_register_handler( 'oev_html5_video', '#^(http|https)://.+\.(mp4|MP4)$#i', 'oev_embed_handler_html5_video' );

function oev_embed_handler_html5_video( $matches, $attr, $url, $rawattr ) {
	$options = get_option( 'wp_oev_settings' ); 
 
	if($options['wp_oev_controls']) $controls = $options['wp_oev_controls'];
	if($options['wp_oev_loop']) $loop = $options['wp_oev_loop'];
	if($options['wp_oev_muted']) $muted = $options['wp_oev_muted'];
	if($options['wp_oev_autoplay']) $autoplay = $options['wp_oev_autoplay'];
	
	$video_attr = $controls.' '.$loop.' '.$muted.' '.$autoplay;
	
	$embed = sprintf(
				'<video '.$video_attr.' width="'.$options['wp_oev_width'].'" height="'.$options['wp_oev_height'].'"><source src="%1$s"  type="video/mp4"></video>',
				esc_attr($matches[0])
				);
	

	$embed = apply_filters( 'oev_html5_mp4_video', $embed, $matches, $attr, $url, $rawattr );
  
  return apply_filters( 'oembed_result', $embed, $url, '' );
 
	
}

/**
 * Creates the default options
 */
register_activation_hook( __FILE__, 'wp_oev_setup_options' );

function wp_oev_setup_options(){
 
    //the default options
    $wp_oev_settings = array(
        'wp_oev_width' => '512',
		'wp_oev_height' => '384',
		'wp_oev_controls'=>'controls',
		'wp_oev_autoplay' =>'',
		'wp_oev_loop'=>'',		
		'wp_oev_muted'=>''
		       
    );
 
    //check to see if present already
    if(!get_option('wp_oev_settings')) {
        //option not found, add new
        add_option('wp_oev_settings', $wp_oev_settings);
    } else {
        //option already in the database
        //so we get the stored value and merge it with default
        $old_op = get_option('wp_oev_settings');
        $wp_oev_settings = wp_parse_args($old_op, $wp_oev_settings);
 
        //update it
        update_option('wp_oev_settings', $wp_oev_settings);
    }
  
}

function wp_oev_restore_options() {
        delete_option('wp_oev_settings');
        wp_oev_setup_options();
    }


define('OEV_SHORTNAME', 'oEmbed Video'); // used to prefix the individual setting field
define('OEV_FULLNAME', 'oEmbed External Video'); // 
define('OEV_PAGE_BASENAME', 'oembed-external-video-settings'); // the settings page slug

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'oev_plugin_action_links' );

function oev_plugin_action_links( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'options-general.php?page='.OEV_PAGE_BASENAME) .'">Settings</a>';  
   return $links;
}

/*
 * Add the admin page
 */
add_action('admin_menu', 'wp_oev_admin_page');
function wp_oev_admin_page(){
   // add_menu_page(OEV_FULLNAME.' Settings', OEV_SHORTNAME, 'administrator', OEV_PAGE_BASENAME, 'wp_oev_admin_page_callback');
     add_submenu_page('options-general.php',OEV_FULLNAME.' Settings', OEV_SHORTNAME, 'administrator', OEV_PAGE_BASENAME, 'wp_oev_admin_page_callback');
	
	}

/*
 * Register the settings
 */
add_action('admin_init', 'wp_oev_register_settings');
function wp_oev_register_settings(){
    //this will save the option in the wp_options table as 'wp_oev_settings'
    //the third parameter is a function that will validate your input values
    register_setting('wp_oev_settings', 'wp_oev_settings', 'wp_oev_settings_validate');
}

function wp_oev_settings_validate($args){
    //$args will contain the values posted in your settings form, you can validate them as no spaces allowed, no special chars allowed or validate values etc.
    if(!isset($args['wp_oev_width']) || !is_numeric($args['wp_oev_width'])){
        //add a settings error because the value is invalid and make the form field blank, so that the user can enter again
        $args['wp_oev_width'] = '';
    add_settings_error('wp_oev_settings', 'wp_oev_invalid_value', 'Please enter a valid number for width!', $type = 'error');   
    }
	
 if(!isset($args['wp_oev_height']) || !is_numeric($args['wp_oev_height'])){
        //add a settings error because the value is invalid and make the form field blank, so that the user can enter again
        $args['wp_oev_height'] = '';
    add_settings_error('wp_oev_settings', 'wp_oev_invalid_value', 'Please enter a valid number for height!', $type = 'error');   
    }

    //make sure you return the args
    return $args;
}

//Display the validation errors and update messages
/*
 * Admin notices
 */
add_action('admin_notices', 'wp_oev_admin_notices');
function wp_oev_admin_notices(){
   settings_errors();
}

//The markup for your plugin settings page
function wp_oev_admin_page_callback(){ 

 echo   '<div class="wrap">
    <h2>'.OEV_FULLNAME.' Settings</h2>
    <form action="options.php" method="post">';
	
        settings_fields( 'wp_oev_settings' );
        do_settings_sections( __FILE__ );

        //get the older values, wont work the first time
        $oev_options = get_option( 'wp_oev_settings' ); 
		
		
echo '<table class="form-table">
            <tr>
                <th scope="row">Video Width</th>
                <td>
                    <fieldset>
                        <label>
                            <input name="wp_oev_settings[wp_oev_width]" type="text" id="wp_oev_width" value="';
							
	echo (isset($oev_options['wp_oev_width']) && $oev_options['wp_oev_width'] != '') ? $oev_options['wp_oev_width'] : '';
		echo '"/>
                            <br />
                            <span class="description">Please enter video width. e.g 512</span>
                        </label>
                    </fieldset>
                </td>
            </tr>
			
			 <tr>
                <th scope="row">Video Height</th>
                <td>
                    <fieldset>
                        <label>
                            <input name="wp_oev_settings[wp_oev_height]" type="text" id="wp_oev_height" value="';
							
	echo (isset($oev_options['wp_oev_height']) && $oev_options['wp_oev_height'] != '') ? $oev_options['wp_oev_height'] : '';
		echo '"/>
                            <br />
                            <span class="description">Please enter video height. e.g 384</span>
                        </label>
                    </fieldset>
                </td>
            </tr>
			
			 <tr>
                <th scope="row">Video Controls</th>
                <td>
                    <fieldset>
                        <label>                         
							<input name="wp_oev_settings[wp_oev_controls]" id="wp_oev_controls" type="checkbox" value="controls"'; 
	echo (isset($oev_options['wp_oev_controls']) && $oev_options['wp_oev_controls'] != '') ? ' checked="checked"' : '';
		echo '/>Enabled
                       
                         
                        </label>
                    </fieldset>
                </td>
            </tr>
			
			 <tr>
                <th scope="row">Video Autoplay</th>
                <td>
                    <fieldset>
                        <label>                         
							<input name="wp_oev_settings[wp_oev_autoplay]" id="wp_oev_autoplay" type="checkbox" value="autoplay"'; 
	echo (isset($oev_options['wp_oev_autoplay']) && $oev_options['wp_oev_autoplay'] != '') ? ' checked="checked"' : '';
		echo '/>Enabled
                       
                         
                        </label>
                    </fieldset>
                </td>
            </tr>
			
			 <tr>
                <th scope="row">Video Loop</th>
                <td>
                    <fieldset>
                        <label>                         
							<input name="wp_oev_settings[wp_oev_loop]" id="wp_oev_loop" type="checkbox" value="loop"'; 
	echo (isset($oev_options['wp_oev_loop']) && $oev_options['wp_oev_loop'] != '') ? ' checked="checked"' : '';
		echo '/>Enabled
                       
                         
                        </label>
                    </fieldset>
                </td>
            </tr>
			
			 <tr>
                <th scope="row">Video Muted</th>
                <td>
                    <fieldset>
                        <label>                         
							<input name="wp_oev_settings[wp_oev_muted]" id="wp_oev_controls" type="checkbox" value="muted"'; 
	echo (isset($oev_options['wp_oev_muted']) && $oev_options['wp_oev_muted'] != '') ? ' checked="checked"' : '';
		echo '/>Enabled
                       
                         
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>
        <input type="submit" value="Save" />
    </form>
</div>';
 }

?>