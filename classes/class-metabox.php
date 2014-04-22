<?php
if ( ! class_exists( 'Wolf_Discography_Metabox' ) ) :
class Wolf_Discography_Metabox {

	var $meta = array();

	function __construct( $meta = array() ) {

		$class_dir = basename( dirname( dirname( __FILE__ ) ) ) . '/' . basename( dirname( __FILE__ ) );
		$assets_dir = plugins_url() . '/' . basename( dirname( dirname( __FILE__ ) ) ) . '/' . basename( dirname( __FILE__ ) ) . '/assets';
		$this->assets_dir = $assets_dir;

		$this->meta = $meta + $this->meta;
		add_action( 'add_meta_boxes', array( $this, 'add_meta' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_styles') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	// --------------------------------------------------------------------------

	/* Add meta box */
	function add_meta() {

	    	foreach ( $this->meta as $k => $v ) {
	    		if ( is_array($v['page'] ) ) {
	    			foreach ( $v['page'] as $p ) {
			    		add_meta_box(
					sanitize_title($k) . '_wolf_meta_box', // $id
				 	$v['title'], // $title 
				 	array($this, 'render'), // $callback
				 	$p, // $page
				 	'normal', // $context
				 	'high'); // $priority	
		    		}
			} else {

		    		add_meta_box(
				sanitize_title($k) . '_wolf_meta_box',
			 	$v['title'],
			 	array($this, 'render'),
			 	$v['page'], 
			 	'normal',
			 	'high');	

			}	
	    	}
	 
	}

	// --------------------------------------------------------------------------

	/**
	* Enqueue Metabox scripts
	*/
	function enqueue_scripts()
	{

		wp_enqueue_script( 'wolf-tour-dates-upload', $this->assets_dir . '/js/min/upload.min.js', 'jquery', true, '1.0' );
		wp_enqueue_media();
		wp_enqueue_script( 'wolf-tour-dates-datepicker', $this->assets_dir . '/js/min/datepicker.min.js', array( 'jquery-ui-datepicker' ), false, true );
		wp_enqueue_script( 'wolf-tour-dates-sortable', $this->assets_dir . '/js/min/sortable.min.js', array( 'jquery-ui-sortable' ), false, true );
		wp_enqueue_script( 'wolf-tour-dates-colorpicker', $this->assets_dir . '/js/min/colorpicker.min.js', array( 'wp-color-picker' ), false, true );
		wp_enqueue_script( 'jquery-ui-slider' );
		
		wp_enqueue_script( 'tipsy', $this->assets_dir . '/js/min/tipsy.min.js', 'jquery', true, '1.0.0' );
		
		/* Datepicker & rating slider CSS */
		wp_enqueue_style( 'jquery-ui-custom', $this->assets_dir . '/css/jquery-ui-custom.css' );
	}

	// --------------------------------------------------------------------------

	/**
	* Enqueue admin Style (admin CSS)
	*/
	function admin_styles()
	{
		wp_enqueue_style( 'wp-color-picker' );
	}

	// --------------------------------------------------------------------------

	/**
	* Display Inputs
	*/
	function render() {

		global $post;
		$meta_fields = array();
		
		$current_post_type = get_post_type( $post->ID );

		foreach ( $this->meta as $k=>$v ) {
			if ( is_array( $v['page'] ) ) {
				if ( in_array( $current_post_type, $v['page'] ) ) {
					$meta_fields = $v['metafields'];
				}
			} else {
				if ( $current_post_type == $v['page'] ) {
					$meta_fields = $v['metafields'];
				}
			}
		}

		// Use nonce for verification
		echo '<input type="hidden" name="custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__) ) . '" />';
		
		// Begin the field table and loop
		echo '<table class="form-table wolf-metabox-table">';

		foreach ( $this->meta as $k=>$v ) {

			if ( isset( $v['help'] ) ) {
				echo '<div class="wolf-metabox-help">' . $v['help'] . '</div>';
			}

		}

		foreach ( $meta_fields as $field ) {
			if ( ! isset( $field['desc'] ) ) $field['desc'] = '';
			if ( ! isset( $field['def'] ) ) $field['def'] = '';
			
			// get value of this field if it exists for this post
			$meta = get_post_meta( $post->ID, $field['id'], true );
			
			if ( ! $meta )
				$meta = $field['def'];
			// begin a table row with
			echo '<tr>
			
			<th style="width:20%"><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
			
			<td>';

				// editor
				if ( $field['type'] == 'editor' ) {
					wp_editor( $meta, $field['id'], $settings = array() );

			
				// text
				} elseif ( $field['type'] == 'text' ) {
				
					echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="'.$meta.'" size="30" />
							<br /><span class="description">' . $field['desc'] . '</span>';
				
				// textarea
				} elseif ( $field['type']=='textarea' ) {
					echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4">'.$meta.'</textarea>
							<br /><span class="description">' . $field['desc'] . '</span>';
				
				// checkbox
				} elseif ( $field['type']=='checkbox' ) {
					echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '" ',$meta ? ' checked="checked"' : '','/>
							<span class="description">' . $field['desc'] . '</span>';
				
				// select
				} elseif ($field['type']=='select') {
					
					echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
					if ( array_keys( $field['options']) != array_keys( array_keys( $field['options'] ) ) ) {
						foreach ( $field['options'] as $k => $option ) {
							echo '<option', $meta == $k ? ' selected="selected"' : '', ' value="'.$k.'">'.$option.'</option>';
						}
					} else {
						foreach ( $field['options'] as $option ) {
							echo '<option', $meta == $option ? ' selected="selected"' : '', ' value="'.$option.'">'.$option.'</option>';
						}
					}
					
					echo '</select><br /><span class="description">' . $field['desc'] . '</span>';
				
				// radio
				} elseif ($field['type']=='radio') {
					
					foreach ( $field['options'] as $option ) {
						echo '<input class="wolf-radio"  type="radio" name="' . $field['id'] . '" id="' . $option['value'] . '" value="' . $option['value'] . '" ',$meta == $option['value'] ? ' checked="checked"' : '',' />
							<label class="radio-label" for="' . $option['value'] . '">' . $option['label'] . '</label><br /><div class="clear"></div>';
					}
					echo '<span class="description">' . $field['desc'] . '</span>';
				
				} elseif ( $field['type']=='checkbox_group' ) {
					
					foreach ( $field['options'] as $option ) {
						echo '<input class="wolf-checkbox" type="checkbox" value="' . $option['value'] . '" name="' . $field['id'] . '[]" id="' . $option['value'] . '"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' /> 
								<label class="checkbox-label" for="' . $option['value'] . '">' . $option['label'] . '</label><br /><div class="clear"></div>';
					}
					echo '<span class="description">' . $field['desc'] . '</span>';
				
				} elseif ( $field['type'] == 'tax_select' ) {
					
					echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">
							<option value="">' . __( 'Select one', 'wolf' ) . '</option>'; // Select One
					
					$terms = get_terms( $field['id'], 'get=all' );
					
					$selected = wp_get_object_terms( $post->ID, $field['id'] );
					foreach ( $terms as $term ) {
						if ( ! empty( $selected ) && !strcmp( $term->slug, $selected[0]->slug ) ) 
							echo '<option value="' . $term->slug . '" selected="selected">'. $term->name . '</option>'; 
						else
							echo '<option value="' . $term->slug . '">'. $term->name . '</option>'; 
					}
					$taxonomy = get_taxonomy($field['id']);
					echo '</select><br /><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy=' . $field['id'] . '">Manage '.$taxonomy->label.'</a></span>';
				
				
				} elseif ( $field['type']=='post_list' ) {
				
				$items = get_posts( array (
					'post_type'	=> $field['post_type'],
					'posts_per_page' => -1
				));
					echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">
							<option value="">Select One</option>'; // Select One
						foreach ( $items as $item) {
							echo '<option value="'.$item->ID.'"',$meta == $item->ID ? ' selected="selected"' : '','>'.$item->post_type.': '.$item->post_title.'</option>';
						} // end foreach
					echo '</select><br /><span class="description">' . $field['desc'] . '</span>';
				
				
				// datepicker
				} elseif ($field['type'] == 'datepicker') {
					echo '<input type="text" class="wolf-metabox-datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="'.$meta.'" size="30" />
							<br /><span class="description">' . $field['desc'] . '</span>';
				
				// colorpicker
				} elseif ($field['type']=='colorpicker') {
					
					echo '<input type="text" class="wolf-metabox-colorpicker wolf-colorpicker-input" name="' . $field['id'] . '" id="' . $field['id'] . '" value="'.$meta.'" />
							<br /><span class="description">' . $field['desc'] . '</span>';


				// slider
				} elseif ($field['type']=='slider') {
				$value = $meta != '' ? $meta : '0';

					echo '<script type="text/javascript">jQuery(function($) { $( "#' . $field['id'] . '-slider" ).slider({
						value: '.$value.',
						min: '.$field['min'].',
						max: '.$field['max'].',
						step: '.$field['step'].',
						slide: function( event, ui ) {
							jQuery( "#' . $field['id'] . '" ).val( ui.value );
						}
					});});</script>';

					echo '<div id="' . $field['id'] . '-slider" class="wolf-slider-meta"></div>
							<input type="text" class="wolf-slider-input" name="' . $field['id'] . '" id="' . $field['id'] . '" value="'.$value.'" size="5" />
							<div style="clear:both"></div>
							<span class="description">' . $field['desc'] . '</span>';
				
				// file
				} elseif ($field['type']=='file') { 
					$meta_img = get_post_meta($post->ID, $field['id'], true);
				?>

				<div>
					<input type="hidden" name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo esc_url($meta_img); ?>">
					<img style="max-width:250px; cursor:pointer;<?php if ( $meta_img == '' ) echo ' display:none;'; ?>" class="wolf-metabox-img-preview wolf-metabox-upload-button" src="<?php echo esc_url($meta_img); ?>" alt="<?php echo $field['id']; ?>">
					<br><a href="#" class="button wolf-reset-metabox-bg"><?php _e( 'Clear', 'wolf' ); ?></a>
					<a href="#" class="button wolf-metabox-upload-button"><?php _e( 'Choose an image', 'wolf' ); ?></a>
				</div>
				
				<div style="clear:both"></div>
				<?php
				/*  Background
				-------------------------------------------*/
				} elseif ($field['type']=='background') {
					$bg_meta_color = get_post_meta($post->ID, $field['id'] . '_color', true);
					$bg_meta_img = get_post_meta($post->ID, $field['id'] . '_img', true);
					$bg_meta_repeat = get_post_meta($post->ID, $field['id'] . '_repeat', true);
					$bg_meta_position = get_post_meta($post->ID, $field['id'] . '_position', true);
					$bg_meta_attachment = get_post_meta($post->ID, $field['id'] . '_attachment', true);
					$bg_meta_size = get_post_meta($post->ID, $field['id'] . '_size', true);
					/* Bg Image */
					?>
					<p><strong><?php _e('Background color', 'wolf'); ?></strong></p>
					<input name="<?php echo  $field['id'] . '_color'; ?>" name="<?php echo  $field['id'] . '_color'; ?>" class="wolf-metabox-colorpicker" type="text" value="<?php echo $bg_meta_color; ?>">
					<br><br>
					
					<p><strong><?php _e('Background image', 'wolf'); ?></strong></p>
					
					<div>
						<input type="hidden" name="<?php echo $field['id']; ?>_img" id="<?php echo $field['id']; ?>_img" value="<?php echo esc_url($bg_meta_img); ?>">
						<img style="max-width:250px; cursor:pointer;<?php if ( $bg_meta_img == '' ) echo ' display:none;'; ?>" class="wolf-metabox-img-preview wolf-metabox-upload-button" src="<?php echo esc_url($bg_meta_img); ?>" alt="<?php echo $field['id']; ?>">
						<br><a href="#" class="button wolf-reset-metabox-bg"><?php _e( 'Clear', 'wolf' ); ?></a>
						<a href="#" class="button wolf-metabox-upload-button"><?php _e( 'Choose an image', 'wolf' ); ?></a>
					</div>
					<br><br>
					<?php
					/* Bg Repeat */
					$options = array( 'repeat', 'no-repeat', 'repeat-x', 'repeat-y' );

					?>
					<br>
					<p><strong><?php _e('Background repeat', 'wolf'); ?></strong></p>
					<select name="<?php echo $field['id'] . '_repeat'; ?>" id="<?php echo $field['id'] . '_repeat'; ?>">
						<?php foreach ($options as $o): ?>
							<option value="<?php echo $o; ?>" <?php if ( $bg_meta_repeat == $o  ) echo 'selected="selected"'; ?>><?php echo $o; ?></option>
						<?php endforeach; ?>
					</select>
					<?php

					/* Bg position */
					$options = array( 
						'center top', 
						'left top' ,
						'right top' , 
						'center bottom', 
						'left bottom' , 
						'right bottom' ,
						'center center' ,
						'left center' ,
						'right center'
					);

					?>
					<br><br>
					<p><strong><?php _e('Background position', 'wolf'); ?></strong></p>
					<select name="<?php echo $field['id'] . '_position'; ?>" id="<?php echo $field['id'] . '_position'; ?>">
						<?php foreach ($options as $o): ?>
							<option value="<?php echo $o; ?>" <?php if ( $bg_meta_position == $o  ) echo 'selected="selected"'; ?>><?php echo $o; ?></option>
						<?php endforeach; ?>
					</select>
					<?php

					/* Attachment
					--------------------*/
					$options = array( 'scroll', 'fixed'); 

					?>
					<br><br>
					<p><strong><?php _e('Background attachment', 'wolf'); ?></strong></p>
					<select name="<?php echo $field['id'] . '_attachment'; ?>" id="<?php echo $field['id'] . '_attachment'; ?>">
						<?php foreach ($options as $o): ?>
							<option value="<?php echo $o; ?>" <?php if ( $bg_meta_attachment == $o  ) echo 'selected="selected"'; ?>><?php echo $o; ?></option>
						<?php endforeach; ?>
					</select>
					<?php

					/* size
					--------------------*/
					$options = array( 'normal', 'tile'); 

					?>
					<br><br>
					<p><strong><?php _e('Background size', 'wolf'); ?></strong></p>
					<select name="<?php echo $field['id'] . '_size'; ?>" id="<?php echo $field['id'] . '_size'; ?>">
						<?php foreach ($options as $o): ?>
							<option value="<?php echo $o; ?>" <?php if ( $bg_meta_size == $o  ) echo 'selected="selected"'; ?>><?php echo $o; ?></option>
						<?php endforeach; ?>
					</select>
					<?php

				// image
				} elseif ($field['type']=='image') {
					$image = get_template_directory_uri().'/images/image.png';	
					
					echo '<span class="custom_default_image" style="display:none">'.$image.'</span>';
					if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium');	$image = $image[0]; }				
					echo	'<input name="' . $field['id'] . '" type="hidden" class="custom_upload_image" value="'.$meta.'" />
								<img src="'.$image.'" class="custom_preview_image" alt="" /><br />
									<input class="custom_upload_image_button button" type="button" value="Choose Image" />
									<small>&nbsp;<a href="#" class="custom_clear_image_button">Remove Image</a></small>
									<br clear="all" /><span class="description">' . $field['desc'] . '</span>';
				
				// repeatable
				} elseif ($field['type']=='repeatable') {
					echo '<a class="wolf-repeatable-add button" href="#">+</a>
							<ul id="' . $field['id'] . '-repeatable" class="wolf-custom-repeatable">';
					$i = 0;
					if ($meta) {
						foreach ( $meta as $row) {
							echo '<li><span class="sort hndle">|||</span>
										<input type="text" name="' . $field['id'] . '['.$i.']" id="' . $field['id'] . '" value="'.$row.'" size="30" />
										<a class="wolf-repeatable-remove button" href="#">-</a></li>';
							$i++;
						}
					} else {
						echo '<li><span class="sort hndle">|||</span>
									<input type="text" name="' . $field['id'] . '['.$i.']" id="' . $field['id'] . '" value="" size="30" />
									<a class="wolf-repeatable-remove button" href="#">-</a></li>';
					}
					echo '</ul>
						<span class="description">' . $field['desc'] . '</span>';
				
			} //end conditions
		echo '</td></tr>';
		} // end foreach
		echo '</table>'; // end table
	}

	// --------------------------------------------------------------------------


	/**
	* Save the Data*
	* Usually "wolf_{meta_value}"
	*/
	function save($post_id)
	{
	    	global $post;

	    	$meta_fields = '';
		
		// verify nonce
		if ( (isset($_POST['wolf_meta_box_nonce'])) && (!wp_verify_nonce($_POST['wolf_meta_box_nonce'], basename(__FILE__)))) 
			return $post_id;
		
		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $post_id;
		
		// check permissions
		if (isset($_POST['post_type']) && is_object($post) ) {
			
			$current_post_type = get_post_type($post->ID);
			
			if ('page' == $_POST['post_type']) {
				if (!current_user_can('edit_page', $post_id))
					return $post_id;
				} elseif (!current_user_can('edit_post', $post_id)) {
					return $post_id;
			}
		
			foreach ( $this->meta as $k=>$v) {

				if (is_array($v['page']))
					$condition = isset($_POST['post_type']) && in_array($_POST['post_type'], $v['page']);
				else
					$condition =isset($_POST['post_type']) && $_POST['post_type'] == $v['page'];

				if ( $condition ) {
					$meta_fields = $v['metafields'];
					
					// loop through fields and save the data
					foreach ($meta_fields as $field) {
						

						if ($field['type'] == 'tax_select') continue;


						if ( $field['type'] == 'background' ) {

							$meta = get_post_meta($post_id, $field['id'], true);
							
							$bg_settings = array('color', 'position', 'repeat', 'attachment', 'size', 'img');

							foreach (  $bg_settings as $s ) {

								$o = $field['id'].'_'.$s;
								
								if ( isset( $_POST[$o] ) ) {
									
									update_post_meta($post_id, $o , $_POST[$o] );
								}

							}


						} // end background

						else{
							$old = get_post_meta($post_id, $field['id'], true);
							$new = '';
							
							if (isset($_POST[$field['id']])) {

								if ( $field['type'] == 'editor' )
									$new = wpautop( wptexturize( $_POST[$field['id']] ) );
								else
									$new = $_POST[$field['id']];
							}
								
							
							if ($new && $new != $old) {

								update_post_meta($post_id, $field['id'], $new);
							
							} elseif ('' == $new && $old) {
								
								delete_post_meta($post_id, $field['id'], $old);
							}
						}


					} // enf foreach
				}
			}					
		}
	}	
}
endif;