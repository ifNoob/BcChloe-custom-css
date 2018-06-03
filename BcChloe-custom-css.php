<?php
/*
Plugin Name: BcChloe Custom CSS
Plugin URI: https://github.com/ifNoob/BcChloe-custom-css
Description: BcChloe Custom CSS for CodeMirror script Page & Post
Author: BcChloe
Author URI: https://bcchloe.jp
Text Domain: bcchloe-custom-css
Version: 1.1
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/*-----------------
* code mirror script
* https://codemirror.net/
-----------------*/

// Exit If Accessed Directly
if ( ! defined( 'ABSPATH' ) ) exit;

//define( 'ONLINE_PLUGIN_FILE', __FILE__ );													# サーバーによるディレクトリなど/wp-content/plugins/online-plugin/online-plugin.php
//define( 'ONLINE_PLUGIN_DIRNAME', dirname( __FILE__ ) );						# サーバーによるディレクトリなど/wp-content/plugins/online-plugin
//define( 'ONLINE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );	# online-plugin/online-plugin.php
define( 'ONLINE_PLUGIN_URL', plugins_url( '', __FILE__ ) );					# http://www.online-inc.jp/wp-content/plugins/online-plugin

/*-----------------
* Custom CSS option panel
-----------------*/
add_action('admin_enqueue_scripts', 'bc_register_codemirror');
add_action('admin_menu', 'bc_custom_css_hooks');
add_action('save_post', 'bc_save_custom_css');
add_action('wp_head','bc_insert_custom_css');

/*-----------------
* Load code mirror css js
* js script footer move error
-----------------*/
function bc_register_codemirror() {
	$plugins_url = ONLINE_PLUGIN_URL;
	wp_enqueue_style( 'bc-custom-css', $plugins_url . '/lib/codemirror.css' );																# default theme

/* color scheme css read */
//	wp_enqueue_style( 'bc_custom-theme', $plugins_url . '/theme/oceanic-next.css' );												# color scheme style load
//	wp_enqueue_style( 'bc_custom-theme', $plugins_url . '/theme/night.css' );																# color scheme style load
	wp_enqueue_style( 'bc_custom-theme', $plugins_url . '/theme/erlang-dark.css' );														#

	wp_enqueue_style( 'bc_custom-addon', $plugins_url . '/addon/lint/lint.css' );															# addon style load
	wp_enqueue_script( 'bc-custom-js', $plugins_url . '/lib/codemirror.js' , array(), '' );										# default js
	wp_enqueue_script( 'bc-custom-css-js', $plugins_url . '/mode/css/css.js' , array(), '' );									# default.js
	wp_enqueue_script( 'bc-custom-addon-lint', $plugins_url . '/addon/lint/lint.js' , array(), '' );					# addon line ja
	wp_enqueue_script( 'bc-custom-addon-csslint', $plugins_url . '/addon/lint/csslint.js' , array(), '' );		#
	wp_enqueue_script( 'bc-custom-addon-css-lint', $plugins_url . '/addon/lint/css-lint.js' , array(), '' );	#
}

/**----------------
* editor meta box
* post page output
-----------------*/
function bc_custom_css_hooks() {
	add_meta_box('bc_code', 'Inline Custom CSS', 'custom_css_input', 'post', 'normal', 'high');
	add_meta_box('bc_code', 'Inline Custom CSS', 'custom_css_input', 'page', 'normal', 'high');
}

/*-----------------
* css input
* code mirror show
-----------------*/
function custom_css_input() {
	global $post;
?>
<?php // code mirror ?>
<style type="text/css">.CodeMirror { border: 1px solid #eee; height: auto; font-size:14px; }</style>
<?php
	echo '<textarea name="code" id="code" class="" rows="5" cols="30">'.get_post_meta($post->ID,'bc_custom_code',true).'</textarea>';
	echo '<input type="hidden" name="bc_custom_css_noncename" id="bc_custom_css_noncename" value="'.wp_create_nonce('bc-custom-code').'" />';
?>
<script type='text/javascript'>
 var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
  lineNumbers: true,
	tabSize: 2,
	mode: "css",
/* color scheme read */
//	theme: "oceanic-next",
//	theme: "night",
	theme: "erlang-dark",
  gutters: ["CodeMirror-lint-markers"],
  lint: true,
  viewportMargin: Infinity
 });
</script>
<?php
}

/**----------------
* css code save
* wp autosave
-----------------*/
function bc_save_custom_css($post_id) {
	if (!wp_verify_nonce($_POST['bc_custom_css_noncename'], 'bc-custom-code')) return $post_id;
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
	$code = $_POST['code'];
	update_post_meta($post_id, 'bc_custom_code', $code);
}

/**----------------
* css code
* post page inline code output
-----------------*/
function bc_insert_custom_css() {
	if (is_page() || is_single()) {
		if (have_posts()) : while (have_posts()) : the_post();
			$bc_buffer = get_post_meta(get_the_ID(), 'bc_custom_code', true);	# custom field read
			$css_compress = compress($bc_buffer);
			echo '<style type="text/css">'."\n". $css_compress ."\n".'</style>'."\n";
		endwhile; endif;
		rewind_posts();
	}
}

/*-----------------
* css compress
* comment & 改行 kill
-----------------*/
function compress($buffer) {
		$buffer = preg_replace('!/[\/*](.*)[*\/]/!', '', $buffer);	# 先に /* comment */ kill
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	return $buffer;
}

?>