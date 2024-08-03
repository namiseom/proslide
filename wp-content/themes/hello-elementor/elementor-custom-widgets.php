<?php
namespace proslide;

class Widgets_loader {
    private static $_instance = null;

    public static function instance()
    {
        if(is_null(self::$_instance)){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function include_widgets_files()
    {
        require_once(__DIR__ . '/widgets/proslide-portfolio.php');
    }

    public function register_widgets()
    {
        $this->include_widgets_files();

        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\PPortfolio());
    }

    public function __construct()
    {
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets'], 99);
    }
}

Widgets_Loader::instance();

?>