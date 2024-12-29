<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Settings\PluginOptions;

class ExpertReview {

	const POST_TYPE = 'expert_review';

	/**
	 * @var Plugin
	 */
	protected $plugin;

	protected $options;
	protected $appearanceOptions;
	protected $additionallyOptions;

	/**
	 * MyPopup constructor.
	 *
	 * @param Plugin              $plugin
	 * @param PluginOptions       $options
	 */
	public function __construct(
		Plugin $plugin,
		PluginOptions $options
	) {
		$this->plugin              = $plugin;
		$this->options             = $options;
	}

	/**
	 * @return void
	 */
	public function init() {
		do_action( __METHOD__, $this );
	}

}
