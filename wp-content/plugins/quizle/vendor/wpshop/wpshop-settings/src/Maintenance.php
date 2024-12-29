<?php

namespace Wpshop\Settings;

use Puc_v4_Factory;
use WP_Error;

class Maintenance implements MaintenanceInterface {

    /**
     * @var string
     */
    protected $verify_url;

    /**
     * @var array
     */
    protected $update_cnf;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $textdomain;

    /**
     * @var string
     */
    protected $file_path;

    /**
     * @param array{'verify_url':string, 'update':array} $config
     * @param string                                     $type
     * @param string                                     $slug
     * @param string                                     $file_path plugin file or theme name
     * @param string                                     $textdomain
     */
    public function __construct( $config, $type, $slug, $file_path, $textdomain ) {
        $this->verify_url = $config['verify_url'] ?? '';
        $this->update_cnf = $config['update'] ?? '';

        $this->type       = in_array( $type, [ 'plugin', 'theme' ] ) ? $type : 'unknown';
        $this->slug       = $slug;
        $this->textdomain = $textdomain;
        $this->file_path  = $file_path;
    }

    /**
     * @return void
     */
    public function init_updates( $license, $license_token = null ) {
        Puc_v4_Factory::buildUpdateChecker(
            $this->update_cnf['url'],
            $this->file_path,
            $this->update_cnf['slug'],
            $this->update_cnf['check_period'],
            $this->update_cnf['opt_name']
        )->addQueryArgFilter( function ( $query_args ) use ( $license, $license_token ) {
            if ( $license ) {
                $query_args['license_key'] = $license;
            }
            if ( $license_token ) {
                $query_args['token'] = $license_token;
            }

            return $query_args;
        } );
    }

    /**
     * @param string   $license
     * @param callable $cb
     *
     * @return bool|WP_Error
     */
    public function activate( $license, $cb ) {
        if ( ! $this->verify_url ) {
            $cb( [
                'license_verify' => '',
                'license_error'  => __( 'Unable to check license without activation url', $this->textdomain ),
            ] );

            return new WP_Error( 'activation_failed', __( 'Unable to check license without activation url', $this->textdomain ) );
        }

        $args = [
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => [
                'action'    => 'activate_license',
                'license'   => $license,
                'item_name' => urlencode( $this->slug ),
                'version'   => $this->get_version_from_metadata(),
                'type'      => $this->type,
                'url'       => home_url(),
                'ip'        => $this->get_ip(),
            ],
        ];

        $response = wp_remote_post( $this->verify_url, $args );
        if ( is_wp_error( $response ) ) {
            $response = wp_remote_post( str_replace( "https", "http", $this->verify_url ), $args );
        }

        if ( is_wp_error( $response ) ) {
            $cb( [
                'license_verify' => '',
                'license_error'  => __( 'Can\'t get response from license server', $this->textdomain ),
            ] );

            return new WP_Error( 'activation_failed', __( 'Can\'t get response from license server', $this->textdomain ) );
        }

        $body = wp_remote_retrieve_body( $response );

        if ( mb_substr( $body, 0, 2 ) == 'ok' ) {
            $cb( [
                'license'        => $license,
                'license_verify' => time() + ( WEEK_IN_SECONDS * 4 ),
                'license_error'  => '',
            ] );

            return true;
        }

        $cb( [
            'license'       => '',
            'license_error' => $body,
        ] );

        return new WP_Error( 'activation_failed', __( 'Unable to check license without activation url', $this->textdomain ) );
    }

    /**
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @return string|null
     */
    protected function get_version_from_metadata() {
        switch ( $this->type ) {
            case 'plugin':
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

                return get_plugin_data( $this->file_path, false, false )['Version'] ?? '';
            case 'theme':
                return wp_get_theme( basename( $this->file_path ) )->get( 'Version' );
            default:
                break;
        }

        return null;
    }

    /**
     * @return string
     */
    protected function get_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}
