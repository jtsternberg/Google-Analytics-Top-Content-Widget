<?php

/**
 * Top Content widget
 */
class Dsgnwrks_Google_Top_Posts_Widgets extends WP_Widget {

	protected $gatc = null;

	public function __construct() {

		$this->gatc = GA_Top_Content::get_instance();

		parent::__construct( 'Dsgnwrks_Google_Top_Posts_Widgets', 'Google Analytics Top Content', array(
			'classname' => 'google_top_posts',
			'description' => 'Show top posts from Google Analytics',
		) );
	}

	 //build the widget settings form
	public function form( $instance ) {

		if ( ! class_exists( 'Yoast_Google_Analytics' ) ) {
			echo $this->gatc->message_one();
			echo '<style type="text/css"> #widget-'. $this->id .'-savewidget { display: none !important; } </style>';
			return;

		}

		if ( ! $this->gatc->id() ) {
			echo $this->gatc->message_two();
			echo '<style type="text/css"> #widget-'. $this->id .'-savewidget { display: none !important; } </style>';
			return;
		}

		$instance = wp_parse_args( (array) $instance, $this->gatc->defaults );
		extract( $instance, EXTR_SKIP );
		$instance['contentfilter'] = is_string( $instance['contentfilter'] ) ? explode( ',', $instance['contentfilter'] ) : (array) $instance['contentfilter'];

		?>
		<p><label><b>Title:</b><input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>"  type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>

		<p><label><b>Show pages with at least __ number of page views:</b> <input class="widefat" name="<?php echo $this->get_field_name( 'pageviews' ); ?>"  type="text" value="<?php echo absint( $pageviews ); ?>" /></label></p>

		<p><label><b>Number to Show:</b> <input class="widefat" name="<?php echo $this->get_field_name( 'number' ); ?>"  type="text" value="<?php echo absint( $number ); ?>" /></label></p>

		<p><label><b>Select how far back you would like analytics to pull from:</b>

			<div class="timestamp-wrap">
				<?php

				echo '<select style="margin-right: 5px;" id="'. $this->get_field_name( 'timeval' ) .'" name="'. $this->get_field_name( 'timeval' ) .'">';

				for ( $i = 1; $i <= 30; $i = $i + 1 ) {
					echo '<option value="'. $i .'"';
					echo selected( $i, $instance['timeval'], false );
					echo '>' . $i;
					echo '</option>';
				}

				echo '</select>';

				echo '<select style="width: 50%;" id="'. $this->get_field_name( 'time' ) .'" name="'. $this->get_field_name( 'time' ) .'">';

				echo '<option value="3600"', selected( '3600', $time ) ,'>hour(s)</option>';
				echo '<option value="86400"', selected( '86400', $time ) ,'>day(s)</option>';
				echo '<option value="2628000"', selected( '2628000', $time ) ,'>month(s)</option>';
				echo '<option value="31536000"', selected( '31536000', $time ) ,'>year(s)</option>';

				echo '</select>';
				?>
			</div>
		</label></p>

		<p><label>
			<span style="width: 80%; float: left; margin-right: 10px;"><b>Remove home page from list:</b> (usually "<i>yoursite.com</i>" is the highest viewed page)<br/></span>
			<input style="margin-top: 15px;" id="<?php echo $this->get_field_id( 'showhome' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'showhome' ); ?>" value="1" <?php checked( 1, $showhome ); ?>/>
		</label></p>

		<p style="clear: both; padding-top: 15px;"><label><b>Remove Site Title From Listings:</b><br/>Your listings will usually be returned with the page/post name as well as the site title. To remove the site title from the listings, place the exact text you would like removed (i.e. <i>- Site Title</i>) here. If there is more than one phrase to be removed, separate them by commas (i.e. <i>- Site Title, | Site Title</i>). <b>Unless your site doesn't output the site titles, then you will need to add this in order for the filter settings below to work.</b> <input class="widefat" style="margin-top:2px;" name="<?php echo $this->get_field_name( 'titleremove' ); ?>"  type="text" value="<?php echo esc_attr( $titleremove ); ?>" /></label></p>

		<p><b>Limit Listings To:</b></p>
		<label><input id="<?php echo $this->get_field_id( 'contentfilter' ); ?>-allcontent" type="checkbox" name="<?php echo $this->get_field_name( 'contentfilter' ); ?>[]" value="1" <?php checked( in_array( 'allcontent', $instance['contentfilter'] ) ); ?>/> Not Limited</label><br>

		<?php

		$content_types = get_post_types( array( 'public' => true ) );
		foreach ( $content_types as $key => $value ) {
			if ( 'attachment' == $value ) {
				continue;
			}
			$selected_value = in_array( $key, $instance['contentfilter'] ) ? 'selected' : '';
			?>
			<label><input id="<?php echo $this->get_field_id( 'contentfilter' ) . '-' . $key; ?>" type="checkbox" name="<?php echo $this->get_field_name( 'contentfilter' ); ?>[]" value="<?php echo $key; ?>" <?php checked( in_array( $key, $instance['contentfilter'] ) ); ?>/> <?php echo $value; ?></label><br>
			<?php
		}
		?>


		<?php if ( 'allcontent' == $instance['contentfilter'] || 'post' == $instance['contentfilter'] ) { ?>

			<p><label><b>Limit Listings To Category:</b><br/>To limit to specific categories, place comma separated category ID's.<input class="widefat" style="margin-top:2px;" name="<?php echo $this->get_field_name( 'catlimit' ); ?>"  type="text" value="<?php echo esc_attr( $catlimit ); ?>" /></label></p>

			<p><label><b>Filter Out Category:</b><br/>To remove specific categories, place comma separated category ID's.<input class="widefat" style="margin-top:2px;" name="<?php echo $this->get_field_name( 'catfilter' ); ?>"  type="text" value="<?php echo esc_attr( $catfilter ); ?>" /></label></p>

		<?php } ?>

		<p><label><b>Filter Out Post/Page IDs:</b><br/>To remove specific posts/pages, place comma separated post/page ID's.<input class="widefat" style="margin-top:2px;" name="<?php echo $this->get_field_name( 'postfilter' ); ?>"  type="text" value="<?php echo esc_attr( $postfilter ); ?>" /></label></p>

		<p><label><b>Thumbnail Size:</b><br/>Optionally display a thumbnail next to the post title (if the post has a thumbnail).<br>
		<select style="margin-top:2px;max-width:100%;" name="<?php echo $this->get_field_name( 'thumb_size' ); ?>">
		<?php
		echo '<option value="" ', selected( empty( $instance['thumb_size'] ), true ) ,'>No Thumbnail</option>';
		foreach ( $this->get_image_size_options() as $size => $label ) {
			echo '<option value="', $size ,'" ', selected( ! empty( $instance['thumb_size'] ) && $size == $instance['thumb_size'] ) ,'>', $label ,'</option>';
		}
		?>
		</select>

		<p><label><b>Thumbnail Alignment:</b><br/>Will only apply if choosing a thumbnail size.<br>
		<select style="margin-top:2px;max-width:100%;" name="<?php echo $this->get_field_name( 'thumb_alignment' ); ?>">
			<option value="" <?php selected( empty( $instance['thumb_alignment'] ), true ); ?>>None</option>
			<option value="alignleft" <?php selected( $instance['thumb_alignment'], 'alignleft' ); ?>>Left Align</option>
			<option value="alignright" <?php selected( $instance['thumb_alignment'], 'alignright' ); ?>>Right Align</option>
			<option value="aligncenter" <?php selected( $instance['thumb_alignment'], 'aligncenter' ); ?>>Centered</option>
		</select>
		<?php

	}

	/**
	 * Get list of registered image sizes
	 *
	 * @since  1.5.6
	 *
	 * @return array  Array of image sizes
	 */
	public function get_image_size_options() {
		global $_wp_additional_image_sizes;

		$image_sizes = array();
		foreach ( get_intermediate_image_sizes() as $size ) {
			$size_name = $size;
			if ( array_key_exists( $size, $_wp_additional_image_sizes ) ) {
				$size_info = $_wp_additional_image_sizes[ $size ];
				$size_name = $size_info['width'] .' x '. $size_info['height'] .' &mdash; '. $size_name;

				if ( is_array( $size_info['crop'] ) ) {
					$size_name .= ' ('. implode( ', ', $size_info['crop'] ) .')';
				} elseif ( $size_info['crop'] ) {
					$size_name .= ' (cropped)';

				}
			}
			$image_sizes[ $size ] = $size_name;
		}

		return $image_sizes;
	}

	// save the widget settings
	public function update( $new_instance, $old_instance ) {
		$cleaned = $old_instance;

		$to_clean = array(
			'esc_attr' => array(
				'title',
				'time',
				'catlimit',
				'catfilter',
				'postfilter',
				'thumb_size',
				'thumb_alignment',
			),
			'absint' => array(
				'pageviews',
				'number',
				'showhome',
				'timeval',
			),
			'sanitize_text_field' => array(
				'titleremove',
			),
		);

		foreach ( $to_clean as $callback => $fields ) {
			foreach ( $fields as $field ) {
				$cleaned[ $field ] = $callback( isset( $new_instance[ $field ] ) ? $new_instance[ $field ] : '' );
			}
		}

		$cleaned['contentfilter'] = is_string( $new_instance['contentfilter'] ) ? explode( ',', $new_instance['contentfilter'] ) : (array) $new_instance['contentfilter'];

		$cleaned['contentfilter'] = in_array( 'allcontent', $cleaned['contentfilter'] )
			? array( 'allcontent' )
			: array_map( 'sanitize_text_field', $cleaned['contentfilter'] );


		$atts = shortcode_atts( $this->gatc->defaults, $cleaned, 'google_top_content' );
		$atts = apply_filters( 'gtc_atts_filter', $atts );
		$unique = md5( serialize( $atts ) );
		delete_transient( 'gtc-'. $this->number . $unique );

		return $cleaned;
	}

	// display the widget
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		};

		echo $this->gatc->top_content_shortcode( $instance, 'widget', $this->number );

		echo $args['after_widget'];

	}

}
