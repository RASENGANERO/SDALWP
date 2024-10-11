<?php

defined( 'WPINC' ) || die;

use Wpshop\Quizle\Admin\MenuPage;
use Wpshop\Quizle\Admin\ResultListTable;
use Wpshop\Quizle\Quizle;

/**
 * @var array $args
 */

/** @var ResultListTable|null $grid */
$grid = $args['grid'] ?? null;

?>
<div class="wrap">
    <h1><?php echo __( 'Results', QUIZLE_TEXTDOMAIN ) ?></h1>
    <?php settings_errors( 'quizle_messages' ); ?>
    <div>
        <?php if ( $grid ): ?>
            <form action="" method="post">
                <input type="hidden" name="post_type" value="<?php echo Quizle::POST_TYPE ?>">
                <input type="hidden" name="page" value="<?php echo MenuPage::RESULT_LIST_SLUG ?>">
                <?php
                $grid->prepare_items();
                //$grid->search_box( __( 'Search' ), 'search_id' );
                $grid->display()
                ?>
            </form>
        <?php endif ?>
    </div>
</div>
