<?php

defined( 'WPINC' ) || die;

/**
 * @var WP_Post $post
 * @var MyPopup $popup
 */

use Wpshop\PluginMyPopup\MyPopup;
use Wpshop\PluginMyPopup\Plugin;
use function Wpshop\PluginMyPopup\container;

?>
<!doctype html>
<html>
<head>
    <?php wp_head() ?>
    <style>
        body {
            background-color: #f8f9fb;
            background-image: linear-gradient(45deg, #f0f2f5 25%, transparent 25%), linear-gradient(-45deg, #f0f2f5 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f2f5 75%), linear-gradient(-45deg, transparent 75%, #f0f2f5 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0;
        }
    </style>
</head>
<body>
<?php if ( container()->get( Plugin::class )->verify() ): ?>
    <div class="">
        <?php $popup->output_popup( $post ); ?>
    </div>

    <?php do_action( 'my_popup:preview_footer' ); ?>
<?php endif ?>

</body>
</html>
