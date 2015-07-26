<div id="rpwwt-<?php echo $args[ 'widget_id' ];?>" class="rpwwt-widget">
	<?php echo $before_widget; ?>
	<?php if ( $title ) echo $before_title . $title . $after_title; ?>
	<ul>
	<?php while ( $r->have_posts() ) : $r->the_post(); ?>
		<li<?php if ( is_sticky() ) { ?> class="rpwwt-sticky"<?php } ?>><a href="<?php the_permalink(); ?>"><?php 
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
						print $default_img;
					endif; // use_default
				endif; // not is_thumb
				// (else do nothing)
			endif; // show_thumb
			// show title if wished
			if ( ! $hide_title ) {
				?><span class="rpwwt-post-title"><?php get_the_title() ? the_title() : the_ID();  ?></span><?php
			}
			?></a><?php 
			if ( $show_date ) : 
				?><div class="rpwwt-post-date"><?php echo get_the_date(); ?></div><?php 
			endif;
			if ( $show_excerpt ) : 
				?><div class="rpwwt-post-excerpt"><?php echo $this->get_the_trimmed_excerpt( $excerpt_length, $excerpt_more ); ?></div><?php 
			endif; ?></li>
	<?php endwhile; ?>
	</ul>
	<?php echo $after_widget; ?>
</div>