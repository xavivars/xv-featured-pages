<?php
/*
Plugin Name: XV Featured Pages
Plugin URI: http://xavi.ivars.me
Description: Featured Pages plugin, based on featured post widget by Genesis
Version: 0.2
Author: Xavi Ivars xavi.ivars@gmail.com
Author URI: http://xavi.ivars.me
License: GPLv3
*/

	class XV_Featured_Pages extends WP_Widget {

		/**
		 * Holds widget settings defaults, populated in constructor.
		 *
		 * @var array
		 */
		protected $defaults;

		/**
		 * Constructor. Set the default widget options and create widget.
		 *
		 * @since 0.1.8
		 */
		function __construct() {

			$this->defaults = array(
				'title'                   => '',
				'posts_cat'               => '',
				'posts_num'               => 1,
				'posts_offset'            => 0,
				'orderby'                 => '',
				'order'                   => '',
				'exclude_displayed'       => 0,
				'show_image'              => 0,
				'image_alignment'         => '',
				'image_size'              => '',
				'show_gravatar'           => 0,
				'gravatar_alignment'      => '',
				'gravatar_size'           => '',
				'show_title'              => 0,
				'show_byline'             => 0,
				'post_info'               => '[post_date] ' . __( 'By', 'genesis' ) . ' [post_author_posts_link] [post_comments]',
				'show_content'            => 'excerpt',
				'content_limit'           => '',
				'more_text'               => __( '[Read More...]', 'genesis' ),
				'extra_num'               => '',
				'extra_title'             => '',
				'more_from_category'      => '',
				'more_from_category_text' => __( 'More Posts from this Category', 'genesis' ),
			);

			$widget_ops = array(
				'classname'   => 'featured-content featuredpost',
				'description' => __( 'Displays featured pages with thumbnails', 'genesis' ),
			);

			$control_ops = array(
				'id_base' => 'xv-featured-pages'
			);

			parent::__construct( 'xv-featured-pages', __( 'XV Featured pages', 'genesis' ), $widget_ops, $control_ops );

		}

		/**
		 * Echo the widget content.
		 *
		 * @since 0.1.8
		 *
		 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
		 * @param array $instance The settings for the particular instance of the widget
		 */
		function widget( $args, $instance ) {

			global $wp_query, $_genesis_displayed_ids;

			extract( $args );

			//* Merge with defaults
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			echo $before_widget;

			//* Set up the author bio
			if ( ! empty( $instance['title'] ) )
				echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

			$query_args = array(
				'post_type' => 'page',
				'tag'       => $instance['page_tags'],
				'orderby'   => 'menu_order',
				'order'     => 'asc',
			);

			$wp_query = new WP_Query( $query_args );

			if ( have_posts() ) : while ( have_posts() ) : the_post();

				genesis_markup( array(
					'html5'   => '<article %s>',
					'xhtml'   => sprintf( '<div class="xv-featured-item %s">', implode( ' ', get_post_class('post') ) ),
					'context' => 'entry',
				) );

				$image = genesis_get_image( array(
					'format'  => 'html',
					'size'    => 'portfolio',
					'context' => 'featured-post-widget',
					'attr'    => genesis_parse_attr( 'entry-image-widget' ),
				) );

				if ( $image )
					printf( '<a href="%s" title="%s" class="%s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), esc_attr( 'alignnone' ), $image );

				echo genesis_html5() ? '<header class="entry-header">' : '';

				if ( genesis_html5() )
					printf( '<h2 class="entry-title"><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );
				else
					printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );
				
				echo genesis_html5() ? '</header>' : '';

				genesis_markup( array(
					'html5' => '</article>',
					'xhtml' => '</div>',
				) );

			endwhile; endif;

			//* Restore original query
			wp_reset_query();

			echo $after_widget;

		}

		/**
		 * Update a particular instance.
		 *
		 * This function should check that $new_instance is set correctly.
		 * The newly calculated value of $instance should be returned.
		 * If "false" is returned, the instance won't be saved/updated.
		 *
		 * @since 0.1.8
		 *
		 * @param array $new_instance New settings for this instance as input by the user via form()
		 * @param array $old_instance Old settings for this instance
		 * @return array Settings to save or bool false to cancel saving
		 */
		function update( $new_instance, $old_instance ) {

			$new_instance['title']     = strip_tags( $new_instance['title'] );
			$new_instance['page_tags'] = strip_tags( $new_instance['page_tags'] );
			return $new_instance;

		}

		/**
		 * Echo the settings update form.
		 *
		 * @since 0.1.8
		 *
		 * @param array $instance Current settings
		 */
		function form( $instance ) {

			//* Merge with defaults
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'genesis' ); ?>:</label>
				<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'page_tags' ); ?>"><?php _e( 'Page tags', 'genesis' ); ?>:</label>
				<input type="text" id="<?php echo $this->get_field_id( 'page_tags' ); ?>" name="<?php echo $this->get_field_name( 'page_tags' ); ?>" value="<?php echo esc_attr( $instance['page_tags'] ); ?>" class="widefat" />
			</p>

			<?php	

		}

	}

	add_action('init', 'tags_support_pages');
	function tags_support_pages() {
		register_taxonomy_for_object_type('post_tag', 'page');
	}

	// Add to the init hook of your theme functions.php file
	add_filter('request', 'my_expanded_request');  
	 
	function my_expanded_request($q) {
		if (isset($q['tag']) || isset($q['category_name'])) {
			if(is_array($q['post_type'])) {
				if(!in_array($q['post_type'], 'page')) {
					array_push($q['post_type'], 'page');
				}
			} else {
				$q['post_type'] = array('post', 'page');
			}
		}
		
		return $q;
	}



	add_action( 'widgets_init', create_function('', 'return register_widget("XV_Featured_Pages");') );
