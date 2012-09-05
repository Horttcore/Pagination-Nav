<?php
/*
Plugin Name: Pagination Nav
Plugin URI: http://horttcore.de
Description: Display a page nav
Version: 0.1
Author: Ralf Hortt
Author URI: http://horttcore.de
License: GPL2
*/



// Avoid direct calls to this file, because now WP core and framework has been used
if ( !function_exists('add_action') ) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;



/**
 * Pagination nav class for WordPress
 *
 * @package Pagination_Nav
 * @author Ralf Hortt
 **/
class Pagination_Nav {



	/**
	 * Class constructor
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	function __construct()
	{
		add_action( 'init', array( $this, 'wp_register_style' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) ); # Remove the filter when you use the template tag
		add_shortcode( 'paginationnav', array( $this, 'shortcode_paginationnav' ) );
	}



	/**
	 * Template tag for displaying the navigation
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	static public function navigation( $return = FALSE )
	{
		global $post, $wp_query;

		// No pagination
		if ( FALSE === strpos( $post->post_content, '<!--nextpage-->') )
			return;

		// Do nothing if there is just one page
		$temp = explode( '<!--nextpage-->', $post->post_content );
		if ( 1 >= count( $temp ) )
			return;

		// Enqueue default style
		wp_enqueue_style( 'paginationnav' );

		// Build output
		$output = '<nav class="pagination-nav">';
		$output .= '<ul>';
		$i = 1;

		foreach ( $temp as $t ) :

			$pattern = ( FALSE === strpos( $t, 'paginationnav' ) ) ? '(?<=<h[1-6]>).*(?=</h[1-6]>)' : '(?<=\[paginationnav title=").*(?="])';
			preg_match( '&' . $pattern . '&Ui', $t, $headlines );
			$headline = $headlines[0];

			$link = ( 1 != $i ) ? get_permalink() . $i . '/' : get_permalink();
			$class = ( $i == $wp_query->query_vars['page'] || ( 1 == $i && 0 == $wp_query->query_vars['page'] ) ) ? 'current-page' : '';

			$output .= '<li class="' . $class . '"><a href="' . $link . '">' . $headline . '</a></li>';

			$i++;

		endforeach;
		
		$output .= '</ul>';
		$output .= '</nav>';

		if ( TRUE === $return ) :
			return $output;
		else :
			echo $output;
		endif;
	}


	/**
	 * Inject navigation
	 *
	 * @access public
	 * @return str HTML output
	 * @author Ralf Hortt
	 **/
	public function the_content( $content )
	{
		global $post;

		if ( FALSE === strpos( $post->post_content, '<!--nextpage-->') ) :
			return $content;
		else :
			return Pagination_Nav::navigation( TRUE ) . $content;
		endif;
	}



	/**
	 * Shortcode to overwrite page title
	 *
	 * This function is just used to hide the snippet
	 * Page title is handled in the filter function
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function shortcode_paginationnav( $atts )
	{
		extract(shortcode_atts(array(
			'title' => null
		), $atts));
	}



	/**
	 * Register default styles
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function wp_register_style()
	{
		wp_register_style( 'paginationnav', plugins_url( 'css/pagination-nav.css', __FILE__ ), FALSE, 'v0.1', 'all' );
	}



}

new Pagination_Nav;