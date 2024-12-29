<?php

defined( 'WPINC' ) || die;

use Wpshop\Quizle\Admin\MetaBoxes;

/**
 * @version 1.2.0
 */

/**
 * @var array{'metaboxes': MetaBoxes} $args
 */

?>

<script type="text/html" id="tmpl-quizle-result-content">
    <div>
        <div class="quizle-form-row">
            <label for="result-title" class="quizle-form-label"><?php echo __( 'Title', QUIZLE_TEXTDOMAIN ) ?></label>
            <input type="text" class="quizle-text js-quizle-result-title" data-name="title" value="{{data.title}}">
        </div>
        <div class="quizle-form-row">
            <label for="result-description" class="quizle-form-label"><?php echo __( 'Description', QUIZLE_TEXTDOMAIN ) ?></label>
            <?php
            $args['metaboxes']->wrap_for_editor( 'quizle-result-{{data.id}}', function () {
                ?>
                <textarea class="quizle-text" id="quizle-result-{{data.id}}" data-name="description">{{data.description}}</textarea>
                <?php
            } );
            ?>
        </div>

        <div class="quizle-form-row">
            <div class="quizle-form-cols">
                <div class="quizle-form-col">
                    <div class="js-image-upload-placeholder"></div>
                </div>
                <div class="quizle-form-col">
                    <label class="quizle-form-label"><?php echo __( 'Image Position', QUIZLE_TEXTDOMAIN ) ?></label>
                    <select data-name="image_position" class="quizle-select">
                        <?php $options = [
                            'background' => _x( 'Background', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                            'left'       => _x( 'Left', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                            'top'        => _x( 'Top', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                            'right'      => _x( 'Right', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                            'bottom'     => _x( 'Bottom', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                        ] ?>
                        <?php foreach ( $options as $key => $option ): ?>
                            <option value="<?php echo $key ?>" {{data.image_position=== "<?php echo esc_js( $key ) ?>" ? "selected" : ""}}><?php echo $option ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="quizle-form-row">
            <div class="quizle-form-cols">
                <div class="quizle-form-col">
                    <label class="quizle-form-label"><?php echo __( 'Button Text', QUIZLE_TEXTDOMAIN ) ?></label>
                    <input type="text" class="quizle-text" data-name="btn_text" value="{{quizleFunctions.getResultBtnText(data)}}" placeholder="<?php echo __( 'fill to output the button', QUIZLE_TEXTDOMAIN ) ?>">
                </div>
                <div class="quizle-form-col">
                    <label class="quizle-form-label"><?php echo __( 'Link', QUIZLE_TEXTDOMAIN ) ?></label>
                    <input type="text" class="quizle-text" data-name="link" value="{{data.link}}" placeholder="<?php echo __( 'fill to output the button', QUIZLE_TEXTDOMAIN ) ?>">
                </div>
            </div>
        </div>
        <div class="quizle-form-row js-result-min-max-value" style="display: none">
            <div class="quizle-form-cols">
                <div class="quizle-form-col">
                    <label class="quizle-form-label"><?php echo __( 'Value Min', QUIZLE_TEXTDOMAIN ) ?></label>
                    <input type="number" class="quizle-text" data-name="value_min" value="{{data.value_min}}">
                    <span><?php esc_html_e( 'current minimal available', QUIZLE_TEXTDOMAIN ); ?>: <span class="js-result-min-value">0</span></span>
                </div>
                <div class="quizle-form-col">
                    <label class="quizle-form-label"><?php echo __( 'Value Max', QUIZLE_TEXTDOMAIN ) ?></label>
                    <input type="number" class="quizle-text" data-name="value_max" value="{{data.value_max}}">
                    <span><?php esc_html_e( 'current maximum available', QUIZLE_TEXTDOMAIN ); ?>: <span class="js-result-max-value">0</span></span>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-quizle-result-redirect-link">
    <div>
        <div class="quizle-form-row">
            <label for="result-redirect-link" class="quizle-form-label"><?php echo __( 'Redirect Link', QUIZLE_TEXTDOMAIN ) ?></label>
            <input type="text" class="quizle-text js-quizle-result-redirect-link" data-name="redirect_link" value="{{data.redirect_link}}">
        </div>
        <div class="quizle-form-row js-result-min-max-value" style="display: none">
            <div class="quizle-form-cols">
                <div class="quizle-form-col">
                    <label class="quizle-form-label"><?php echo __( 'Value Min', QUIZLE_TEXTDOMAIN ) ?></label>
                    <input type="number" class="quizle-text" data-name="value_min" value="{{data.value_min}}">
                    <span><?php esc_html_e( 'current minimal available', QUIZLE_TEXTDOMAIN ); ?>: <span class="js-result-min-value">0</span></span>
                </div>
                <div class="quizle-form-col">
                    <label class="quizle-form-label"><?php echo __( 'Value Max', QUIZLE_TEXTDOMAIN ) ?></label>
                    <input type="number" class="quizle-text" data-name="value_max" value="{{data.value_max}}">
                    <span><?php esc_html_e( 'current maximum available', QUIZLE_TEXTDOMAIN ); ?>: <span class="js-result-max-value">0</span></span>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-quizle-result">
    <div class="quizle-result js-quizle-result-item">
        <input type="hidden" data-name="id" value="{{data.id}}">

        <div class="quizle-result-header quizle-result__header">
            <div class="quizle-result-header__move"></div>
            <div class="quizle-result-header__title js-quizle-result-action-expand">
                <?php echo __( 'Result', QUIZLE_TEXTDOMAIN ) ?> -
                <span class="quizle-result-header__title-additional">{{data.title}}</span>
            </div>
            <div class="quizle-result-header__actions">
                <div class="quizle-result-action quizle-result-action--copy" title="<?php echo __( 'Duplicate Result', QUIZLE_TEXTDOMAIN ) ?>"></div>
                <div class="quizle-result-action quizle-result-action--delete" title="<?php echo __( 'Remove Result', QUIZLE_TEXTDOMAIN ) ?>" data-confirm="<?php echo __( 'Are you sure you want to remove the result?', QUIZLE_TEXTDOMAIN ) ?>"></div>
                <div class="quizle-result-action quizle-result-action--expand js-quizle-result-action-expand"></div>
            </div>
        </div>
        <div class="quizle-result__body">
            <div class="js-quizle-result-init">
                <button class="button" data-result_type="content"><?php echo __( 'Set Content', QUIZLE_TEXTDOMAIN ) ?></button>
                <button class="button" data-result_type="redirect_link"><?php echo __( 'Set Redirect Link', QUIZLE_TEXTDOMAIN ) ?></button>
            </div>
        </div>
    </div>
</script>
