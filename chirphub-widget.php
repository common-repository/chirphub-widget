<?php
/**
 * Plugin Name: ChirpHub Widget
 * Plugin URI: http://ChirpHub.com/plugins.html
 * Description: Displays the status of a particular ChirpHub device
 * Version: 1.2
 * Author: Joel Pearson, ChirpHub.com
 * Author URI: http://ChirpHub.com
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/*  Copyright 2012 Joel Pearson (email: wp-plugin@chirphub.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2 
    or any later version, as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'chirphub_load_widgets' );

/**
 * Register our widget.
 */
function chirphub_load_widgets() {
	register_widget( 'ChirpHub_Widget' );
}

class ChirpHub_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function ChirpHub_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'chirphub', 
                                     'description' => __('Your ChirpHub status', 'chirphub') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 500, 
                                      'height' => 350, 
                                      'id_base' => 'chirphub-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'chirphub-widget', 
                                 __('ChirpHub Widget', 'chirphub'), 
                                 $widget_ops, 
                                 $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters( 'widget_title', $instance['title'] );
		$status_format = htmlspecialchars_decode( $instance['text_prefix'], ENT_NOQUOTES );
		$device = $instance['url'];
		$base_url = 'http://www.chirphub.com/status/';
		$url = '';

		if ( $device ) {
			/* Backwards compatibility (until v1.3): */
			/* Allow full URLs for now, but prefer just the device ID */
			if ( substr( $device, 0, 7 ) == 'http://' or substr( $device, 0, 8 ) == 'https://' ) {
				$url = $device;
			}
			else {
				$url = $base_url . $device;
			}

			/* Default to JSON if no file type is specified */
			if ( substr( $url, -4 ) !== '.txt' and substr( $url, -5 ) !== '.json' ) {
				$url = $url . '.json';
			}
		}

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* If device was entered by user, fetch it and display status. */
		if ( $url ) {
			$response_success = FALSE;
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {   // webpage was retrieved successfully
				$response_success = TRUE;
				$response_body = $response['body'];
				$item_names = array( 'body' );
				$item_values = array( $response_body );
				$content_type = $response['headers']['content-type'];

				// If this is a JSON file, decode it
				if ( $content_type == 'application/json' ) {
	                                $json = $response_body;
					$data = json_decode( $json, true );
	                                if ( is_null( $data ) ) {
						$response_success = FALSE;
					}
				}

				// If we have JSON data, put it into $item_names and $item_values
				if ( $response_success && $content_type == 'application/json' ) {
					$date = $data['date'];
					$day_of_week = $data['day_of_week'];
					$message = $data['message'];
					$signal_strength = $data['signal_strength'];
					$time  = $data['time'];

					$item_names = array_keys( $data );
					$item_values = array();
					for ( $i = 0, $size = sizeof( $item_names ); $i < $size; ++$i ) {
						$name = $item_names[$i];
						array_push($item_values, $data[$name]);
						$item_names[$i] = '$' . $name;
					}

					if ( $message == 'lunch' ) {
						$message = 'out to lunch';
					}
				}
			}

			if ( $response_success ) {
				/* Substitute in any variables specified in $status_format using the JSON data */
				$count = 0;
 				$message = str_ireplace( $item_names, $item_values, $status_format, $count );

				/* Backwards compatibility (until v1.3): */
				/* If no variables were specified, just append the response to the format */
				if ($count < 1) {
					$message = $status_format . ' ' . $response_body . '.';
				}
				echo "<ul><li>$message</li></ul>";
			}
			else {
				echo "<ul><li>(Status not available.)</li></ul>";
			}
		}
		else {
			echo "<ul><li>(No device ID was entered.)</li></ul>";

		}

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text_prefix'] = htmlspecialchars( $new_instance['text_prefix'], ENT_NOQUOTES, 'UTF-8' );
		$instance['url'] = strip_tags( $new_instance['url'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 
				'title' => __( 'ChirpHub Status', 'chirphub' ), 
				'text_prefix' => __( 'We are $message as of $day_of_week $time.', 'chirphub' ), 
				'url' => '' 
				);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- widget title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:', 'hybrid' ); ?>
			</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" 
			       name="<?php echo $this->get_field_name( 'title' ); ?>" 
			       value="<?php echo $instance['title']; ?>" 
			       style="width:100%;" />
		</p>

		<!-- text_prefix: Text Input (Also known as "status format" -->
		<p>
			<label for="<?php echo $this->get_field_id( 'text_prefix' ); ?>">
				<?php _e( 'Status message format:', 'chirphub' ); ?>
			</label>
			<textarea rows="3" cols="50"  
				id="<?php echo $this->get_field_id( 'text_prefix' ); ?>" 
				name="<?php echo $this->get_field_name( 'text_prefix' ); ?>" 
				style="width:100%;"><?php echo $instance['text_prefix']; ?></textarea>

		</p>

		<!-- url: Text Input (Also known as "device" -->
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>">
				<?php _e( 'ChirpHub device ID or URL:', 'chirphub' ); ?>
			</label> 
			<input id="<?php echo $this->get_field_id( 'url' ); ?>" 
			       name="<?php echo $this->get_field_name( 'url' ); ?>" 
			       value="<?php echo $instance['url']; ?>" 
			       style="width:100%;" />
		</p>

	<?php
	}
}

?>
