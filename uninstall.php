<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $post;

$args      = array(
	'post_type'      => 'invoicetotal',
	'posts_per_page' => - 1,
);
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		wp_delete_post( $post->ID);
	}
}

$args      = array(
	'post_type'      => 'optiontotal',
	'posts_per_page' => - 1,
);
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		wp_delete_post( $post->ID);
	}
}

$args      = array(
	'post_type'      => 'selecttotal',
	'posts_per_page' => - 1,
);
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		wp_delete_post( $post->ID);
	}
}

$args      = array(
	'post_type'      => 'stepstotal',
	'posts_per_page' => - 1,
);
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		wp_delete_post( $post->ID);
	}
}

$args      = array(
	'post_type'      => 'steptotal',
	'posts_per_page' => - 1,
);
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		wp_delete_post( $post->ID);
	}
}

unregister_post_type( 'steptotal' );
unregister_post_type( 'stepstotal' );
unregister_post_type( 'selecttotal' );
unregister_post_type( 'optiontotal' );
unregister_post_type( 'invoicetotal' );