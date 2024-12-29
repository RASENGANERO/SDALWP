<?php

namespace Wpshop\ExpertReview\Settings;

use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

/**
 * Class QaOptions
 * @package Wpshop\ExpertReview\Settings
 *
 * @property string|null $qa_question_tag
 * @property string|null $qa_answer_tag
 * @property string|null $pluses_header_tag
 */
class QaOptions extends AbstractOptionStorage {

    const SECTION = 'expert_review_qa';

    /**
     * @inheritDoc
     */
    public function getSection() {
        return self::SECTION;
    }
}
