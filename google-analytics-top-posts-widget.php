<?php
/*
Plugin Name:  Google Analytics Top Content Widget
Description: Widget and shortcode to display top content according to Google Analytics. ("Google Analytics by Yoast" plugin required)
Plugin URI: http://j.ustin.co/yWTtmy
Author: Jtsternberg
Author URI: http://jtsternberg.com/about
Donate link: http://j.ustin.co/rYL89n
Version: 1.6.1
*/


add_action( 'widgets_init', 'dsgnwrks_register_google_top_posts_widget' );
/**
 * Register Top Content widgets
 */
function dsgnwrks_register_google_top_posts_widget() {
	register_widget( 'Dsgnwrks_Google_Top_Posts_Widgets' );
}

class GA_Top_Content {

	private $id = null;
	private $list_format = '';
	private $list_item_format = '';

	public function __construct() {

		$this->defaults = array(
			'title'           => 'Top Viewed Content',
			'pageviews'       => 20,
			'number'          => 5,
			'timeval'         => '1',
			'time'            => '2628000',
			'showhome'        => 0,
			'titleremove'     => '',
			'contentfilter'   => '',
			'catlimit'        => '',
			'catfilter'       => '',
			'thumb_size'      => '',
			'thumb_alignment' => '',
			'postfilter'      => '',
			'update'          => false,
		);

		require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';
		add_action( 'tgmpa_register', array( $this, 'register_required_plugins' ) );

		add_filter( 'tgmpa_complete_link_text', array( $this, 'change_link_text' ) );
		add_filter( 'tgmpa_complete_link_url', array( $this, 'change_link_url' ) );

		// Top Content Shortcode
		add_shortcode( 'google_top_content', array( $this, 'top_content_shortcode' ) );
		add_shortcode( 'google_analytics_views', array( $this, 'views_shortcode' ) );

	}

	/**
	 * Register the required plugins for The "Google Analytics Top Content" plugin.
	 */
	public function register_required_plugins() {

		$plugins = array(
			array(
				'name'     => 'Google Analytics by Yoast',
				'slug'     => 'google-analytics-for-wordpress',
				'required' => true,
			),
		);

		$widgets_url = '<a href="' . get_admin_url( '', 'widgets.php' ) . '" title="' . __( 'Setup Widget', 'top-google-posts' ) . '">' . __( 'Setup Widget', 'top-google-posts' ) . '</a>';

		$config = array(
			'domain'           => 'top-google-posts',
			'default_path'     => '',
			'parent_menu_slug' => 'plugins.php',
			'parent_url_slug'  => 'plugins.php',
			'menu'             => 'install-required-plugins',
			'has_notices'      => true,
			'is_automatic'     => true,
			'message'          => '',
			'strings'          => array(
				'page_title'                      => __( 'Install Required Plugins', 'top-google-posts' ),
				'menu_title'                      => __( 'Install Plugins', 'top-google-posts' ),
				'installing'                      => __( 'Installing Plugin: %s', 'top-google-posts' ), // %1$s = plugin name
				'oops'                            => __( 'Something went wrong with the plugin API.', 'top-google-posts' ),
				'notice_can_install_required'     => _n_noop( 'The "Google Analytics Top Content" plugin requires the following plugin: %1$s.', 'This plugin requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
				'notice_can_install_recommended'  => _n_noop( 'This plugin recommends the following plugin: %1$s.', 'This plugin recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
				'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
				'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
				'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
				'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
				'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this plugin: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this plugin: %1$s.' ), // %1$s = plugin name(s)
				'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
				'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
				'activate_link'                   => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
				'return'                          => __( 'Return to Required Plugins Installer', 'top-google-posts' ),
				'plugin_activated'                => __( 'Plugin activated successfully.', 'top-google-posts' ),
				'complete'                        => __( 'All plugins installed and activated successfully. %s', 'top-google-posts' ) // %1$s = dashboard link
			)
		);

		tgmpa( $plugins, $config );

	}

	public function message_one() {
		return sprintf(
			'<p><strong>%s</strong></p><p><a href="%s" class="thickbox" title="%s">%s</a> | <a href="%s" class="thickbox" title="%s">%s</a>.</p>',
			sprintf( __( 'The "Google Analytics Top Content" widget requires the plugin, %s, to be installed and activated.', 'gatpw' ), '<em>"Google Analytics by Yoast"</em>' ),
			admin_url( 'plugins.php?page=install-required-plugins' ),
			__( 'Install Google Analytics by Yoast', 'gatpw' ),
			__( 'Install plugin', 'gatpw' ),
			admin_url( 'plugins.php' ),
			__( 'Activate Google Analytics by Yoast', 'gatpw' ),
			__( 'Activate plugin', 'gatpw' )
		);
	}

	public function message_two() {
		return sprintf(
			'<p>%s</p><p><a href="%s">%s</a>.</p>',
			__( 'You must first login to Google Analytics in the "Google Analytics by Yoast" settings for this widget to work.', 'gatpw' ),
			admin_url( 'admin.php?page=yst_ga_settings' ),
			__( 'Go to plugin settings', 'gatpw' )
		);
	}

	public function change_link_text( $complete_link_text ) {
		return __( 'Go to "Google Analytics by Yoast" plugin settings', 'gatpw' );
	}

	public function change_link_url( $complete_link_url ) {
		return admin_url( 'admin.php?page=yst_ga_settings' );
	}

	public function top_content_shortcode( $atts, $context = 'shortcode', $number = 0 ) {
		static $inline_style = false;
		static $inline_style_done = false;

		if ( ! class_exists( 'Yoast_Google_Analytics' ) ) {
			return $this->message_one();
		}
		if ( ! $this->id() ) {
			return $this->message_two();
		}

		$atts = shortcode_atts( $this->defaults, $atts, 'google_top_content' );
		$atts = apply_filters( 'gtc_atts_filter', $atts );
		$unique = md5( serialize( $atts ) );
		$trans_id = 'gtc-'. $number . $unique;

		$trans = '';
		// @Dev
		// $atts['update'] = true;
		if ( empty( $atts['update'] ) && ! isset( $_GET['delete-trans'] ) ) {
			$trans = get_transient( $trans_id );
			$transuse = "\n<!-- using transient - {$trans_id} -->\n";
		}

		if ( ! empty( $trans ) ) {
			return $transuse . apply_filters( 'gtc_cached_list_output', $trans ) . $transuse;
		}

		$transuse = "\n<!-- not using transient - {$trans_id} -->\n";

		$time = ( $atts['timeval'] * $atts['time'] );
		$time_diff = abs( time() - $time );

		if ( strpos( $atts['time'], 'month' ) ) {
			$time = str_replace( '-month', '', $atts['time'] );
			$month = $time * 60 * 60 * 24 * 30.416666667;
			$time_diff = abs( time() - $month );
		}

		$atts['context'] = ( $context ) ? $context : 'shortcode';

		$params = array(
			'ids'         => 'ga:'. $this->id(),
			'start-date'  => date( 'Y-m-d', $time_diff ),
			'end-date'    => date( 'Y-m-d' ),
			'dimensions'  => 'ga:pageTitle,ga:pagePath',
			'metrics'     => 'ga:pageViews',
			'sort'        => '-ga:pageviews',
			'filters'     => urlencode( 'ga:pageviews>' . $atts['pageviews'] ),
			'max-results' => 100,
		);

		$pages = $this->parse_list_response( $this->make_request( $params, 'top_content_'. $atts['context'] ) );

		$pages = apply_filters( 'gtc_pages_filter', $pages, $atts );

		$list = '';
		if ( ! $pages ) {
			return $list;
		}

		$urlarray = array();
		$counter = 1;

		foreach ( $pages as $page ) {
			$url = $page['path'];
			// Url is index and we don't want the homepage, skip
			if ( $url == '/' && ! in_array( $atts['showhome'], array( 'no', '0', 0, false ), true ) ) {
				continue;
			}

			// We need to check if there are duplicates with query vars
			$path = pathinfo( $url );
			$query_var = strpos( $url, '?' );
			$default_permalink = strpos( $path['filename'], '?p=' );
			// Strip the query var off the url (if not using default permalinks)
			$url = ( ! ( isset( $atts['keep_query_vars'] ) && $atts['keep_query_vars'] ) && false !== $query_var && false === $default_permalink )
				? substr( $url, 0, $query_var )
				: $url;

			// Allow modification of the URL
			$url = apply_filters( 'gtc_page_url', $url );

			// Url already exists? skip it
			if ( in_array( $url, $urlarray ) ) {
				continue;
			}

			$urlarray[] = $url;
			$wppost = null;
			$thumb = '';

			if ( 'allcontent' != $atts['contentfilter'] || '' != $atts['catlimit'] || '' != $atts['catfilter'] || '' != $atts['postfilter'] || ! empty( $atts['thumb_size'] ) ) {

				if ( false !== $default_permalink ) {
					$wppost = get_post( (int) str_replace( '?p=', '', $path['filename'] ) );
				}
				if ( ! $wppost && ! empty( $url ) && trim( $url ) != '/' ) {
					$content_types = get_post_types( array( 'public' => true ) );

					foreach ( $content_types as $type ) {
						if ( 'attachment' == $type ) {
							continue;
						}

						$object_name = is_post_type_hierarchical( $type ) ? $url : @end( @array_filter( @explode( '/', $url ) ) );

						if ( $wppost = get_page_by_path( $object_name, OBJECT, $type ) ) {
							break;
						}
					}
				}

				if ( $atts['contentfilter'] && 'allcontent' != $atts['contentfilter'] ) {
					if ( empty( $wppost ) ) {
						continue;
					}
					if ( $wppost->post_type != $atts['contentfilter'] ) {
						continue;
					}
				}

				if ( 'allcontent' == $atts['contentfilter'] || 'post' == $atts['contentfilter'] ) {

					if ( $atts['catlimit'] != '' ) {
						$limit_array = array();
						$catlimits = esc_attr( $atts['catlimit'] );
						$catlimits = explode( ', ', $catlimits );
						foreach ( $catlimits as $catlimit ) {
							if ( in_category( $catlimit, $wppost ) ) {
								$limit_array[] = $wppost->ID;
							}
						}
						if ( ! in_array( $wppost->ID, $limit_array ) ) {
							continue;
						}
					}

					if ( $atts['catfilter'] != '' ) {
						$filter_array = array();
						$catfilters = esc_attr( $atts['catfilter'] );
						$catfilters = explode( ', ', $catfilters );
						foreach ( $catfilters as $catfilter ) {
							if ( in_category( $catfilter, $wppost ) ) {
								$filter_array[] = $wppost->ID;
							}
						}
						if ( in_array( $wppost->ID, $filter_array ) ) {
							continue;
						}
					}
				}

				if ( $atts['postfilter'] != '' ) {
					$postfilter_array = array();
					$postfilters = esc_attr( $atts['postfilter'] );
					$postfilters = explode( ', ', $postfilters );
					foreach ( $postfilters as $postfilter ) {
						if ( $postfilter == $wppost->ID ) {
							$postfilter_array[] = $wppost->ID;
						}
					}
					if ( in_array( $wppost->ID, $postfilter_array ) ) {
						continue;
					}
				}
			}

			$title = stripslashes( wp_filter_post_kses( apply_filters( 'gtc_page_title', $page['name'], $page, $wppost ) ) );

			if ( ! empty( $atts['thumb_size'] ) && $wppost && isset( $wppost->ID ) ) {
				$class = 'attachment-'. $atts['thumb_size'] .' wp-post-image';
				if ( ! empty( $atts['thumb_alignment'] ) ) {
					$class .= ' '. $atts['thumb_alignment'];
					$inline_style = true;
				}
				$thumb = get_the_post_thumbnail( $wppost->ID, $atts['thumb_size'], array( 'class' => $class ) );
				$thumb = apply_filters( 'gtc_list_item_thumb', '<a class="gtc-content-thumb" href="'. $url .'">' . $thumb . '</a>', $url, $thumb, $wppost, $counter, $title, $url, $atts );
			}

			if ( ! empty( $atts['titleremove'] ) ) {
				$removes = explode( ',', sanitize_text_field( $atts['titleremove'] ) );
				foreach ( $removes as $remove ) {
					$title = str_ireplace( trim( $remove ), '', $title );
				}
			}

			$list .= apply_filters( 'gtc_list_item', sprintf( $this->list_item_format(), $thumb, $url, $title ), $page, $wppost, $counter, $title, $url );

			$counter++;

			if ( $counter > $atts['number'] ) {
				break;
			}
		}

		$list = sprintf( $this->list_format(), $list );

		if ( $inline_style && ! $inline_style_done ) {
			$list = '<style type="text/css">.gtc-list li:after{content:"";display: block;clear:both;}</style>'. $list;
			$inline_style_done = true;
		}

		$list = apply_filters( 'gtc_list_output', $list );

		set_transient( $trans_id, $list, DAY_IN_SECONDS );

		return $transuse . $list . $transuse;
	}

	public function views_shortcode( $atts, $content ) {

		if ( ! $this->id() || ! class_exists( 'Yoast_Google_Analytics' ) ) {
			return '';
		}

		$defaults = array(
			'post_id' => get_the_ID(),
			'start_date' => date( 'Y-m-d', time() - ( DAY_IN_SECONDS * 30 ) ),
			'end_date' => date( 'Y-m-d' ),
		);
		$atts = shortcode_atts( $defaults, $atts, 'google_analytics_views' );
		$atts = apply_filters( 'gtc_atts_filter_analytics_views', $atts );
		$atts['post_id'] = absint( $atts['post_id'] );
		$unique = md5( serialize( $atts ) );
		$trans_id = 'gtc-' . $atts['post_id'] . $unique;

		$count = 0;
		// @Dev
		// $atts['update'] = true;
		if ( empty( $atts['update'] ) && ! isset( $_GET['delete-trans'] ) ) {
			$count = get_transient( $trans_id );
			$transuse = "\n<!-- using transient - {$trans_id} -->\n";
		}

		if ( empty( $count ) ) {
			$transuse = "\n<!-- not using transient - {$trans_id} -->\n";

			$permalink = get_permalink( $atts['post_id'] );
			$url_data  = parse_url( $permalink );
			$link_uri  = substr( $url_data['path'] . ( isset( $url_data['query'] ) ? ( '?' . $url_data['query'] ) : '' ), -20 );

			if ( empty( $link_uri ) || 'draft' == get_post_status( $atts['post_id'] ) ) {
				return '';
			}

			$params = array(
				'ids'         => 'ga:'. $this->id(),
				'dimensions'  => 'ga:pageTitle,ga:pagePath',
				'metrics'     => 'ga:pageViews',
				'filters'     => urlencode( 'ga:pagePath=~' . $link_uri . '.*' ),
				'max-results' => 100,
				'start-date'  => $atts['start_date'],
				'end-date'    => $atts['end_date'],
			);

			$data = $this->make_request( $params, 'views_shortcode' );

			$count = 0;

			if ( isset( $data['totalsForAllResults'] ) && is_array( $data['totalsForAllResults'] ) ) {
				$count = reset( array_values( $data['totalsForAllResults'] ) );
			} elseif ( isset( $data['rows'] ) && is_array( $data['rows'] ) ) {
				foreach ( $data['rows'] as $row ) {
					$count = $count + $row[2];
				}
			}

			if ( $count ) {
				set_transient( $trans_id, $count, DAY_IN_SECONDS );
			}
		}

		if ( ! $count ) {
			return;
		}

		if ( $content ) {
			$clean_content = wp_kses_post( $content );
			// tildes because double-asterisks are associated with bold in markdown
			$replacements = array( '**count**', '~~count~~' );
			$replaced_content = str_ireplace( $replacements, '<span class="gtc-count">'. $count .'</span>', $clean_content );
			return $transuse . $replaced_content . $transuse;
		}

		return $count;

	}

	public function id() {
		if ( is_null( $this->id ) ) {
			$options = Yoast_GA_Options::instance()->options;
			$this->id = isset( $options['analytics_profile'] ) ? $options['analytics_profile'] : '';
		}

		return $this->id;
	}

	public function list_format() {
		if ( ! $this->list_format ) {
			$this->list_format = apply_filters( 'gtc_list_format', '<ol class="gtc-list">%1$s</ol>' );
		}

		return $this->list_format;
	}

	public function list_item_format() {
		if ( ! $this->list_item_format ) {
			$this->list_item_format = apply_filters( 'gtc_list_item_format', '<li>%1$s<a class="gtc-link" href="%2$s">%3$s</a></li>' );
		}

		return $this->list_item_format;
	}

	public function make_request( $params, $context = '' ) {
		if ( ! class_exists( 'Yoast_Google_Analytics' ) ) {
			return;
		}

		if ( ! is_admin() ) {
			$path = dirname( GAWP_FILE );
			$files_to_include = array(
				'Yoast_Google_CacheParser'           => '/vendor/yoast/api-libs/google/io/Google_CacheParser.php',
				'Yoast_Google_Utils'           => '/vendor/yoast/api-libs/google/service/Google_Utils.php',
				'Yoast_Google_HttpRequest'           => '/vendor/yoast/api-libs/google/io/Google_HttpRequest.php',
				'Yoast_Google_IO'           => '/vendor/yoast/api-libs/google/io/Google_IO.php',
				'Yoast_Google_WPIO'           => '/vendor/yoast/api-libs/google/io/Google_WPIO.php',
				'Yoast_Google_Auth'           => '/vendor/yoast/api-libs/google/auth/Google_Auth.php',
				'Yoast_Google_OAuth2'           => '/vendor/yoast/api-libs/google/auth/Google_OAuth2.php',
				'Yoast_Google_Cache'           => '/vendor/yoast/api-libs/google/cache/Google_Cache.php',
				'Yoast_Google_WPCache'           => '/vendor/yoast/api-libs/google/cache/Google_WPCache.php',
				'Yoast_Google_Client'           => '/vendor/yoast/api-libs/google/Google_Client.php',
				'Yoast_Google_Analytics_Client' => '/vendor/yoast/api-libs/googleanalytics/class-google-analytics-client.php',
			);
			foreach ( $files_to_include as $class => $file ) {
				require_once $path . $file;
			}
		}

		$params = apply_filters( 'gtc_analytics_request_params', $params );

		if ( $context && is_scalar( $context ) ) {
			$params = apply_filters( "gtc_analytics_{$context}_request_params", $params );
		}

		$response = Yoast_Google_Analytics::get_instance()->do_request( add_query_arg( $params, 'https://www.googleapis.com/analytics/v3/data/ga' ) );

		return isset( $response['response']['code'] ) && 200 == $response['response']['code']
			? wp_remote_retrieve_body( $response )
			: false;
	}

	public function parse_list_response( $body ) {
		$data = array();

		if ( ! isset( $body['rows'] ) || ! is_array( $body['rows'] ) ) {
			return false;
		}

		$data = array();
		foreach ( $body['rows'] as $key => $item ) {
			$data[] = $this->parse_data_row( $item );
		}

		return $data;
	}

	/**
	 * Parse a row for the list storage type
	 *
	 * @param $item
	 *
	 * @return array
	 */
	private function parse_data_row( $item ) {
		return array(
			'name'  => (string) $item[0],
			'path'  => (string) $item[1],
			'value' => (int) $item[2],
		);
	}

}


/**
 * Top Content widget
 */
class Dsgnwrks_Google_Top_Posts_Widgets extends WP_Widget {

	public function __construct() {
		$this->gatc = new GA_Top_Content();

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

		<p><label>
		<b>Limit Listings To:</b>
		<select name="<?php echo $this->get_field_name( 'contentfilter' ); ?>">
		<?php
		echo '<option value="allcontent" '. selected( esc_attr( $instance['contentfilter'] ), '' ) .'>Not Limited</option>';

		$content_types = get_post_types( array( 'public' => true ) );
		foreach ( $content_types as $key => $value ) {
			if ( 'attachment' == $value ) {
				continue;
			}
			$selected_value = esc_attr( $instance['contentfilter'] ) == $key ? 'selected' : '';
			echo "<option value='". esc_attr( $key ) ."' $selected_value>$value</option>";
		}
		?>
		</select>

		</label>
		</p>

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
				'contentfilter',
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
