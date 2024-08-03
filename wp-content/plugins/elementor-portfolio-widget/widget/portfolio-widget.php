<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Elementor_Portfolio_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'portfolio_widget';
    }

    public function get_title() {
        return __( 'Portfolio Widget', 'elementor-portfolio-widget' );
    }

    public function get_icon() {
        return 'fa fa-briefcase';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'elementor-portfolio-widget' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __( 'Number of Portfolios', 'elementor-portfolio-widget' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $query_args = [
            'post_type' => 'portfolio',
            'posts_per_page' => $settings['posts_per_page'],
        ];

        $portfolio_query = new WP_Query( $query_args );

        if ( $portfolio_query->have_posts() ) {
            echo '<div class="portfolio-widget">';
            while ( $portfolio_query->have_posts() ) {
                $portfolio_query->the_post();
                $company_logo = get_post_meta( get_the_ID(), 'company_logo', true );
                $company_name = get_post_meta( get_the_ID(), 'company_name', true );
                $desc = get_post_meta( get_the_ID(), 'desc', true );
                $sales = get_post_meta( get_the_ID(), 'sales', true );
                ?>
                <div class="portfolio-item">
                    <?php if ( $company_logo ) : ?>
                        <img src="<?php echo esc_url( $company_logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" />
                    <?php endif; ?>
                    <h3><?php echo esc_html( $company_name ); ?></h3>
                    <p><?php echo esc_html( $desc ); ?></p>
                    <p><strong>Sales:</strong> <?php echo esc_html( $sales ); ?></p>
                    <div class="portfolio-featured-image"><?php the_post_thumbnail( 'medium' ); ?></div>
                </div>
                <?php
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . __( 'No portfolios found.', 'elementor-portfolio-widget' ) . '</p>';
        }
    }

    protected function _content_template() {}
}
