<?php

class GA_Top_Content {

	protected static $single_instance = null;
	private $id = null;
	private $list_format = '';
	private $list_item_format = '';
	private $item = array();
	private $counter = 0;

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return GA_Top_Content A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	protected function __construct() {

		$this->defaults = array(
			'title'           => __( 'Top Viewed Content', 'google-analytics-top-content' ),
			'pageviews'       => 20,
			'number'          => 5,
			'timeval'         => '1',
			'time'            => '2628000',
			'showhome'        => 0,
			'titleremove'     => '',
			'contentfilter'   => array(),
			'catlimit'        => '',
			'catfilter'       => '',
			'thumb_size'      => '',
			'thumb_alignment' => '',
			'postfilter'      => '',
			'update'          => false,
		);

		// only do TGM Plugin Activation if we don't already have MonsterInsights Google Analytics active
		if ( ! defined( 'GAWP_VERSION' ) ) {
			require_once GATC_DIR . 'vendor/tgm-plugin-activation/class-tgm-plugin-activation.php';
			add_action( 'tgmpa_register', array( $this, 'register_required_plugins' ) );

			add_filter( 'tgmpa_complete_link_text', array( $this, 'change_link_text' ) );
			add_filter( 'tgmpa_complete_link_url', array( $this, 'change_link_url' ) );
		}

		// Top Content Shortcode
		add_shortcode( 'google_top_content', array( $this, 'top_content_shortcode' ) );
		add_shortcode( 'google_analytics_views', array( $this, 'views_shortcode' ) );

		// Flush shortcode cache
		add_action( 'save_post', array( $this, 'flush_on_save' ), 10, 2 );
	}

	public function flush_on_save( $post_id, $post ) {

		if (
			defined( 'DOING_AJAX' )
			|| defined( 'DOING_AUTOSAVE' )
			|| wp_is_post_revision( $post_id )
			|| wp_is_post_autosave( $post_id )
			|| ! current_user_can( 'edit_pages' )
		) {
			return;
		}

		$has_top_content = false !== strpos( $post->post_content, '[google_top_content' );
		$has_views       = false !== strpos( $post->post_content, '[google_analytics_views' );

		if ( ! $has_top_content && ! $has_views ) {
			return;
		}

		add_filter( 'gtc_atts_filter', array( $this, 'set_flush_attribute' ) );
		add_filter( 'gtc_atts_filter_analytics_views', array( $this, 'set_flush_attribute' ) );

		// Cause shortcodes to be run
		do_shortcode( $post->post_content );
	}

	public function set_flush_attribute( $atts ) {
		$atts['update'] = true;
		return $atts;
	}

	/**
	 * Register the required plugins for The "Google Analytics Top Content" plugin.
	 */
	public function register_required_plugins() {

		$plugins = array(
			array(
				'name'     => 'Google Analytics for WordPress by MonsterInsights',
				'slug'     => 'google-analytics-for-wordpress',
				'required' => true,
			),
		);

		$widgets_url = '<a href="' . get_admin_url( '', 'widgets.php' ) . '" title="' . __( 'Setup Widget', 'google-analytics-top-content' ) . '">' . __( 'Setup Widget', 'google-analytics-top-content' ) . '</a>';

		$config = array(
			'domain'           => 'google-analytics-top-content',
			'default_path'     => '',
			'parent_slug'      => 'plugins.php',
			'capability'       => 'install_plugins',
			'menu'             => 'install-required-plugins',
			'has_notices'      => true,
			'is_automatic'     => true,
			'message'          => '',
			'strings'          => array(
				'page_title'                      => __( 'Install Required Plugins', 'google-analytics-top-content' ),
				'menu_title'                      => __( 'Install Plugins', 'google-analytics-top-content' ),
				'installing'                      => __( 'Installing Plugin: %s', 'google-analytics-top-content' ), // %1$s = plugin name
				'oops'                            => __( 'Something went wrong with the plugin API.', 'google-analytics-top-content' ),
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
				'return'                          => __( 'Return to Required Plugins Installer', 'google-analytics-top-content' ),
				'plugin_activated'                => __( 'Plugin activated successfully.', 'google-analytics-top-content' ),
				'complete'                        => __( 'All plugins installed and activated successfully. %s', 'google-analytics-top-content' ) // %1$s = dashboard link
			)
		);

		tgmpa( $plugins, $config );

	}

	public function message_one() {
		return sprintf(
			'<p><strong>%s</strong></p><p><a href="%s" class="thickbox" title="%s">%s</a> | <a href="%s" class="thickbox" title="%s">%s</a>.</p>',
			sprintf( __( 'The "Google Analytics Top Content" widget requires the plugin, %s, to be installed and activated.', 'google-analytics-top-content' ), '<em>"Google Analytics for WordPress by MonsterInsights"</em>' ),
			admin_url( 'plugins.php?page=install-required-plugins' ),
			__( 'Install Google Analytics for WordPress by MonsterInsights', 'google-analytics-top-content' ),
			__( 'Install plugin', 'google-analytics-top-content' ),
			admin_url( 'plugins.php' ),
			__( 'Activate Google Analytics for WordPress by MonsterInsights', 'google-analytics-top-content' ),
			__( 'Activate plugin', 'google-analytics-top-content' )
		);
	}

	public function message_two() {
		$url = sprintf(
			'<p>%s</p><p><a href="%s">%s</a>.</p>',
			__( 'You must first authenticate to Google Analytics in the "Google Analytics for WordPress by MonsterInsights" settings for this widget to work.', 'google-analytics-top-content' ),
			admin_url( 'admin.php?page=yst_ga_settings' ),
			__( 'Go to plugin settings', 'google-analytics-top-content' )
		);
		if ( defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			$url = sprintf(
				'<p>%s</p><p><a href="%s">%s</a>.</p>',
				__( 'You must first authenticate to Google Analytics in the "Google Analytics for WordPress by MonsterInsights" settings for this widget to work.', 'google-analytics-top-content' ),
				admin_url( 'admin.php?page=monsterinsights_settings' ),
				__( 'Go to plugin settings', 'google-analytics-top-content' )
			);
		}

		return $url;
	}

	public function change_link_text( $complete_link_text ) {
		return __( 'Go to "Google Analytics for WordPress by MonsterInsights" plugin settings', 'google-analytics-top-content' );
	}

	public function change_link_url( $complete_link_url ) {
		$page = defined( 'MONSTERINSIGHTS_VERSION' ) ? 'monsterinsights_settings' : 'yst_ga_settings';
		return admin_url( 'admin.php?page=' . $page );
	}

	public function top_content_shortcode( $atts = array(), $context = 'shortcode', $number = 0 ) {
		static $inline_style = false;
		static $inline_style_done = false;

		if ( ! defined( 'GAWP_VERSION' ) ) {
			return $this->message_one();
		}

		if ( ! $this->id() ) {
			return $this->message_two();
		}

		$atts = shortcode_atts( $this->defaults, $atts, 'google_top_content' );
		$atts = apply_filters( 'gtc_atts_filter', $atts );

		$atts['contentfilter'] = is_string( $atts['contentfilter'] ) ? explode( ',', $atts['contentfilter'] ) : (array) $atts['contentfilter'];

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

		// $urlarray = array();
		$this->counter = 1;

		foreach ( $pages as $this->item ) {

			$this->item['post'] = null;

			$should_display = apply_filters( 'gtc_should_display_item', $this->should_display_item( $atts ), $this->item, $atts );

			// This is not the page you are looking for.
			if ( false === $should_display ) {
				continue;
			}

			$title      = $this->get_title();
			$thumb_info = $this->get_thumb_info( $atts, $title );

			$thumb = isset( $thumb_info['thumb'] ) ? $thumb_info['thumb'] : '';

			if ( isset( $thumb_info['inline_style'] ) && $thumb_info['inline_style'] ) {
				$inline_style = true;
			}

			$title     = $this->maybe_clean_title( $atts, $title );
			$list_item = sprintf( $this->list_item_format(), $thumb, $this->item['url'], $title, $this->counter );

			$list_item = apply_filters( 'gtc_list_item', $list_item, $this->item, $this->item['post'], $this->counter, $title, $this->item['url'], $thumb );

			if ( ! $list_item ) {
				continue;
			}

			$list .= $list_item;

			$this->counter++;

			if ( $this->counter > $atts['number'] ) {
				break;
			}
		}

		$list = sprintf( $this->list_format(), $list );

		if ( $inline_style && ! $inline_style_done ) {
			$list = '<style type="text/css">.gtc-list li:after{content:"";display: block;clear:both;}</style>'. $list;
			$inline_style_done = true;
		}

		$list = apply_filters( 'gtc_list_output', $list, $atts );

		$cache_expiration = apply_filters( 'gtc_top_content_shortcode_cache_expiration', DAY_IN_SECONDS );
		set_transient( $trans_id, $list, $cache_expiration );

		return $transuse . $list . $transuse;
	}

	protected function should_display_item( $atts ) {
		static $urlarray = array();

		$url = $this->item['path'];

		if ( ! $this->should_show_home( $atts ) ) {
			// Url is index (or paginated) and we don't want the homepage, skip
			if ( '/' === $url || preg_match( '~^\/[1-9]+\/?$~', $url ) ) {
				return false;
			}
		}

		// We need to check if there are duplicates with query vars
		$path = pathinfo( $url );
		$query_var = strpos( $url, '?' );
		$is_default_permalink = false !== strpos( $path['filename'], '?p=' );

		// Strip the query var off the url (if not using default permalinks)
		$url = ! $this->has( $atts, 'keep_query_vars' ) && false !== $query_var && ! $is_default_permalink
			? substr( $url, 0, $query_var )
			: $url;

		// Allow modification of the URL
		$url = apply_filters( 'gtc_page_url', $url );

		// Url already exists? skip it
		if ( in_array( $url, $urlarray ) ) {
			return false;
		}

		$urlarray[] = $url;
		$this->item['url'] = $url;
		$this->item['post'] = null;

		// If any filters were requested which will require looking up the WP Post object...
		if (
			! in_array( 'allcontent', $atts['contentfilter'] )
			|| '' != $atts['catlimit']
			|| '' != $atts['catfilter']
			|| '' != $atts['postfilter']
			|| ! empty( $atts['thumb_size'] )
		) {
			$post = $this->get_wp_post_object( $this->item['url'], $is_default_permalink, $path );

			$this->item['post'] = $this->maybe_filter_by_wp_post_object( $post, $atts );

			if ( false === $this->item['post'] ) {
				return false;
			}
		}

		return true;
	}

	protected function get_title() {
		return stripslashes( wp_filter_post_kses( apply_filters( 'gtc_page_title', $this->item['name'], $this->item, $this->item['post'] ) ) );
	}

	protected function maybe_clean_title( $atts, $title ) {
		if ( ! empty( $atts['titleremove'] ) ) {
			foreach ( explode( ',', sanitize_text_field( $atts['titleremove'] ) ) as $to_remove ) {
				$title = str_ireplace( trim( $to_remove ), '', $title );
			}
		}

		return $title;
	}

	protected function get_thumb_info( $atts, $title ) {
		static $inline_style = false;

		if ( empty( $atts['thumb_size'] ) || empty( $this->item['post']->ID ) ) {
			return '';
		}

		$class = 'attachment-'. $atts['thumb_size'] .' wp-post-image';
		$maybe_inline_style = false;

		if ( ! empty( $atts['thumb_alignment'] ) ) {
			$class .= ' '. $atts['thumb_alignment'];
			$maybe_inline_style = true;
		}

		$thumb = get_the_post_thumbnail( $this->item['post']->ID, $atts['thumb_size'], array( 'class' => $class ) );

		$thumb_link = '';
		if ( $thumb ) {
			$thumb_link = sprintf( '<a class="gtc-content-thumb" href="%s">%s</a>', $this->item['url'], $thumb );
			if ( $maybe_inline_style ) {
				$inline_style = true;
			}
		}

		$thumb = apply_filters( 'gtc_list_item_thumb', $thumb_link, $this->item['url'], $thumb, $this->item['post'], $this->counter, $title, $this->item['url'], $atts );

		return compact( 'thumb', 'inline_style' );
	}

	protected function maybe_filter_by_wp_post_object( $post, $atts ) {
		if ( isset( $post->post_status ) && 'publish' !== $post->post_status ) {
			return false;
		}

		if ( $atts['contentfilter'] && ! in_array( 'allcontent', $atts['contentfilter'] ) ) {
			if ( empty( $post ) ) {
				return false;
			}

			if ( ! in_array( $post->post_type, $atts['contentfilter'] ) ) {
				return false;
			}
		}

		if ( in_array( 'allcontent', $atts['contentfilter'] ) || in_array( 'post', $atts['contentfilter'] ) ) {
			if ( empty( $post ) ) {
				return false;
			}

			if ( '' != $atts['catlimit'] && ! $this->in_categories( $post, $atts['catlimit'] ) ) {
				return false;
			}

			if ( '' != $atts['catfilter'] && $this->in_categories( $post, $atts['catfilter'] ) ) {
				return false;
			}
		}

		if ( '' != $atts['postfilter'] ) {
			if ( empty( $post ) ) {
				return false;
			}

			$postfilters = array_map( 'absint', explode( ',', esc_attr( $atts['postfilter'] ) ) );

			if ( in_array( absint( $post->ID ), $postfilters, true ) ) {
				return false;
			}
		}

		return $post;
	}

	protected function in_categories( $post, $filter ) {
		$cats = array_map( 'trim', explode( ',', esc_attr( $filter ) ) );
		if ( empty( $cats ) ) {
			return false;
		}

		foreach ( $cats as $cat ) {
			if ( in_category( $cat, $post ) ) {
				return true;
			}
		}

		return false;
	}

	protected function should_show_home( $atts ) {
		return $this->is_in( $atts, 'show_home', array( '0', 0, 'yes', 'true', true ) );
	}

	protected function is_in( $atts, $var, $in ) {
		return isset( $atts[ $var ] ) && in_array( $atts[ $var ], $in, true );
	}

	protected function has( $atts, $var ) {
		return isset( $atts[ $var ] ) && $atts[ $var ] ? $atts[ $var ] : false;
	}

	public function get_wp_post_object( $url, $is_default_permalink, $path ) {
		$post_id = 0;

		// Check default permalinks
		if ( $is_default_permalink ) {
			$post_id = (int) str_replace( '?p=', '', $path['filename'] );
		}

		// Check if we can get a post ID from the url
		$post_id = $post_id ? $post_id : url_to_postid( $url );

		if ( empty( $post_id ) ) {
			return null;
		}

		// If we have a post ID, attempt to get the post object
		$wppost = get_post( $post_id );

		if ( ! empty( $wppost ) ) {
			return $wppost;
		}

		if ( empty( $url ) || '/' === trim( $url ) ) {
			return $wppost;
		}


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

		return $wppost;
	}

	public function views_shortcode( $atts = array(), $content = '' ) {

		if ( ! $this->id() || ! defined( 'GAWP_VERSION' ) ) {
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

			$link_uri  = urldecode( $url_data['path'] );
			if ( ! empty( $url_data['query'] ) ) {
				$link_uri .= '?' . $url_data['query'];
			}

			$link_uri  = function_exists( 'mb_substr' ) ? mb_substr( $link_uri, -20 ) : substr( $link_uri, -20 );

			if ( empty( $link_uri ) || 'draft' == get_post_status( $atts['post_id'] ) ) {
				return '';
			}

			$filters = sprintf( 'ga:pagePath=~%s.*', $link_uri );

			/**
			 * Build GA $filters param and allow filtering
			 *
			 * @var string
			 */
			$filters = apply_filters( 'gtc_views_shortcode_ga_filters_param', $filters, $atts, $link_uri );

			$params = array(
				'ids'         => 'ga:'. $this->id(),
				'dimensions'  => 'ga:pageTitle,ga:pagePath',
				'metrics'     => 'ga:pageViews',
				'filters'     => urlencode( $filters ),
				'max-results' => 100,
				'start-date'  => $atts['start_date'],
				'end-date'    => $atts['end_date'],
			);

			$data = $this->make_request( $params, 'views_shortcode' );

			$count = 0;

			if ( isset( $data['totalsForAllResults'] ) && is_array( $data['totalsForAllResults'] ) ) {
				$data  = array_values( $data['totalsForAllResults'] );
				$count = reset( $data );
			} elseif ( isset( $data['rows'] ) && is_array( $data['rows'] ) ) {
				foreach ( $data['rows'] as $row ) {
					$count = $count + $row[2];
				}
			}

			if ( $count ) {
				$cache_expiration = apply_filters( 'gtc_views_shortcode_cache_expiration', DAY_IN_SECONDS );
				set_transient( $trans_id, $count, $cache_expiration );
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
		if ( null === $this->id ) {
			if ( defined( 'MONSTERINSIGHTS_VERSION' ) ) {
				$this->id = monsterinsights_get_option( 'analytics_profile', false );
			} else {
				$options = Yoast_GA_Options::instance()->options;
				$this->id = isset( $options['analytics_profile'] ) ? $options['analytics_profile'] : '';
			}
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
		if ( ! defined( 'GAWP_VERSION' ) ) {
			trigger_error( 'GATC: ' . __( 'No requests can be made because Google Analytics Top Content Widget requires the Google Analytics for WordPress by MonsterInsights plugin to be installed and activated.', 'google-analytics-top-content' ), E_USER_WARNING );
			return;
		}

		$ga = $this->get_ga_intance();
		if ( empty( $ga ) ) {
			return false;
		}

		$params = apply_filters( 'gtc_analytics_request_params', $params );

		if ( $context && is_scalar( $context ) ) {
			$params = apply_filters( "gtc_analytics_{$context}_request_params", $params );
		}

		$response = $ga->do_request( add_query_arg( $params, 'https://www.googleapis.com/analytics/v3/data/ga' ) );

		return isset( $response['response']['code'] ) && 200 == $response['response']['code']
			? wp_remote_retrieve_body( $response )
			: false;
	}

	public function get_ga_intance() {
		if ( version_compare( GAWP_VERSION, '7.0.0' ) >= 0 ) {
			return false;
		}

		if ( function_exists( 'MonsterInsights' ) ) {
			$ga = MonsterInsights()->ga;

			if ( empty( $ga ) ) {
				// LazyLoad GA for Frontend
				require_once MONSTERINSIGHTS_PLUGIN_DIR . 'includes/admin/google.php';
				MonsterInsights()->ga = $ga = new MonsterInsights_GA();
			}

			return $ga;
		}

		static $files_to_include = null;

		if ( ! is_admin() && null === $files_to_include ) {
			$files_to_include = array(
				'Yoast_Google_CacheParser'      => '/vendor/yoast/api-libs/google/io/Google_CacheParser.php',
				'Yoast_Google_Utils'            => '/vendor/yoast/api-libs/google/service/Google_Utils.php',
				'Yoast_Google_HttpRequest'      => '/vendor/yoast/api-libs/google/io/Google_HttpRequest.php',
				'Yoast_Google_IO'               => '/vendor/yoast/api-libs/google/io/Google_IO.php',
				'Yoast_Google_WPIO'             => '/vendor/yoast/api-libs/google/io/Google_WPIO.php',
				'Yoast_Google_Auth'             => '/vendor/yoast/api-libs/google/auth/Google_Auth.php',
				'Yoast_Google_OAuth2'           => '/vendor/yoast/api-libs/google/auth/Google_OAuth2.php',
				'Yoast_Google_Cache'            => '/vendor/yoast/api-libs/google/cache/Google_Cache.php',
				'Yoast_Google_WPCache'          => '/vendor/yoast/api-libs/google/cache/Google_WPCache.php',
				'Yoast_Google_Client'           => '/vendor/yoast/api-libs/google/Google_Client.php',
				'Yoast_Google_Analytics_Client' => '/vendor/yoast/api-libs/googleanalytics/class-google-analytics-client.php',
			);

			if ( version_compare( GAWP_VERSION, '5.4.3' ) >= 0 ) {
				unset( $files_to_include['Yoast_Google_Analytics_Client'] );
				$files_to_include['Yoast_Api_Google_Client'] = '/vendor/yoast/api-libs/class-api-google-client.php';
			}

			$path = dirname( GAWP_FILE );

			foreach ( $files_to_include as $class => $file ) {
				if ( ! class_exists( $class, true ) ) {
					require_once $path . $file;
				}
			}
		}

		return Yoast_Google_Analytics::get_instance();
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
