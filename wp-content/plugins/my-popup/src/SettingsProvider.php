<?php

namespace Wpshop\PluginMyPopup;

use Wpshop\PluginMyPopup\Settings\PluginOptions;
use Wpshop\SettingApi\OptionField\Checkbox;
use Wpshop\SettingApi\OptionField\Color;
use Wpshop\SettingApi\OptionField\Number;
use Wpshop\SettingApi\OptionField\Select;
use Wpshop\SettingApi\OptionField\Text;
use Wpshop\SettingApi\OptionField\Textarea;
use Wpshop\SettingApi\Section\Section;
use Wpshop\SettingApi\SettingsProviderInterface;
use Wpshop\SettingApi\SettingsPage\TabSettingsPage;

class SettingsProvider implements SettingsProviderInterface {

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var PluginOptions
     */
    protected $baseOptions;

    /**
     * SettingsProvider constructor.
     *
     * @param PluginOptions       $baseOptions
     */
    public function __construct(
        Plugin $plugin,
        PluginOptions $baseOptions
    ) {
        $this->plugin      = $plugin;
        $this->baseOptions = $baseOptions;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSubmenu() {

        $baseOptions = $this->baseOptions;

        $submenu = new TabSettingsPage(
            __( 'Settings', Plugin::TEXT_DOMAIN ),
            __( 'Settings', Plugin::TEXT_DOMAIN ),
            'delete_posts',
            'my-popup-setting'
        );

        $submenu->setParentSlug( AdminMenu::SETTINGS_SLUG );
//		$submenu->setParentSlug( 'edit.php?post_type=' . MyPopup::POST_TYPE );

        $submenu->addSection( $section = new Section(
            $baseOptions->getSection(),
            __( 'Main', Plugin::TEXT_DOMAIN ),
            PluginOptions::class
        ) );

        $section->addField( $field = new Text( 'license' ) );
        $field
            ->setLabel( __( 'License', Plugin::TEXT_DOMAIN ) )
            ->setPlaceholder( $baseOptions->license ? '*****' : __( 'Enter license key', Plugin::TEXT_DOMAIN ) )
            ->setValue( $baseOptions->show_license_key ? null : '' )
            ->setSanitizeCallback( function ( $value ) use ( $baseOptions ) {
                if ( ! $value && ! $baseOptions->show_license_key ) {
                    $value = $baseOptions->license;
                }
                $value = trim( $value );
                if ( current_user_can( 'administrator' ) && $value ) {
                    $this->plugin->activate( $value );
                }

                return null;
            } )
        ;

//        if ( apply_filters( 'my_popup_settings:show_license_key', true ) ) {
//            $section->addField( $field = new Checkbox( 'show_license_key' ) );
//            $field
//                ->setLabel( __( 'Show License', Plugin::TEXT_DOMAIN ) )
//                ->setDescription( __( 'Show license key in input', Plugin::TEXT_DOMAIN ) )
//                ->setEnabled( current_user_can( 'administrator' ) )
//            ;
//        }

        if ( ! $this->plugin->verify() ) {
            return $submenu;
        }


        return $submenu;
    }
}
