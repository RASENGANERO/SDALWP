<?php

namespace Wpshop\Quizle\Admin;

use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use function Wpshop\Quizle\admin_icon_url;
use function Wpshop\Quizle\get_template_part;

class MenuPage {

    const RESULT_LIST_SLUG = 'quizle-results';
    const SETTINGS_SLUG    = 'quizle-settings';

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }


    /**
     * @return void
     */
    public function init() {
        add_action( 'admin_menu', [ $this, '_setup_menu' ] );
    }

    /**
     * @return void
     */
    public function _setup_menu() {
        if ( $this->settings->verify() ) {
            add_submenu_page(
                'edit.php?post_type=' . Quizle::POST_TYPE,
                __( 'Results', QUIZLE_TEXTDOMAIN ),
                __( 'Results', QUIZLE_TEXTDOMAIN ),
                'manage_options',
                self::RESULT_LIST_SLUG,
                function () {
                    if ( empty( $_REQUEST['quiz_result_id'] ) ) {
                        if ( ! empty( $_GET['quizle-removed'] ) ) {
                            add_settings_error(
                                'quizle_messages',
                                'quizle_result_removed',
                                QuizleResultActions::get_success_message( $_GET['quizle-removed'] ),
                                'success'
                            );
                        }
                        get_template_part( 'admin/results', null, [
                            'grid' => PluginContainer::get( ResultListTable::class ),
                        ] );
                    } else {
                        get_template_part( 'admin/result', 'single', [
                            'result' => PluginContainer::get( Database::class )->get_quizle_result( $_REQUEST['quiz_result_id'] ?? - 1 ),
                        ] );
                    }
                }
            );

            add_submenu_page(
                null,
                __( 'Analytics', QUIZLE_TEXTDOMAIN ),
                __( 'Analytics', QUIZLE_TEXTDOMAIN ),
                'manage_options',
                'analytics',
                function () {
                    if ( $quizle_id = $_REQUEST['id'] ?? null ) {
                        get_template_part( 'admin/analytics', 'single', [
                            'post' => get_post( $quizle_id ),
                        ] );
                    } else {
                        get_template_part( 'admin/analytics' );
                    }
                }
            );

            add_submenu_page(
                'edit.php?post_type=' . Quizle::POST_TYPE,
                _x( 'Quizle Settings', 'settings', QUIZLE_TEXTDOMAIN ),
                __( 'Settings', QUIZLE_TEXTDOMAIN ),
                'manage_options',
                self::SETTINGS_SLUG,
                function () {
                    if ( isset( $_GET['settings-updated'] ) ) {
                        add_settings_error( 'quizle_messages', 'quizle_settings_updated', __( 'Settings saved.' ), 'success' );
                    }
                    get_template_part( 'admin/settings' );
                }
            );
        } else {
            add_menu_page(
                __( 'Quizle', QUIZLE_TEXTDOMAIN ),
                __( 'Quizle', QUIZLE_TEXTDOMAIN ),
                'manage_options',
                self::SETTINGS_SLUG,
                function () {
                    get_template_part( 'admin/settings' );
                },
                admin_icon_url()
            );
        }
    }
}
