<?php
/**
 * Plugin Name: Student Widget
 * Description: Adds student widget and shortcode
 * Author: Kristian Vassilev
 * Version: 1.0.0
 */

// STUDENT SHORTCODE

// shortcode logic 
function ob_student_shortcode($atts) { 

    // Adds student_id as attribute
    $attribute = shortcode_atts(array(
        'student_id' => 'placeholder'
    ), $atts);

    // Checks if the attribute is numeric
    $student_id_checker = intval($attribute['student_id']);
    if(!$student_id_checker){
        return 'Student ID must be numeric';
    }

    // Stores the info needed for the query (p = post ID)
    $post_info = array(
        'post_type' => 'student',
        'p'=> $student_id_checker
    );

    // Query to get the student info based on the post_info
    $student_query = new WP_Query($post_info);

    // Returns the content of the student if it exists
    if(!$student_query -> have_posts()) {
        return 'No student found';
    }else{ 
        $student_query -> the_post();?>
        <div style="width:300px;">
        <h4 style="text-align: center; padding:5px;"><?php the_title();?></h4><?php
        the_post_thumbnail('thumbnail');?>
        <h4 style="text-align: center; padding:5px;">Student is: <?php echo get_post_meta(get_the_ID(),'_student_class_value_key')[0];?></h4>
        </div><?php
    }
} 

// shortcode registration
add_shortcode('student', 'ob_student_shortcode'); 


// STUDENT WIDGET

// Creating the widget 
class Ob_Student_Widget extends WP_Widget {
  
    public function __construct() {
        $widget_ops = array(
            'classname' => 'ob-student-widget',
            'description' => 'Student Profiles Widget',
        );
        parent::__construct( 'ob_student', 'Student Profiles', $widget_ops );
    }

    // Back-End

    public function form( $instance ){

        echo 'No options so far';

    }




    // Front-End

    public function widget( $args, $instance ){

        
        $students_per_page = 4;
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $loop = new WP_Query( array( 
					'post_type' => 'student', 
					'posts_per_page' => $students_per_page,
					'paged' => $paged,
					'meta_key' => '_is_active_student',
					'meta_value'=> 'true'
					) ); 
            


        echo $args['before_widget'];

        if ( have_posts($loop) ){
            while ( $loop -> have_posts() ) : $loop -> the_post();?>
            
            <div style="width: 300px;"><?php
                the_post_thumbnail( 'standard' );    
                the_title();?>
                <?php echo get_post_meta(get_the_ID(),'_student_class_value_key')[0];?>
            </div><?php


            endwhile;
        }


        echo $args['after_widget'];
    }




}


add_action( 'widgets_init', function(){
    register_widget( 'Ob_Student_Widget' );
} );
 ?>