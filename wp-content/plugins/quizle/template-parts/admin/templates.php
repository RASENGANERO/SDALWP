<?php

/**
 * @version  1.4.0
 */

defined( 'WPINC' ) || die;

use Wpshop\Quizle\Quizle;

?>

<div class="wrap wpshop-settings-wrap">

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div class="quizle-import wpshop-settings-box">
        <div class="wpshop-settings-header">
            <div class="quizle-import__header wpshop-settings-header__title">
                <span><?php echo __( 'Import' ) ?></span>
            </div>
        </div>
        <p class="description"><?php printf( __( 'Import Quizles (max files size: %s)', QUIZLE_TEXTDOMAIN ), ini_get( 'upload_max_filesize' ) ) ?></p>

        <form action="" method="post" class="quizle-import-form js-quizle-import-form">
            <fieldset>
                <input type="file">
                <label>
                    <input type="hidden" name="override" value="0">
                    <input type="checkbox" name="override" value="1"> <?php echo __( 'Override existing quizles', QUIZLE_TEXTDOMAIN ) ?>
                </label>
                <button type="submit" class="wpshop-settings-button js-quizle-import"><?php echo __( 'Import' ) ?></button>
            </fieldset>
        </form>
    </div>

    <?php /*
    <div class="quizle-import wpshop-settings-box">
        <?php
        $url = add_query_arg( [
            'post_type' => Quizle::POST_TYPE,
            'page'      => 'quizle-templates',
        ], admin_url( 'edit.php' ) )
        ?>
        <form action="<?php echo $url ?>" enctype="multipart/form-data" method="post">
            <input type="file" name="file">
            <button type="submit" class="wpshop-settings-button"><?php echo __( 'Create Quizle from Text File', 'quizle' ) ?></button>
        </form>
        <p><?php echo __( 'Example of file content', 'quizle' ) ?>:</p>
        <pre>
Вопрос 1
Ответ1|Ответ2|Ответ3|Ответ4

Вопрос 2
Ответ1|Ответ2|Ответ3
        </pre>
        <p><?php echo __( 'After uploading the file, you will be redirected to the quizle editing page. You have to add additional data and publish the quizle.', 'quizle' ) ?></p>
    </div>

    <div style="position: absolute; width: 0; height: 0; overflow: hidden; z-index: -9999;">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <symbol id="ico-heart" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                <path d="M446.17 97.44c-44.92-44.59-118.08-44.59-163 0l-27.17 27-27.17-27c-44.92-44.59-118.08-44.59-163 0a113.64 113.64 0 000 161.8C78.44 271.8 250 442 256 448c5.5-5.59 178.05-176.63 190.17-188.76a113.64 113.64 0 000-161.8zm-22.65 139.61L256 403.32 88.48 237.05a82.19 82.19 0 01-24.55-58.71c-.88-44.63 38.67-83.82 83.4-82.85 43.65-4.1 80.52 47.92 108.67 73.6 28.4-25.79 64.84-77.59 108.67-73.6 72.45-1.87 111.72 91.66 58.85 141.56z" fill="currentColor"/>
            </symbol>
            <symbol id="ico-heart-solid" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M446.17 97.44c-44.92-44.59-118.08-44.59-163 0l-27.17 27-27.17-27c-44.92-44.59-118.08-44.59-163 0a113.64 113.64 0 000 161.8C78.44 271.8 250 442 256 448c5.5-5.59 178.05-176.63 190.17-188.76a113.64 113.64 0 000-161.8z" fill="currentColor"></path>
            </symbol>
            <symbol id="cloud-download-light" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path d="M371.31 364.69a16 16 0 010 22.62l-88 88a16 16 0 01-22.62 0l-88-88a16 16 0 0122.62-22.62L256 425.37V260a16 16 0 0132 0v165.37l60.69-60.68a16 16 0 0122.62 0zM446.74 229a176 176 0 10-350.1-36A112 112 0 000 305c.52 61.66 51.46 111 113.13 111A14.83 14.83 0 00128 401.17v-2.34A14.83 14.83 0 00113.17 384c-44.25 0-81.41-36.2-81.17-80.44a79.95 79.95 0 0197.09-77.7A146.89 146.89 0 01128 208a144.38 144.38 0 01.75-14.75 144 144 0 11278.89 63.31 64 64 0 0121.24 126.14A15.92 15.92 0 00416 398.22v.43a15.78 15.78 0 0018.77 15.52 96 96 0 0012-185.14z" fill="currentColor"></path>
            </symbol>
        </svg>
    </div>

 */ ?>

    <script id="tmpl-quizle-template-item" type="text/html">
        <div class="quizle-template wpshop-settings-box quizle-template-list__item js-quizle-template-item" data-id="{{data.id}}" style="position:relative;">
            <div class="quizle-template__top">
                <div class="quizle-template-title">
                    <div class="quizle-template-title__text">{{data.title}}</div>
                    <div class="quizle-template-title__icons">
                        <div class="quizle-template-likes" title="<?php echo __( 'likes', QUIZLE_TEXTDOMAIN ) ?>">
                            <svg width="24" height="24" class="quizle-template-likes__icon quizle-template--pointer js-quizle-template-like" data-liked="{{data.liked}}">
                                <# if (parseInt(data.liked)) { #>
                                <use xlink:href="#ico-heart-solid"></use>
                                <# } else { #>
                                <use xlink:href="#ico-heart"></use>
                                <# } #>
                            </svg>
                            <div class="quizle-template-likes__counter js-quizle-template-like-count">
                                {{{data.likes ? data.likes : 0}}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="quizle-template-body">
                    <img class="quizle-template-body__preview-img" src="{{data.image}}" alt="">
                    <div class="quizle-template-body__tags quizle-template-tags">
                        <# for (var i = 0; i < data.tags.length; i++) { #>
                        <div class="quizle-template-tags__item">{{data.tags[i].name}}</div>
                        <# } #>
                    </div>
                    <div class="quizle-template-body__description">{{data.description}}</div>
                </div>
            </div>
            <div class="quizle-template__bottom">
                <button class="wpshop-settings-button js-quizle-template-item-apply" data-name="{{data.name}}"><?php echo __( 'Create Quizle', QUIZLE_TEXTDOMAIN ) ?></button>
                <div class="quizle-template-progress js-progress-container"></div>
                <div class="quizle-template-downloads" title="<?php echo __( 'downloads', QUIZLE_TEXTDOMAIN ) ?>">
                    <svg width="24" height="24" class="quizle-template-downloads__icon">
                        <use xlink:href="#cloud-download-light"></use>
                    </svg>
                    <div class="quizle-template-downloads__counter js-quizle-template-download-count">
                        {{{data.downloads ? data.downloads : 0}}}
                    </div>
                </div>
            </div>
        </div>
    </script>

    <div class="quizle-templates">
        <h2 class="quizle-templates__header"><?php echo __( 'Templates', QUIZLE_TEXTDOMAIN ) ?></h2>
        <div class="quizle-templates__body quizle-template-list js-templates-container">
            <div><?php echo __( 'Loading...', QUIZLE_TEXTDOMAIN ) ?></div>
        </div>
    </div>
</div>
