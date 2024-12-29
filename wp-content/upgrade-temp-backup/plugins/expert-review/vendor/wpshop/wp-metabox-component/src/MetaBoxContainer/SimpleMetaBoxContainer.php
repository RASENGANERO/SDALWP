<?php

namespace Wpshop\MetaBox\MetaBoxContainer;

use WP_Post;
use Wpshop\MetaBox\Element\RenderEventInterface;
use Wpshop\MetaBox\Element\SaveEventInterface;
use Wpshop\MetaBox\ElementInterface;
use Wpshop\MetaBox\Form\Element\AfterFieldInfoInterface;
use Wpshop\MetaBox\Form\Element\FormElement;
use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\Hidden;
use Wpshop\MetaBox\Form\Element\LabelAwareInterface;
use Wpshop\MetaBox\Form\Render\ElementRenderer;
use Wpshop\MetaBox\Form\Render\LabelRenderer;
use Wpshop\MetaBox\SaveCallbackInterface;

class SimpleMetaBoxContainer extends AbstractMetaBoxContainer implements SaveCallbackInterface {

	use MetaBoxElementTrait;

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
	 * SimpleMetaBoxContainer constructor.
	 *
	 * @param LabelRenderer   $labelRenderer
	 * @param ElementRenderer $elementRenderer
	 */
	public function __construct( LabelRenderer $labelRenderer, ElementRenderer $elementRenderer ) {
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
	 * @return string
	 */
	public function render( WP_Post $post ) {
		$elements = $this->getElements();
		if ( ! count( $elements ) ) {
			return '';
		}

		$result = '<table class="form-table wpcourses-form-table">';

		foreach ( $elements as $element ) {

			$beforeRender = $afterRender = null;
			if ( $element instanceof RenderEventInterface ) {
				$beforeRender = $element->getOnBeforeRender();
				$afterRender  = $element->getOnAfterRender();
			}

			do_action( 'wpshop_simple_metabox_render_element', $element, $post, $this );
			do_action( 'wpshop_simple_metabox_render_element:' . $this->getFullElementId( $element ), $element, $post, $this );

			$element
				->grabValue( $post )
				->setName( $this->prependNameAttribute( $element->getName(), $this->getId() ) )
			;

			if ( is_callable( $beforeRender ) ) {
				$beforeRender( $element, $post, $this );
			}

			if ( ! $element->shouldRender() ) {
				continue;
			}

			if ( $element instanceof FormElementInterface ) {
				$result .= $this->renderFormElement( $element );
			} elseif ( method_exists( $element, '__toString' ) ) {
				$result .= $this->renderElement( $element );
			}

			if ( is_callable( $afterRender ) ) {
				$afterRender( $element, $post, $this );
			}
		}

		$result .= '</table>';

		echo $result;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function save( WP_Post $post ) {

		do_action( 'wpshop_simple_metabox_save_before', $post, $this );

		if ( wp_is_post_autosave( $post->ID ) ||
		     wp_is_post_revision( $post->ID )
		) {
			return;
		}

		$data = isset( $_POST[ $this->getId() ] ) ? $_POST[ $this->getId() ] : [];

		$data = apply_filters( 'wpshop_simple_metabox_save_data', $data, $this );

		foreach ( $this->getElements() as $element ) {

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

			do_action( 'wpshop_simple_metabox_save_element', $post, $data, $element, $this );
			do_action( 'wpshop_simple_metabox_save_element_' . $element->getName(), $post, $data, $element, $this );

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

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	protected function renderFormElement( FormElementInterface $element ) {

		ElementRenderer::setUniqueId( $element );
		ElementRenderer::appendCssClass( $element, 'wpshop-metabox-element' );

		$result = '';
		if ( $element instanceof Hidden ) {
			$result .= '<tr style="display: none">';
		} else {
			$result .= '<tr>';
		}

		if ( ! $element->getOption( 'disable_label' ) &&
		     $element instanceof LabelAwareInterface &&
		     $element->getLabel()
		) {
			$result .= sprintf( '<th>%s</th>', $this->labelRenderer->render( $element ) );
		} else {
			$result .= '<th></th>';
		}
		$result .= '<td>' . $this->elementRenderer->render( $element );

		if ( $element instanceof AfterFieldInfoInterface ) {
			$result .= $element->getAfterFieldInfo() ? sprintf( '<span>%s</span>', $element->getAfterFieldInfo() ) : '';
		}

		if ( $description = $element->getDescription() ) {
			$result .= sprintf( '<p class="description">%s</p>', esc_html( $description ) );
		}
		$result .= '</td>';
		$result .= '</tr>';

		return $result;
	}

	/**
	 * @param ElementInterface $element
	 *
	 * @return string
	 */
	protected function renderElement( $element ) {
		$result = '<tr>';
		$result .= '<td colspan="2" style="padding-left: 0">';
		$result .= (string) $element;
		$result .= '</td >';
		$result .= '</tr>';

		return $result;
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
	 * @param ElementInterface $element
	 *
	 * @return string
	 */
	public function getFullElementId( ElementInterface $element ) {
		return $this->getId() . ':' . $element->getName();
	}
}
