<?php

namespace Wpshop\PluginMyPopup;

use Wpshop\MetaBox\Form\Element\ColorPicker;
use Wpshop\MetaBox\Form\Element\RawHtml;
use Wpshop\MetaBox\Form\Render\FormMediaFile;
use Wpshop\MetaBox\MetaBoxContainer\SimpleMetaBoxContainer;
use Wpshop\MetaBox\MetaBoxManager;
use Wpshop\MetaBox\Provider\MetaBoxProviderInterface;
use Wpshop\MetaBox\Provider\ScriptProviderInterface;
use Wpshop\MetaBox\Provider\StyleProviderInterface;
use Wpshop\PluginMyPopup\MetaBox\Container\TabbedMetaBoxContainer;
use Wpshop\PluginMyPopup\MetaBox\Tab;

class MetaBoxProvider implements
    MetaBoxProviderInterface,
    ScriptProviderInterface,
    StyleProviderInterface {

    use TemplateRendererTrait;

    /**
     * @var SimpleMetaBoxContainer
     */
    protected $metaBoxPrototype;

    /**
     * @var TabbedMetaBoxContainer
     */
    protected $tabbedMetaBoxContainerPrototype;

    /**
     * MetaBoxProvider constructor.
     *
     * @param SimpleMetaBoxContainer $metaBoxPrototype @deprecated
     * @param TabbedMetaBoxContainer $tabbedMetaBoxContainerPrototype
     */
    public function __construct(
        SimpleMetaBoxContainer $metaBoxPrototype,
        TabbedMetaBoxContainer $tabbedMetaBoxContainerPrototype
    ) {
        $this->metaBoxPrototype                = $metaBoxPrototype;
        $this->tabbedMetaBoxContainerPrototype = $tabbedMetaBoxContainerPrototype;
    }

    /**
     * @param MetaBoxManager $manager
     *
     * @return void
     */
    public function initMetaBoxes( MetaBoxManager $manager ) {


        $manager->addMetaBox( $box = clone $this->tabbedMetaBoxContainerPrototype );
        $box
            ->setId( 'display_box' )
            ->setTitle( __( 'Display', Plugin::TEXT_DOMAIN ) )
            ->setScreen( MyPopup::POST_TYPE )
        ;


        $box->addTab( $tab = new Tab( 'tab_general', __( 'Main settings', Plugin::TEXT_DOMAIN ) ) );
        $box->setSaveCallback( function ( \WP_Post $post, $data, $element ) {
            foreach ( $data as $key => $value ) {
                update_post_meta( $post->ID, $key, $value );
            }
        } );

        $tab->addElement( $element = new RawHtml() );
        $element->setRenderCallback( function ( $post ) {
            return $this->render( 'general-tab-html', [
                'post'      => $post,
                'namespace' => 'display_box',
                'DEFAULTS'  => Plugin::$DEFAULTS,
            ] );
        } );

        $box->addTab( $tab = new Tab( 'tab_appearance', __( 'Appearance', Plugin::TEXT_DOMAIN ) ) );

        $tab->addElement( $element = new RawHtml() );
        $element->setRenderCallback( function ( $post ) {
            return $this->render( 'appearance-tab-html', [
                'post'      => $post,
                'namespace' => 'display_box',
                'DEFAULTS'  => Plugin::$DEFAULTS,
            ] );
        } );


        $box->addTab( $tab = new Tab( 'tab_content', __( 'Content', Plugin::TEXT_DOMAIN ) ) );

        $tab->addElement( $element = new RawHtml() );
        $element->setRenderCallback( function ( $post ) {
            return $this->render( 'content-tab-html', [
                'post'      => $post,
                'namespace' => 'display_box',
                'DEFAULTS'  => Plugin::$DEFAULTS,
            ] );
        } );


        $box->addTab( $tab = new Tab( 'tab_rules', __( 'Output rules', Plugin::TEXT_DOMAIN ) ) );

        $tab->addElement( $element = new RawHtml() );
        $element->setRenderCallback( function ( $post ) {
            return $this->render( 'rules-tab-html', [
                'post'      => $post,
                'namespace' => 'display_box',
                'DEFAULTS'  => Plugin::$DEFAULTS,
            ] );
        } );


        $box->addTab( $tab = new Tab( 'tab_presets', __( 'Presets', Plugin::TEXT_DOMAIN ) ) );

        $tab->addElement( $element = new RawHtml() );
        $element->setRenderCallback( function ( $post ) {
            return $this->render( 'presets-tab-html', [
                'post'      => $post,
                'namespace' => 'display_box',
                'DEFAULTS'  => Plugin::$DEFAULTS,
                'is_dev'    => defined( 'MY_POPUP_DEV_MODE' ) ? MY_POPUP_DEV_MODE : false,
            ] );
        } );

        $box->addTab( $tab = new Tab( 'tap_import', __( 'Import and Export', Plugin::TEXT_DOMAIN ) ) );
        $tab->addElement( $element = new RawHtml() );
        $element->setRenderCallback( function ( $post ) {
            return $this->render( 'import-export-tab-html', [
                'post' => $post,
            ] );
        } );
    }

    protected function isDraft( $popup_post, $element ) {
        return in_array( get_post_status( $popup_post ), [
                'draft',
                'auto-draft',
            ] ) && null == get_post_meta( $popup_post->ID, $element->getName(), true );
    }

    /**
     * @inheritDoc
     */
    public function enqueueScripts() {
        add_action( 'current_screen', function () {
            if ( \get_current_screen()->post_type !== MyPopup::POST_TYPE ) {
                return;
            }
            $this->_enqueueScripts();
        } );
    }

    protected function _enqueueScripts() {
        add_action( 'admin_enqueue_scripts', function () {

            // color picker deps
            wp_enqueue_script( 'wp-color-picker', 'jquery' );
            $selector = ColorPicker::SELECTOR_CLASS;
            $js       = <<<"JS"
jQuery(function($) {
    \$('.{$selector}').wpColorPicker({
	    change: function (event, ui) {
	        jQuery(event.target).trigger('color-picker:change');
	    },
	    clear: function (event) {
	        jQuery(event.target).parent().find('.wp-color-picker').trigger('color-picker:clear');
    	}
    });
});
JS;
            wp_add_inline_script( 'wp-color-picker', $js );

            // media file deps
            wp_enqueue_media();

            $browseSelector = '.js-my-popup-form-element-browse';
            $urlSelector    = '.js-my-popup-form-element-url';
            $js             = <<<"JS"
jQuery(function($) {
	\$('{$browseSelector}').on('click', function (event) {
	    event.preventDefault();

	    var self = $(this);

	    var fileFrame = wp.media.frames.file_frame = wp.media({
	        title: self.data('uploader_title'),
	        button: {
	            text: self.data('uploader_button_text'),
	        },
	        multiple: false
	    });

	    fileFrame.on('select', function () {
	        attachment = fileFrame.state().get('selection').first().toJSON();

	        self.prev('{$urlSelector}').val(attachment.url);
	        self.prev('{$urlSelector}').trigger('change');
	    });

	    fileFrame.open();
	});
});
JS;
            wp_add_inline_script( 'jquery', $js );
//			FormMediaFile::registerInlineScript();
        } );
    }

    public function enqueueStyles() {
        add_action( 'current_screen', function () {
            if ( \get_current_screen()->post_type !== MyPopup::POST_TYPE ) {
                return;
            }
            $this->_enqueueStyles();
        } );
    }

    protected function _enqueueStyles() {
        // color picker deps
        wp_enqueue_style( 'wp-color-picker' );
    }
}
