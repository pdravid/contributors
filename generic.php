<?php
    /*
Plugin Name: Contributors
Plugin URI:  www.google.com
Description: checkbox test1
Version:     1.0.0
Author:      Pushkar Dravid
Author URI:  http://www.pushkardravid.wcevision.in
License:     GPL2
*/


add_action( 'add_meta_boxes', 'add_custom_boxx' );

    function add_custom_boxx( $post ) {
        add_meta_box(
            'Meta Box', // ID, should be a string.
            'Featured People', // Meta Box Title.
            'people_meta_boxx', // Your call back function, this is where your form field will go.
            'post', // The post type you want this to show up on, can be post, page, or custom post type.
            'side', // The placement of your meta box, can be normal or side.
            'core' // The priority in which this will be displayed.
        );
}


function people_meta_boxx($post) {
    wp_nonce_field( 'my_awesome_nonce', 'awesome_nonce' );    
    $checkboxMeta = get_post_meta( $post->ID );
    
    $html="";

    $blogusers = get_users( 'blog_id=1&orderby=nicename&role=author' );
        // Array of WP_User objects.
    foreach ( $blogusers as $user ) {
    ?>
    <input type="checkbox" 
    name="<?php echo $user->display_name;?>"
    id="<?php echo $user->display_name;?>" 
    <?php if ( isset ( $checkboxMeta[$user->display_name] ) ){ 
    checked( $checkboxMeta[$user->display_name][0], 'yes' );  }?> />
    <?php echo $user->display_name;?><br />
      
      <?php
        echo $html;

    } 


    ?>

<?php }

add_action( 'save_post', 'save_people_checkboxesx' );
    function save_people_checkboxesx( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;
        if ( ( isset ( $_POST['my_awesome_nonce'] ) ) && ( ! wp_verify_nonce( $_POST['my_awesome_nonce'], plugin_basename( __FILE__ ) ) ) )
            return;
        if ( ( isset ( $_POST['post_type'] ) ) && ( 'page' == $_POST['post_type'] )  ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }    
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

         $blogusers = get_users( 'blog_id=1&orderby=nicename&role=author' );
        // Array of WP_User objects.
            foreach ( $blogusers as $user ) {
        

        //saves bob's value
        if( isset( $_POST[ $user->display_name ] ) ) {
            update_post_meta( $post_id, $user->display_name, 'yes' );
        } else {
            update_post_meta( $post_id, $user->display_name, 'no' );
        }

    }

         
}

function display_contributors($content){

    wp_nonce_field( 'my_awesome_nonce', 'awesome_nonce' );    
    $checkboxMeta = get_post_meta( $post->ID );
    
      $html = '<div style="border:1px solid;padding:20px;" ><strong>Contributors<br></strong>';
      $html2 = $html;
      $postid = get_the_id();
    $blogusers = get_users( 'blog_id=1&orderby=nicename&role=author' );
    foreach($blogusers as $user ){
        global $wpdb;

        $key = $wpdb->get_var("select meta_value from $wpdb->postmeta where meta_key='$user->display_name' and post_id = '$postid'");
        if($key == 'yes'){
             $html.= get_avatar( $user->email, 32) .'<span>&nbsp &nbsp' .$user->display_name.'<br>';
        }
    }
    if($html == $html2){
        $html.= "No contributors!";
    }
    $html.= "</div>";
    $content.= $html;
    return $content;
}

add_filter('the_content','display_contributors');


?>