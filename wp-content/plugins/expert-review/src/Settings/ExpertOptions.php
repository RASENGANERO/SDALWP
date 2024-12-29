<?php

namespace Wpshop\ExpertReview\Settings;

use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

/**
 * Class ExpertOptions
 * @package Wpshop\ExpertReview\Settings
 *
 * @property string|null $experts
 * @property int|null    $use_user_expert_links
 */
class ExpertOptions extends AbstractOptionStorage {

    const SECTION = 'expert_review_experts';

    /**
     * @return string
     */
    public function getSection() {
        return self::SECTION;
    }

    public function get_by_id( $id ) {
        if ( $this->experts ) {
            $items = json_decode( $this->experts, true );
            foreach ( $items as $item ) {
                if ( $item['id'] == $id ) {
                    return $item;
                }
            }
        }

        return null;
    }
}
