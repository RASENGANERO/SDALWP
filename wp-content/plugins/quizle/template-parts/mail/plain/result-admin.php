<?php

/**
 * @version 1.3.0
 */


defined( 'WPINC' ) || die;

use function Wpshop\Quizle\get_template_part;
use function Wpshop\Quizle\sanitize_phone;

/**
 * @var array $args
 */

/** @var \Wpshop\Quizle\QuizleResult $result */
$result = $args['result'];

echo __( 'New Quizle Contacts', QUIZLE_TEXTDOMAIN );
echo PHP_EOL;
echo PHP_EOL;

echo __( 'Name', QUIZLE_TEXTDOMAIN ) . ': ' . $result->name;
echo PHP_EOL;
echo __( 'Email', QUIZLE_TEXTDOMAIN ) . ': ' . $result->email;
echo PHP_EOL;

if ( $result->phone ):
    echo __( 'Phone', QUIZLE_TEXTDOMAIN ) . ': ' . sanitize_phone( $result->phone );
    echo PHP_EOL;
endif;

foreach ( $result->get_from_additional_data( 'messengers', [] ) as $messenger => $value ):
    echo ucfirst( $messenger ) . ': ' . $value;
    echo PHP_EOL;
endforeach;
echo PHP_EOL;
echo __( 'Result Details', QUIZLE_TEXTDOMAIN );
echo PHP_EOL;
get_template_part( 'mail/plain/_result-details', '', [ 'result' => $result ] ); ?>
