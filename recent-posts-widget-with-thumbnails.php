<?php
/*
Plugin Name: Recent Posts Widget With Thumbnails
Plugin URI:  http://wordpress.org/plugins/recent-posts-widget-with-thumbnails/
Description: Small and fast plugin to display in the sidebar a list of linked titles and thumbnails of the most recent postings
Version:     3.0
Author:      Martin Stehle
Author URI:  http://stehle-internet.de
Text Domain: recent-posts-thumbnails
Domain Path: /languages
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

/**
 * Recent_Posts_Widget_With_Thumbnails widget class
 *
 * @since 1.0
 */
class Recent_Posts_Widget_With_Thumbnails extends WP_Widget {

	var $plugin_slug;  // identifier of this plugin for WP
	var $plugin_version; // number of current plugin version
	var $number_posts;  // number of posts to show in the widget
	var $default_thumb_dimensions;  // dimensions of the thumbnail
	var $default_thumb_width;  // custom width of the thumbnail
	var $default_thumb_height; // custom height of the thumbnail
	var $default_thumb_url; // URL of the default thumbnail
	var $default_excerpt_length; // number of chars of excerpt
	var $default_excerpt_more; // characters to indicate further text
	var $text_domain; // text domain of this plugin
	var $css_file_path; // path of the public css file

	function __construct() {
		switch ( get_locale() ) {
			case 'de_DE':
				$widget_name = 'Letzte Beitr&auml;ge, mit Vorschaubildern';
				$widget_desc = 'Liste deiner aktuellsten Beitr&auml;ge, mit klickbaren &Uuml;berschriften und Vorschaubildern.';
				break;
			default:
				$widget_name = 'Recent Posts, With Thumbnails';
				$widget_desc = 'List of your site&#8217;s most recent posts, with clickable title and thumbnails.';
		}
		$this->plugin_slug  = 'recent-posts-widget-with-thumbnails';
		$this->plugin_version  = '3.0';
		$this->number_posts  = 5;
		$this->default_thumb_dimensions  = 'custom';
		$this->default_thumb_width  = (int) round( get_option( 'thumbnail_size_w', 110 ) / 2 );
		$this->default_thumb_height = (int) round( get_option( 'thumbnail_size_h', 110 ) / 2 );
		$this->default_excerpt_length = apply_filters( 'excerpt_length', 55 );
		$this->default_excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$this->default_thumb_url = plugins_url( 'default_thumb.gif', __FILE__ );
		$this->text_domain = 'recent-posts-thumbnails';
		$this->css_file_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'public.css';
		
		$widget_ops = array( 'classname' => $this->plugin_slug, 'description' => $widget_desc );
		parent::__construct( $this->plugin_slug, $widget_name, $widget_ops );

		add_action( 'admin_init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_style' ) );
	}

	function widget( $args, $instance ) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( $this->plugin_slug, 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args[ 'widget_id' ] ) ) {
			$args[ 'widget_id' ] = $this->id;
		}

		if ( isset( $cache[ $args[ 'widget_id' ] ] ) ) {
			echo $cache[ $args[ 'widget_id' ] ];
			return;
		}

		ob_start();
		extract( $args );

		$title = ( ! empty( $instance[ 'title' ] ) ) ? esc_attr( $instance[ 'title' ] ) : __( 'Recent Posts With Thumbnails', $this->text_domain );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number_posts		= ( ! empty( $instance[ 'number_posts' ] ) ) ? absint( $instance[ 'number_posts' ] ) : $this->number_posts;
		$default_url 		= ( ! empty( $instance[ 'default_url' ] ) ) ? esc_url( $instance[ 'default_url' ] ) : $this->default_thumb_url;
		$excerpt_length 	= ( ! empty( $instance[ 'excerpt_length' ] ) ) ? absint( $instance[ 'excerpt_length' ] ) : $this->default_excerpt_length;
		$excerpt_more 	    = ( ! empty( $instance[ 'excerpt_more' ] ) or '' == $instance[ 'excerpt_more' ] ) ? esc_attr( $instance[ 'excerpt_more' ] ) : $this->default_excerpt_more;
		$thumb_dimensions   = ( isset( $instance[ 'thumb_dimensions' ] ) ) ? esc_attr( $instance[ 'thumb_dimensions' ] )  : $this->default_thumb_dimensions;
		$keep_aspect_ratio  = ( isset( $instance[ 'keep_aspect_ratio' ] ) ) ? $instance[ 'keep_aspect_ratio' ] : false;
		$hide_title			= ( isset( $instance[ 'hide_title' ] ) ) ? $instance[ 'hide_title' ] : false;
		$show_excerpt		= ( isset( $instance[ 'show_excerpt' ] ) ) ? $instance[ 'show_excerpt' ] : false;
		$show_date 			= ( isset( $instance[ 'show_date' ] ) ) ? $instance[ 'show_date' ] : false;
		$show_thumb 		= ( isset( $instance[ 'show_thumb' ] ) ) ? $instance[ 'show_thumb' ] : false;
		$use_default 		= ( isset( $instance[ 'use_default' ] ) ) ? $instance[ 'use_default' ] : false;
		$try_1st_img 		= ( isset( $instance[ 'try_1st_img' ] ) ) ? $instance[ 'try_1st_img' ] : false;
		$only_1st_img 		= ( isset( $instance[ 'only_1st_img' ] ) ) ? $instance[ 'only_1st_img' ] : false;

		// sanitizes vars
		if ( ! $number_posts )  	$number_posts = $this->number_posts;
		if ( ! $thumb_dimensions )	$thumb_dimensions = $this->default_thumb_dimensions;
		if ( ! $default_url )	    $default_url = $this->default_thumb_url;

		if ( $thumb_dimensions == $this->default_thumb_dimensions ) {
			$thumb_width  = ( ! empty( $instance[ 'thumb_width' ]  ) ) ? absint( $instance[ 'thumb_width' ]  ) : $this->default_thumb_width;
			$thumb_height = ( ! empty( $instance[ 'thumb_height' ] ) ) ? absint( $instance[ 'thumb_height' ] ) : $this->default_thumb_height;
		} else {
			$thumb_width  = get_option( $thumb_dimensions . '_size_w' );
			$thumb_height = get_option( $thumb_dimensions . '_size_h' );
			if ( ! $thumb_width )  $thumb_width  = $this->default_thumb_width;
			if ( ! $thumb_height ) $thumb_height = $this->default_thumb_height;
		}
		$size = array( $thumb_width, $thumb_height );

		// default image code
		$default_attr = array(
			'src'	=> $default_url,
			'class'	=> "attachment-" . join( 'x', $size ),
			'alt'	=> '',
		);
		$default_img = '<img ';
		$default_img .= rtrim( image_hwstring( $thumb_width, $thumb_height ) );
		foreach ( $default_attr as $name => $value ) {
			$default_img .= ' ' . $name . '="' . $value . '"';
		}
		$default_img .= ' />';
		
		/**
		 * Filter the arguments for the Recent Posts widget
		 *
		 * @since 1.0
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args An array of arguments used to retrieve the recent posts.
		 */
		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number_posts,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true
		) ) );

		if ( $r->have_posts()) :

			// print list
			include 'includes/widget.php';

			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args[ 'widget_id' ] ] = ob_get_flush();
			wp_cache_set( $this->plugin_slug, $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

	function update( $new_widget_settings, $old_widget_settings ) {
		$instance = $old_widget_settings;
		// sanitize user input before update
		$instance[ 'number_posts' ]		= absint( $new_widget_settings[ 'number_posts' ] );
		$instance[ 'thumb_width' ] 		= absint( $new_widget_settings[ 'thumb_width' ] );
		$instance[ 'thumb_height' ] 	= absint( $new_widget_settings[ 'thumb_height' ] );
		$instance[ 'excerpt_length' ] 	= absint( $new_widget_settings[ 'excerpt_length' ] );
		$instance[ 'excerpt_more' ] 	= strip_tags( $new_widget_settings[ 'excerpt_more' ] );
		$instance[ 'thumb_dimensions' ] = strip_tags( $new_widget_settings[ 'thumb_dimensions' ]);
		$instance[ 'title' ] 			= strip_tags( $new_widget_settings[ 'title' ]);
		$instance[ 'default_url' ] 		= strip_tags( $new_widget_settings[ 'default_url' ]);
		$instance[ 'keep_aspect_ratio'] = ( isset( $new_widget_settings[ 'keep_aspect_ratio' ] ) ) ? (bool) $new_widget_settings[ 'keep_aspect_ratio' ] : false;
		$instance[ 'hide_title' ] 		= ( isset( $new_widget_settings[ 'hide_title' ] ) ) ? (bool) $new_widget_settings[ 'hide_title' ] : false;
		$instance[ 'show_excerpt' ] 	= ( isset( $new_widget_settings[ 'show_excerpt' ] ) ) ? (bool) $new_widget_settings[ 'show_excerpt' ] : false;
		$instance[ 'show_date' ] 		= ( isset( $new_widget_settings[ 'show_date' ] ) ) ? (bool) $new_widget_settings[ 'show_date' ] : false;
		$instance[ 'show_thumb' ] 		= ( isset( $new_widget_settings[ 'show_thumb' ] ) ) ? (bool) $new_widget_settings[ 'show_thumb' ] : false;
		$instance[ 'use_default' ] 		= ( isset( $new_widget_settings[ 'use_default' ] ) ) ? (bool) $new_widget_settings[ 'use_default' ] : false;
		$instance[ 'try_1st_img' ] 		= ( isset( $new_widget_settings[ 'try_1st_img' ] ) ) ? (bool) $new_widget_settings[ 'try_1st_img' ] : false;
		$instance[ 'only_1st_img' ] 	= ( isset( $new_widget_settings[ 'only_1st_img' ] ) ) ? (bool) $new_widget_settings[ 'only_1st_img' ] : false;

		// empty widget cache
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions[ $this->plugin_slug ]) )
			delete_option( $this->plugin_slug );

		// delete current css file to let make new one via $this->enqueue_public_style()
		if ( file_exists( $this->css_file_path ) ) {
			// remove the file
			unlink( $this->css_file_path );
		}

		// return sanitized current widget settings
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( $this->plugin_slug, 'widget' );
	}

	function form( $instance ) {
		$title             = ( isset( $instance[ 'title' ] ) ) ? esc_attr( $instance[ 'title' ] ) : '';
		$default_url       = ( isset( $instance[ 'default_url' ] ) ) ? esc_url( $instance[ 'default_url' ] ) : $this->default_thumb_url;
		$thumb_dimensions  = ( isset( $instance[ 'thumb_dimensions' ] ) )  ? esc_attr( $instance[ 'thumb_dimensions' ] )  : $this->default_thumb_dimensions;
		$thumb_width       = ( isset( $instance[ 'thumb_width' ] ) ) ? absint( $instance[ 'thumb_width' ] )  : $this->default_thumb_width;
		$thumb_height      = ( isset( $instance[ 'thumb_height' ] ) ) ? absint( $instance[ 'thumb_height' ] ) : $this->default_thumb_height;
		$number_posts      = ( isset( $instance[ 'number_posts' ] ) ) ? absint( $instance[ 'number_posts' ] ) : $this->number_posts;
		$keep_aspect_ratio = ( isset( $instance[ 'keep_aspect_ratio' ] ) ) ? (bool) $instance[ 'keep_aspect_ratio' ] : false;
		$hide_title        = ( isset( $instance[ 'hide_title' ] ) ) ? (bool) $instance[ 'hide_title' ] : false;
		$show_excerpt      = ( isset( $instance[ 'show_excerpt' ] ) ) ? (bool) $instance[ 'show_excerpt' ] : false;
		$excerpt_length    = ( isset( $instance[ 'excerpt_length' ] ) ) ? absint( $instance[ 'excerpt_length' ] ) : $this->default_excerpt_length;
		$excerpt_more      = ( isset( $instance[ 'excerpt_more' ] ) or '' == $instance[ 'excerpt_more' ] ) ? esc_attr( $instance[ 'excerpt_more' ] ) : $this->default_excerpt_more;
		$show_date         = ( isset( $instance[ 'show_date' ] ) ) ? (bool) $instance[ 'show_date' ] : false;
		$show_thumb        = ( isset( $instance[ 'show_thumb' ] ) ) ? (bool) $instance[ 'show_thumb' ] : true;
		$use_default       = ( isset( $instance[ 'use_default' ] ) ) ? (bool) $instance[ 'use_default' ] : false;
		$try_1st_img       = ( isset( $instance[ 'try_1st_img' ] ) ) ? (bool) $instance[ 'try_1st_img' ] : false;
		$only_1st_img      = ( isset( $instance[ 'only_1st_img' ] ) ) ? (bool) $instance[ 'only_1st_img' ] : false;
		
		// sanitize vars
		if ( ! $number_posts )	    $number_posts = $this->number_posts;
		if ( ! $thumb_dimensions )	$thumb_dimensions = $this->default_thumb_dimensions;
		if ( ! $thumb_width )	    $thumb_width = $this->default_thumb_width;
		if ( ! $thumb_height )	    $thumb_height = $this->default_thumb_height;
		if ( ! $default_url )	    $default_url = $this->default_thumb_url;
		
		// compute ids only once to improve performance
		$id_title             = $this->get_field_id( 'title' );
		$id_number_posts      = $this->get_field_id( 'number_posts' );
		$id_show_date         = $this->get_field_id( 'show_date' );
		$id_show_thumb        = $this->get_field_id( 'show_thumb' );
		$id_thumb_dimensions  = $this->get_field_id( 'thumb_dimensions' );
		$id_thumb_width       = $this->get_field_id( 'thumb_width' );
		$id_thumb_height      = $this->get_field_id( 'thumb_height' );
		$id_keep_aspect_ratio = $this->get_field_id( 'keep_aspect_ratio' );
		$id_hide_title        = $this->get_field_id( 'hide_title' );
		$id_show_excerpt      = $this->get_field_id( 'show_excerpt' );
		$id_excerpt_length    = $this->get_field_id( 'excerpt_length' );
		$id_excerpt_more      = $this->get_field_id( 'excerpt_more' );
		$id_try_1st_img       = $this->get_field_id( 'try_1st_img' );
		$id_only_1st_img      = $this->get_field_id( 'only_1st_img' );
		$id_use_default       = $this->get_field_id( 'use_default' );
		$id_default_url       = $this->get_field_id( 'default_url' );
		
		global $_wp_additional_image_sizes;
		$wp_standard_image_size_labels = array();
		$label = 'Thumbnail';
		$wp_standard_image_size_labels[ 'thumbnail' ] = __( $label );
		$label = 'Medium';
		$wp_standard_image_size_labels[ 'medium' ] = __( $label );
		$label = 'Large';
		$wp_standard_image_size_labels[ 'large' ] = __( $label );
		$label = 'Full Size';
		$wp_standard_image_size_labels[ 'full' ] = __( $label );
		
		$wp_standard_image_size_names = array_keys( $wp_standard_image_size_labels );
		
		$text = 'Settings';
		$label_settings = __( $text );
		$text = 'Media';
		$label_media = _x( $text, 'post type general name' );
		$label = sprintf( '%s &rsaquo; %s', $label_settings, $label_media );
		if ( current_user_can( 'manage_options' ) ) {
			$media_trail = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( admin_url( 'options-media.php' ) ), $label );
		} else {
			$media_trail= sprintf( '<em>%s</em>', $label );
		}

		// print form in widgets
		include 'includes/form.php';

	}
	
	/**
	 * Load the widget's CSS in the HEAD section of the frontend
	 *
	 * @since 2.3
	 */
	public function enqueue_public_style () {
		// load style only in frontend
		if ( is_admin() ) return;
		// make sure the css file exists
		if ( ! file_exists( $this->css_file_path ) ) {
			// make the file
			$this->make_css_file();
		}
		// enqueue the style
		wp_enqueue_style(
			$this->plugin_slug . '-public-style',
			plugin_dir_url( __FILE__ ) . 'public.css',
			array(),
			$this->plugin_version,
			'all' 
		);
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->text_domain;
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Returns the id of the first image in the content, else 0
	 *
	 * @access   private
	 * @since     2.0
	 *
	 * @return    integer    the post id of the first content image
	 */
	private function get_first_content_image_id ( $content ) {
		// set variables
		global $wpdb;
		// look for images in HTML code
		preg_match_all( '/<img[^>]+>/i', $content, $all_img_tags );
		if ( $all_img_tags ) {
			foreach ( $all_img_tags[ 0 ] as $img_tag ) {
				// find class attribute and catch its value
				preg_match( '/<img.*?class\s*=\s*[\'"]([^\'"]+)[\'"][^>]*>/i', $img_tag, $img_class );
				if ( $img_class ) {
					// Look for the WP image id
					preg_match( '/wp-image-([\d]+)/i', $img_class[ 1 ], $found_id );
					// if first image id found: check whether is image
					if ( $found_id ) {
						$img_id = absint( $found_id[ 1 ] );
						// if is image: return its id
						if ( wp_get_attachment_image_src( $img_id ) ) {
							return $img_id;
						}
					} // if(found_id)
				} // if(img_class)
				
				// else: try to catch image id by its url as stored in the database
				// find src attribute and catch its value
				preg_match( '/<img.*?src\s*=\s*[\'"]([^\'"]+)[\'"][^>]*>/i', $img_tag, $img_src );
				if ( $img_src ) {
					// delete optional query string in img src
					$url = preg_replace( '/([^?]+).*/', '\1', $img_src[ 1 ] );
					// delete image dimensions data in img file name, just take base name and extension
					$guid = preg_replace( '/(.+)-\d+x\d+\.(\w+)/', '\1.\2', $url );
					// look up its ID in the db
					$found_id = $wpdb->get_var( $wpdb->prepare( "SELECT `ID` FROM $wpdb->posts WHERE `guid` = '%s'", $guid ) );
					// if first image id found: return it
					if ( $found_id ) {
						return absint( $found_id );
					} // if(found_id)
				} // if(img_src)
			} // foreach(img_tag)
		} // if(all_img_tags)
		
		// if nothing found: return 0
		return 0;
	}

	/**
	 * Echoes the thumbnail of first post's image and returns success
	 *
	 * @access   private
	 * @since     2.0
	 *
	 * @return    bool    success on finding an image
	 */
	private function the_first_posts_image ( $content, $size ) {
		// look for first image
		$thumb_id = $this->get_first_content_image_id( $content );
		// if there is first image then show first image
		if ( $thumb_id ) :
			echo wp_get_attachment_image( $thumb_id, $size );
			return true;
		else :
			return false;
		endif; // thumb_id
	}

	/**
	 * Generate the css file with stored settings
	 *
	 * @since 2.3
	 */
	private function make_css_file () {

		// get stored settings
		$all_instances = $this->get_settings();

		// generate CSS
		$css_code = sprintf( '.%s ul { list-style: outside none none; }', $this->plugin_slug );
		$css_code .= "\n"; 
		$css_code .= sprintf( '.%s ul li { overflow: hidden; margin: 0 0 1.5em; }', $this->plugin_slug );
		$css_code .= "\n"; 
		$css_code .= sprintf( '.%s ul li img { display: inline; float: left; margin: .3em .75em .75em 0; }', $this->plugin_slug );
		$css_code .= "\n";
		$set_default = true;

		foreach ( $all_instances as $number => $settings ) {
			// set width and height
			$width = $this->default_thumb_width;
			$height = $this->default_thumb_height;
			$thumb_dimensions = isset( $settings[ 'thumb_dimensions' ] ) ? esc_attr( $settings[ 'thumb_dimensions' ] )  : $this->default_thumb_dimensions;
			if ( $thumb_dimensions == $this->default_thumb_dimensions ) {
				if ( isset( $settings[ 'thumb_width' ] ) ) {
					$width  = absint( $settings[ 'thumb_width' ]  );
				}
				if ( isset( $settings[ 'thumb_height' ] ) ) {
					$height = absint( $settings[ 'thumb_height' ] );
				}
			} else {
				$width  = get_option( $thumb_dimensions . '_size_w', $this->default_thumb_width );
				$height  = get_option( $thumb_dimensions . '_size_h', $this->default_thumb_height );
			} // $settings[ 'thumb_dimensions' ]
			// get aspect ratio option
			$keep_aspect_ratio = false;
			if ( isset( $settings[ 'keep_aspect_ratio' ] ) ) {
				$keep_aspect_ratio = (bool) $settings[ 'keep_aspect_ratio' ];
			}
			// set CSS code
			if ( $keep_aspect_ratio ) {
				$css_code .= sprintf( '#%s-%d img { max-width: %dpx; width: 100%%; height: auto; }', $this->plugin_slug, $number, $width );
				$css_code .= "\n"; 
			} else {
				$css_code .= sprintf( '#%s-%d img { width: %dpx; height: %dpx; }', $this->plugin_slug, $number, $width, $height );
				$css_code .= "\n"; 
			}
			// override default code
			$set_default = false;
		} // foreach ( $all_instances as $number => $settings )
		// set at least this statement if no settings are stored
		if ( $set_default ) {
			$css_code .= sprintf( '.%s img { width: %dpx; height: %dpx; }', $this->plugin_slug, $this->default_thumb_width, $this->default_thumb_height );
			$css_code .= "\n"; 
		}
		
		// write file safely; print inline CSS on error
		try {
			if ( false === @file_put_contents( $this->css_file_path, $css_code ) ) {
				throw new Exception();
			}
		} catch (Exception $e) {
			print "\n<!-- Recent Posts Widget With Thumbnails: Could not open the CSS file! Print inline CSS instead: -->\n";
			printf( "<style type='text/css'>%s</style>\n", $css_code );
		}
	}

	/**
	 * Returns the shortened excerpt, must use in a loop.
	 *
	 * @since 3.0
	 */
	private function get_the_trimmed_excerpt ( $len = 55, $more = ' [&hellip;]' ) {
		
		// get current post's excerpt
		$excerpt = get_the_excerpt();

		// if excerpt is longer than desired
		if ( mb_strlen( $excerpt ) > $len ) {
			// get excerpt in desired length
			$sub_excerpt = mb_substr( $excerpt, 0, $len - 4 );
			// get array of shortened excerpt words
			$excerpt_words = explode( ' ', $sub_excerpt );
			// get the length of the last word in the shortened excerpt
			$excerpt_cut = - ( mb_strlen( $excerpt_words[ count( $excerpt_words ) - 1 ] ) );
			// if there is no empty string
			if ( $excerpt_cut < 0 ) {
				// get the shorter excerpt until the last word
				$excerpt = mb_substr( $sub_excerpt, 0, $excerpt_cut );
			} else {
				// get the shortened excerpt
				$excerpt = $sub_excerpt;
			}
			// append ellipses
			$excerpt .= $more;
		}
		// return text
		return $excerpt;
	}

}

/**
 * Register widget on init
 *
 * @since 1.0
 */
function register_recent_posts_widget_with_thumbnails () {
	register_widget('Recent_Posts_Widget_With_Thumbnails');
}
add_action('init', 'register_recent_posts_widget_with_thumbnails', 1);