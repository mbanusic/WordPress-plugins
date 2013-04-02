<?php

class mbanusic_Slider_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'mbanusic_slider_widget', // Base ID
            'Slider', // Name
            array( 'description' => __( 'Slider using the CarouFredSel'), )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance )
    {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        if ($title)
            $id = strtolower(str_replace(' ', '_', $title));
        else
            $id = 'slider';

        $posts = get_option('mbanusic_slider_posts');
        ?>
<script type="text/javascript" language="javascript">
	jQuery(function() {
		//	Scrolled by user interaction
		jQuery('#<?php echo $id; ?>').carouFredSel({
			width:648,
			height:415,
			auto:{
       			timeoutDuration : 5000
    			},
			pagination: "#pager",
			next : "#carousel .main_arrow.arrow_next",
			prev : "#carousel .main_arrow.arrow_prev",
			scroll : {
				duration : 500
			}
		});
	});
</script>

<div id="carousel" class="widget">
	<ul id="<?php echo $id; ?>">
			<?php
				$i=0;
                foreach($posts as $post_id) {
                       if ( (isset($post_id['od']) && $post_id['od'] && strtotime($post_id['od']) > time() ) || ( isset($post_id['do']) && $post_id['do'] && strtotime($post_id['do'])<time() ))
                           continue;
            	$post = get_post($post_id['id']);
                       if ($post->post_status != 'publish')
                           continue;

				$post = get_post($post_id['id']);
				$post_categories = wp_get_post_categories($post->ID);
				$cats = array();
            ?>
			<li class="slide"><a href="<?php echo get_permalink($post->ID); ?>"> <?php echo get_the_post_thumbnail($post->ID, 'mbanusic_slider_crop'); ?></a>
				<div class="carousel_caption">
					<div class="head_title">
					<?php
					$j=0;
					foreach($post_categories as $c){
						if($j<=0){
							$cat = get_category( $c );
							$cats[] = array( 'name' => $cat->name, 'id' => $cat->term_id );
							echo '<a href="'.get_category_link($cats[0]['id']).'">' . $cats[0]['name'] . '</a>';
						}$j++;
					}
					?>
					</div>
					<h2 class="title"><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></h2>
					<div class="meta"><?php echo get_the_time('', $post->ID);  ?> | <?php the_author_meta( 'user_nicename' , $post->post_author); ?> </div>
				</div>
				</li>
			<?php $i++;
                }
        wp_reset_query();
        ?>
		</ul>
		<div class="main_arrow arrow_next"><span></span></div>
		<div class="main_arrow arrow_prev"><span></span></div>
	<div id="pager" class="pager"> </div>
	<div class="clear"></div>
</div>
<?php
}
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance )
    {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance )
    {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'text_domain' );
        }
        ?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
	<?php _e( 'Title:' ); ?>
	</label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php
    }

}