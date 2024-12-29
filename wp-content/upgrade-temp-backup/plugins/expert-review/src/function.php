<?php

//use Wpshop\PluginMyPopup\Settings\AppearanceOptions;
//use Wpshop\PluginMyPopup\Logger;
//use Wpshop\PluginMyPopup\PluginContainer;
//use Wpshop\PluginMyPopup\Settings\PluginOptions;

function expert_review_activate() {
//	if ( PluginContainer::has( PluginOptions::class ) ) {
//		$options = PluginContainer::get( PluginOptions::class );
//
//		$options->error_log_level = Logger::DISABLED;
//
//		$options->save( PluginOptions::MODE_ADD );
//	}
//    if ( PluginContainer::has( AppearanceOptions::class ) ) {
//        $appearanceOptions = PluginContainer::get( AppearanceOptions::class );
//
//        $appearanceOptions->color_overlay = '#000000';
//        $appearanceOptions->overlay_opacity = '50';
//        $appearanceOptions->animation = 'bounceIn';
//
//        $appearanceOptions->save(AppearanceOptions::MODE_ADD);
//    }
}

function expert_review_deactivate() {

}

function expert_review_uninstall() {
//	if ( PluginContainer::has( PluginOptions::class ) ) {
//		PluginContainer::get( PluginOptions::class )->destroy();
//	}
}
