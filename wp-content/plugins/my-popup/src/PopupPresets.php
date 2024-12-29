<?php

namespace Wpshop\PluginMyPopup;

use Wpshop\PluginMyPopup\Rule\RuleAutocomplete;

class PopupPresets {

    const OPTION_KEY = '_my_popup_presets';

    const POPUP_RESOURCES_PLACEHOLDER = '%%my_popup_resources%%';

    /**
     * @var string
     */
    protected $plugin_dir_path;

    /**
     * @var string
     */
    protected $plugin_dir_url;

    /**
     * @var string
     */
    protected $cdn_url;

    /**
     * @return void
     */
    public function init( $plugin_file ) {
        $this->plugin_dir_path = untrailingslashit( plugin_dir_path( $plugin_file ) );
        $this->plugin_dir_url  = untrailingslashit( plugin_dir_url( $plugin_file ) );
        $this->cdn_url         = 'https://my-popup.ru/presets';

        $this->setup_ajax();
    }

    /**
     * @return void
     */
    public function setup_ajax() {
        if ( is_admin() ) {
            add_action( 'my_popup:admin_enqueue_scripts', [ $this, '_localize_script' ] );
            add_action( 'wp_ajax_save_my_popup_preset', [ $this, 'save_preset_ajax' ] );
            add_action( 'wp_ajax_my_popup_preset_select', [ $this, 'select_preset_ajax' ] );
            add_action( 'wp_ajax_my_popup_preset_remove', [ $this, 'remove_preset_ajax' ] );
            add_action( 'wp_ajax_my_popup_prepare_import', [ $this, 'prepare_import_data' ] );
        }
    }

    /**
     * @return void
     */
    public function save_preset_ajax() {
        if ( wp_verify_nonce( $_REQUEST['nonce'], 'my_popup_presets' ) ) {
            if ( $this->save_preset() ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( [ 'message' => 'Unable to save preset' ] );
            }
        }
        wp_send_json_error( [ 'message' => 'Forbidden' ] );
    }

    /**
     * @return void
     */
    public function select_preset_ajax() {
        if ( wp_verify_nonce( $_REQUEST['nonce'], 'my_popup_presets' ) ) {
            if ( empty( $_REQUEST['id'] ) ) {
                wp_send_json_error( [ 'message' => 'Unable to find preset without id' ] );
            }

            $presets = $this->get_presets();

            if ( array_key_exists( $_REQUEST['id'], $presets ) ) {
                $preset          = $presets[ $_REQUEST['id'] ];
                $preset['items'] = array_map( function ( $item ) {
                    $item = $this->filter_item( $item );
                    $item = $this->prepare_rules( $item );

                    return $item;
                }, $preset['items'] );

                wp_send_json_success( [ 'result' => $preset ] );
            }

            wp_send_json_error( [ 'message' => 'Unable to find preset with id "' . $_REQUEST['id'] . '"' ] );
        }

        wp_send_json_error( [ 'message' => 'Forbidden' ] );
    }

    /**
     * @return void
     */
    public function prepare_import_data() {
        if ( wp_verify_nonce( $_REQUEST['nonce'], 'my_popup_presets' ) ) {
            $items = map_deep( $_REQUEST['items'], 'wp_unslash' );
            $items = array_map( function ( $item ) {
                return $this->prepare_rules( $item );
            }, $items );
            wp_send_json_success( [ 'items' => $items ] );
        }

        wp_send_json_error( [ 'message' => 'Forbidden' ] );
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function prepare_rules( $item ) {
        if ( isset( $item['id'] ) && $item['id'] === 'rules_pages' ) {
            if ( $item['value'] ) {
                $rules                    = json_decode( $item['value'], true );
                $rules                    = array_reverse( $rules );
                $item['value']            = json_encode( $rules );
                $item['preRenderedPills'] = $this->prepare_pills( $rules );
            }
        }

        return $item;
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    protected function prepare_pills( $rules ) {
        $rule_values = container()->get( RuleAutocomplete::class )->gather_rules_values( $rules );
        $result      = [];
        foreach ( $rules as $rule ) {
            ob_start();
            Utilities::render_rule_pills( $rule, $rule_values );
            $result[] = ob_get_clean();;
        }

        return $result;
    }

    /**
     * @return void
     */
    public function remove_preset_ajax() {
        if ( wp_verify_nonce( $_REQUEST['nonce'], 'my_popup_presets' ) ) {
            if ( empty( $_REQUEST['id'] ) ) {
                wp_send_json_error( [ 'message' => 'Unable to find preset without id' ] );
            }

            $presets = $this->get_presets();

            if ( array_key_exists( $_REQUEST['id'], $presets ) ) {
                unset( $presets[ $_REQUEST['id'] ] );
                if ( $this->save_in_file( $presets ) ) {
                    wp_send_json_success();
                } else {
                    wp_send_json_error( [ 'message' => 'Unable to save presets option' ] );
                }
            }

            wp_send_json_error( [ 'message' => 'Unable to find preset with id "' . $_REQUEST['id'] . '"' ] );
        }

        wp_send_json_error( [ 'message' => 'Forbidden' ] );
    }

    /**
     * @return bool
     */
    protected function save_preset() {
        if ( empty( $_REQUEST['name'] ) ||
             empty( $_REQUEST['image'] ) ||
             empty( $_REQUEST['items'] )
        ) {
            return false;
        }
        $items = $_REQUEST['items'];
        usort( $items, function ( $a, $b ) {
            return strcmp( $a['name'], $b['name'] );
        } );

        $items = $this->replace_image_urls( $items );

        $hash = md5( $p = array_reduce( $items, function ( $carry, $item ) {
            $carry .= $item['name'] . ':' . $item['value'] . PHP_EOL;

            return $carry;
        }, '' ) );

        $presets = $this->get_presets();

        if ( ! array_key_exists( $hash, $items ) ) {
            $presets[ $hash ] = [
                'name'  => $_REQUEST['name'],
                'image' => $_REQUEST['image'],
                'items' => $items,
            ];
        }

        return (bool) $this->save_in_file( $presets );
    }

    /**
     * @param array $item
     *
     * @return array
     *
     */
    protected function filter_item( $item ) {

        $item = apply_filters( 'my_popup:presets_filter_item', $item );

        if ( in_array( $item['name'], [
            'display_box[my_popup_background_image][image]',
            'display_box[my_popup_icon][image]',
        ] ) ) {
            $item['value'] = str_replace(
                self::POPUP_RESOURCES_PLACEHOLDER,
                $this->cdn_url,
                $item['value']
            );
        }

        $item['value'] = stripslashes( $item['value'] );

        return $item;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    protected function replace_image_urls( array $items ) {
        return array_map( function ( $item ) {
            if ( in_array( $item['name'], [
                'display_box[my_popup_background_image][image]',
                'display_box[my_popup_icon][image]',
            ] ) ) {
                $search = implode( '/', [
                    $this->plugin_dir_url,
                    'assets',
                    'public',
                    'images',
                ] );

                $replace = self::POPUP_RESOURCES_PLACEHOLDER;

                $item['value'] = str_replace( $search, $replace, $item['value'] );
            }

            return $item;
        }, $items );
    }

    /**
     * @param array $presets
     *
     * @return bool|int
     */
    protected function save_in_file( $presets ) {
        $file = implode( DIRECTORY_SEPARATOR, [
            $this->plugin_dir_path,
            'data',
            'presets.json',
        ] );
        wp_mkdir_p( dirname( $file ) );

        return file_put_contents( $file, json_encode( $presets, JSON_PRETTY_PRINT ) );
    }

    /**
     * @return array
     */
    public function get_from_file() {
        $file = implode( DIRECTORY_SEPARATOR, [
            $this->plugin_dir_path,
            'data',
            'presets.json',
        ] );
        if ( file_exists( $file ) ) {
            return json_decode( file_get_contents( $file ), true );
        }

        return [];
    }

    /**
     * @param string $image_file
     *
     * @return string
     */
    public function image_url( $image_file ) {
        return 'https://my-popup.ru/presets/preview/' . $image_file;
    }

    /**
     * @return array
     */
    public function get_presets() {
        return $this->get_from_file();
    }

    /**
     * @return void
     */
    public function _localize_script() {
        wp_localize_script( 'my-popup-scripts', 'my_popup_globals', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'my_popup_presets' ),
        ] );
    }
}
