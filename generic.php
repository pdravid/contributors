<?php
/*
Plugin Name: Contributors
Plugin URI:  https://github.com/pushkardravid/contributors
Description: A wordpress plugin to select and display contributors for a post
Version:     1.0.0
Author:      Pushkar Dravid
Author URI:  https://github.com/pushkardravid
License:     GPL2
*/

/**
*This function adds the css file.
*/
add_action('wp_head','add_my_style');

function add_my_style(){
    wp_enqueue_style("my_style",plugins_url("contributors/css/style.css"));
}

add_action( 'add_meta_boxes', 'add_custom_box' );

/**
*This function is used to add a metabox to our post screen.
*/
function add_custom_box( $post ) {
    add_meta_box(
        'Meta Box', // ID of the metabox.
        'Contributors', // Meta Box Title.
        'users_meta_box', // The call back function, the function responsible for displying all the users.
        'post', // This metabox will be displayed on all post types.
        'side', // The placement of our contributors meta box, can be normal or side.
        'core' // The priority in which this will be displayed.
    );
}

/**
*This is the callback function. This function is called by the metabox to display the list of all the users.
*/

function users_meta_box($post) { 

    $checkboxMeta = get_post_meta($post->ID,'contributors',true);
    $contributors = unserialize($checkboxMeta);
    $all_users = get_users();
    // Array of WP_User objects.
    foreach ( $all_users as $user ) {
?>
   <input type="checkbox" 
    value="<?php echo $user->ID;?>"
    name="contributors[]"
    id="<?php echo $user->ID;?>" 
    <?php if ( is_array($contributors) && in_array($user->ID,$contributors) ){ 
        echo "checked = 'checked'"; }?> />
    <?php echo $user->display_name;?><br />
    
<?php

    } 
}


add_action( 'save_post', 'save_users_checkboxes' );

/**
*This is function that is responsible for saving the states of the checkboxes and update them accordingly.
*/
function save_users_checkboxes( $post_id ) {

   

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
    $contributors = serialize($_POST['contributors']);
    update_post_meta($post_id,'contributors',$contributors);
     $all_users = get_users();
    
}

add_filter('the_content','display_contributors');
/**
*This function is used to display the outout that is the list of contributors on the front side.
*/
function display_contributors($content){

    global $post;

    if(!is_singular("post"))
        return;

    $checkboxMeta = get_post_meta($post->ID,'contributors',true);
    $contributors = unserialize($checkboxMeta);
    $html = '';
    $html.= '
    <div class="contributors">
    <h2 class="heading">Contributors for this post</h2> ';
    $html2 = $html;
    $display_users = get_users(array('include'=>$contributors)); //This fetches an array of only those users that have been checked on the admin side. 
    if(is_array($contributors)){
    foreach($display_users as $user ){
        $html.= '<div class="users">'.get_avatar( $user->ID, 32) .'<a class="anchor" href="'.get_author_posts_url($user->ID).'" style=" color:#fff;text-decoration:none;">'.$user->display_name.'</a></div><br>';
    };
}

    if($html == $html2){
        $html.= '<span class="anchor">No contributors!</span>';
    }

    $html.= '</div>';
    $content.= $html;    
    return $content;
}

?>
