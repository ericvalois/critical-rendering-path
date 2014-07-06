<?php
/*/
Plugin Name: Critical Rendering Path
Plugin URI: bulledev.com
Description: WordPress plugin to help you with the Critical Rendering Path of your WordPress website.
Version: 0.1
Author: Eric Valois
Author URI: www.ericvalois.com
/*/

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'smashing_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'smashing_post_meta_boxes_setup' );

/* Meta box setup function. */
function smashing_post_meta_boxes_setup() {
  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'add_store_meta_boxes' );

  /* Save post meta on the 'save_post' hook. */
  add_action( 'save_post', 'save_adresse_meta', 10, 2 );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function add_store_meta_boxes() {

add_meta_box(
  'critical_rendering_path',      // Unique ID
  esc_html__( 'Critical Rendering Path' ),    // Title
  'afficher_store_meta_box',    // Callback function
  '',          // Admin page (or post type)
  'normal',         // Context
  'default'         // Priority
  );
}

/* Display the post meta box. */
function afficher_store_meta_box( $object, $box ) { ?>

<?php wp_nonce_field( basename( __FILE__ ), 'critical_nonce' ); ?>

<p>
<label for="critical_style"><?php _e( "Inline CSS" ); ?></label>
<br />
<textarea class="widefat" name="critical_style" id="critical_style"><?php echo esc_attr( get_post_meta( $object->ID, 'critical_style', true ) ); ?></textarea>
</p>

<p>
<label for="critical_disable_stylesheet"><?php _e( "Stylesheet key" ); ?></label>
<br />
<input class="widefat" type="text" name="critical_disable_stylesheet" id="critical_disable_stylesheet" value="<?php echo esc_attr( get_post_meta( $object->ID, 'critical_disable_stylesheet', true ) ); ?>" size="30" />
<span class="description">Locate the function "wp_enqueue_style" in your theme enter here the name of your stylesheet</span>
</p>

<?php }

/* Save the meta box's post metadata. */
function save_adresse_meta( $post_id, $post ) {

    /* Verify the nonce before proceeding. */
    if ( !isset( $_POST['critical_nonce'] ) || !wp_verify_nonce( $_POST['critical_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
      return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value[] = $_POST['critical_style'];
    $new_meta_value[] = $_POST['critical_disable_stylesheet'];

    /* Get the meta key. */
    $meta_key[] = 'critical_style';
    $meta_key[] = 'critical_disable_stylesheet';

    $cpt = 0;
    foreach($new_meta_value as $meta):
        /* Get the meta value of the custom field key. */
        $meta_value = get_post_meta( $post_id, $meta_key[$cpt], true );

        /* If a new meta value was added and there was no previous value, add it. */
        if ( $new_meta_value[$cpt] && '' == $meta_value )
            add_post_meta( $post_id, $meta_key[$cpt], $new_meta_value[$cpt], true );

        /* If the new meta value does not match the old value, update it. */
        elseif ( $new_meta_value[$cpt] && $new_meta_value[$cpt] != $meta_value )
            update_post_meta( $post_id, $meta_key[$cpt], $new_meta_value[$cpt] );

        /* If there is no new meta value but an old value exists, delete it. */
        elseif ( '' == $new_meta_value[$cpt] && $meta_value )
            delete_post_meta( $post_id, $meta_key[$cpt], $meta_value );
            $cpt++;
    endforeach;
}

function insert_some_css() {

    global $post;
    echo '<style type="text/css">';
    echo get_post_meta( $post->ID, 'critical_style', true );
    echo '</style>';
}

// Remove stylesheet if a stylesheet key is added
add_action('the_post', function_to_add);
function function_to_add()
{
    global $post;

    if( get_post_meta( $post->ID, 'critical_disable_stylesheet', true ) != ""){
      wp_dequeue_style(get_post_meta( $post->ID, 'critical_disable_stylesheet', true ));
      wp_deregister_style(get_post_meta( $post->ID, 'critical_disable_stylesheet', true ));
    }

    // Inject the css in the head
    add_action('wp_head', 'insert_some_css');
    
}



