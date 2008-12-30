<?php
/*
Plugin Name: Tumblr Recent Photos Widget
Plugin URI: http://www.vjcatkick.com/
Description: Shows a list of recent photos from Tumber.
Version: 0.0.3
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
		$tumblr_recents_src_length = $options['tumblr_recents_src_length'];

		$output = '<div id="tumblr_recent_photos"><ul>';

		// section to get tumblr photos from here 
		$request = 'http://' . $tuid . '.tumblr.com/api/read/json?num=999&type=photo';

		$ci = curl_init($request);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		$input = curl_exec($ci);

		$input = str_replace('var tumblr_api_read = ','',$input);
		$input = str_replace(';','',$input);

		$value = json_decode($input, true);
		$content =  $value['posts'];

		$item = $tumblr_recents_src_count;	// the number of items you want to display
		$type = 'photo-url-75';	// 75, 100, 250, 400, 500

		$i = 0;$stopper = 0;$dispc = 0;
		do {
			if ($content[$i]['type'] == 'photo') {
				$output .=  ('<a href="' . $content[$i]['url'] . '" target="_blank" ><img src="' . $content[$i][$type] . '" width="75" vspace = "4" hspace="4" alt="" title="" /></a>' );
				if( $dispc % 2 ) { $output .= '<br />'; }
				$dispc++;
		    }
			++$i;
			if( ++$stopper > 100 ) { break; }	// make sure it will stop
		} while( $dispc < $item );
		if( $dispc == 0 ) $output .= "No photos.";
		// tumblr section end

		$output .= '</ul></div>';

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
			$newoptions['tumblr_recents_src_length'] = (int) $_POST["tumblr_recents_src_length"];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_tumblr_recent_photos', $options);
		}

		// those are default value
		if ( !$options['tumblr_recents_src_count'] ) $options['tumblr_recents_src_count'] = 4;
		if ( !$options['tumblr_recents_src_length'] ) $options['tumblr_recents_src_length'] = 75;

		$tumblr_recents_src_count = $options['tumblr_recents_src_count'];
		$tumblr_recents_src_length = $options['tumblr_recents_src_length'];

		$title = htmlspecialchars($options['tumblr_recents_src_title'], ENT_QUOTES);
		$tuid = htmlspecialchars($options['tumblr_recents_src_uid'], ENT_QUOTES);
?>

	    <?php _e('Title:'); ?> <input style="width: 170px;" id="tumblr_recents_src_title" name="tumblr_recents_src_title" type="text" value="<?php echo $title; ?>" /><br />
		<?php _e('Name:'); ?> <input style="width: 100px;" id="tumblr_recents_src_uid" name="tumblr_recents_src_uid" type="text" value="<?php echo $tuid; ?>" />.tumblr.com<br />
            <p style="text-align: left;"><?php _e('Number of photos to show:'); ?> <input style="width: 20px;" id="tumblr_recents_src_count" name="tumblr_recents_src_count" type="text" value="<?php echo $tumblr_recents_src_count; ?>" /> 
  	    <input type="hidden" id="tumblr_recents_src_submit" name="tumblr_recents_src_submit" value="1" />

<?php
	} /* widget_tumblr_recent_photos_control() */

	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget('Tumblr Recent Photos', 'widget_tumblr_recent_photos');
	register_widget_control('Tumblr Recent Photos', 'widget_tumblr_recent_photos_control' );
} /* widget_tumblr_recent_photos_init() */

// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_tumblr_recent_photos_init');

?>