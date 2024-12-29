<?php

namespace Wpshop\PluginMyPopup;

use DateTime;
use Exception;

class Logger {

    const FILE = 'my-popup.log';

    const DISABLED = 0;

    const LVL_ERROR   = 1;
    const LVL_WARNING = 2;
    const LVL_INFO    = 4;

    public function __construct( $level ) {
        $this->level = $level;
    }

    /**
     * bit mask of LEVEL_* constants
     * @var int
     */
    protected $level = self::LVL_ERROR | self::LVL_WARNING;

    /**
     * @param int $level
     *
     * @return string
     */
    protected function levelStr( $level ) {
        return [
                   self::LVL_ERROR   => 'error',
                   self::LVL_WARNING => 'warning',
                   self::LVL_INFO    => 'info',
               ][ $level ];
    }

    /**
     * @param string                 $level
     * @param string|array|Exception $message
     *
     * @return void
     */
    public function log( $level, $message ) {
        if ( $this->level & $level ) {
            try {
                $date = ( new DateTime( 'now' ) )->format( 'Y-m-d H:i:s' );
            } catch
            ( Exception $e ) {
                $date = date( 'Y-m-d H:i:s' );
                $this->write( self::LVL_ERROR, $e->getMessage(), $date );
            }
            $this->write( $level, $this->stringMessage( $message ), $date );
        }
    }

    /**
     * @param int    $level
     * @param string $message
     * @param string $date
     *
     * @return void
     */
    protected function write( $level, $message, $date ) {
        $file = WP_CONTENT_DIR . '/' . self::FILE;
        if ( ! file_exists( $file ) ) {
            touch( $file );
            chmod( $file, 0644 );
        }
        error_log( sprintf( "[%s] [%s] Message: %s\n", $date, $this->levelStr( $level ), $message ), 3, $file );
    }

    /**
     * @param string|array $message
     *
     * @return void
     */
    public function info( $message ) {
        $this->log( self::LVL_INFO, $message );
    }

    /**
     * @param string|array $message
     *
     * @return void
     */
    public function warning( $message ) {
        $this->log( self::LVL_WARNING, $message );
    }

    /**
     * @param string|array|Exception $message
     *
     * @return void
     */
    public function error( $message ) {
        $this->log( self::LVL_ERROR, $message );
    }

    /**
     * @param string|array|Exception $message
     *
     * @return string
     */
    protected function stringMessage( $message ) {
        if ( $message instanceof Exception ) {
            $message = $this->level & self::LVL_ERROR ? $message->__toString() : $message->getMessage();
        }

        if ( is_array( $message ) ) {
            $message = print_r( $message, true );
        }

        return (string) $message;
    }
}
