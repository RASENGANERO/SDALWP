<?php

namespace Wpshop\Quizle\Integration;

use Wpshop\Quizle\QuizleResult;
use Wpshop\Quizle\Social;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\ob_get_content;

trait IntegrationTrait {

    /**
     * @param QuizleResult $result
     * @param array        $data
     *
     * @return false|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function get_note_text( $result, $data ) {
        $text = '';

        foreach ( array_keys( container()->get( Social::class )->get_messengers() ) as $messenger ) {
            if ( array_key_exists( $messenger, $data ) ) {
                $text .= "$messenger: {$data[$messenger]}" . PHP_EOL;
            }
        }

        $text .= PHP_EOL;

        $text .= ob_get_content( function () use ( $result ) {
            \Wpshop\Quizle\get_template_part(
                'mail/plain/_result-details',
                '',
                [ 'result' => $result ]
            );
        } );

        return trim( $text );
    }
}
