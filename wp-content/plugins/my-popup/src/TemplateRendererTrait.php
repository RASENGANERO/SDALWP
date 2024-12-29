<?php

namespace Wpshop\PluginMyPopup;

trait TemplateRendererTrait {

    /**
     * @param string $__file__
     * @param array  $__params__
     *
     * @return false|string
     * @throws \Exception
     */
    protected function render( $__file__, $__params__ = [] ) {
        $base     = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/';
        $__file__ = $base . '/' . $__file__ . '.php';

        $_obInitialLevel_ = ob_get_level();
        ob_start();
        ob_implicit_flush( false );
        extract( $__params__, EXTR_OVERWRITE );
        try {
            require $__file__;

            return ob_get_clean();
        } catch ( \Exception $e ) {
            while ( ob_get_level() > $_obInitialLevel_ ) {
                if ( ! @ob_end_clean() ) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }
}
