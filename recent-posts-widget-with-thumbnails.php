<?php
/*
Plugin Name: Recent Posts Widget With Thumbnails
Plugin URI:  http://wordpress.org/plugins/recent-posts-widget-with-thumbnails/
Description: Small and fast plugin to display in the sidebar a list of linked titles and thumbnails of the most recent postings
Version:     1.0
Author:      Martin Stehle
Author URI:  http://stehle-internet.de
Text Domain: recent-posts-thumbnails
Domain Path: /languages
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
+/

/**
 * Recent_Posts_Widget_With_Thumbnails widget class
 *
 * @since 1.0
 */
class Recent_Posts_Widget_With_Thumbnails extends WP_Widget {

	var $thumb_width;  // width of the thumbnail
	var $thumb_height; // height of the thumbnail

	function __construct() {
		switch ( get_locale() ) {
			case 'de_DE':
				$widget_name = 'Letzte Beitr&auml;ge, mit Vorschaubildern';
				$widget_desc = 'Liste deiner aktuellsten Beitr&auml;ge, mit klickbaren &Uuml;berschriften und Vorschaubildern.';
				break;
			default:
				$widget_name = 'Recent Posts Widget With Thumbnails';
				$widget_desc = 'List of your site&#8217;s most recent posts, with clickable title and thumbnails.';
		}
		$this->thumb_width  = 55;
		$this->thumb_height = 55;
		$widget_ops = array( 'classname' => 'recent-posts-widget-with-thumbnails', 'description' => $widget_desc );
		parent::__construct( 'recent-posts-widget-with-thumbnails', $widget_name, $widget_ops );

		add_action( 'admin_init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		add_action( 'wp_head', array( $this, 'print_list_css' ) );
	}

	function widget( $args, $instance ) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'recent-posts-widget-with-thumbnails', 'widget' );
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

		$title = ( ! empty( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : __( 'Recent Posts Widget With Thumbnails', 'recent-posts-thumbnails' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance[ 'number' ] ) ) ? absint( $instance[ 'number' ] ) : 5;
		if ( ! $number )
			$number = 5;
		$show_date = isset( $instance[ 'show_date' ] ) ? $instance[ 'show_date' ] : false;
		$show_thumb = isset( $instance[ 'show_thumb' ] ) ? $instance[ 'show_thumb' ] : false;

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
			'posts_per_page'      => $number,
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
					if ( has_post_thumbnail() ) : 
						the_post_thumbnail( array( $this->thumb_width, $this->thumb_height ) ); 
					#else: ? ><img src="/wp-content/themes/x-k/images/icon.gif" alt="" width="45" height="45" /><?php 
					endif; 
				endif; 
				get_the_title() ? the_title() : the_ID(); 
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
			wp_cache_set( 'recent-posts-widget-with-thumbnails', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ]);
		$instance[ 'number' ] = (int) $new_instance[ 'number' ];
		$instance[ 'show_date' ] = isset( $new_instance[ 'show_date' ] ) ? (bool) $new_instance[ 'show_date' ] : false;
		$instance[ 'show_thumb' ] = isset( $new_instance[ 'show_thumb' ] ) ? (bool) $new_instance[ 'show_thumb' ] : false;
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions[ 'recent-posts-widget-with-thumbnails' ]) )
			delete_option( 'recent-posts-widget-with-thumbnails' );

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'recent-posts-widget-with-thumbnails', 'widget' );
	}

	function form( $instance ) {
		$title      = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
		$number     = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 5;
		$show_date  = isset( $instance[ 'show_date' ] ) ? (bool) $instance[ 'show_date' ] : false;
		$show_thumb = isset( $instance[ 'show_thumb' ] ) ? (bool) $instance[ 'show_thumb' ] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_thumb ); ?> id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>"><?php _e( 'Display post featured image?', 'recent-posts-thumbnails' ); ?></label></p>
<?php
	}
	
	/**
	 * Print the widget's CSS in the HEAD section of the frontend
	 *
	 * @since 1.0
	 */
	function print_list_css () {
		print '<style type="text/css">';
		print "\n";
		print '.recent-posts-widget-with-thumbnails ul li { overflow: hidden; font-size: 91%; margin: 0 0 1.5em; }';
		print "\n";
		printf ('.recent-posts-widget-with-thumbnails ul li img { display: inline; float: left; margin: .3em .75em .75em 0; width: %dpx; height: %dpx; }', $this->thumb_width, $this->thumb_height );
		print "\n";
		print '</style>';
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = 'recent-posts-thumbnails';
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

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