<?php
/**
 * Plugin Name: Elementor Portfolio Widget
 * Description: -.
 * Version: 1.0
 * Author: Dita
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function register_portfolio_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/portfolio-widget.php' );
    $widgets_manager->register( new \Elementor_Portfolio_Widget() );
}
add_action( 'elementor/widgets/register', 'register_portfolio_widget' );
