<?php

namespace Wpshop\ExpertReview\Settings;

use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

/**
 * Class AdvancedOptions
 * @package Wpshop\ExpertReview\Settings
 *
 * @property int|null    $use_json_ld_faq_microdata
 * @property string|null $expert_microdata_type
 * @property string|null $comment_to_scroll_selector
 * @property int|null    $ask_question_link_new_tab
 * @property string|null $email_to
 * @property string|null $email_cc
 * @property string|null $email_bcc
 * @property string|null $email_to_expert
 * @property bool|null   $enable_consent_checkbox
 * @property bool|null   $enable_templates
 */
class AdvancedOptions extends AbstractOptionStorage {

    const SECTION = 'expert_review_advanced';

    /**
     * @return string
     */
    public function getSection() {
        return self::SECTION;
    }
}
