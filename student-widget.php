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

        // PART 1: Extract the data from the instance variable
     $instance = wp_parse_args( (array) $instance, array( 'posts_per_page' => '' ) );
     $posts_per_page = $instance['posts_per_page'];
     $student_status = $instance['student_status'];   

     // PART 2-3: Display the fields
     ?>
     <!-- PART 2: Widget Posts per page field START -->
     <p>
      <label for="<?php echo $this->get_field_id('posts_per_page'); ?>">Posts per page: 
        <input class="widefat" id="<?php echo $this->get_field_id('posts_per_page'); ?>" 
               name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="text" 
               value="<?php echo attribute_escape($posts_per_page); ?>" />
      </label>
      </p>
      <!-- Widget Posts per page field END -->

     <!-- PART 3: Widget Student Status field START -->
     <p>
      <label for="<?php echo $this->get_field_id('text'); ?>">Active / inactive: 
        <select class='widefat' id="<?php echo $this->get_field_id('student_status'); ?>"
                name="<?php echo $this->get_field_name('student_status'); ?>" type="text">
          <option value='active'<?php echo ($student_status=='active')?'selected':''; ?>>
            Active
          </option>
          <option value='inactive'<?php echo ($student_status=='inactive')?'selected':''; ?>>
            Inactive
          </option> 
        </select>                
      </label>
     </p>
     <!-- Widget Student Status field END -->
     <?php 

    }

    // Front-End

    public function widget( $args, $instance ){

        
        // PART 1: Extracting the arguments + getting the values
        extract($args, EXTR_SKIP);
        $posts_per_page = empty($instance['posts_per_page']) ? ' ' : apply_filters('widget_title', $instance['posts_per_page']);
        $student_status = empty($instance['student_status']) ? '' : $instance['student_status'];
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        if($student_status == 'active'){
            $loop = new WP_Query( array( 
                'post_type' => 'student', 
                'posts_per_page' => $posts_per_page,
                'paged' => $paged,
                'meta_key' => '_is_active_student',
                'meta_value'=> 'true'
                ) ); 
        }else if($student_status == 'inactive'){
            $loop = new WP_Query( array( 
                'post_type' => 'student', 
                'posts_per_page' => $posts_per_page,
                'paged' => $paged,
                'meta_key' => '_is_active_student',
                'meta_value'=> 'false'
                ) ); 
        }
        

        echo (isset($before_widget)?$before_widget:'');

        if ( have_posts($loop) ){
            while ( $loop -> have_posts() ) : $loop -> the_post();?>
            
            <div style="width: 300px;"><?php
                the_post_thumbnail( 'standard' );    
                the_title();?>
                <?php echo get_post_meta(get_the_ID(),'_student_class_value_key')[0];?>
            </div><?php

            endwhile;
        }

        echo (isset($after_widget)?$after_widget:'');
    }
}


add_action( 'widgets_init', function(){
    register_widget( 'Ob_Student_Widget' );
} );
 ?>