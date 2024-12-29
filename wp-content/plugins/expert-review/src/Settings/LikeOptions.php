<?php

namespace Wpshop\ExpertReview\Settings;

use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

/**
 * Class LikeOptions
 * @package Wpshop\ExpertReview\Settings
 *
 * @property int|null    $likes_before_content
 * @property int|null    $likes_after_content
 * @property string|null $exclude_post_ids
 * @property string|null $exclude_post_categories
 * @property string|null $likes_content
 * @property int|null    $likes_for_comment
 * @property string|null $comment_likes_content
 * @property string      $microdata_type
 * @property int|null    $microdata_likes
 * @property int|null    $microdata_dislikes
 */
class LikeOptions extends AbstractOptionStorage {

    const SECTION = 'expert_review_additional';

    /**
     * @inheritDoc
     */
    public function getSection() {
        return self::SECTION;
    }
}
