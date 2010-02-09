<?php
/*
Plugin Name: Tumblr Recent Photos Widget
Plugin URI: http://www.vjcatkick.com/?page_id=3008
Description: Shows a list of recent photos from Tumber.
Version: 0.1.3
Author: V.J.Catkick
Author URI: http://www.vjcatkick.com/
*/

/*
Based on Display photos from Tumblr (using JSON method) entry at Drupal
http://drupal.org/node/277619

License: GPL
Compatibility: WordPress 2.0 with Widget-plugin.

Installation:
Place the widget_tumblr_recent_photos.php file in your /wp-content/plugins/ directory
and activate through the administration panel, and then go to the widget panel and
drag it to where you would like to have it!
*/

/*  Copyright V.J.Catkick - http://www.vjcatkick.com/

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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/* Changelog
* Fri Sept 19 2008 - v0.0.1
- Initial release
* Sun Sept 20 2008 - v0.0.2
- bug fixed - max number of photos
* Tue Dec 30 2008 - v0.0.3
- compatibility fix
* Jan 03 2009 - v0.1.1
- upgrade, no requires JSON lib, from this version, we are using XML
- requires PHP 5.1 or later version
* Fev 01 2009 - v0.1.2 - non release
- add: additional HTML box
* Fev 09 2010 - v0.1.3
- fixed: float bug
- adjusted: argument of image size at XML - 4:75 -> 5:75 and so on
*/


function widget_tumblr_recent_photos_init() {
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_tumblr_recent_photos( $args ) {
		extract($args);

		$options = get_option('widget_tumblr_recent_photos');
		$title = $options['tumblr_recents_src_title'];
		$tuid = $options['tumblr_recents_src_uid'];
		$tumblr_recents_src_count = $options['tumblr_recents_src_count'];
		$tumblr_recents_img_style = $options['tumblr_recents_img_style'];
		$tumblr_recents_photo_size = $options['tumblr_recents_photo_size'];
		$tumblr_recents_display_pagelink = $options['tumblr_recents_display_pagelink'];
		$tumblr_recents_additional_html = $options['tumblr_recents_additional_html'];		// 0.1.2

		$output = '<div id="tumblr_recent_photos"><ul>';
		// --

if ( function_exists('simplexml_load_file') ) {

	$tumblr_userid = $tuid;
	$tumblr_num = $tumblr_recents_src_count;
	$img_style = $tumblr_recents_img_style;
	$tumblr_size = $tumblr_recents_photo_size;
	$display_pagelink = (boolean) $tumblr_recents_display_pagelink;
	$pagecounter = 0;		// for future use

	$_tumblrurl = 'http://' . $tumblr_userid . '.tumblr.com/api/read?start=' . $pagecounter . '&num=' . $tumblr_num . '&type=photo';
	$_tumblrurl  = urlencode( $_tumblrurl );	// for only compatibility
	$_tumblr_xml = @simplexml_load_file( $_tumblrurl );

echo '<!-- ';
print_r( $_tumblr_xml );
echo ' -->';


	foreach( $_tumblr_xml->posts[0]->post as $p ) {
		$photourl = $p->{"photo-url"}[$tumblr_size];		// 4 = 75px sq
		$linkurl = $p[url];
		$output .= '<a href="' . $linkurl . '" target="_blank" >';
		$output .= '<img src="' . $photourl . '" border="0" alt="' . $linkurl . '" style="' . $img_style . '" />';		// 0.1.3 fix (adding alt)
		$output .= '</a>';
	} /* foreach */
//	$output .= '<br clear="both" >';

	if( $display_pagelink ) {
		$output .= '<div style="width:100%; text-align:center; font-size:7pt; margin-right:10px; margin-bottom:3px;" >';
		$output .='<a href="http://' . $tumblr_userid . '.tumblr.com/" target="_blank" >';
		$output .= $_tumblr_xml->tumblelog[title];
		$output .= '</a></div>';
	} /* if */

	// 0.1.2
	if( $tumblr_recents_additional_html ) {
		$output .= str_replace( "\\","", $tumblr_recents_additional_html );
	} /* if */

}else{
	$output .= 'Requires PHP 5.1';
} /* if else */

		// --
		$output .= '</ul></div>';
		$output .= '<br clear="all" />';	// 0.1.3

		// These lines generate the output
		echo $before_widget . $before_title . $title . $after_title;
		echo $output;
		echo $after_widget;
	} /* widget_tumblr_recent_photos() */

	function widget_tumblr_recent_photos_control() {
		$options = $newoptions = get_option('widget_tumblr_recent_photos');
		if ( $_POST["tumblr_recents_src_submit"] ) {
			$newoptions['tumblr_recents_src_title'] = strip_tags(stripslashes($_POST["tumblr_recents_src_title"]));
			$newoptions['tumblr_recents_src_uid'] = strip_tags(stripslashes($_POST["tumblr_recents_src_uid"]));
			$newoptions['tumblr_recents_src_count'] = (int) $_POST["tumblr_recents_src_count"];
			$newoptions['tumblr_recents_img_style'] = $_POST["tumblr_recents_img_style"];
			$newoptions['tumblr_recents_photo_size'] = (int) $_POST["tumblr_recents_photo_size"];
			$newoptions['tumblr_recents_display_pagelink'] = (boolean) $_POST["tumblr_recents_display_pagelink"];
			$newoptions['tumblr_recents_additional_html'] = $_POST["tumblr_recents_additional_html"];	// 0.1.2
		} /* if */
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_tumblr_recent_photos', $options);
		} /* if */

		// those are default value
		if ( !$options['tumblr_recents_src_count'] ) $options['tumblr_recents_src_count'] = 4;
		if ( !$options['tumblr_recents_img_style'] ) $options['tumblr_recents_img_style'] = 'float:left; margin-right:12px; margin-bottom:8px;';
		if ( !$options['tumblr_recents_photo_size'] ) $options['tumblr_recents_photo_size'] = 4;

		$tumblr_recents_src_count = $options['tumblr_recents_src_count'];
		$tumblr_recents_img_style = $options['tumblr_recents_img_style'];
		$tumblr_recents_photo_size = $options['tumblr_recents_photo_size'];
		$tumblr_recents_display_pagelink = $options['tumblr_recents_display_pagelink'];
		$tumblr_recents_additional_html = $options['tumblr_recents_additional_html'];		// 0.1.2

		$title = htmlspecialchars($options['tumblr_recents_src_title'], ENT_QUOTES);
		$tuid = htmlspecialchars($options['tumblr_recents_src_uid'], ENT_QUOTES);
?>

		<?php _e('Title:'); ?> <input style="width: 170px;" id="tumblr_recents_src_title" name="tumblr_recents_src_title" type="text" value="<?php echo $title; ?>" /><br />
		<?php _e('Tumblr ID:'); ?> <input style="width: 100px;" id="tumblr_recents_src_uid" name="tumblr_recents_src_uid" type="text" value="<?php echo $tuid; ?>" />.tumblr.com<br />
		<?php _e('Number of photos to show:'); ?> <input style="width: 20px;" id="tumblr_recents_src_count" name="tumblr_recents_src_count" type="text" value="<?php echo $tumblr_recents_src_count; ?>" /> <br />

		<?php _e('Img CSS:'); ?><br /><input style="width: 100%;" id="tumblr_recents_img_style" name="tumblr_recents_img_style" type="textarea" value="<?php echo $tumblr_recents_img_style; ?>" /><br />
		<?php _e('Img Size:'); ?> 
<!--		<input style="" id="tumblr_recents_photo_size" name="tumblr_recents_photo_size" type="radio" value="0" <?php if( $tumblr_recents_photo_size == 0 ) {echo 'checked';} ?> />500 -->
<!--		<input style="" id="tumblr_recents_photo_size" name="tumblr_recents_photo_size" type="radio" value="1" <?php if( $tumblr_recents_photo_size == 1 ) {echo 'checked';} ?> />500 -->
<?php
// renumber 0.1.3
?>
		<input style="" id="tumblr_recents_photo_size" name="tumblr_recents_photo_size" type="radio" value="2" <?php if( $tumblr_recents_photo_size == 2 ) {echo 'checked';} ?> />400
		<input style="" id="tumblr_recents_photo_size" name="tumblr_recents_photo_size" type="radio" value="3" <?php if( $tumblr_recents_photo_size == 3 ) {echo 'checked';} ?> />250
		<input style="" id="tumblr_recents_photo_size" name="tumblr_recents_photo_size" type="radio" value="4" <?php if( $tumblr_recents_photo_size == 4 ) {echo 'checked';} ?> />125
		<input style="" id="tumblr_recents_photo_size" name="tumblr_recents_photo_size" type="radio" value="5" <?php if( $tumblr_recents_photo_size == 5 ) {echo 'checked';} ?> />75<br />

		<input style="" id="tumblr_recents_display_pagelink" name="tumblr_recents_display_pagelink" type="checkbox" value="1" <?php if( $tumblr_recents_display_pagelink ) {echo 'checked';} ?> />Display tumblr link<br />

		<?php _e('Additional HTML:'); ?><br />
		<textarea rows=3 id="tumblr_recents_additional_html" name="tumblr_recents_additional_html" /><?php echo $tumblr_recents_additional_html; ?></textarea><br />

  	    <input type="hidden" id="tumblr_recents_src_submit" name="tumblr_recents_src_submit" value="1" />

<?php
	} /* widget_tumblr_recent_photos_control() */

	register_sidebar_widget('Tumblr Recent Photos', 'widget_tumblr_recent_photos');
	register_widget_control('Tumblr Recent Photos', 'widget_tumblr_recent_photos_control' );
} /* widget_tumblr_recent_photos_init() */

add_action('plugins_loaded', 'widget_tumblr_recent_photos_init');

?>