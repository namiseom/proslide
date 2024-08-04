<?php
namespace proslide\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; //exit if accessed directly
}

class PPortfolio extends Widget_Base {

	public function get_name() {
		return 'proslide_portfolio';
	}

	public function get_title() {
		return 'Proslide Portfolio';
	}

	public function get_categories() {
		return array( 'general' );
	}

	private function get_posts_options() {
		$posts = get_posts(
			array(
				'post_type'      => 'portfolio',
				'post_status'    => 'public',
				'posts_per_page' => -1,
			)
		);

		$options = array();

		foreach ( $posts as $post ) {
			// phpcs:ignore
			$options[ $post->ID ] = apply_filters( 'the_title', $post->post_title );
		}

		return $options;
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => 'Settings',
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Source', 'HelloElementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''       => 'Recent',
					'manual' => 'Manual',
				),
			)
		);

		$this->add_control(
			'order_by',
			array(
				'label'     => __( 'Order By', 'HelloElementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'     => __( 'Default', 'HelloElementor' ),
					'name'     => __( 'Name', 'HelloElementor' ),
					'rand'     => __( 'Random', 'HelloElementor' ),
					'post__in' => __( 'Post In', 'HelloElementor' ),
				),
				'condition' => array(
					'source' => array( '' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => __( 'Order', 'HelloElementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => __( 'DESC', 'HelloElementor' ),
					'ASC'  => __( 'ASC', 'HelloElementor' ),
				),
				'condition' => array(
					'source' => array( '' ),
				),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'post_id',
			array(
				'label'       => __( 'Post Title', 'HelloElementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => false,
				'default'     => '',
				'options'     => $this->get_posts_options(),
				'label_block' => true,
			)
		);

		$this->add_control(
			'slides',
			array(
				'label'       => __( 'Slides', 'HelloElementor' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(),
				'title_field' => 'Post {{{ post_id }}}',
				'condition'   => array(
					'source' => array( 'manual' ),
				),
			)
		);

		$this->end_controls_section();
	}

	private function get_query() {
		$settings = $this->get_settings_for_display();

		$args = array(
			'post_type'      => 'portfolio',
			'post_status'    => 'publish',
		);

		if ( 'manual' === $settings['source'] ) {
			$post_ids = wp_list_pluck( $settings['slides'], 'post_id' );

			$args['posts_per_page'] = -1;
			$args['post__in']       = array_merge( array( 0 ), $post_ids );
			$args['orderby']        = 'post__in';
		} else {
			$args['posts_per_page'] = -1;
			$args['orderby']        = $settings['order_by'];
			$args['order']          = $settings['order'];
		}

		return new \WP_Query( $args );
	}

	private function getSlideData( $item ) {
		return array(
			'image' => $item['url'],
		);
	}

	private function getSlideshowData( $all_items ) {
		$slides = array();
		foreach ( $all_items as $item ) {
			$slides[] = $this->getSlideData( $item );
		}
		return array(
			'type'      => 'slideshow',
			'slideshow' => array(
				'slides' => $slides,
			),
		);
	}

	private function renderPortfolioRow() {
		?>
		<?php
		//get background for each portfolio
		$port_bg = get_field('background');
		if( !empty( $port_bg ) ): ?>
			<div class="proslide-portfolio__bg" style="background:url(<?php echo esc_url($port_bg['url']); ?>)">
				<div class="proslide-portfolio__wrapper">
					<?php
					//get company logo
					$company_logo = get_field('company_logo');
					if( !empty( $company_logo ) ): ?>
						<img src="<?php echo esc_url($company_logo['url']); ?>" alt="<?php echo esc_attr($company_logo['alt']); ?>" class="proslide-portfolio__logo" />
					<?php endif; ?>
					<?php the_post_thumbnail( 'post-thumbnail', array( 'class' => 'proslide-portfolio__thumb' ) ); ?>
					<div class="proslide-portfolio__info">
						<h3 class='proslide-portfolio__title'><?php the_title(); ?></h3>
						<div class='proslide-portfolio__content'><?php the_content(); ?></div>
						<p class='proslide-portfolio__sales'><?php the_field( 'sales' ); ?></p>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php
	}
	protected function render() {
		$query = $this->get_query();
		?>
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="proslide-portfolio">
					<?php $this->renderPortfolioRow(); ?>
				</div>
				<?php
			}
			?>
		<div class="just-white">
		<?php
		$this->renderPortfolioRow();
		?>
		</div>
		<?php
	}
}

?>
