<?php
/*
Plugin Name: Recent Posts Widget With Thumbnails
Plugin URI:  http://wordpress.org/plugins/recent-posts-widget-with-thumbnails/
Description: Small and fast plugin to display in the sidebar a list of linked titles and thumbnails of the most recent postings
Version:     2.3
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
	var $default_thumb_width;  // width of the thumbnail
	var $default_thumb_height; // height of the thumbnail
	var $default_thumb_url; // URL of the default thumbnail
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
		$this->plugin_version  = '2.3';
		$this->number_posts  = 5;
		$this->default_thumb_width  = 55;
		$this->default_thumb_height = 55;
		$this->default_thumb_url = plugins_url( 'default_thumb.gif', __FILE__ );
		$this->text_domain = 'recent-posts-thumbnails';
		$this->css_file_path = plugin_dir_path( __FILE__ ) . 'public.css';
		
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

		$title = ( ! empty( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : __( 'Recent Posts With Thumbnails', $this->text_domain );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number_posts		= ( ! empty( $instance[ 'number_posts' ] ) ) ? absint( $instance[ 'number_posts' ] ) : $this->number_posts;
		$thumb_width 		= ( ! empty( $instance[ 'thumb_width' ] ) ) ? absint( $instance[ 'thumb_width' ] ) : $this->default_thumb_width;
		$thumb_height 		= ( ! empty( $instance[ 'thumb_height' ] ) ) ? absint( $instance[ 'thumb_height' ] ) : $this->default_thumb_height;
		$default_url 		= ( ! empty( $instance[ 'default_url' ] ) ) ? $instance[ 'default_url' ] : $this->default_thumb_url;
		$keep_aspect_ratio  = isset( $instance[ 'keep_aspect_ratio' ] ) ? $instance[ 'keep_aspect_ratio' ] : false;
		$hide_title			= isset( $instance[ 'hide_title' ] ) ? $instance[ 'hide_title' ] : false;
		$show_date 			= isset( $instance[ 'show_date' ] ) ? $instance[ 'show_date' ] : false;
		$show_thumb 		= isset( $instance[ 'show_thumb' ] ) ? $instance[ 'show_thumb' ] : false;
		$use_default 		= isset( $instance[ 'use_default' ] ) ? $instance[ 'use_default' ] : false;
		$try_1st_img 		= isset( $instance[ 'try_1st_img' ] ) ? $instance[ 'try_1st_img' ] : false;
		$only_1st_img 		= isset( $instance[ 'only_1st_img' ] ) ? $instance[ 'only_1st_img' ] : false;

		// sanitizes vars
		if ( ! $number_posts ) 	$number_posts = $this->number_posts;
		if ( ! $thumb_width )	$thumb_width = $this->default_thumb_width;
		if ( ! $thumb_height )	$thumb_height = $this->default_thumb_height;
		if ( ! $default_url )	$default_url = $this->default_thumb_url;

		$size 	= array( $thumb_width, $thumb_height );

		/**
		 * Filter the arguments for the Recent Posts widget.
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
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<li><a href="<?php the_permalink(); ?>"><?php 
				if ( $show_thumb ) : 
					$is_thumb = false;
					// if only first image
					if ( $only_1st_img ) :
						// try to find and display the first post's image and return success
						$is_thumb = $this->the_first_posts_image( get_the_content(), $size );
					else :
						// else 
						// look for featured image
						if ( has_post_thumbnail() ) : 
							// if there is featured image then show featured image
							echo wp_get_attachment_image( get_post_thumbnail_id(), $size ); // alt: the_post_thumbnail( $size ); use wp_get_attachment_image() instead to have the same behaviour as in $this->the_first_posts_image()
							$is_thumb = true;
						else :
							// else 
							// if user wishes first image trial
							if ( $try_1st_img ) :
								// try to find and display the first post's image and return success
								$is_thumb = $this->the_first_posts_image( get_the_content(), $size );
							endif; // try_1st_img 
						endif; // has_post_thumbnail
					endif; // only_1st_img
					// if there is no image 
					if ( ! $is_thumb ) :
						// if user allows default image then
						if ( $use_default ) :
							// next line inspired by code of wp_get_attachment_image()
							$hwstring = image_hwstring( $thumb_width, $thumb_height );
							$dimensions = join( 'x', $size );
							$default_attr = array(
								'src'	=> $default_url,
								'class'	=> "attachment-$dimensions",
								'alt'	=> '',
							);
							$html = rtrim("<img $hwstring");
							foreach ( $default_attr as $name => $value ) {
								$html .= " $name=" . '"' . $value . '"';
							}
							$html .= ' />';
							print $html;
						endif; // use_default
					endif; // not is_thumb
					// (else do nothing)
				endif; // show_thumb
				// show title if wished
				if ( ! $hide_title ) {
					get_the_title() ? the_title() : the_ID(); 
				}
				?></a><?php 
				if ( $show_date ) : 
					?> <span class="post-date"><?php echo get_the_date(); ?></span><?php 
				endif; ?></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
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
		$instance[ 'number_posts' ]	= absint( $new_widget_settings[ 'number_posts' ] );
		$instance[ 'thumb_width' ] 	= absint( $new_widget_settings[ 'thumb_width' ] );
		$instance[ 'thumb_height' ] = absint( $new_widget_settings[ 'thumb_height' ] );
		$instance[ 'title' ] 		= strip_tags( $new_widget_settings[ 'title' ]);
		$instance[ 'default_url' ] 	= strip_tags( $new_widget_settings[ 'default_url' ]);
		$instance[ 'keep_aspect_ratio' ] = isset( $new_widget_settings[ 'keep_aspect_ratio' ] ) ? (bool) $new_widget_settings[ 'keep_aspect_ratio' ] : false;
		$instance[ 'hide_title' ] 	= isset( $new_widget_settings[ 'hide_title' ] ) ? (bool) $new_widget_settings[ 'hide_title' ] : false;
		$instance[ 'show_date' ] 	= isset( $new_widget_settings[ 'show_date' ] ) ? (bool) $new_widget_settings[ 'show_date' ] : false;
		$instance[ 'show_thumb' ] 	= isset( $new_widget_settings[ 'show_thumb' ] ) ? (bool) $new_widget_settings[ 'show_thumb' ] : false;
		$instance[ 'use_default' ] 	= isset( $new_widget_settings[ 'use_default' ] ) ? (bool) $new_widget_settings[ 'use_default' ] : false;
		$instance[ 'try_1st_img' ] 	= isset( $new_widget_settings[ 'try_1st_img' ] ) ? (bool) $new_widget_settings[ 'try_1st_img' ] : false;
		$instance[ 'only_1st_img' ] = isset( $new_widget_settings[ 'only_1st_img' ] ) ? (bool) $new_widget_settings[ 'only_1st_img' ] : false;

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
		$title        = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
		$default_url  = isset( $instance[ 'default_url' ] ) ? esc_url( $instance[ 'default_url' ] ) : $this->default_thumb_url;
		$thumb_width  = isset( $instance[ 'thumb_width' ] )  ? absint( $instance[ 'thumb_width' ] )  : $this->default_thumb_width;
		$thumb_height = isset( $instance[ 'thumb_height' ] ) ? absint( $instance[ 'thumb_height' ] ) : $this->default_thumb_height;
		$number_posts = isset( $instance[ 'number_posts' ] ) ? absint( $instance[ 'number_posts' ] ) : $this->number_posts;
		$keep_aspect_ratio = isset( $instance[ 'keep_aspect_ratio' ] ) ? (bool) $instance[ 'keep_aspect_ratio' ] : false;
		$hide_title   = isset( $instance[ 'hide_title' ] ) ? (bool) $instance[ 'hide_title' ] : false;
		$show_date    = isset( $instance[ 'show_date' ] ) ? (bool) $instance[ 'show_date' ] : false;
		$show_thumb   = isset( $instance[ 'show_thumb' ] ) ? (bool) $instance[ 'show_thumb' ] : true;
		$use_default  = isset( $instance[ 'use_default' ] ) ? (bool) $instance[ 'use_default' ] : false;
		$try_1st_img  = isset( $instance[ 'try_1st_img' ] ) ? (bool) $instance[ 'try_1st_img' ] : false;
		$only_1st_img = isset( $instance[ 'only_1st_img' ] ) ? (bool) $instance[ 'only_1st_img' ] : false;
		
		// sanitize vars
		if ( ! $number_posts )	$number_posts = $this->number_posts;
		if ( ! $thumb_width )	$thumb_width = $this->default_thumb_width;
		if ( ! $thumb_height )	$thumb_height = $this->default_thumb_height;
		if ( ! $default_url )	$default_url = $this->default_thumb_url;
		
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number_posts' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_posts' ); ?>" type="text" value="<?php echo $number_posts; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>

		<h4><?php _e( 'Thumbnail Options', $this->text_domain ); ?>:</h4>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_thumb ); ?> id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>"><?php _e( 'Display post featured image?', $this->text_domain ); ?></label></p>
		
		<p><label for="<?php echo $this->get_field_id( 'thumb_width' ); ?>"><?php _e( 'Width of thumbnail', $this->text_domain ); ?>:</label>
		<input id="<?php echo $this->get_field_id( 'thumb_width' ); ?>" name="<?php echo $this->get_field_name( 'thumb_width' ); ?>" type="text" value="<?php echo $thumb_width; ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id( 'thumb_height' ); ?>"><?php _e( 'Height of thumbnail', $this->text_domain ); ?>:</label>
		<input id="<?php echo $this->get_field_id( 'thumb_height' ); ?>" name="<?php echo $this->get_field_name( 'thumb_height' ); ?>" type="text" value="<?php echo $thumb_height; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $keep_aspect_ratio ); ?> id="<?php echo $this->get_field_id( 'keep_aspect_ratio' ); ?>" name="<?php echo $this->get_field_name( 'keep_aspect_ratio' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'keep_aspect_ratio' ); ?>"><?php _e( 'Use aspect ratios of original images?', $this->text_domain ); ?> <em><?php _e( 'If checked the given width is used to determine the height of the thumbnail automatically. This option also supports responsive web design.', $this->text_domain ); ?></em></label></p>
		
		<p><input class="checkbox" type="checkbox" <?php checked( $hide_title ); ?> id="<?php echo $this->get_field_id( 'hide_title' ); ?>" name="<?php echo $this->get_field_name( 'hide_title' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'hide_title' ); ?>"><?php _e( 'Do not show title?', $this->text_domain ); ?> <em><?php _e( 'Make sure you set a default thumbnail for posts without a thumbnail, otherwise there will be no link.', $this->text_domain ); ?></em></label> </p>
		
		<p><input class="checkbox" type="checkbox" <?php checked( $try_1st_img ); ?> id="<?php echo $this->get_field_id( 'try_1st_img' ); ?>" name="<?php echo $this->get_field_name( 'try_1st_img' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'try_1st_img' ); ?>"><?php _e( "Try to use the post's first image if post has no featured image?", $this->text_domain ); ?></label></p>
		
		<p><input class="checkbox" type="checkbox" <?php checked( $only_1st_img ); ?> id="<?php echo $this->get_field_id( 'only_1st_img' ); ?>" name="<?php echo $this->get_field_name( 'only_1st_img' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'only_1st_img' ); ?>"><?php _e( 'Use first image only, ignore featured image?', $this->text_domain ); ?></label></p>
		
		<p><input class="checkbox" type="checkbox" <?php checked( $use_default ); ?> id="<?php echo $this->get_field_id( 'use_default' ); ?>" name="<?php echo $this->get_field_name( 'use_default' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'use_default' ); ?>"><?php _e( 'Use default thumbnail if no image could be determined?', $this->text_domain ); ?></label></p>
		
		<p><label for="<?php echo $this->get_field_id( 'default_url' ); ?>"><?php _e( 'URL of default thumbnail (start with http://)', $this->text_domain ); ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'default_url' ); ?>" name="<?php echo $this->get_field_name( 'default_url' ); ?>" type="text" value="<?php echo $default_url; ?>" /></p>

		<p><?php _e( 'Do you like the plugin?', $this->text_domain ); ?> <a href="http://wordpress.org/support/view/plugin-reviews/recent-posts-widget-with-thumbnails"><?php _e( 'Please rate it at wordpress.org!', $this->text_domain ); ?></a></p>
<?php
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
		$css_code = sprintf( '.%s ul li { overflow: hidden; margin: 0 0 1.5em; }', $this->plugin_slug );
		$css_code .= "\n"; 
		$css_code .= sprintf( '.%s ul li img { display: inline; float: left; margin: .3em .75em .75em 0; }', $this->plugin_slug );
		$css_code .= "\n";
		$set_default = true;
		foreach ( $all_instances as $number => $settings ) {
			if ( isset( $settings[ 'thumb_width' ] ) ) {
				$width  = absint( $settings[ 'thumb_width' ]  );
				if ( ! $width ) $width = $this->default_thumb_width;
			}
			if ( isset( $settings[ 'thumb_height' ] ) ) {
				$height = absint( $settings[ 'thumb_height' ] );
				if ( ! $height ) $height = $this->default_thumb_height;
			}
			$keep_aspect_ratio = false;
			if ( isset( $settings[ 'keep_aspect_ratio' ] ) ) {
				$keep_aspect_ratio = (bool) $settings[ 'keep_aspect_ratio' ];
			}
			if ( $keep_aspect_ratio ) {
				$css_code .= sprintf( '#%s-%d img { max-width: %dpx; width: 100%%; height: auto; }', $this->plugin_slug, $number, $width );
				$css_code .= "\n"; 
			} else {
				$css_code .= sprintf( '#%s-%d img { width: %dpx; height: %dpx; }', $this->plugin_slug, $number, $width, $height );
				$css_code .= "\n"; 
			}
			$set_default = false;
		}
		// set at least this statement if no settings are stored
		if ( $set_default ) {
			$css_code .= sprintf( '.%s img { width: %dpx; height: %dpx; }', $this->plugin_slug, $this->default_thumb_width, $this->default_thumb_height );
			$css_code .= "\n"; 
		}
		
		// write file
		$fh = fopen( $this->css_file_path, 'w' );
		#if ( $fhd ) {
			fwrite ( $fh, $css_code );
			fclose( $fh );
		#}
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