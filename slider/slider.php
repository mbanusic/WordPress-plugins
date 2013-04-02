<?php
/*
Plugin Name: CarouFredSel Slider
Plugin URI: http://mbanusic.com
Description: Carousel slider plugin with widget
Version: 0.2
Author: Marko Banušić
Author URI: http://mbanusic.com
License: MIT
*/

//setup the size of the image here to properly crop
add_image_size( 'mbanusic_slider_crop', 648, 415, true );

//add widget

add_action( 'widgets_init'. 'mbanusic_widgets_init' );
function mbanusic_widgets_init()
{
    require_once('widget.php');
    register_widget( 'mbanusic_Slider_Widget' );
}

// WP_Ajax function to retrieve post titles
add_action('wp_ajax_mbanusic_slider_get_posts',  'mbanusic_slider_ajax_get_posts');
function mbanusic_slider_ajax_get_posts()
{
    global $wpdb;

    $results = $wpdb->get_results($wpdb->prepare('SELECT * FROM wp_posts WHERE post_title LIKE %s AND (post_type = "post") ORDER BY post_date DESC', "%" . $_GET['term'] . "%"));
    $return = array();
    foreach ($results as $result) {
        $return[] = array('id' => $result->ID, 'value' => $result->post_title);
    }

    echo json_encode($return);
    exit();
}

// Enqueue scripts
add_action('admin_init', 'mbanusic_slider_admin_init');
function mbanusic_slider_admin_init()
{
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_style('jquery.ui.theme', plugins_url('/css/smoothness/jquery-ui-1.9.2.custom.css', __FILE__));

}

// Enqueue scripts
add_action('wp_enqueue_scripts', 'mbanusic_init_scripts');
function mbanusic_init_scripts()
{
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'debounce', plugins_url( '/js/carousel/jquery.ba-throttle-debounce.min.js', __FILE__) );
    wp_enqueue_script( 'carouFredSel', plugins_url( '/js/carousel/jquery.carouFredSel.js', __FILE__) );
    wp_enqueue_script( 'mousewheel', plugins_url( '/js/carousel/jquery.mousewheel.min.js', __FILE__) );
    wp_enqueue_script( 'touchSwipe', plugins_url( '/js/carousel/jquery.touchSwipe.min.js', __FILE__) );
    wp_enqueue_script( 'roundabout', plugins_url('/js/carousel/jquery.roundabout.min.js', __FILE__) );
}


// Add menu item
add_action( 'admin_menu', 'mbanusic_slider_menu' );
function mbanusic_slider_menu()
{
    add_theme_page( 'Slider', 'Slider', 'edit_posts' , 'mbanusic_slider', 'mbanusic_slider' );
}

// Render menu item
function mbanusic_slider()
{
    if ($_POST['submit']) {
        $save = array();
        for($i=0; $i<sizeof($_POST['id']); $i++) {
            $save[] = array('id' => $_POST['id'][$i], 'od' => $_POST['od'][$i], 'do' => $_POST['do'][$i] );
        }
        $saved = update_option('mbanusic_slider_posts', $save);
        if ($saved){

            ?>
            <div>Saved!</div>
        <?php
        }
    }
    $items = get_option('mbanusic_slider_posts');
    ?>
    <style>
        #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
        #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
        #sortable li span { position: absolute; margin-left: -1.3em; }
    </style>
    <script>
        jQuery(function($) {
            jQuery( "#sortable" ).sortable();
            jQuery( "#sortable" ).disableSelection();
            jQuery('#sortable input').live('click.sortable mousedown.sortable',function(ev){
                ev.target.focus();
            });
            jQuery('#sortable .ui-icon-close').live('click', function() {
                $(this).parent().parent().remove();
            });
            jQuery("#sortable").delegate(".datepicker", "focusin", function () {
                $(this).datepicker();
            });
            jQuery( "#new" ).autocomplete({
                source: ajaxurl+"?action=bs_get_posts",
                minLength: 3,
                select: function( event, ui ) {
                    jQuery(".newid").val(ui.item.id);
                }
            });
            jQuery(".dodaj").bind('click', function() {
                $('#sortable').append('<li class="ui-state-default"><div style="float:left;"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div><div style="float:left;">'+$('.new').val()+'<input type="hidden" name="id[]" value="'+$('.newid').val()+'"></div><div style="float:right;"><span class="ui-icon ui-icon-close"></span></div> <div style="float: right; margin-top: -3px; margin-right: 30px;"><?php _e('From'); ?>: <input type="text" class="datepicker" name="od[]" value="" /> <?php _e('To'); ?>:<input type="text" class="datepicker" name="do[]" value="" /></div></li>')
            })
        });
    </script>
    <h1>Carousel slider</h1>
    <div>
        <form method="post">
            <ul id="sortable">
                <?php
                foreach ($items as $item) {
                    $post = get_post($item['id']);
                    ?>
                    <li class="ui-state-default"><div style="float:left;"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div><div style="float:left;"><?php echo $post->post_title; ?><input type="hidden" name="id[]" value="<?php echo $post->ID; ?>"></div><div style="float:right;"><span class="ui-icon ui-icon-close"></span></div> <div style="float: right; margin-top: -3px; margin-right: 30px;"><?php _e('From') ?>: <input type="text" class="datepicker" name="od[]" value="<?php echo $item['od'] ?>" /> <?php _e('To') ?>:<input type="text" class="datepicker" name="do[]" value="<?php echo $item['do'] ?>" /></div> </li>
                <?php
                }
                ?>
            </ul>
            <input type="submit" name="submit" class="button" value="Spremi">

        </form>
    </div>
    <?php  ?>
    <div>
        <input type="text" name="new" class="new" id="new">
        <input type="hidden" name="newid" class="newid">
        <button class="dodaj"><?php _e('Add') ?></button>
    </div>

<?php

}