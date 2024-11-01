<?php
/*
 * Plugin main file
 * @package   steptotal
 * @copyright 2023 Muratshaev DOO
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://muratshaev.me/steptotalplugin/
 *
 * @wordpress-plugin
 * Plugin Name:       Step by step calculator
 * Plugin URI:        https://muratshaev.me/steptotalplugin/
 * Description:       Plugin for step by step calculator for the cost of a product or service
 * Tested up to:      6.1
 * Requires PHP:      7.3
 * Version:			  1.0
 * Stable tag:        1.0
 * Author:            Yevgeniy Muratshayev
 * Author URI:        https://muratshaev.me/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       steptotal
 */

/**
 * Add localization
 */
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'steptotal', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}
);


/**
 * Add style plugin
 */
add_action( 'wp_enqueue_scripts', 'steptotal_name_scripts' );
function steptotal_name_scripts() {
	wp_enqueue_style( 'related-styles', plugins_url( '/css/style.css', __FILE__ ) );
}

/**
 * Add type post Step Total
 */
function stp_post_create_steptotal_posttype() {
	$labels = array(
		'name'               => _x( 'Step by step Calculator', 'Type posts Step by step Calculator', 'steptotal' ),
		'singular_name'      => _x( 'Step by step Calculator', 'Type posts Step by step Calculator', 'steptotal' ),
		'menu_name'          => __( 'Step by step Calculator', 'steptotal' ),
		'all_items'          => __( 'All steps', 'steptotal' ),
		'view_item'          => __( 'Look Step of calculator', 'steptotal' ),
		'add_new_item'       => __( 'Add new Step for Calculator', 'steptotal' ),
		'add_new'            => __( 'Add new', 'steptotal' ),
		'edit_item'          => __( 'Edit Step of caclulator', 'steptotal' ),
		'update_item'        => __( 'Update Step of calculator', 'steptotal' ),
		'search_items'       => __( 'Search Step', 'steptotal' ),
		'not_found'          => __( 'Not found', 'steptotal' ),
		'not_found_in_trash' => __( 'Not found in trash', 'steptotal' ),
	);
	$args   = array(
		'label'               => __( 'steptotal', 'steptotal' ),
		'description'         => __( 'Step by step Calculator', 'steptotal' ),
		'labels'              => $labels,
		'supports'            => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'comments',
			'revisions',
			'custom-fields',
		),
		'taxonomies'          => array( 'steptotal' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 15,
		'menu_icon'           => 'dashicons-tickets-alt',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'steptotal', $args );
}

add_action( 'init', 'stp_post_create_steptotal_posttype', 0 );

/**
 * Add meta boxess for Step Total
 */
add_action( 'add_meta_boxes', 'stp_steptotal_fields', 1 );
function stp_steptotal_fields() {
	add_meta_box( 'stp_steptotal_fields', 'Addition fields', 'stp_steptotal_fields_func', 'steptotal', 'normal', 'high' );
}

function stp_steptotal_fields_func( $post ) {
	global $post;
	$step_name = get_post_meta( $post->ID, 'step_name', true );
	$img       = get_post_meta( $post->ID, 'img', true );
	?>
	<p><?php echo esc_html( __( 'Shorcode for insert:', 'steptotal' ) ); ?> [steptotal
		id="<?php echo esc_html( $post->ID ); ?>"]</p>
	<p><label for="step_name"><?php echo esc_html( __( 'Step name', 'steptotal' ) ); ?></label></p>
	<input type="text" name="step_name" id="step_name" value="<?php echo esc_html( $step_name ); ?>">
	<p><label for="img"><?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?></label></p>
	<input type="text" id="img" placeholder="<?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?>" name="img"
		   value="<?php echo esc_url( $img ); ?>" style="70%">
	<input type="button" class="buttoncam" value="<?php echo esc_html( __( 'Select Photo', 'steptotal' ) ); ?>">
	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>"/>
	<script>
		jQuery(document).ready(function () {
			jQuery('.buttoncam').click(function (e) {
				e.preventDefault();
				var inp = document.getElementById('img');
				//var inp = jQuery(this).siblings('input');
				var image = wp.media({
					title: '<?php echo esc_html( __( 'Upload Image', 'steptotal' ) ); ?>',
					multiple: false
				}).open()
					.on('select', function (e) {
						var uploaded_image = image.state().get('selection').first();
						var image_url = uploaded_image.toJSON().url;
						document.getElementById('img').value = image_url;
					});
			});

		});
	</script>
	<?php
}

add_action( 'save_post', 'stp_step_total_meta_save' );
function stp_step_total_meta_save( $post_id ) {
	if ( ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST['step_name'] ) ) {
		return false;
	}
	$step_name = sanitize_text_field( $_POST['step_name'] );
	$img       = sanitize_url( $_POST['img'] );
	update_post_meta( $post_id, 'step_name', $step_name );
	update_post_meta( $post_id, 'img', $img );

	return $post_id;
}

/**
 * Add type post Steps for Step Total
 */
add_action( 'init', 'stp_post_create_steps_total_posttype', 0 );
function stp_post_create_steps_total_posttype() {
	$labels = array(
		'name'               => _x( 'Steps for Step by step Calculator', 'Type posts Step by step Calculator', 'steptotal' ),
		'singular_name'      => _x( 'Steps for Step by step Calculator', 'Type posts Steps for Step by step Calculator', 'steptotal' ),
		'menu_name'          => __( 'Steps', 'steptotal' ),
		'all_items'          => __( 'All steps', 'steptotal' ),
		'view_item'          => __( 'Look Step', 'steptotal' ),
		'add_new_item'       => __( 'Add new Step', 'steptotal' ),
		'add_new'            => __( 'Add new step', 'steptotal' ),
		'edit_item'          => __( 'Edit Step', 'steptotal' ),
		'update_item'        => __( 'Update Step', 'steptotal' ),
		'search_items'       => __( 'Search Step', 'steptotal' ),
		'not_found'          => __( 'Not found', 'steptotal' ),
		'not_found_in_trash' => __( 'Not found in trash', 'steptotal' ),
	);
	$args   = array(
		'label'               => __( 'stepstotal', 'steptotal' ),
		'description'         => __( 'Step by step Calculator', 'steptotal' ),
		'labels'              => $labels,
		'supports'            => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'comments',
			'revisions',
			'custom-fields',
		),
		'taxonomies'          => array( 'stepstotal' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 16,
		'menu_icon'           => 'dashicons-laptop',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'stepstotal', $args );
}

/**
 * Add metabox for steps for step total
 */
add_action( 'add_meta_boxes', 'stp_steps_fields', 1 );
function stp_steps_fields() {
	add_meta_box( 'stp_steps_fields', 'Addition fields', 'stp_steps_fields_func', 'stepstotal', 'normal', 'high' );
}

function stp_steps_fields_func( $post ) {
	global $post;
	$step      = get_post_meta( $post->ID, 'step', true );
	$orderstep = get_post_meta( $post->ID, 'orderstep', true );
	$step_name = get_post_meta( $post->ID, 'step_name', true );
	$img       = get_post_meta( $post->ID, 'img', true );
	?>
	<p><?php echo esc_html( __( 'Step by step Calculator', 'steptotal' ) ); ?></p>
	<select name="step" id="step">
		<?php
		$args      = array(
			'post_type'      => 'steptotal',
			'posts_per_page' => - 1,
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				if ( $step == $post->ID ) {
					echo '<option value="' . esc_html( $post->ID ) . '" selected>' . esc_html( get_post_meta( $post->ID, 'step_name', true ) ) . '</option>';
				} else {
					echo '<option value="' . esc_html( $post->ID ) . '">' . esc_html( get_post_meta( $post->ID, 'step_name', true ) ) . '</option>';
				}
			}
		}
		?>
	</select>
	<p><label for="orderstep"><?php echo esc_html( __( 'Order', 'steptotal' ) ); ?></label></p>
	<p><input type="number" name="orderstep" id="orderstep" value="<?php echo esc_html( $orderstep ); ?>"></p>
	<p><label for="step_name"><?php echo esc_html( __( 'Step name', 'steptotal' ) ); ?></label></p>
	<p><input type="text" name="step_name" id="step_name" value="<?php echo esc_html( $step_name ); ?>"></p>
	<p><label for="img"><?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?></label></p>
	<input type="text" id="img" placeholder="<?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?>" name="img"
		   value="<?php echo esc_url( $img ); ?>" style="70%">
	<input type="button" class="buttoncam" value="<?php echo esc_html( __( 'Select Photo', 'steptotal' ) ); ?>">
	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>"/>
	<script>
		jQuery(document).ready(function () {
			jQuery('.buttoncam').click(function (e) {
				e.preventDefault();
				var inp = document.getElementById('img');
				//var inp = jQuery(this).siblings('input');
				var image = wp.media({
					title: '<?php echo esc_html( __( 'Upload Image', 'steptotal' ) ); ?>',
					multiple: false
				}).open()
					.on('select', function (e) {
						var uploaded_image = image.state().get('selection').first();
						var image_url = uploaded_image.toJSON().url;
						document.getElementById('img').value = image_url;
					});
			});
		});
	</script>
	<?php
}

add_action( 'save_post', 'stp_steps_total_meta_save' );
function stp_steps_total_meta_save( $post_id ) {
	if ( ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST['step_name'] ) ) {
		return false;
	}
	$step      = sanitize_text_field( $_POST['step'] );
	$step_name = sanitize_text_field( $_POST['step_name'] );
	$orderstep = sanitize_text_field( $_POST['orderstep'] );
	$img       = sanitize_url( $_POST['img'] );
	update_post_meta( $post_id, 'step', $step );
	update_post_meta( $post_id, 'orderstep', $orderstep );
	update_post_meta( $post_id, 'step_name', $step_name );
	update_post_meta( $post_id, 'img', $img );

	return $post_id;
}

/**
 * Add type Select for Step Total
 */
add_action( 'init', 'stp_post_create_select_steps_posttype', 0 );
function stp_post_create_select_steps_posttype() {
	$labels = array(
		'name'               => _x( 'Select for Calculator', 'Type posts Select Calculator', 'steptotal' ),
		'singular_name'      => _x( 'Select for Calculator', 'Type posts Select for Calculator', 'steptotal' ),
		'menu_name'          => __( 'Select', 'steptotal' ),
		'all_items'          => __( 'All Select', 'steptotal' ),
		'view_item'          => __( 'Look Select', 'steptotal' ),
		'add_new_item'       => __( 'Add new Select', 'steptotal' ),
		'add_new'            => __( 'Add new Select', 'steptotal' ),
		'edit_item'          => __( 'Edit Select', 'steptotal' ),
		'update_item'        => __( 'Update Select', 'steptotal' ),
		'search_items'       => __( 'Search Select', 'steptotal' ),
		'not_found'          => __( 'Not found', 'steptotal' ),
		'not_found_in_trash' => __( 'Not found in trash', 'steptotal' ),
	);
	$args   = array(
		'label'               => __( 'selecttotal', 'steptotal' ),
		'description'         => __( 'Select for Calculator', 'steptotal' ),
		'labels'              => $labels,
		'supports'            => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'comments',
			'revisions',
			'custom-fields',
		),
		'taxonomies'          => array( 'selecttotal' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 17,
		'menu_icon'           => 'dashicons-editor-ol',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'selecttotal', $args );
}

/**
 * Add metabox for Select for Step Total
 */
add_action( 'add_meta_boxes', 'stp_select_fields', 1 );
function stp_select_fields() {
	add_meta_box( 'stp_select_fields', 'Addition Fields', 'stp_select_fields_func', 'selecttotal', 'normal', 'high' );
}

function stp_select_fields_func( $post ) {
	global $post;
	$step_step   = get_post_meta( $post->ID, 'step_step', true );
	$orderstep   = get_post_meta( $post->ID, 'orderstep', true );
	$select_name = get_post_meta( $post->ID, 'select_name', true );
	$price       = get_post_meta( $post->ID, 'price', true );
	$img         = get_post_meta( $post->ID, 'img', true );

	?>
	<p><label for="step_step"><?php echo esc_html( __( 'Step of Calculator', 'steptotal' ) ); ?></label></p>
	<select name="step_step" id="step_step">
		<?php
		$args      = array(
			'post_type'      => 'stepstotal',
			'posts_per_page' => - 1,
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				if ( $step_step == $post->ID ) {
					echo '<option value="' . esc_html( $post->ID ) . '" selected>' . esc_html( get_post_meta( $post->ID, 'step_name', true ) ) . '</option>';
				} else {
					echo '<option value="' . esc_html( $post->ID ) . '">' . esc_html( get_post_meta( $post->ID, 'step_name', true ) ) . '</option>';
				}
			}
		}
		?>
	</select>
	<p><label for="orderstep"><?php echo esc_html( __( 'Order', 'steptotal' ) ); ?></label></p>
	<input type="number" name="orderstep" id="orderstep" value="<?php echo esc_html( $orderstep ); ?>">
	<p><label for="select_name"><?php echo esc_html( __( 'Step name', 'steptotal' ) ); ?></label></p>
	<input type="text" name="select_name" id="select_name" value="<?php echo esc_html( $select_name ); ?>">
	<p><label for="price"><?php echo esc_html( __( 'Price', 'steptotal' ) ); ?></label></p>
	<input type="number" name="price" id="price" value="<?php echo esc_html( $price ); ?>">
	<p><label for="img"><?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?></label></p>
	<input type="text" id="img" placeholder="<?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?>" name="img"
		   value="<?php echo esc_url( $img ); ?>" style="70%">
	<input type="button" class="buttoncam" value="<?php echo esc_html( __( 'Select Photo', 'steptotal' ) ); ?>">
	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>"/>
	<script>
		jQuery(document).ready(function () {
			jQuery('.buttoncam').click(function (e) {
				e.preventDefault();
				var inp = document.getElementById('img');
				//var inp = jQuery(this).siblings('input');
				var image = wp.media({
					title: '<?php echo esc_html( __( 'Upload Image', 'steptotal' ) ); ?>',
					multiple: false
				}).open()
					.on('select', function (e) {
						var uploaded_image = image.state().get('selection').first();
						var image_url = uploaded_image.toJSON().url;
						document.getElementById('img').value = image_url;
					});
			});

		});
	</script>
	<?php
}

add_action( 'save_post', 'stp_select_total_meta_save' );
function stp_select_total_meta_save( $post_id ) {
	if ( ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST['select_name'] ) ) {
		return false;
	}
	$step_step   = sanitize_text_field( $_POST['step_step'] );
	$select_name = sanitize_text_field( $_POST['select_name'] );
	$price       = sanitize_text_field( $_POST['price'] );
	$orderstep   = sanitize_text_field( $_POST['orderstep'] );
	$img         = sanitize_url( $_POST['img'] );
	update_post_meta( $post_id, 'step_step', $step_step );
	update_post_meta( $post_id, 'orderstep', $orderstep );
	update_post_meta( $post_id, 'select_name', $select_name );
	update_post_meta( $post_id, 'price', $price );
	update_post_meta( $post_id, 'img', $img );

	return $post_id;
}

/**
 * Add type Option for Step total
 */
add_action( 'init', 'stp_post_create_option_steps_posstype', 0 );
function stp_post_create_option_steps_posstype() {
	$labels = array(
		'name'               => _x( 'Option for Calculator', 'Type posts Option for Calculator', 'steptotal' ),
		'singular_name'      => _x( 'Option for Calculator', 'Type posts Option for Calculator', 'steptotal' ),
		'menu_name'          => __( 'Options', 'steptotal' ),
		'all_items'          => __( 'All options', 'steptotal' ),
		'view_item'          => __( 'Look Option', 'steptotal' ),
		'add_new_item'       => __( 'Add new Option', 'steptotal' ),
		'add_new'            => __( 'Add new Option', 'steptotal' ),
		'edit_item'          => __( 'Edit Option', 'steptotal' ),
		'update_item'        => __( 'Update Option', 'steptotal' ),
		'search_items'       => __( 'Search Option', 'steptotal' ),
		'not_found'          => __( 'Not found', 'steptotal' ),
		'not_found_in_trash' => __( 'Not found in trash', 'steptotal' ),
	);
	$args   = array(
		'label'               => __( 'optiontotal', 'steptotal' ),
		'description'         => __( 'Option for Calculator', 'steptotal' ),
		'labels'              => $labels,
		'supports'            => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'comments',
			'revisions',
			'custom-fields',
		),
		'taxonomies'          => array( 'optiontotal' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 18,
		'menu_icon'           => 'dashicons-arrow-up',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'optiontotal', $args );
}

/**
 * Add metabox for Option for Step total
 */
add_action( 'add_meta_boxes', 'stp_option_fields', 1 );
function stp_option_fields() {
	add_meta_box( 'stp_option_fields', 'Addition fields', 'option_fileds_func', 'optiontotal', 'normal', 'high' );
}

function option_fileds_func( $post ) {
	global $post;
	$select_step = get_post_meta( $post->ID, 'select_step', true );
	$orderstep   = get_post_meta( $post->ID, 'orderstep', true );
	$option_name = get_post_meta( $post->ID, 'option_name', true );
	$price       = get_post_meta( $post->ID, 'price', true );
	$img         = get_post_meta( $post->ID, 'img', true );

	?>
	<p><label for="select_step"><?php echo esc_html( __( 'Select for Calculator' ) ); ?></label></p>
	<select name="select_step" id="select_step">
		<?php
		$args      = array(
			'post_type'     => 'selecttotal',
			'post_per_page' => - 1,
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				if ( $select_step == $post->ID ) {
					echo '<option value="' . esc_html( $post->ID ) . '" selected>' . esc_html( get_post_meta( $post->ID, 'select_name', true ) ) . '</option>';
				} else {
					echo '<option value="' . esc_html( $post->ID ) . '">' . esc_html( get_post_meta( $post->ID, 'select_name', true ) ) . '</option>';
				}
			}
		}
		?>
	</select>
	<p><label for="orderstep"><?php echo esc_html( __( 'Order', 'steptotal' ) ); ?></label></p>
	<input type="number" name="orderstep" id="orderstep" value="<?php echo esc_html( $orderstep ); ?>">
	<p><label for="option_name"><?php echo esc_html( __( 'Option name', 'steptotal' ) ); ?></label></p>
	<input type="text" name="option_name" id="option_name" value="<?php echo esc_html( $option_name ); ?>">
	<p><label for="price"><?php echo __( 'Price', 'steptotal' ); ?></label></p>
	<input type="number" name="price" id="price" value="<?php echo esc_html( $price ); ?>">
	<p><label for="img"><?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?></label></p>
	<input type="text" id="img" placeholder="<?php echo esc_html( __( 'Photo', 'steptotal' ) ); ?>" name="img"
		   value="<?php echo esc_url( $img ); ?>" style="70%">
	<input type="button" class="buttoncam" value="<?php echo esc_html( __( 'Select Photo', 'steptotal' ) ); ?>">
	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>"/>
	<script>
		jQuery(document).ready(function () {
			jQuery('.buttoncam').click(function (e) {
				e.preventDefault();
				var inp = document.getElementById('img');
				//var inp = jQuery(this).siblings('input');
				var image = wp.media({
					title: '<?php echo esc_html( __( 'Upload Image', 'steptotal' ) ); ?>',
					multiple: false
				}).open()
					.on('select', function (e) {
						var uploaded_image = image.state().get('selection').first();
						var image_url = uploaded_image.toJSON().url;
						document.getElementById('img').value = image_url;
					});
			});

		});
	</script>
	<?php
}

add_action( 'save_post', 'stp_option_total_meta_save' );
function stp_option_total_meta_save( $post_id ) {
	if ( ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST['option_name'] ) ) {
		return false;
	}
	$select_step = sanitize_text_field( $_POST['select_step'] );
	$option_name = sanitize_text_field( $_POST['option_name'] );
	$price       = sanitize_text_field( $_POST['price'] );
	$orderstep   = sanitize_text_field( $_POST['orderstep'] );
	$img         = sanitize_url( $_POST['img'] );
	update_post_meta( $post_id, 'select_step', $select_step );
	update_post_meta( $post_id, 'orderstep', $orderstep );
	update_post_meta( $post_id, 'option_name', $option_name );
	update_post_meta( $post_id, 'price', $price );
	update_post_meta( $post_id, 'img', $img );

	return $post_id;
}

/**
 * Add type post for invoices
 */
add_action( 'init', 'stp_create_invoicetotal_posttype', 0 );
function stp_create_invoicetotal_posttype() {
	$labels = array(
		'name'               => _x( 'Invoice for Calculator', 'Type posts Invoice for Calculator', 'steptotal' ),
		'singular_name'      => _x( 'Invoice for Calculator', 'Type posts Invoice for Calculator', 'steptotal' ),
		'menu_name'          => __( 'Invoice for Calculator', 'steptotal' ),
		'all_items'          => __( 'All Invoices for Calculators', 'steptotal' ),
		'view_item'          => __( 'Look Invoice for Calculator', 'steptotal' ),
		'add_new_item'       => __( 'Add new Invoice for Calculator', 'steptotal' ),
		'add_new'            => __( 'Add new', 'steptotal' ),
		'edit_item'          => __( 'Edit Invoice for Calculator', 'steptotal' ),
		'update_item'        => __( 'Update Invoice for Calculator', 'steptotal' ),
		'search_items'       => __( 'Search Invoice for Calculator', 'steptotal' ),
		'not_found'          => __( 'Not found', 'steptotal' ),
		'not_found_in_trash' => __( 'Not found in trash', 'steptotal' ),
	);
	$args   = array(
		'label'               => __( 'invoicetotal', 'steptotal' ),
		'description'         => __( 'Invoice for Calculator', 'steptotal' ),
		'labels'              => $labels,
		'supports'            => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'comments',
			'revisions',
			'custom-fields',
		),
		'taxonomies'          => array( 'invoicetotal' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 19,
		'menu_icon'           => 'dashicons-media-document',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'invoicetotal', $args );
}

/**
 * add metabox for Invoice Step Total
 */
add_action( 'add_meta_boxes', 'stp_invoice_fields', 1 );
function stp_invoice_fields() {
	add_meta_box( 'stp_invoice_fields', 'Additions fields', 'stp_invoice_fields_func', 'invoicetotal', 'normal', 'high' );
}

function stp_invoice_fields_func( $post ) {
	global $post;

	$total_calc  = get_post_meta( $post->ID, 'total_calc', true );
	$user_name   = get_post_meta( $post->ID, 'user_name', true );
	$email       = get_post_meta( $post->ID, 'email', true );
	$total_price = get_post_meta( $post->ID, 'total_price', true );

	?>
	<p><label for="total_calc"><?php echo esc_html( __( 'Step by step Calculator', 'steptotal' ) ); ?></label></p>
	<select name="total_calc" id="total_calc">
		<?php
		$args      = array(
			'post_type'      => 'steptotal',
			'posts_per_page' => - 1,
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				if ( $total_calc == $post->ID ) {
					echo '<option value="' . esc_html( $post->ID ) . '" selected>' . esc_html( get_post_meta( $post->ID, 'step_name', true ) ) . '</option>';
				} else {
					echo '<option value="' . esc_html( $post->ID ) . '">' . esc_html( get_post_meta( $post->ID, 'step_name', true ) ) . '</option>';
				}
			}
		}
		?>
	</select>
	<p><label for="user_name"><?php echo esc_html( __( 'Username', 'steptotal' ) ); ?></label></p>
	<input type="text" name="user_name" id="user_name" value="<?php echo esc_html( $user_name ); ?>">
	<p><label for="email"><?php echo esc_html( __( 'Email', 'steptotal' ) ); ?></label></p>
	<input type="email" name="email" id="email" value="<?php echo esc_html( $email ); ?>">
	<p><label for="total_price"><?php echo esc_html( __( 'Total Price', 'steptotal' ) ); ?></label></p>
	<input type="number" name="total_price" id="total_price" value="<?php echo esc_html( $total_price ); ?>">
	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>"/>
	<?php
}

add_action( 'save_post', 'stp_invoice_total_meta_save' );
function stp_invoice_total_meta_save( $post_id ) {
	if ( ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST['user_name'] ) ) {
		return false;
	}
	$total_calc  = sanitize_text_field( $_POST['total_calc'] );
	$user_name   = sanitize_text_field( $_POST['user_name'] );
	$email       = sanitize_email( $_POST['email'] );
	$total_price = sanitize_text_field( $_POST['total_price'] );

	update_post_meta( $post_id, 'total_calc', $total_calc );
	update_post_meta( $post_id, 'user_name', $user_name );
	update_post_meta( $post_id, 'email', $email );
	update_post_meta( $post_id, 'total_price', $total_price );

	return $post_id;
}

/**
 * Add Shortcode for plugin
 */
add_shortcode( 'steptotal', 'stp_steptotal_shortcode' );
function stp_steptotal_shortcode( $atts ) {
	global $wp;
	global $post;
	$current_slug = add_query_arg( array(), $wp->request );
	$atts         = shortcode_atts(
		array(
			'id' => 0,
		),
		$atts
	);
	$id_calc      = esc_attr( $atts['id'] );
	$calc_post    = get_post( $id_calc );
	$calc_title   = esc_html( get_the_title( $id_calc ) );

	$txt      = '';
	$img_step = get_post_meta( $id_calc, 'img', true );
	$img_step = '<img src="' . esc_url( $img_step ) . '" class="img-fluid">';

	// ----------- Steps ---------------------------
	$ar_steps    = array();
	$count_steps = 0;
	$args_steps  = array(
		'post_type'      => 'stepstotal',
		'posts_per_page' => - 1,
		'orderby'        => 'meta_value_num',
		'meta_key'       => 'orderstep',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'   => 'step',
				'value' => $id_calc,
			),
		),
	);
	$the_query   = new WP_Query( $args_steps );
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$ar_steps[ $count_steps ]['ID']   = $post->ID;
			$ar_steps[ $count_steps ]['name'] = get_post_meta( $post->ID, 'step_name', true );
			$count_steps ++;
		}
	}

	if ( isset( $_REQUEST['mod'] ) ) {
		$mod  = sanitize_text_field( $_REQUEST['mod'] );
		$step = 0;
		if ( isset( $_REQUEST['finishBut'] ) ) {
			$id_steps = sanitize_text_field( $_REQUEST['id_steps'] );
			$step     = 1;
		}

		if ( isset( $_REQUEST['finalButt'] ) ) {
			$step = 2;
		}

		if ( isset( $_REQUEST['nextButton'] ) ) {
			$id_steps = sanitize_text_field( $_REQUEST['id_steps'] );
		}

		if ( isset( $_REQUEST['backButton'] ) ) {
			$id_steps = sanitize_text_field( $_REQUEST['id_back_steps'] );
		}

		// ------------- Steps -------------
		if ( $step == 0 ) {
			$txt          = $txt . '
                <div class="container">
                    <div class="sc-row">
                        <div class="sc-100">
                            <h2>' . esc_html( get_post_meta( $id_steps, 'step_name', true ) ) . '</h2>
                        </div>
                    </div>
                
            ';
			$ar_img_steps = get_post_meta( $id_steps, 'img', true );
			$img_steps    = '<img src="' . esc_url( $ar_img_steps ) . '" class="img-fluid">';
			$txt          = $txt . '
            <div class="sc-row">
                <div class="sc-100">' . $img_steps . '</div>
            </div>';
			$txt          = $txt . '<form name="selectForm" action="' . get_home_url() . '/' . $current_slug . '/" method="post">';
			$select       = sanitize_text_field( $_REQUEST['select'] );
			if ( $select == 0 ) {
				$txt = $txt . '<div class="sc-row"><div class="sc-10"><label class="radio"><input type="radio" name="select" value="0" checked class="step-radio"><span>&nbsp;</span></label></div><div class="sc-30"></div><div class="sc-60">' . esc_html( __( 'No select', 'steptotal' ) ) . '</div></div>';
			} else {
				$txt = $txt . '<div class="sc-row"><div class="sc-10"><label class="radio"><input type="radio" name="select" value="0" class="step-radio"><span>&nbsp;</span></label></div><div class="sc-30"></div><div class="sc-60">' . esc_html( __( 'No select', 'steptotal' ) ) . '</div></div>';
			}
			$txt         = $txt . '<hr>';
			$select_args = array(
				'post_type'      => 'selecttotal',
				'posts_per_page' => - 1,
				'orderby'        => 'meta_value_num',
				'meta_key'       => 'orderstep',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'   => 'step_step',
						'value' => $id_steps,
					),
				),
			);
			$the_query   = new WP_Query( $select_args );
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$txt         = $txt . '<div class="sc-row"><div class="sc-10">';
					$select_name = 'select_' . $id_steps;
					$arSel       = sanitize_text_field( $_REQUEST[ $select_name ] );
					if ( $arSel == $post->ID ) {
						$txt = $txt . '<label class="radio"><input type="radio" name="select" value="' . esc_html( $post->ID ) . '" checked class="step-radio"><span>&nbsp;</span></label>';
					} else {
						$txt = $txt . '<label class="radio"><input type="radio" name="select" value="' . esc_html( $post->ID ) . '" class="step-radio"><span>&nbsp;</span></label>';
					}
					$txt           = $txt . '</div>';
					$ar_img_select = get_post_meta( $post->ID, 'img', true );
					$img_select    = '<img src="' . esc_url( $ar_img_select ) . '" class="img-fluid">';
					$txt           = $txt . '<div class="sc-30">' . $img_select . '</div>';
					$txt           = $txt . '<div class="sc-50">' . apply_filters( 'the_content', $post->post_content ) . '</div>';
					$txt           = $txt . '<div class="sc-10">' . esc_html( get_post_meta( $post->ID, 'price', true ) ) . '</div>';
					$txt           = $txt . '</div>';

					$select_id = $post->ID;

					$opt = stp_getOptionSelect_m( $select_id );

					$count_opt = count( $opt );
					$met_opt   = 0;
					for ( $m = 0; $m < $count_opt; $m ++ ) {
						if ( $met_opt == 0 ) {
							$txt     = $txt . '<div class="sc-row"><div class="sc-100">' . esc_html( __( 'Options:', 'steptotal' ) ) . '</div></div>';
							$met_opt = 1;
						}
						$txt      = $txt . '<div class="sc-row">';
						$met      = 0;
						$opt_name = 'options_' . $id_steps;
						$arOpt    = sanitize_text_field( $_REQUEST[ $opt_name ] );
						$arOpt    = explode( ',', $arOpt );
						$c_opt    = count( $arOpt );
						if ( $c_opt > 0 ) {
							for ( $c = 0; $c < $c_opt; $c ++ ) {
								if ( $opt[ $m ]['ID'] == $arOpt[ $c ] ) {
									$met = 1;
								}
							}
						}
						if ( $met == 1 ) {
							$txt = $txt . '<div class="sc-10"><label class="sc-check"><input type="checkbox" name="opt_' . $opt[ $m ]['ID'] . '" value="' . $opt[ $m ]['ID'] . '" checked class="step-checkbox"><span>&nbsp;</span></label></div>';
						} else {
							$txt = $txt . '<div class="sc-10"><label class="sc-check"><input type="checkbox" name="opt_' . $opt[ $m ]['ID'] . '" value="' . $opt[ $m ]['ID'] . '" class="step-checkbox"><span>&nbsp;</span></label></div>';
						}
						$txt = $txt . '<div class="sc-30">' . $opt[ $m ]['img'] . '</div>';
						$txt = $txt . '<div class="sc-50">' . $opt[ $m ]['content'] . '</div>';
						$txt = $txt . '<div class="sc-10">' . $opt[ $m ]['price'] . '</div>';
						$txt = $txt . '</div>';
					}

					$txt = $txt . '<hr>';
				}
			}

			if ( isset( $_REQUEST['prev_id'] ) ) {
				$select      = sanitize_text_field( $_REQUEST['select'] );
				$prev_id     = sanitize_text_field( $_REQUEST['prev_id'] );
				$optionsData = stp_getOptionSelect_m( $select );
				$count_opt   = count( $optionsData );
				$options     = '';
				for ( $g = 0; $g < $count_opt; $g ++ ) {
					$this_opt_name = 'opt_' . $optionsData[ $g ]['ID'];
					if ( isset( $_REQUEST[ $this_opt_name ] ) ) {
						$options = $options . '' . $optionsData[ $g ]['ID'] . ',';
					}
				}
			}

			for ( $i = 0; $i < $count_steps; $i ++ ) {
				$this_name_field = 'select_' . $ar_steps[ $i ]['ID'];
				if ( $ar_steps[ $i ]['ID'] == $prev_id ) {
					$txt = $txt . '<input type="hidden" value="' . $select . '" name="' . $this_name_field . '">';
				} else {
					$txt = $txt . '<input type="hidden" name="' . $this_name_field . '" value="' . sanitize_text_field( $_REQUEST[ $this_name_field ] ) . '">';
				}
				$this_name_opt_field = 'options_' . $ar_steps[ $i ]['ID'];
				if ( $ar_steps[ $i ]['ID'] == $prev_id ) {
					$txt = $txt . '<input type="hidden" name="' . $this_name_opt_field . '" value="' . $options . '">';
				} else {
					$txt = $txt . '<input type="hidden" name="' . $this_name_opt_field . '" value="' . sanitize_text_field( $_REQUEST[ $this_name_opt_field ] ) . '">';
				}
			}

			for ( $i = 0; $i < $count_steps; $i ++ ) {
				if ( $ar_steps[ $i ]['ID'] == $id_steps ) {
					$txt = $txt . '<input type="hidden" name="prev_id" value="' . $id_steps . '">';
					$txt = $txt . '<div class="sc-row">';
					$txt = $txt . '<input type="hidden" name="mod" value="jump">';
					if ( $i > 0 ) {
						$m   = $i - 1;
						$txt = $txt . '<input type="hidden" name="id_back_steps" value="' . $ar_steps[ $m ]['ID'] . '">';
						$txt = $txt . '<div class="sc-20"><input type="submit" name="backButton" value="' . esc_html( __( 'Back', 'steptotal' ) ) . '" class="submit-button"></div>';
					}
					if ( $i < ( $count_steps - 1 ) ) {
						$m   = $i + 1;
						$txt = $txt . '<input type="hidden" name="id_steps" value="' . $ar_steps[ $m ]['ID'] . '">';
						$txt = $txt . '<div class="sc-20"><input type="submit" name="nextButton" value="' . esc_html( __( 'Next', 'steptotal' ) ) . '" class="submit-button"></div>';
					}
					if ( $i == ( $count_steps - 1 ) ) {
						$m   = $i;
						$txt = $txt . '<input type="hidden" name="id_steps" value="' . $ar_steps[ $m ]['ID'] . '">';
						$txt = $txt . '<div class="sc-20"><input type="submit" name="finishBut" value="' . esc_html( __( 'Apply', 'steptotal' ) ) . '" class="submit-button"></div>';
					}
					$txt = $txt . '</div>';
				}
			}
			$txt = $txt . '</form>';

			$txt = $txt . '</div>';
		}
		// ----------------- end steps ----------------------
		// ------------- Final proccess ------------------
		if ( $step == 1 ) {
			$total = 0;

			$txt = '';

			$txt = $txt . '<div class="container">';
			$txt = $txt . '<div class="sc-row"><div class="sc-100"><h2>' . esc_html( __( 'Total', 'steptotal' ) ) . '</h2></div></div>';
			$txt = $txt . '<div class="sc-row"><dib class="sc-100">';
			$txt = $txt . '<table class="table">';
			$txt = $txt . '<thead>';
			$txt = $txt . '<tr>';
			$txt = $txt . '<th>Section</th><th>' . esc_html( __( 'Configuration', 'steptotal' ) ) . '</th><th>' . esc_html( __( 'Price', 'steptotal' ) ) . '</th><th>' . esc_html( __( 'Option', 'steptotal' ) ) . '</th>';
			$txt = $txt . '</tr>';
			$txt = $txt . '</thead>';
			$txt = $txt . '<tbody>';

			$text_send = '';

			for ( $i = 0; $i < $count_steps; $i ++ ) {
				$name_select = 'select_' . $ar_steps[ $i ]['ID'];
				if ( isset( $_REQUEST[ $name_select ] ) ) {
					$select = sanitize_text_field( $_REQUEST[ $name_select ] );
					if ( $select != '' ) {
						$res_select   = stp_getSelect_m( $select );
						$txt          = $txt . '<tr>';
						$txt          = $txt . '<td>' . $ar_steps[ $i ]['name'] . '</td>';
						$text_send    = $text_send . '' . $ar_img_steps[ $i ]['name'] . ' ';
						$txt          = $txt . '<td>' . $res_select['name'] . '</td>';
						$text_send    = $text_send . ' ' . $res_select['name'] . ' ';
						$txt          = $txt . '<td>' . $res_select['price'] . '</td>';
						$text_send    = $text_send . ' ' . $res_select['price'] . ' ';
						$total        = $total + $res_select['price'];
						$txt          = $txt . '<td>';
						$name_options = 'options_' . $ar_steps[ $i ]['ID'];
						$options      = sanitize_text_field( $_REQUEST[ $name_options ] );
						$arOpt        = explode( ',', $options );
						$count_opt    = count( $arOpt );

						for ( $f = 0; $f < $count_opt; $f ++ ) {
							if ( $arOpt[ $f ] != '' ) {
								$opt       = stp_getOptions_m( $arOpt[ $f ] );
								$txt       = $txt . '<p>' . $opt['name'] . ' - ' . $opt['price'] . '</p>';
								$total     = $total + $opt['price'];
								$text_send = $text_send . ' ' . $opt['name'] . ' ' . $opt['price'] . ' ';
							}
						}
						$text_send = $text_send . '. ';
						$txt       = $txt . '</td>';
						$txt       = $txt . '</tr>';
					}
				}
			}
			$txt       = $txt . '<tr>';
			$txt       = $txt . '<td>' . esc_html( __( 'Total:', 'steptotal' ) ) . ' </td><td></td><td></td><td>' . $total . '</td>';
			$txt       = $txt . '</tr>';
			$txt       = $txt . '</tbody>';
			$txt       = $txt . '</table>';
			$txt       = $txt . '</div></div>';
			$text_send = $text_send . '' . esc_html( __( 'Total:', 'steptotal' ) ) . ':  ' . $total;
			$txt       = $txt . '<div class="sc-row">';
			$txt       = $txt . '<form name="mailForm" action="' . get_home_url() . '/' . $current_slug . '/" method="post">';
			$txt       = $txt . '<p>' . esc_html( __( 'Create order', 'steptotal' ) ) . ' <input type="checkbox" name="orderCheck" value="bid"></p>';
			$txt       = $txt . '<label for="user_name">' . esc_html( __( 'You name:', 'steptotal' ) ) . '</label>';
			$txt       = $txt . '<input type="text" name="user_name" class="form-control">';
			$txt       = $txt . '<label for="email">' . esc_html( __( 'Email', 'steptotal' ) ) . '</label><input type="email" name="email" class="form-control">';
			$txt       = $txt . '<input type="hidden" name="mod">';
			$txt       = $txt . '<input type="hidden" name="text_send" value="' . $text_send . '">';
			$txt       = $txt . '<input type="hidden" name="step_calc" value="' . $id_calc . '">';
			$txt       = $txt . '<input type="hidden" name="total_price" value="' . $total . '">';
			$txt       = $txt . '<input type="submit" name="finalButt" value="' . esc_html( __( 'Send', 'steptotal' ) ) . '" class="submit-button" style="margin-top: 30px">';
			$txt       = $txt . '</form>';
			$txt       = $txt . '</div>';
			$txt       = $txt . '</div>';
		}
		// ------------- ENd Final proccess --------------

		// -------------- Send email and finish -----------------
		if ( $step == 2 ) {

			$total_calc  = sanitize_text_field( $_REQUEST['total_calc'] );
			$user_name   = sanitize_text_field( $_REQUEST['user_name'] );
			$email       = sanitize_email( $_REQUEST['email'] );
			$total_price = sanitize_text_field( $_REQUEST['total_price'] );
			$text_send   = sanitize_textarea_field( $_REQUEST['text_send'] );

			if ( isset( $_REQUEST['orderCheck'] ) ) {
				$bid = 1;
			} else {
				$bid = 0;
			}

			$title     = 'Bid on ' . esc_html( get_the_title( $total_calc ) );
			$post_data = array(
				'post_title'   => $title,
				'post_status'  => 'private',
				'post_content' => $text_send,
				'post_type'    => 'invoicetotal',
			);
			$post_id   = wp_insert_post( $post_data, true );
			update_post_meta( $post_id, 'total_calc', $total_calc );
			update_post_meta( $post_id, 'user_name', $user_name );
			update_post_meta( $post_id, 'email', $email );
			update_post_meta( $post_id, 'total_price', $total_price );
			update_post_meta( $post_id, 'bid', $bid );

			$to      = $email;
			$subject = 'Choising Configuration';
			$message = 'You have selected the following configuration: ' . $text_send;
			wp_mail( $to, $subject, $message );
			?>
			<div class="container">
				<div class="sc-row">
					<div class="sc-100">
						<div class="alert alert-success" role="alert">
							<?php echo esc_html( __( 'Thanks. Done!', 'steptotal' ) ); ?>
						</div>
					</div>
				</div>
			</div>
			<script>
				setTimeout(function () {
					window.location.href = "<?php echo get_home_url(); ?>/";
				}, 1 * 1000);
			</script>
			<?php
		}
		// -------------- End Send email and finish -------------

	} else {
		$txt = $txt . '
        <div class="container" style="color: #fff;">
            <div class="sc-row">
                <div class="sc-100">
                    <h2>' . $calc_title . '</h2>
                </div>
            </div>
            <div class="sc-row">
                <div class="sc-100">
                    ' . $img_step . '
                </div>
            </div>
            <div class="sc-row">
                <div class="sc-100">';
		$txt = $txt . '' . apply_filters( 'the_content', $calc_post->post_content );
		$txt = $txt . '
                </div>
            </div>
            <div class="sc-row">
                <div class="sc-100">
                    <form name="nextForm" method="post" action="' . get_home_url() . '/' . $current_slug . '/">
                    <input type="hidden" name="mod" value="jump">
                    <input type="hidden" name="id_steps" value="' . $ar_steps[0]['ID'] . '">
                    <input type="submit" name="nextButton" value="' . __( 'Next', 'steptotal' ) . '" class="submit-button">
                    </form>
                </div>
            </div>
        </div>
        ';
	}

	return $txt;
}

/**
 * functions get options of select
 */

function stp_getOptionSelect_m( $select ) {
	global $post;
	$res         = array();
	$count       = 0;
	$option_args = array(
		'post_type'      => 'optiontotal',
		'posts_per_page' => - 1,
		'orderby'        => 'meta_value_num',
		'meta_key'       => 'orderstep',
		'meta_query'     => array(
			array(
				'key'   => 'select_step',
				'value' => $select,
			),
		),
	);
	$the_query   = new WP_Query( $option_args );
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$res[ $count ]['ID']      = esc_html( $post->ID );
			$res[ $count ]['price']   = esc_html( get_post_meta( $post->ID, 'price', true ) );
			$res[ $count ]['title']   = esc_html( get_post_meta( $post->ID, 'option_name', true ) );
			$ar_img_opt               = esc_url( get_post_meta( $post->ID, 'img', true ) );
			$res[ $count ]['img']     = '<img src="' . $ar_img_opt . '" class="img-fluid">';
			$res[ $count ]['content'] = apply_filters( 'the_content', $post->post_content );
			$count ++;
		}
	}

	return $res;
}


/**
 * function getting array fields selection
 */

function stp_getSelect_m( $select ) {
	global $post;
	$res       = array();
	$args      = array(
		'post_type' => 'selecttotal',
		'p'         => $select,
	);
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$res['ID']     = esc_html( $post->ID );
			$res['name']   = esc_html( get_post_meta( $post->ID, 'select_name', true ) );
			$res['price']  = esc_html( get_post_meta( $post->ID, 'price', true ) );
			$ar_img_select = esc_url( get_post_meta( $post->ID, 'img', true ) );
			$res['img']    = '<img src="' . $ar_img_select . '" class="img-fluid">';
		}
	}

	return $res;
}

/**
 * function getting array fields options
 */

function stp_getOptions_m( $opt ) {
	global $post;
	$res       = array();
	$args      = array(
		'post_type' => 'optiontotal',
		'p'         => $opt,
	);
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$res['ID']     = esc_html( $post->ID );
			$res['name']   = esc_html( get_post_meta( $post->ID, 'option_name', true ) );
			$res['price']  = esc_html( get_post_meta( $post->ID, 'price', true ) );
			$ar_img_select = esc_url( get_post_meta( $post->ID, 'img', true ) );
			$res['img']    = '<img src="' . $ar_img_select . '" class="img-fluid">';
		}
	}

	return $res;
}
