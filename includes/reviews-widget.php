<?php 
/**
 * Makes a custom Widget for displaying woocommerce reviews
  *
 * @package WordPress
 * @subpackage woocommerce-reviews-widget
 * @since woocommerce-reviews-widget
 */
 
 class WooCommerce_Widget_Products_Reviews extends WP_Widget {

	/** Variables to setup the widget. */
	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_name;
	
 function WooCommerce_Widget_Products_Reviews() {
		
		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget_products_reviews';
		$this->woo_widget_description = __( 'Display a list of your products reviews', 'woo-reviews-widget' );
		$this->woo_widget_idbase = 'widget_products_reviews';
		$this->woo_widget_name = __('WooCommerce Products Reviews List', 'woo-reviews-widget' );
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );
		
		/* Create the widget. */
		$this->WP_Widget('products_reviews', $this->woo_widget_name, $widget_ops);

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	/** @see WP_Widget */
	function widget($args, $instance) {
		global $woocommerce,  $wpdb;
		$order_by = $link_title = '';
		
		$cache = wp_cache_get('widget_products_reviews', 'widget');

		if ( !is_array($cache) ) $cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Products Reviews', 'woo-reviews-widget') : $instance['title'], $instance, $this->id_base);
		$number_of_comments = (int) $instance['number_of_comments'];
		$orderby 		= isset($instance['orderby']) ? $instance['orderby'] : 'rand';    	
		$order 			= isset($instance['order']) ? $instance['order'] : 'ASC';    
		$shop_thumbnail = isset($instance['shop_thumbnail'] ) ? $instance['shop_thumbnail'] : '1';		
		
		
		$rand_numeric = rand(1, 2500000);
		if ($orderby == 'rand') {
			$order_by = 'RAND ('.$rand_numeric.')';
		} else if ($orderby == 'name') {
			$order_by = 'p.post_title' . ' ' . $order;
		} else if ($orderby == 'date') {
			$order_by = 'c.comment_date'  . ' ' . $order;
		}
			
    	
		echo $before_widget; 
		if ( $title ) echo $before_title . $title . $after_title;
		
		/*Custom output html*/

			$query = "SELECT c.* FROM {$wpdb->prefix}posts p, {$wpdb->prefix}comments c WHERE p.ID = c.comment_post_ID AND c.comment_approved > 0 AND p.post_type = 'product' AND p.post_status = 'publish' AND p.comment_count > 0 	ORDER BY ".$order_by." LIMIT 0, ". $number_of_comments;
			$comments_products = $wpdb->get_results($query, OBJECT);
			if ($comments_products) {
				foreach ($comments_products as $comment_product) {
					$id_ = $comment_product->comment_post_ID;
					$name_author = 	$comment_product->comment_author;
					$comment_id  = 	$comment_product->comment_ID;
						if ( get_the_title($id_) ) { 
							 $link_title = get_the_title($id_); 
						}  else { 
							 $link_title = $id_;
						}	
						$image_link   = wp_get_attachment_image_src( get_post_thumbnail_id($id_), 'shop_thumbnail');
						$out_reviews .= '<li id="comment-'.$comment_id.'" class="list-item">';
							$out_reviews .= '<a href="'.get_comment_link($comment_id).'" title="'.esc_attr($link_title).'">';
							if (has_post_thumbnail($id_) && ($shop_thumbnail)) { 
								$out_reviews .= get_the_post_thumbnail($id_, 'shop_thumbnail');
							}
							$out_reviews .= $link_title . '</a>';
							$out_reviews .= '<p class="content-comment">'.get_comment_excerpt( $comment_id ) .'</p>';
							$out_reviews .= '<p class="box-author">'.$name_author.'</p>';
						$out_reviews .= '</li>';						
				}
			}
			
			if ($out_reviews != '') {
				$out_reviews = '<ul id="comments-list-products" class="comments-list-products">' . $out_reviews . '</ul>'; 
			} else {
				$out_reviews = '<ul id="comments-list-products" class="comments-list-products"><li><p class="content-comment">'. __('No products reviews.') . '</p></li></ul>'; 
			}
			echo $out_reviews;		
		/*End output*/
		
		echo $after_widget; 

		$content = ob_get_clean();
		if ( isset( $args['widget_id'] ) ) $cache[$args['widget_id']] = $content;
		echo $content;
		wp_cache_set('widget_products_reviews', $cache, 'widget');
	}

	/** @see WP_Widget->update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number_of_comments'] = (int) $new_instance['number_of_comments'];
		$instance['orderby'] = strip_tags($new_instance['orderby']);
		$instance['order'] = strip_tags($new_instance['order']);
		$instance['shop_thumbnail'] = strip_tags($new_instance['shop_thumbnail']);
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_products_reviews']) ) delete_option('widget_products_reviews');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_products_reviews', 'widget');
	}

	/** @see WP_Widget->form */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		if (isset( $instance['number_of_comments'] ) && ( (int)$instance['number_of_comments'] > 0))  {
			$number = $instance['number_of_comments'];
		} else {
			$number = 5;
		}		
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'RAND';
		$order = isset( $instance['order'] ) ? $instance['order'] : 'ASC';
		$shop_thumbnail = isset( $instance['shop_thumbnail'] ) ? $instance['shop_thumbnail'] : '1';
		
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woo-reviews-widget'); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number_of_comments'); ?>"><?php _e('Number of comments to show:', 'woo-reviews-widget'); ?></label>
		<input id="<?php echo esc_attr( $this->get_field_id('number_of_comments') ); ?>" name="<?php echo esc_attr( $this->get_field_name('number_of_comments') ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by:', 'woo-reviews-widget') ?></label>
		<select id="<?php echo esc_attr( $this->get_field_id('orderby') ); ?>" name="<?php echo esc_attr( $this->get_field_name('orderby') ); ?>">
			<option value="rand" <?php selected($orderby, 'order'); ?>><?php _e('Random Order', 'woo-reviews-widget'); ?></option>
			<option value="name" <?php selected($orderby, 'name'); ?>><?php _e('Name', 'woo-reviews-widget'); ?></option>
			<option value="date" <?php selected($orderby, 'date'); ?>><?php _e('Date', 'woo-reviews-widget'); ?></option>
		</select></p>
		
		<p><label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order:', 'woo-reviews-widget') ?></label>
		<select id="<?php echo esc_attr( $this->get_field_id('order') ); ?>" name="<?php echo esc_attr( $this->get_field_name('order') ); ?>">
			<option value="ASC"  <?php selected($order, 'ASC'); ?>><?php _e('ASC', 'woo-reviews-widget'); ?></option>
			<option value="DESC" <?php selected($order, 'DESC'); ?>><?php _e('DESC', 'woo-reviews-widget'); ?></option>
		</select></p>
		
		<p>	<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'shop_thumbnail' ) ) ?>" name="<?php echo esc_attr( $this->get_field_name('shop_thumbnail') ) ?>" value="1" <?php checked(true, $shop_thumbnail ) ?> />
			<label for="<?php echo $this->get_field_id( 'shop_thumbnail' ) ?>"><?php _e( 'Show thumbnail product', 'woocommerce' ) ?></label>
		</p>
		
		<?php
	}
}
