<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Integration\AmoCRM;
use function Wpshop\Quizle\container;

$settings = container()->get( Settings::class );

?>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Integrations', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/integrations/' ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'integrations.emails', __( 'Emails', QUIZLE_TEXTDOMAIN ) ); ?>
    <?php
    // todo добавить описание как это работает
    // Можно даже назвать типа E-mail по умолчанию
    // А внизу подробно расписать и можно указать, что через запятую несколько указать
    // ?>
</div>

<div class="wpshop-settings-form-row">
    <?php
    $name = 'integrations.webhook.urls';
    $args = [
        'id'   => uniqid( "{$name}." ),
        'cols' => '',
        'rows' => 5,
    ] ?>
    <div class="wpshop-settings-form-row__label">
        <label for="<?php echo esc_attr( $args['id'] ) ?>"><?php echo __( 'Webhook Urls', QUIZLE_TEXTDOMAIN ) ?></label>
    </div>
    <div class="wpshop-settings-form-row__body">
        <?php $settings->render_textarea_field( $name, $args ) ?>
        <p class="description"><?php echo __( 'new line separated values', 'quizle' ) ?></p>
    </div>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Telegram', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/integrations/#telegram'
    ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'integrations.telegram.enabled', __( 'Enable', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'integrations.telegram.token', __( 'Token', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'integrations.telegram.chat_id', __( 'Chat Id', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'AmoCRM', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/integrations/#amocrm'
    ) ?>
</div>


<?php /*
<div class="wpshop-settings-form-row">
    <div class="wpshop-settings-form-row__label">
        <?php echo __( 'AmoCRM Integration', 'quizle' ) ?>
    </div>
    <div class="wpshop-settings-form-row__body">
        <?php // see https://www.amocrm.ru/developers/content/oauth/button ?>
        <?php if ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE == 'local' || is_ssl() ): ?>
            <script type="text/javascript">
                function quizleAmoCRMIntegrationCallback() {
                    const el = document.createElement('div');
                    el.innerText = '<?php echo __( 'Unable to setup integration: access denied', 'quizle' ) ?>';
                    el.style.color = "#ff0c0c"
                    document.querySelector('.quizle-amocrm-integration-messages').appendChild(el);
                    setTimeout(function () {
                        el.remove();
                    }, 5000);
                }
            </script>
            <div style="width: 100%">
                <script
                        class="amocrm_oauth"
                        charset="utf-8"
                        data-name="<?php echo __( 'AmoCRM Quizle', 'quizle' ) ?>"
                        data-description="<?php echo __( 'Integration of AmoCRM with Quizle', 'quizle' ) ?>"
                        data-redirect_uri="<?php echo esc_attr( container()->get( AmoCRM::class )->get_redirect_url() ) ?>"
                        data-secrets_uri="<?php echo esc_attr( container()->get( AmoCRM::class )->get_redirect_url( [ 'action' => 'secrets' ] ) ) ?>"
                        data-logo=""
                        data-scopes="crm,notifications"
                        data-title="<?php echo __( 'Enable Integration', 'quizle' ) ?>"
                        data-compact="false"
                        data-class-name=""
                        data-color="blue"
                        data-state="state"
                        data-error-callback="quizleAmoCRMIntegrationCallback"
                        data-mode="popup"
                        src="https://www.amocrm.ru/auth/button.min.js"
                ></script>
            </div>
            <div class="quizle-amocrm-integration-messages"></div>
        <?php else: ?>
            <div>
                <span class="description"><?php echo __( 'Integration is not available on the site without SSL', 'quizle' ) ?></span>
            </div>
        <?php endif ?>
    </div>
</div>
*/ ?>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'integrations.amocrm.enabled', __( 'Enable', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'integrations.amocrm.base_domain', __( 'Account Base Domain', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_password_input( 'integrations.amocrm.long_term_token', __( 'Long-term Token', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'integrations.amocrm.price', __( 'Price', 'quizle' ) ); ?>
</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Bitrix24', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/integrations/#bitrix24'
    ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'integrations.bitrix.enabled', __( 'Enable', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_password_input( 'integrations.bitrix.endpoint', __( 'Webhook for Rest API Call', 'quizle' ), [ 'placeholder' => 'https://[your-subdomain].bitrix24.ru/rest/1/[your-key]/' ] ); ?>
</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Яндекс.Метрика', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/integrations/#yandex-metrika'
    ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'integrations.metrika.enabled', __( 'Enable', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox(
        'integrations.metrika.disable_code_output',
        __( 'Disable integration code from Quizle', 'quizle' ),
        [],
        __( 'use this option if you already have integration with Yadnex.Metrika enabled in another way and do not want the code of integration to be duplicated', 'quizle' )
    ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'integrations.metrika.counter', __( 'Counter ID', 'quizle' ) ); ?>
</div>

<div>
    <h3><?php echo __( 'Available target identifiers', 'quizle' ) ?></h3>
    <ul>
        <li><code>quizle:start</code>
            — <?php echo __( 'It is triggered the first time you interact with a quiz', 'quizle' ) // Срабатывает при первом взаимодействии с квизом. ?>
        </li>
        <li><code>quizle:finish</code>
            — <?php echo __( 'It is triggered when the last question is sent', 'quizle' ) // Срабатывает при отправке последнего вопроса ?>
        </li>
        <li><code>quizle:next</code>
            — <?php echo __( 'Triggered by the "Next" button.', 'quizle' ) // Срабатывает по кнопке "Дальше" ?></li>
        <li><code>quizle:prev</code>
            — <?php echo __( 'Triggered by the "Prev" button.', 'quizle' ) // Срабатывает по кнопке "Назад" ?></li>
        <li><code>quizle:show_results</code>
            — <?php echo __( 'Triggers when results are displayed', 'quizle' ) // Срабатывает при показе результатов ?>
        </li>
        <li><code>quizle:show_contacts</code>
            — <?php echo __( 'Triggers when a contact form is displayed', 'quizle' ) // Срабатывает при показе контактной формы ?>
        </li>
        <li><code>quizle:submit_contacts</code>
            — <?php echo __( 'Triggers when contact info is submitted', 'quizle' ) // Срабатывает при отправке контактных данных  ?>
        </li>
        <li><code>quizle:progress</code>
            — <?php echo __( 'Triggers when progress is changed', 'quizle' ) // Срабатывает при изменении прогресса ?>
        </li>
    </ul>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'General Settings', 'quizle' ) ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'integrations.skip_empty_contacts', __( 'Do not submit result with empty contact data', 'quizle' ) ); ?>
</div>

