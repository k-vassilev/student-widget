<?php
/**
 * Plugin Name: Student Widget
 * Description: Adds student widget and shortcode
 * Author: Kristian Vassilev
 * Version: 1.0.0
 *
 * @package student-widget
 */

// Creating the widget.
/**
 * Student widget class.
 */
class Ob_Student_Widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'ob-student-widget',
			'description' => 'Student Profiles Widget',
		);
		parent::__construct( 'ob_student', 'Student Profiles', $widget_ops );
	}

	// Back-End.

	public function form( $instance ) {

		// PART 1: Extract the data from the instance variable.

		$instance       = wp_parse_args( (array) $instance, array( 'posts_per_page' => '' ) );
		$posts_per_page = $instance['posts_per_page'];
		$student_status = $instance['student_status'];

		// PART 2-3: Display the fields.
		?>
		<!-- PART 2: Widget Posts per page field START -->
		<p>
		<label for="<?php echo $this->get_field_id( 'posts_per_page' ); ?>">Posts per page: 
		<input class="widefat" id="<?php echo $this->get_field_id( 'posts_per_page' ); ?>" 
			name="<?php echo $this->get_field_name( 'posts_per_page' ); ?>" type="text" 
			value="<?php echo esc_attr( $posts_per_page ); ?>" />
		</label>
		</p>
		<!-- Widget Posts per page field END -->

	<!-- PART 3: Widget Student Status field START -->
		<p>
		<label for="<?php echo $this->get_field_id( 'text' ); ?>">Active / inactive: 
		<select class='widefat' id="<?php echo $this->get_field_id( 'student_status' ); ?>"
			name="<?php echo $this->get_field_name( 'student_status' ); ?>" type="text">
			<option value='active'<?php echo ( 'active' === $student_status ) ? 'selected' : ''; ?>>
			Active
			</option>
			<option value='inactive'<?php echo ( 'inactive' === $student_status ) ? 'selected' : ''; ?>>
			Inactive
			</option> 
		</select>                
		</label>
		</p>
		<!-- Widget Student Status field END -->
		<?php
	}

	// Front-End.

	public function widget( $args, $instance ) {

		// PART 1: Extracting the arguments + getting the values.
		extract( $args, EXTR_SKIP );
		$posts_per_page = empty( $instance['posts_per_page'] ) ? ' ' : apply_filters( 'widget_title', $instance['posts_per_page'] );
		$student_status = empty( $instance['student_status'] ) ? '' : $instance['student_status'];
		$paged          = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

		if ( 'active' === $student_status ) {
			$loop = new WP_Query(
				array(
					'post_type'      => 'student',
					'posts_per_page' => $posts_per_page,
					'paged'          => $paged,
					'meta_key'       => '_is_active_student',
					'meta_value'     => 'true',
				)
			);
		} elseif ( 'inactive' === $student_status ) {
			$loop = new WP_Query( 
				array(
					'post_type'      => 'student',
					'posts_per_page' => $posts_per_page,
					'paged'          => $paged,
					'meta_key'       => '_is_active_student',
					'meta_value'     => 'false',
				)
			);
		}

		echo ( isset( $before_widget ) ? $before_widget : '' );

		if ( have_posts( $loop ) ) {
			while ( $loop -> have_posts() ) : $loop -> the_post(); 
				?>
				<div style="width: 300px;">
				<?php
				the_post_thumbnail( 'standard' );
				the_title();
				?>
				<?php echo get_post_meta( get_the_ID(), '_student_class_value_key' )[0]; ?>
				</div><?php

			endwhile;
		}
		echo ( isset( $after_widget ) ? $after_widget : '' );
	}
}



add_action(
	'widgets_init',
	function() {
		register_widget( 'Ob_Student_Widget' );
	}
);


// Register Sidebar.

/**
 * Creates the sidebar
 *
 * @return void
 */
function ob_sidebar() {

	register_sidebar(
		array(
			'name'         => 'Student Sidebar',
			'id'           => 'ob-sidebar',
			'before_title' => '<h4 class="widget-title">',
			'after_title'  => '</h4>',
		)
	);
}
add_action( 'widgets_init', 'ob_sidebar' );


/**
 * Grab latest post title by an author!
 *
 * @param array $data Options for the function.
 * @return string|null Post title for the latest,* or null if none.
 */
function my_awesome_func( $data ) {
	$posts = get_posts(
		array(
			'author' => $data['id'],
		)
	);

	if ( empty( $posts ) ) {
		return null;
	}
	return $posts[0]->post_title;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'myplugin/v1',
			'/author/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'callback' => 'my_awesome_func',
			)
		);
	}
);