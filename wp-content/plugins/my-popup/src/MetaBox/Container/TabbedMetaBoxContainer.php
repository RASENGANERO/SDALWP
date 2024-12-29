<?php

namespace Wpshop\PluginMyPopup\MetaBox\Container;

use WP_Post;
use Wpshop\MetaBox\Element\RenderEventInterface;
use Wpshop\MetaBox\Element\SaveEventInterface;
use Wpshop\MetaBox\Form\Element\AfterFieldInfoInterface;
use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\LabelAwareInterface;
use Wpshop\MetaBox\Form\Render\ElementRenderer;
use Wpshop\MetaBox\Form\Render\LabelRenderer;
use Wpshop\MetaBox\MetaBoxContainer\AbstractMetaBoxContainer;
use Wpshop\MetaBox\SaveCallbackInterface;
use Wpshop\PluginMyPopup\MetaBox\Element\CustomRenderInterface;
use Wpshop\PluginMyPopup\MetaBox\Tab;
use Wpshop\PluginMyPopup\Plugin;
use function Wpshop\PluginMyPopup\get_preview_base_url;

class TabbedMetaBoxContainer extends AbstractMetaBoxContainer implements SaveCallbackInterface {

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var LabelRenderer
     */
    protected $labelRenderer;

    /**
     * @var ElementRenderer
     */
    protected $elementRenderer;

    /**
     * @var \Closure
     */
    protected $saveCallback;

    /**
     * @var Tab[]
     */
    protected $tabs = [];

    /**
     * TabbedMetaBoxContainer constructor.
     *
     * @param Plugin          $plugin
     * @param LabelRenderer   $labelRenderer
     * @param ElementRenderer $elementRenderer
     */
    public function __construct(
        Plugin $plugin,
        LabelRenderer $labelRenderer,
        ElementRenderer $elementRenderer
    ) {
        $this->plugin          = $plugin;
        $this->labelRenderer   = $labelRenderer;
        $this->elementRenderer = $elementRenderer;
        $this->saveCallback    = function ( WP_Post $post, array $data, $element ) {
            if ( $element instanceof FormElementInterface && isset( $data[ $element->getName() ] ) ) {
                update_post_meta( $post->ID, $element->getName(), $data[ $element->getName() ] );
            }
        };
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function render( WP_Post $post ) {
        $tabHeaders = '';
        $tabContent = '';

        $active = true;
        foreach ( $this->tabs as $tab ) {
            $uniqid     = uniqid( $tab->getId() . '_' );
            $tabHeaders .= $this->renderTabHeader( $tab, $uniqid, $active );
            $tabContent .= $this->renderTabContent( $tab, $this->tabContentId( $uniqid ), $post, $active );
            $active     = false;
        }

        $html = '';
        $html .= '<script type="text/javascript">var my_popup_edit_page = 1;</script>';
        $html .= '<ul class="wpshop-metabox-tabs" role="tablist">';
        $html .= $tabHeaders;
        $html .= '</ul>';
        $html .= '<div class="wpshop-metabox-tabs-content">';
        $html .= $tabContent;
        $html .= '</div>';

        if ( $this->plugin->verify() ) {
            $html = '<div class="popup-preview js-popup-preview">' .
                    '    <div class="mypopup-loader"></div>' .
                    '    <div class="popup-preview-area js-my-popup-preview-area" data-id="' . $post->ID . '" data-base_url="' . get_preview_base_url() . '"></div>' .
                    '    <div class="popup-preview-buttons">' .
                    '        <span class="button js-popup-preview-stick" data-toggle_txt="' . __( 'Unstick Preview', Plugin::TEXT_DOMAIN ) . '">' . __( 'Stick Preview', Plugin::TEXT_DOMAIN ) . '</span>' .
                    '        <span class="button js-popup-preview-hide" data-toggle_txt="' . __( 'Show Preview', Plugin::TEXT_DOMAIN ) . '">' . __( 'Hide Preview', Plugin::TEXT_DOMAIN ) . '</span>' .
                    '        <span class="button js-popup-preview-refresh">' . __( 'Refresh', Plugin::TEXT_DOMAIN ) . '</span>' .
                    '    </div>' .
                    '</div>' .
                    '<div class="main-metabox-area js-my-popup-settings">' . $html . '</div>';
        }

        echo $html;
    }

    /**
     * @param Tab     $tab
     * @param string  $id
     * @param WP_Post $post
     * @param bool    $active
     *
     * @return string
     */
    protected function renderTabContent( Tab $tab, $id, $post, $active ) {
        $html = sprintf(
            '<div class="wpshop-metabox-tab-content%s" id="%s" role="tabpanel" aria-labelledby="tab_general">',
            $active ? ' active' : '',
            $id
        );

        $elements = $tab->getElements();
        foreach ( $elements as $element ) {
            $beforeRender = $afterRender = null;
            if ( $element instanceof RenderEventInterface ) {
                $beforeRender = $element->getOnBeforeRender();
                $afterRender  = $element->getOnAfterRender();
            }

            do_action( 'wpshop_tabbed_metabox_render_element', $element, $post, $this );
            do_action( 'wpshop_tabbed_metabox_render_element:' . $this->getId() . ':' . $element->getName(), $element, $post, $this );

            $element
                ->grabValue( $post )
                ->setName( $this->prependNameAttribute( (string) $element->getName(), $this->getId() ) )
            ;

            if ( is_callable( $beforeRender ) ) {
                $beforeRender( $element, $post, $this );
            }

            if ( ! $element->shouldRender() ) {
                continue;
            }

            if ( $element instanceof FormElementInterface ) {
                $html .= $this->renderFormElement( $element );
            } elseif ( method_exists( $element, '__toString' ) ) {
                $html .= (string) $element;
            }

            if ( is_callable( $afterRender ) ) {
                $afterRender( $element, $post, $this );
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param FormElementInterface $element
     *
     * @return string
     */
    protected function renderFormElement( FormElementInterface $element ) {

        ElementRenderer::setUniqueId( $element );
        ElementRenderer::appendCssClass( $element, 'wpshop-metabox-element' );

        $result = '<div class="wpshop-meta-row">';
        if ( $element instanceof CustomRenderInterface ) {
            $result .= $this->elementRenderer->render( $element );
        } else {
            $result .= '<div class="wpshop-meta-field">';

            if ( ! $element->getOption( 'disable_label' ) &&
                 $element instanceof LabelAwareInterface &&
                 $element->getLabel()
            ) {
                $result .= sprintf( '<div class="wpshop-meta-field__label">%s</div>', $this->labelRenderer->render( $element ) );
            }

            $result .= '<div class="wpshop-meta-field__body">';
            $result .= $this->elementRenderer->render( $element );

            if ( $element instanceof AfterFieldInfoInterface ) {
                $result .= $element->getAfterFieldInfo() ? sprintf( '<span>%s</span>', $element->getAfterFieldInfo() ) : '';
            }

            if ( $description = $element->getDescription() ) {
                $result .= sprintf( '<br><p class="description">%s</p>', esc_html( $description ) );
            }

            $result .= '</div>'; // .wpshop-meta-field__body

            $result .= '</div>'; // .wpshop-meta-field
        }

        $result .= '</div>';

        return $result;
    }

    /**
     * @param string $name
     * @param string $prefix
     *
     * @return string
     */
    protected function prependNameAttribute( $name, $prefix ) {
        $pos = strpos( $name, '[' );
        if ( $pos > 0 ) {
            $a = substr( $name, 0, $pos );
            $b = substr( $name, $pos );

            return "{$prefix}[{$a}]{$b}";
        }

        return "{$prefix}[{$name}]";
    }

    /**
     * @param Tab    $tab
     * @param string $id
     * @param bool   $active
     *
     * @return string
     */
    protected function renderTabHeader( Tab $tab, $id, $active = false ) {
        $html = '<li class="wpshop-metabox-tab">';
        $html .= sprintf(
            '<a class="wpshop-metabox-tab-link%4$s" id="%1$s" data-toggle="tab" href="#%2$s" role="tab" aria-controls="%2$s" aria-selected="%5$s">%3$s</a>',
            $id,
            $this->tabContentId( $id ),
            $tab->getTitle(),
            $active ? ' active' : '',
            $active ? 'true' : 'false'
        );
        $html .= '</li>';

        return $html;
    }

    /**
     * @param $id
     *
     * @return string
     */
    protected function tabContentId( $id ) {
        return 'box_' . $id;
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function save( WP_Post $post ) {
        do_action( 'wpshop_tabbed_metabox_save_before', $post, $this );

        if ( wp_is_post_autosave( $post->ID ) ||
             wp_is_post_revision( $post->ID )
        ) {
            return;
        }

        $data = isset( $_POST[ $this->getId() ] ) ? $_POST[ $this->getId() ] : [];

        $data = apply_filters( 'wpshop_tabbed_metabox_save_data', $data, $this );

        foreach ( $this->tabs as $tab ) {
            foreach ( $tab->getElements() as $element ) {
                $beforeSave = $afterSave = null;
                if ( $element instanceof SaveEventInterface ) {
                    $beforeSave = $element->getOnBeforeSave();
                    $afterSave  = $element->getOnAfterSave();
                }

                // prevent save disabled elements
                if ( $element instanceof FormElementInterface && $element->getAttribute( 'disabled' ) ) {
                    continue;
                }

                $element->grabValue( $post );

                if ( is_callable( $beforeSave ) ) {
                    $beforeSave( $post, $data, $element, $this );
                }

                do_action( 'wpshop_tabbed_metabox_save_element', $post, $data, $element, $this );
                do_action( 'wpshop_tabbed_metabox_save_element_' . $element->getName(), $post, $data, $element, $this );

                if ( $element instanceof SaveCallbackInterface ) {
                    if ( $callback = $element->getSaveCallback() ) {
                        $callback( $post, $data, $element );
                    }
                } elseif ( $callback = $this->getSaveCallback() ) {
                    $callback( $post, $data, $element );
                }

                if ( is_callable( $afterSave ) ) {
                    $afterSave( $post, $data, $element, $this );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setSaveCallback( $callback ) {
        $this->saveCallback = $callback;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSaveCallback() {
        return $this->saveCallback;
    }

    /**
     * @param Tab $tab
     *
     * @return Tab
     */
    public function addTab( Tab $tab ) {
        $this->tabs[ $tab->getId() ] = $tab;

        return $tab;
    }
}
