<?php

namespace Wpshop\ExpertReview\Settings;

use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

/**
 * Class PluginOptions
 *
 * @property string|null $license
 * @property int|null    $show_license_key
 * @property int|null    $error_log_level
 * @property string|null $license_verify
 * @property string|null $license_error
 */
class PluginOptions extends AbstractOptionStorage {

	const SECTION = 'expert_review_base';

	/**
	 * @return string
	 */
	public function getSection() {
		return self::SECTION;
	}

	/**
	 * @return string|null
	 */
	public function getLicense() {
		return get_option( 'expert-review-license', null );
	}

	/**
	 * @param string $value
	 *
	 * @return void
	 */
	public function setLicense( $value ) {
		update_option( 'expert-review-license', $value );
	}

	/**
	 * @return string|null
	 */
	public function getLicenseVerify() {
		return get_option( 'expert-review-license-verify', null );
	}

	/**
	 * @param string|null $value
	 *
	 * @return $this
	 */
	public function setLicenseVerify( $value ) {
		update_option( 'expert-review-license-verify', $value );

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getLicenseError() {
		return get_option( 'expert-review-license-error', null );
	}

	/**
	 * @param string|null $value
	 *
	 * @return $this
	 */
	public function setLicenseError( $value ) {
		update_option( 'expert-review-license-error', $value );

		return $this;
	}
}
