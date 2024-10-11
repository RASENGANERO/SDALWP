<?php

namespace Wpshop\Settings;

abstract class AbstractSettings {

    const VERSION = '0.1.0';

    const ASSETS_VERSION = '0.1.0';

    const TEXT_DOMAIN = '{{text-domain}}';

    /**
     * @var MaintenanceInterface
     */
    protected $maintenance;

    /**
     * @var array
     */
    protected $tabs = [];

    /**
     * @var string
     */
    protected $reg_option;

    /**
     * @var string
     */
    protected $reg_option_group;

    /**
     * @var string
     */
    protected $option;

    /**
     * @var string
     */
    protected $option_group;

    /**
     * @var string
     */
    protected $welcome_option;

    /**
     * @var string
     */
    protected $capability = 'manage_options';

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var array
     */
    protected $sanitizers = [];

    /**
     * @var array|null
     */
    protected $_options;

    /**
     * @var array|null
     */
    protected $_options_with_defaults;

    /**
     * @param MaintenanceInterface $maintenance
     * @param string|string[]      $reg_option ['reg-option', 'reg-option-group'] or just reg option name
     * @param string|string[]      $option     ['option', 'option-group'] or just option name, uses for store settings
     */
    public function __construct( MaintenanceInterface $maintenance, $reg_option, $option ) {
        $reg_option = is_array( $reg_option ) ? $reg_option : [ $reg_option, $reg_option . '-group' ];
        $option     = is_array( $option ) ? $option : [ $option, $option . '-group' ];

        $this->maintenance = $maintenance;
        [ $this->reg_option, $this->reg_option_group ] = $reg_option;
        [ $this->option, $this->option_group ] = $option;
        $this->welcome_option = "{$this->option}--welcome";
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'init', function () {
            $this->verify() && $this->maintenance->init_updates( $this->get_reg_option()['license'] );
        } );

        add_action( 'init', function () {
            if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) !== 'POST' ||
                 ! current_user_can( $this->capability )
            ) {
                return;
            }

            if ( ( $_POST['option_page'] ?? '' ) === $this->reg_option_group ) {
                if ( is_multisite() && ! current_user_can( 'manage_network_options' ) ) {
                    wp_die( __( 'Sorry, you are not allowed to modify unregistered settings for this site.', static::TEXT_DOMAIN ) );
                }

                check_admin_referer( $this->reg_option_group . '-options' );

                $license = $_POST[ $this->reg_option ]['license'] ?? '';
                $result  = $this->maintenance->activate( $license, function ( $params ) {
                    $opt = wp_parse_args( $params, [
                        'license'        => '',
                        'license_verify' => '',
                        'license_error'  => '',
                    ] );

                    update_option( $this->reg_option, $opt );
                } );

                switch ( $this->maintenance->get_type() ) {
                    case 'plugin':
                        wp_redirect( add_query_arg( 'plugin-activated', is_wp_error( $result ) ? 0 : 1, wp_get_referer() ) );
                        die;
                    case 'theme':
                        wp_redirect( add_query_arg( 'theme-activated', is_wp_error( $result ) ? 0 : 1, wp_get_referer() ) );
                        break;
                    default:
                        break;
                }

                wp_redirect( wp_get_referer() );
                die;
            }
        } );

        add_filter( 'removable_query_args', function ( $removable_query_args ) {
            $removable_query_args[] = 'plugin-activated';
            $removable_query_args[] = 'theme-activated';

            return array_unique( $removable_query_args );
        }, 11 );

        add_action( 'admin_init', function () {
            register_setting( $this->reg_option_group, $this->reg_option );

            if ( ! $this->verify() ) {
                $this->add_tab( 'dashboard-activate', __( 'Dashboard', static::TEXT_DOMAIN ) );

                return;
            } else {
                $this->add_tab( 'dashboard', __( 'Dashboard', static::TEXT_DOMAIN ) );
            }

            register_setting( $this->option_group, $this->option );

            $this->setup_tabs();
        } );

        add_action( 'updated_option', function ( $option ) {
            if ( $option === $this->option ) {
                $this->_options = null;
            }
        } );

        add_filter( "sanitize_option_{$this->option}", function ( $value ) {
            foreach ( $this->sanitizers as $key => $fn ) {
                if ( array_key_exists( $key, $value ) && is_callable( $fn ) ) {
                    $value[ $key ] = call_user_func( $fn, $value[ $key ] );
                }
            }

            return $value;
        } );

        $action = 'wpshop_settings_hide_welcome';
        add_action( "wp_ajax_{$action}", function () {
            update_option( $this->welcome_option, 1 );
            wp_send_json_success();
        } );

        $action = 'wpshop_settings_remove_license';
        add_action( "wp_ajax_{$action}", function () {
            if ( ! current_user_can( 'administrator' ) ) {
                wp_send_json_error();
            }
            delete_option( $this->reg_option );
            wp_send_json_success();
        } );
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function doc_link( $type ) {
        return '#';
    }

    /**
     * @return void
     */
    protected function setup_tabs() {

    }

    /**
     * @return bool
     */
    public function verify() {
        $opt = $this->get_reg_option();

        return ( $opt['license'] && $opt['license_verify'] && ! $opt['license_error'] );
    }

    /**
     * @return array
     */
    public function get_reg_option() {
        return wp_parse_args( get_option( $this->reg_option, [] ), [
            'license'        => '',
            'license_verify' => '',
            'license_error'  => '',
        ] );
    }

    /**
     * @return bool
     */
    public function do_show_welcome() {
        return ! get_option( $this->welcome_option, 0 );
    }

    /**
     * @param string $name
     * @param string $label
     *
     * @return $this
     */
    public function add_tab( $name, $label, $template_name = null ) {
        $id = 'tab-' . sanitize_html_class( $name );
        if ( null === $template_name ) {
            $template_name = $name;
        }
        $this->tabs[ $name ] = compact( 'name', 'label', 'id', 'template_name' );

        return $this;
    }

    /**
     * @return array
     */
    public function get_tabs() {
        return $this->tabs;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function get_input_name( $name ) {
        return $this->option . "[{$name}]";
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function get_reg_input_name( $name ) {
        return $this->reg_option . "[{$name}]";
    }

    /**
     * @param string $key
     * @param bool   $null_default get null if value is same as default
     *
     * @return mixed|null
     */
    public function get_value( $key, $null_default = false ) {
        if ( null === $this->_options ) {
            $this->_options               = (array) get_option( $this->option, [] );
            $this->_options_with_defaults = wp_parse_args( $this->_options, $this->defaults );
        }

        if ( $null_default ) {
            if ( array_key_exists( $key, $this->_options ) &&
                 array_key_exists( $key, $this->defaults ) &&
                 $this->_options[ $key ] === $this->defaults[ $key ]
            ) {
                return null;
            }
        }

        return $this->_options_with_defaults[ $key ] ?? null;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function get_default( $key ) {
        return array_key_exists( $key, $this->defaults ) ? $this->defaults[ $key ] : null;
    }

    /**
     * @return array
     */
    public function get_defaults() {
        return $this->defaults;
    }

    /**
     * @return $this
     */
    public function clear_database() {
        delete_option( $this->option );
        delete_option( $this->reg_option );
        delete_option( "{$this->option}--welcome" );

        return $this;
    }

    /**
     * @return void
     */
    public function render_reg_input() {
        settings_fields( $this->reg_option_group );
        ?>
        <input name="<?php echo "{$this->reg_option}[license]" ?>" type="text" value="" placeholder="XX0000-000000-000000000000000000-0000" class="wpshop-settings-text">
        <button type="submit" class="wpshop-settings-button"><?php echo __( 'Activate', static::TEXT_DOMAIN ) ?></button>
        <?php
    }

    /**
     * @param string $title
     * @param string $description
     * @param string $doc_link
     *
     * @return void
     */
    public function render_header( $title, $description = '', $doc_link = '' ) {
        ?>
        <div class="wpshop-settings-header__title">
            <span><?php echo $title ?></span>
            <?php if ( $doc_link ): ?>
                <a href="<?php echo esc_attr( $doc_link ) ?>" target="_blank" rel="noopener" class="wpshop-settings-help-ico">?</a>
            <?php endif ?>
        </div>
        <?php if ( $description ): ?>
            <div class="wpshop-settings-header__description">
                <?php echo $description ?>
            </div>
        <?php endif;
    }

    /**
     * @param string $title
     * @param string $description
     * @param string $doc_link
     *
     * @return void
     */
    public function render_subheader( $title, $description = '', $doc_link = '' ) {
        ?>
        <div class="wpshop-settings-subheader">
            <div class="wpshop-settings-subheader__title">
                <span><?php echo $title ?></span>
                <?php if ( $doc_link ): ?>
                    <a href="<?php echo esc_attr( $doc_link ) ?>" target="_blank" rel="noopener" class="wpshop-settings-help-ico">?</a>
                <?php endif ?>
            </div>
            <?php if ( $description ): ?>
                <div class="wpshop-settings-subheader__description">
                    <?php echo $description ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param string $name input name
     * @param string $title
     * @param array  $args
     *
     * @return void
     */
    public function render_input( $name, $title, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        ?>
        <div class="wpshop-settings-form-row__label">
            <label for="<?php echo esc_attr( $args['id'] ) ?>"><?php echo $title ?></label>
        </div>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_input_field( $name, $args ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public function render_input_field( $name, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'type' => 'text',
            'id'   => uniqid( "{$name}." ),
        ] );

        $input_name = $this->get_input_name( $name );
        $attributes = [];
        foreach ( [ 'type', 'min', 'max', 'step' ] as $attr ) {
            if ( array_key_exists( $attr, $args ) ) {
                $attributes[] = "$attr=\"{$args[$attr]}\"";
            }
        }
        $attributes = implode( ' ', $attributes );
        $attributes = $attributes ? " $attributes" : '';
        ?>
        <input id="<?php echo esc_attr( $args['id'] ) ?>"
               name="<?php echo esc_attr( $input_name ) ?>"
               value="<?php echo $this->get_value( $name ) ?>"<?php echo $attributes ?>>
        <?php
    }

    /**
     * @param string $name input name
     * @param string $title
     * @param array  $options
     * @param array  $args
     *
     * @return void
     */
    public function render_select( $name, $title, array $options, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        ?>
        <div for="<?php echo esc_attr( $args['id'] ) ?>" class="wpshop-settings-form-row__label">
            <label><?php echo $title ?></label>
        </div>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_select_field( $name, $options, $args ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $args
     *
     * @return void
     */
    public function render_select_field( $name, array $options, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $input_name = $this->get_input_name( $name );
        $classes    = implode( ' ', (array) ( $args['classes'] ?? [] ) );
        $classes    = $classes ? " $classes" : '';
        ?>
        <select id="<?php echo esc_attr( $args['id'] ) ?>"
                name="<?php echo esc_attr( $input_name ) ?>"
                class="<?php echo $classes ?>">
            <?php foreach ( $options as $value => $label ): ?>
                <option value="<?php echo $value ?>"<?php selected( $this->get_value( $name ), $value ) ?>><?php echo $label ?></option>
            <?php endforeach ?>
        </select>
        <?php
    }

    /**
     * @param string $name input name
     * @param string $label
     * @param array  $args
     *
     * @return void
     */
    public function render_checkbox( $name, $label = '', array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        ?>
        <label for="<?php echo esc_attr( $args['id'] ) ?>" class="wpshop-settings-form-label">
            <?php $this->render_checkbox_field( $name, $label, $args ); ?>
        </label>
        <?php
    }

    /**
     * @param string $name
     * @param string $label
     * @param array  $args
     *
     * @return void
     */
    public function render_checkbox_field( $name, $label = '', array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $input_name = $this->get_input_name( $name );
        $classes    = implode( ' ', (array) ( $args['classes'] ?? [] ) );
        $classes    = $classes ? " $classes" : '';

        $data_attributes = [];
        foreach ( $args as $key => $value ) {
            if ( substr( $key, 0, 5 ) === 'data-' ) {
                $data_attributes[] = "$key=\"$value\"";
            }
        }
        $data_attributes = implode( ' ', $data_attributes );
        $data_attributes = $data_attributes ? " $data_attributes" : '';
        ?>
        <input type="hidden" name="<?php echo $input_name ?>" value="0">
        <input type="checkbox"
               class="wpshop-settings-switch-box<?php echo $classes ?>"
               name="<?php echo esc_attr( $input_name ) ?>"
               id="<?php echo esc_attr( $args['id'] ) ?>"
            <?php echo $data_attributes ?>
               value="1"<?php checked( $this->get_value( $name ) ) ?>>
        <?php echo esc_html( $label ) ?>
        <?php
    }

    /**
     * @param string $name
     * @param string $title
     * @param array  $args
     *
     * @return void
     */
    public function render_textarea( $name, $title, $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        ?>
        <div class="wpshop-settings-form-row__label">
            <label for="<?php echo esc_attr( $args['id'] ) ?>"><?php echo $title ?></label>
        </div>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_textarea_field( $name, $args ) ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public function render_textarea_field( $name, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'cols' => '',
            'rows' => 5,
            'id'   => uniqid( "{$name}." ),
        ] );

        $input_name = $this->get_input_name( $name );
        ?>
        <textarea name="<?php echo esc_attr( $input_name ) ?>"
                  id="<?php echo esc_attr( $args['id'] ) ?>"
                  cols="<?php echo esc_attr( $args['cols'] ) ?>"
                  rows="<?php echo esc_attr( $args['rows'] ) ?>"><?php echo esc_textarea( $this->get_value( $name ) ) ?></textarea>
        <?php
    }

    /**
     * @param string $name input name
     * @param string $label
     * @param array  $args
     *
     * @return void
     */
    public function render_color_picker( $name, $label, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );
        ?>
        <div class="wpshop-settings-form-row__label">
            <label for="<?php echo $args['id'] ?>"><?php echo $label ?></label>
        </div>
        <div class="wpshop-settings-form-row__body">
            <?php $this->render_color_picker_field( $name, $args ); ?>
        </div>
        <?php
    }

    /**
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public function render_color_picker_field( $name, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'id' => uniqid( "{$name}." ),
        ] );

        $input_name = $this->get_input_name( $name );
        ?>
        <input type="text"
               id="<?php echo esc_attr( $args['id'] ) ?>"
               name="<?php echo $input_name ?>"
               value="<?php echo $this->get_value( $name ) ?>"
               data-default-color="<?php echo esc_attr( $args['default'] ?? '' ) ?>"
               class="js-wpshop-settings-color-picker">
        <?php
    }

    /**
     * @param callable $cb
     *
     * @return void
     */
    public function wrap_form( $cb ) {
        $has_cap = current_user_can( 'manage_options' );

        if ( $has_cap && $this->verify() ) {
            ?>
            <form action="options.php" method="post">
            <?php
        }

        $cb( $this );

        if ( $has_cap && $this->verify() ) {
            ?>
            <div class="wpshop-settings-container__save js-wpshop-settings-container-save">
                <?php settings_fields( $this->option_group ); ?>
                <button type="submit" class="wpshop-settings-button"><?php echo __( 'Save', static::TEXT_DOMAIN ) ?></button>
            </div>
            </form>
            <?php
        }
    }

    /**
     * @return string[]
     */
    public function get_tab_icons() {
        return [
            'dashboard'          => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M273.27 7.08A24.14 24.14 0 0 0 256.09 0c-6.17-.02-12.35 2.32-17.06 7.03l-232 232c-9.37 9.37-9.37 24.57 0 33.94C11.72 277.66 17.86 280 24 280s12.28-2.34 16.97-7.03L64 249.94V464c0 8.84 7.16 16 16 16h352c8.84 0 16-7.16 16-16V250.19l22.73 22.73c4.72 4.72 10.91 7.08 17.09 7.08s12.37-2.36 17.09-7.08c9.44-9.44 9.44-24.75 0-34.19M399.99 133.81l-32-32M224 432V304h64v128h-64Zm176 0h-64V272c0-8.84-7.16-16-16-16H192c-8.84 0-16 7.16-16 16v160h-64V201.94L255.88 58.06 400 202.18v229.81Z" fill="currentColor"></path></svg>',
            'dashboard-activate' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M273.27 7.08A24.14 24.14 0 0 0 256.09 0c-6.17-.02-12.35 2.32-17.06 7.03l-232 232c-9.37 9.37-9.37 24.57 0 33.94C11.72 277.66 17.86 280 24 280s12.28-2.34 16.97-7.03L64 249.94V464c0 8.84 7.16 16 16 16h352c8.84 0 16-7.16 16-16V250.19l22.73 22.73c4.72 4.72 10.91 7.08 17.09 7.08s12.37-2.36 17.09-7.08c9.44-9.44 9.44-24.75 0-34.19M399.99 133.81l-32-32M224 432V304h64v128h-64Zm176 0h-64V272c0-8.84-7.16-16-16-16H192c-8.84 0-16 7.16-16 16v160h-64V201.94L255.88 58.06 400 202.18v229.81Z" fill="currentColor"></path></svg>',
        ];
    }

    /**
     * @param string $slug
     * @param string $name
     * @param array  $args
     *
     * @return false|void
     * @see \get_template_part()
     */
    public static function get_template_part( $slug, $name = null, $args = [] ) {
        do_action( "get_template_part_{$slug}", $slug, $name, $args );

        $templates = [];
        $name      = (string) $name;
        if ( '' !== $name ) {
            $templates[] = "{$slug}-{$name}.php";
        }

        $templates[] = "{$slug}.php";

        do_action( 'get_template_part', $slug, $name, $templates, $args );

        if ( ! static::locate_template( $templates, true, false, $args ) ) {
            return false;
        }
    }

    /**
     * @param string|array $template_names
     * @param bool         $load
     * @param bool         $require_once
     * @param array        $args
     *
     * @return string|null
     * @see \locate_template()
     */
    protected static function locate_template( $template_names, $load = false, $require_once = true, $args = [] ) {
        $located = null;
        foreach ( (array) $template_names as $template_name ) {
            if ( ! $template_name ) {
                continue;
            }

            if ( file_exists( static::get_template_parts_root() . $template_name ) ) {
                $located = static::get_template_parts_root() . $template_name;
                break;
            }
        }

        if ( ! file_exists( $located ) ) {
            trigger_error( 'Unable to locate template file ' . $located );

            return null;
        }


        if ( $load && '' !== $located ) {
            load_template( $located, $require_once, $args );
        }

        return $located;
    }

    /**
     * @return string
     */
    protected static function get_template_parts_root() {
        throw new \RuntimeException( "Unimplemented" );
    }
}
