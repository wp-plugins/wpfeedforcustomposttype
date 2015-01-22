<?php
/*
Plugin Name: Codeboxr RSS Feed for custom post types
Plugin URI: http://codeboxr.com/product/rss-feed-manager-for-custom-post-types
Description: Shows or merges feeds for custom post type with default posts 
Author: Codeboxr
Version: 1.2
Author URI: http://codeboxr.com
*/
/*
    Copyright 2012-2015  Codeboxr (email : sabuj@codeboxr.com)
    

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// avoid direct calls to this file where wp core files not present
if (!function_exists ('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}
/*
 * Use WordPress 2.6 Constants
 */
if (!defined('WP_CONTENT_DIR')) {
	define( 'WP_CONTENT_DIR', ABSPATH.'wp-content');
}
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
}
if (!defined('WP_PLUGIN_DIR')) {
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
}
if (!defined('WP_PLUGIN_URL')) {
	define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
}

$wpfeedforcustomposttype = get_option('wpfeedforcustomposttype');

register_activation_hook( __FILE__, 'wpfeedforcustomposttype_activate' );
register_deactivation_hook(__FILE__, 'wpfeedforcustomposttype_deactivation');
add_action('admin_menu', 'wpfeedforcustomposttype_admin');   //adding menu in admin menu settings

// Add actions to load language
add_action('init', 'textdomain_init_wpfeedforcustomposttype');

function textdomain_init_wpfeedforcustomposttype(){
    load_plugin_textdomain('wpfeedforcustomposttype', false, basename( dirname( __FILE__ ) ) . '/languages' );
}



//plugin activation action
function wpfeedforcustomposttype_activate()
{
    global $wpfeedforcustomposttype;
    $defaults = array('post' => 'on' );
    foreach($defaults  as $key => $value)
    {
        $wpfeedforcustomposttype[$key] = $value;
    }
    update_option('wpfeedforcustomposttype',$wpfeedforcustomposttype);

}

//plugin deactivation action
function wpfeedforcustomposttype_deactivation()
{
    global $wpfeedforcustomposttype;
    //let's keep the otpion table clean
    delete_option('wpfeedforcustomposttype');
    
}

function wpfeedforcustomposttype_admin()
{
    global $wpfeedforcustomposttype_hook, $wpfeedforcustomposttype;
    //add_options_page(page_title, menu_title, access_level/capability, file, [function]);
    if (function_exists('add_options_page')) {
            $page_hook = add_options_page(__('RSS for Custom Posts types','wpfeedforcustomposttype'), __('RSS 4 Posts Types', 'wpfeedforcustomposttype'), 'manage_options', 'wpfeedforcustomposttype', 'wpfeedforcustomposttype_admin_option');
    }    
    

}

add_action( 'init', 'cb_create_wpfeedforcustomposttype', 0 );
function cb_create_wpfeedforcustomposttype(){
    add_filter('request', 'cb_wpfeedforcustomposttype_feedrequest');
}

function cb_wpfeedforcustomposttype_feedrequest($qv) {
	if (isset($qv['feed']) && !isset($qv['post_type'])){
            $wpfeedforcustomposttype = get_option('wpfeedforcustomposttype'); 
            //var_dump($wpfeedforcustomposttype);
            $ptypes =  array();
            if(!empty($wpfeedforcustomposttype)):
                foreach($wpfeedforcustomposttype as $key => $value){
                    if($value == 'on')
                    $ptypes[] = $key;
                }
                $qv['post_type'] = $ptypes;
            endif;
        }    
	return $qv;
}

//admin option page
function wpfeedforcustomposttype_admin_option()
{        
    ?>
    
	<?php
        global $wpfeedforcustomposttype;
        $builtinposts = array();
        $customposts  = array();
        $alltypeposts = array();

        $builtinargs = array(
          'public'   => true,
          'show_ui'  => true,
          '_builtin' => true
          //'publicly_queryable' => true
        ); 

        $customargs = array(
          'public'   => true,
          'show_ui'  => true,
          '_builtin' => false
          //'publicly_queryable' => true
        ); 

        $output = 'objects'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $post_typesb = get_post_types($builtinargs, $output, $operator); 
        foreach ($post_typesb  as $post_typeb ) {
            $label = $post_typeb->labels->name;
            $name = $post_typeb->name;
            $alltypeposts[$name] = $label;
            $builtinposts[$name] = $label;
            //var_dump($label);
        }
        //var_dump($builtinposts);
        
        $post_typesc = get_post_types($customargs, $output, $operator); 

        foreach ($post_typesc  as $post_typec ) {
            $label = $post_typec->labels->name;
            $name = $post_typec->name;
            $alltypeposts[$name] = $label;
            $customposts[$name]  = $label;
        }                    
        
	if(isset($_POST['uwpfeedforcustomposttype'])) {
		check_admin_referer('wpfeedforcustomposttype');

//		echo '<pre>';
//		print_r($_POST);
//		echo '<pre>';

		//post var
		foreach($alltypeposts  as $key => $value){
            $wpfeedforcustomposttype[$key] = isset($_POST['pt'.$key])? trim($_POST['pt'.$key]): '';
            //var_dump($_POST['pt'.$key]);
        }
        update_option('wpfeedforcustomposttype',$wpfeedforcustomposttype);
        //var_dump($wpfeedforcustomposttype);
                
	}//end main if       
        
        $wpfeedforcustomposttype = (array)get_option('wpfeedforcustomposttype');       

        if(isset($_POST['uwpfeedforcustomposttype'])) {
            echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.__('Options updated','wpfeedforcustomposttype').'</p></div>';
        }

?>
	<div class="wrap">

		<div id="icon-options-general" class="icon32"><span class="dashicons dashicons-rss"></span></div>
		<h2><?php echo __('RSS Feed Manager for Custom Post Types', 'wpfeedforcustomposttype'); ?></h2>

		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">

				<!-- main content -->
				<div id="post-body-content">

					<div class="meta-box-sortables ui-sortable">

						<div class="postbox">
							<div class="inside">
								<h2><?php echo __('Plugin Settings','wpfeedforcustomposttype'); ?></h2>
								<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
									<?php wp_nonce_field('wpfeedforcustomposttype'); ?>
									<table cellspacing="0" class="widefat">
										<thead>
										<tr>
											<th style="" class="row-title" scope="col"><?php echo __('Post Types','wpfeedforcustomposttype') ?></th>
											<th style="" class="row-title" scope="col"><?php echo __('Selection','wpfeedforcustomposttype') ?></th>
										</tr>
										</thead>
										<tfoot>
										<tr>
											<th style="" class="row-title" scope="col"><?php echo __('Post Types','wpfeedforcustomposttype') ?></th>
											<th style="" class="row-title" scope="col"><?php echo __('Selection','wpfeedforcustomposttype') ?></th>
										</tr>
										</tfoot>
										<tbody>
										<?php
										//var_dump($alltypeposts);
										echo '<tr><td colspan="2"><h3>'.__('Built-in Posts Types','wpfeedforcustomposttype'),'</h3></td></tr>';
										foreach ($builtinposts  as $key => $value ) {
											$oldval = isset($wpfeedforcustomposttype[$key]) ? $wpfeedforcustomposttype[$key]: '';

											echo '<tr>';
											echo '<td>'. $value.'['.$key. ']</td>';
											echo '<td><label for="pt'.$key.'"><input id="pt'.$key.'" type="checkbox" name="pt'.$key.'" '.checked('on',$oldval,false).' /> Enable/Disable</label></td>';
											echo '</tr>';
										}
										echo '<tr><td colspan="2"><h3>'. __('Custom Posts Types','wpfeedforcustomposttype').'</h3></td></tr>';
										foreach ($customposts  as $key => $value ) {
											$oldval = isset($wpfeedforcustomposttype[$key]) ? $wpfeedforcustomposttype[$key]: '';

											echo '<tr>';
											echo '<td>'. $value.'['.$key. ']</td>';
											echo '<td><label for="pt'.$key.'"><input id="pt'.$key.'" type="checkbox" name="pt'.$key.'" '.checked('on',$oldval,false).' /> Enable/Disable</label></td>';
											echo '</tr>';
										}
										?>

										<tr valign="top">
											<td></td>
											<td><input type="submit" name="uwpfeedforcustomposttype" class="button-primary" value="<?php echo  __('Save Changes','wpfeedforcustomposttype'); ?>" ></td>
										</tr>
										</tbody>
									</table>
								</form>
							</div> <!-- .inside -->

						</div> <!-- .postbox -->

					</div> <!-- .meta-box-sortables .ui-sortable -->

				</div> <!-- post-body-content -->

				<!-- sidebar -->
				<div id="postbox-container-1" class="postbox-container">

					<div class="meta-box-sortables">

							<?php
							$plugin_data = get_plugin_data( __FILE__ );
							//var_dump($plugin_data);
							?>
							<div class="postbox">
								<h3><span>Plugin Info</span></h3>
								<div class="inside">
									<p>Plugin Name : <?php echo $plugin_data['Title']?> <?php echo $plugin_data['Version']?></p>
									<!--p>Plugin Url: <?php echo $plugin_data['PluginURI']; ?></p-->
									<p>Author : <?php echo $plugin_data['Author']?></p>
									<p>Website : <a href="http://codeboxr.com" target="_blank">codeboxr.com</a></p>
									<p>Email : <a href="mailto:info@codeboxr.com" target="_blank">info@codeboxr.com</a></p>
									<p>
										<a href="http://facebook.com/codeboxr" target="_blank"><span class="dashicons dashicons-facebook-alt"></span></a>
										<a href="http://twitter.com/codeboxr" target="_blank"><span class="dashicons dashicons-twitter"></span></a>
										<a href="http://linkedin.com/company/codeboxr" target="_blank">Ln<!--span class="genericon genericon-linkedin-alt"></span--></a>
										<a href="https://plus.google.com/+codeboxr" target="_blank"><span class="dashicons dashicons-googleplus"></span></a>
									</p>



								</div> <!-- .inside -->

							</div> <!-- .postbox -->

							<div class="postbox">
								<h3><span>Help & Supports</span></h3>
								<div class="inside">
									<p>Support: <a href="http://codeboxr.com/contact-us.html" target="_blank">Contact Us</a></p>
									<p><i class="icon-envelope"></i> <a href="mailto:info@codeboxr.com">info@codeboxr.com</a></p>
								</div>
							</div>
							<div class="postbox">
								<h3><span>Codeboxr Updates</span></h3>
								<div class="inside">
									<?php
									include_once(ABSPATH . WPINC . '/feed.php');
									if(function_exists('fetch_feed')) {
										$feed = fetch_feed('http://codeboxr.com/feed');
										// $feed = fetch_feed('http://feeds.feedburner.com/codeboxr'); // this is the external website's RSS feed URL
										if (!is_wp_error($feed)) : $feed->init();
											$feed->set_output_encoding('UTF-8'); // this is the encoding parameter, and can be left unchanged in almost every case
											$feed->handle_content_type(); // this double-checks the encoding type
											$feed->set_cache_duration(21600); // 21,600 seconds is six hours
											$limit = $feed->get_item_quantity(6); // fetches the 18 most recent RSS feed stories
											$items = $feed->get_items(0, $limit); // this sets the limit and array for parsing the feed

											$blocks = array_slice($items, 0, 6); // Items zero through six will be displayed here
											echo '<ul>';
											foreach ($blocks as $block) {
												$url = $block->get_permalink();
												echo '<li><a target="_blank" href="'.$url.'">';
												echo '<strong>'.$block->get_title().'</strong></a><br/>';
												//var_dump($block->get_description());
												//echo $block->get_description();
												//echo substr($block->get_description(),0, strpos($block->get_description(), "<br />")+4);
												echo '</li>';

											}//end foreach
											echo '</ul>';
										endif;
									}									?>
								</div>
							</div>
					</div> <!-- .meta-box-sortables -->

				</div> <!-- #postbox-container-1 .postbox-container -->

			</div> <!-- #post-body .metabox-holder .columns-2 -->

			<br class="clear">
		</div> <!-- #poststuff -->

	</div> <!-- .wrap -->
    <?php
}

//add plugin setting page link in plugin listing page
function add_wpfeedforcustomposttype_settings_link( $links ) {
  $settings_link = '<a href="options-general.php?page=wpfeedforcustomposttype">Settings</a>';
  array_unshift( $links, $settings_link );
  return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'add_wpfeedforcustomposttype_settings_link' );

?>