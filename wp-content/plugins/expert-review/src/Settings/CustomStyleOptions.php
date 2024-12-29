<?php

namespace Wpshop\ExpertReview\Settings;

use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

/**
 * Class CustomStyle
 * @package Wpshop\ExpertReview\Settings
 *
 * @property bool   $enabled
 * @property string $background_color
 * @property string $primary_color
 * @property string $gradient_color
 * @property string $text_color
 * @property string $button_text_color
 */
class CustomStyleOptions extends AbstractOptionStorage {

    const SECTION = 'expert_review_style';

    /**
     * @var string[]
     */
    protected $defaults = [
        'background_color'  => '#ffeff9',
        'primary_color'     => '#ce2fc9',
        'gradient_color'    => '#ce5fca',
        'text_color'        => '#303030',
        'button_text_color' => '#ffffff',
    ];

    /**
     * @inheridoc
     */
    public function getSection() {
        return self::SECTION;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get( $name ) {
        $value = parent::__get( $name );
        if ( null === $value ) {
            if ( array_key_exists( $name, $this->defaults ) ) {
                $value = $this->defaults[ $name ];
            }
        }

        return $value;
    }

}
