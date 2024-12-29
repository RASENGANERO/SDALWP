<?php

namespace Wpshop\Quizle\Admin;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use ZipArchive;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\generate_quizle_name;
use function Wpshop\Quizle\generate_string_lower;
use function Wpshop\Quizle\get_quizle;

class ImportExport {

    const ATTACHMENT_HASH_META_KEY = '_quizle_attachment_hash';

    /**
     * @return void
     */
    public function init() {
        $post_type = Quizle::POST_TYPE;
        add_filter( "bulk_actions-edit-{$post_type}", [ $this, '_add_bulk_actions' ] );
        add_action( "handle_bulk_actions-edit-{$post_type}", [ $this, '_handle_bulk_actions' ], 10, 3 );

        add_action( 'post_submitbox_misc_actions', [ $this, '_add_single_quizle_export_button' ], 100 );

//        add_action( 'manage_posts_extra_tablenav', [ $this, '_add_import_input' ] );
        if ( wp_doing_ajax() ) {
            $action = 'quizle_import';
            add_action( "wp_ajax_{$action}", [ $this, '_import' ] );
        }

        add_filter( 'intermediate_image_sizes_advanced', [ $this, '_filter_image_sizes' ], 10, 3 );

        add_action( 'init', [ $this, 'handle_create_from_text_file' ], 100 );
    }

    /**
     * @return void
     */
    public function _add_single_quizle_export_button() {
        if ( ! get_current_screen() ||
             'quizle' !== get_current_screen()->id
        ) {
            return;
        }

        $url = add_query_arg( [
            'post_type' => Quizle::POST_TYPE,
        ], admin_url( 'edit.php' ) );
        ?>
        <div class="misc-pub-section">
            <button class="button js-quizle-export"
                    data-form_action="<?php echo $url ?>"
                    data-action="export-quizle"
                    data-post_id="<?php the_ID(); ?>"
                    data-nonce="<?php echo wp_create_nonce( 'bulk-posts' ) ?>"
                    style="width: 100%"><?php echo __( 'Export Quizle', QUIZLE_TEXTDOMAIN ) ?></button>
        </div>
        <?php
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function handle_create_from_text_file() {
        if ( ! is_admin() ) {
            return;
        }
        if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
            return;
        }

        $request = wp_parse_args( $_REQUEST, [
            'page'      => '',
            'post_type' => '',
        ] );


        if ( $request['page'] !== 'quizle-templates' ||
             $request['post_type'] !== Quizle::POST_TYPE
        ) {
            return;
        }

        $questions = [];
        $row_state = 'question';

        if ( isset( $_FILES['file'] ) && $_FILES['file']['error'] == 0 ) {
            $uploaded_file = $_FILES['file']['tmp_name'];

            if ( $handle = fopen( $uploaded_file, 'r' ) ) {


                while ( ( $line = fgets( $handle ) ) !== false ) {
                    $line = trim( $line );

                    if ( ! $line ) {
                        $row_state = 'empty_line';
                    }

                    switch ( $row_state ) {
                        case 'question':
                            $questions[] = [
                                'answers'                  => [],
                                'conditions'               => [],
                                'title'                    => $line,
                                'type'                     => 'check',
                                'columns'                  => '1',
                                'description'              => '',
                                'right_answer_description' => '',
                                'media'                    => '',
                                'required'                 => 0,
                                'is_multiple'              => 0,
                                'question_id'              => generate_string_lower( 8 ),
                            ];

                            $row_state = 'answers';
                            break;
                        case 'answers':
                            $answers = explode( '|', $line );
                            foreach ( $answers as $answer ) {
                                $answer = trim( $answer );

                                $questions[ count( $questions ) - 1 ]['answers'][] = [
                                    'name'      => $answer,
                                    'value'     => '',
                                    'answer_id' => generate_string_lower( 8 ),
                                    'type'      => 'general',
                                ];
                            }

                            $row_state = 'question';
                            break;
                        case 'empty_line':
                        default:
                            $row_state = 'question';
                            continue 2;
                    }

                }
                fclose( $handle );
            }
        }

        if ( $questions ) {
            $post_id = wp_insert_post( [
                'post_type'   => Quizle::POST_TYPE,
                'post_title'  => '',
                'post_name'   => generate_quizle_name(),
                'post_status' => 'draft',
            ] );

            if ( is_wp_error( $post_id ) ) {
                // todo handle error
            } else {
                update_post_meta( $post_id, 'quizle-questions', json_encode( $questions, JSON_UNESCAPED_UNICODE ) );
                wp_redirect( get_edit_post_link( $post_id, '' ) );
                die;
            }
        }
    }

    /**
     * @param array $action
     *
     * @return array
     */
    public function _add_bulk_actions( $action ) {
        $action['export-quizle'] = __( 'Export' );

        return $action;
    }

    /**
     * @param string $redirect_url
     * @param string $action
     * @param array  $post_ids
     *
     * @return mixed
     */
    public function _handle_bulk_actions( $redirect_url, $action, $post_ids ) {
        if ( 'export-quizle' === $action ) {
            $this->export( $post_ids );
        }

        return $redirect_url;
    }

    /**
     * @param string $which
     *
     * @return void
     */
    public function _add_import_input( $which ) {
        if ( 'top' !== $which ) {
            return;
        }
        if ( ! get_current_screen() || 'edit-quizle' !== get_current_screen()->id ) {
            return;
        }
        ?>
        <fieldset>
            <input type="file" name="import_file">
            <button class="button js-quizle-import"><?php echo __( 'Import' ) ?></button>
        </fieldset>
        <?php
    }

    /**
     * @param array $new_sizes
     * @param array $image_meta
     * @param int   $attachment_id
     *
     * @return array
     */
    public function _filter_image_sizes( $new_sizes, $image_meta, $attachment_id ) {
        if ( get_post_meta( $attachment_id, self::ATTACHMENT_HASH_META_KEY, true ) ) {
            return array_filter( $new_sizes, function ( $key ) {
                return $key === 'thumbnail';
            }, ARRAY_FILTER_USE_KEY );
        }

        return $new_sizes;
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _import() {
        if ( empty( $_FILES['file']['tmp_name'] ) ) {
            wp_send_json_error( new WP_Error( 'quizle_import', __( 'Unable to import empty data', QUIZLE_TEXTDOMAIN ) ) );
        }

        $file = $_FILES['file']['tmp_name'];

        if ( 'application/zip' == mime_content_type( $file ) ) {
            $tmp_dir = get_temp_dir();

            $zip = new ZipArchive();
            $zip->open( $file );
            if ( ! $zip->extractTo( $tmp_dir ) ) {
                wp_send_json_error( new WP_Error( 'quizle_import', __( 'Unable to extract zipped import file', QUIZLE_TEXTDOMAIN ) ) );
            }

            $file = $tmp_dir . 'quizle-data.json';

            if ( ! file_exists( $file ) ) {
                wp_send_json_error( new WP_Error( 'quizle_import', __( 'Import file not found.', QUIZLE_TEXTDOMAIN ) ) );
            }
        }

        $content = file_get_contents( $file );
        if ( false === $content ) {
            wp_send_json_error( new WP_Error( 'quizle_import', __( 'Unable to read import file data', QUIZLE_TEXTDOMAIN ) ) );
        }

        $data = \json_decode( $content, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( new WP_Error( 'quizle_import', __( 'Unable to decode import data: ' . json_last_error_msg() ) ) );
        }

        foreach ( $data['items'] as $item ) {
            $this->import( $item, $_REQUEST['override'] ?? false );
        }

        wp_send_json_success();
    }

    /**
     * <pre>
     * $item = [
     *      'title' => '',
     *      'name' => '',
     *      'meta' => []
     * ];
     *
     * @param array $item
     *
     * @return int
     */
    public function import( $item, $override = false ) {
        $media_types = [ 'video', 'image' ];

        $quizle = $override && ! empty( $item['name'] ) ? get_quizle( $item['name'], 'any' ) : null;

        if ( $quizle ) {
            $post_id = $quizle->ID;
        } else {
            $post_id = wp_insert_post( [
                'post_type'   => Quizle::POST_TYPE,
                'post_title'  => $item['title'],
                'post_name'   => $item['name'],
                'post_status' => 'publish',
            ] );

            if ( is_wp_error( $post_id ) ) {
                wp_send_json_error( $post_id );
            }
        }

        $get_attachment_url = function ( $url, $type = 'image' ) use ( $item, $post_id ) {
            $content = file_get_contents( $url );
            $result  = $this->save_and_get_attachment_url(
                basename( $url ),
                sprintf( __( 'Image for Quizle: %s', QUIZLE_TEXTDOMAIN ), $item['title'] ),
                $content,
                $type,
                $post_id
            );

            if ( $result instanceof WP_Error ) {
                trigger_error( 'Unable store attachment: ' . $result->get_error_message(), E_USER_WARNING );
            } elseif ( ! $result ) {
                trigger_error( 'Unable store attachment.', E_USER_WARNING );
            } else {
                return $result;
            }

            return null;
        };

        foreach ( $item['meta'] as $key => $value ) {

            if ( 'welcome-img' == $key && $value ) {
                $value = $get_attachment_url( $value );
            }

            if ( 'quizle-questions' == $key && $value ) {
                $questions = \json_decode( $value, true );
                if ( \json_last_error() !== JSON_ERROR_NONE ) {
                    trigger_error( 'Unable to decode quizle item questions on import: ' . json_last_error_msg(), E_USER_WARNING );
                } else {
                    foreach ( $questions as &$question ) {
                        foreach ( $question['answers'] as &$answer ) {
                            if ( ! empty( $answer['image'] ) ) {
                                $answer['image'] = $get_attachment_url( $answer['image'] );
                            }
                        }

                        if ( ! empty( $question['media'] ) && in_array( $question['media']['type'], $media_types ) ) {
                            $question['media']['url'] = $get_attachment_url(
                                $question['media']['url'],
                                $question['media']['type']
                            );
                        }
                    }

                    $value = wp_slash( wp_json_encode( $questions, JSON_UNESCAPED_UNICODE ) );
                }
            }

            if ( 'quizle-results' == $key && $value ) {
                $results = \json_decode( $value, true );
                if ( \json_last_error() !== JSON_ERROR_NONE ) {
                    trigger_error( 'Unable to decode quizle item results on import: ' . json_last_error_msg(), E_USER_WARNING );
                } else {
                    foreach ( $results as &$result ) {
                        if ( ! empty( $result['image'] ) ) {
                            $result['image'] = $get_attachment_url( $result['image'] );
                        }
                    }
                    $value = wp_slash( wp_json_encode( $results, JSON_UNESCAPED_UNICODE ) );
                }
            }

            update_post_meta( $post_id, $key, $value );
        }

        return $post_id;
    }

    /**
     * @param array $post_ids
     *
     * @return void
     */
    public function export( array $post_ids ) {
        $fields = container()->get( MetaBoxes::class )->get_fields();

        $posts = get_posts( [
            'post_type'      => Quizle::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => - 1,
            'include'        => $post_ids,
        ] );

        $result = [
            'version' => '1.1',
            'items'   => [],
            'locale'  => get_locale(),
        ];
        foreach ( $posts as $post ) {
            $item = [
                'name'  => $post->post_name,
                'title' => $post->post_title,
                'meta'  => [],
            ];
            foreach ( $fields as $key => $config ) {
                if ( is_array( $config ) && array_key_exists( 'export', $config ) ) {
                    $export = $config['export'];
                } else {
                    $export = is_numeric( $key ) ? [ $config ] : [ $key ];
                }
                foreach ( $export as $key ) {
                    $item['meta'][ $key ] = get_post_meta( $post->ID, $key, true );
                }
            }
            $result['items'][] = $item;
        }

        $this->write( json_encode( $result, JSON_PRETTY_PRINT ), true );
        die;
    }

    /**
     * @param string $content
     * @param bool   $zip
     *
     * @return void
     * @throws \Exception
     */
    protected function write( $content, $zip = false ) {
        $datetime = new \DateTime( 'now', wp_timezone() );

        if ( $zip && extension_loaded( 'zip' ) ) {
            $arch_name = "quizle-data-{$datetime->format('Ymd_His')}.zip";
            $file_name = 'quizle-data.json';

            $file = tempnam( get_temp_dir(), 'zip' );
            register_shutdown_function( 'unlink', $file );

            $zip = new ZipArchive();
            $zip->open( $file, ZipArchive::OVERWRITE );
            $zip->addFromString( $file_name, $content );
            $zip->close();

            header( 'Content-Type: application/zip' );
            header( 'Content-Length: ' . filesize( $file ) );
            header( 'Content-Disposition: attachment; filename="' . $arch_name . '"' );

            readfile( $file );
        } else {
            $file_name = "quizle-data-{$datetime->format('Ymd_His')}.json";

            header( "Cache-Control: no-cache, no-store, must-revalidate" );
            header( "Content-Type: application/json" );
            header( "Content-Length: " . mb_strlen( $content, "8bit" ) );

            header( "Content-Disposition: attachment; filename=\"{$file_name}\"" );

            echo $content;
        }
    }

    /**
     * @param string $name
     * @param string $title
     * @param string $file_content
     * @param string $type
     * @param int    $quizle_id
     *
     * @return false|string|WP_Error
     */
    protected function save_and_get_attachment_url( $name, $title, $file_content, $type = 'image', $quizle_id = 0 ) {

        $upload_dir = wp_upload_dir();

        $hash = md5( $file_content );

        // detect same file by hash
        $posts = get_posts( [
            'post_type'   => 'attachment',
            'post_status' => 'any',
            'meta_query'  => [
                [
                    'key'   => self::ATTACHMENT_HASH_META_KEY,
                    'value' => $hash,
                ],
            ],
        ] );
        if ( $posts ) {
            $attachment = current( $posts );

            return wp_get_attachment_url( $attachment->ID );
        }

        // try to create unique file name
        $file = "{$upload_dir['path']}/{$name}";
        if ( file_exists( $file ) ) {
            $name_parts = explode( '.', $name );
            for ( $i = 1 ; $i <= 100000 ; $i ++ ) {
                $name = implode( '.', [ $name_parts[0] . '-' . $i, $name_parts[1] ] );
                $file = "{$upload_dir['path']}/{$name}";
                if ( ! file_exists( $file ) ) {
                    break;
                }
            }
        }

        if ( false === file_put_contents( $file, $file_content ) ) {
            return new WP_Error( 'save_attachment', __( 'Unable to save attachment file', 'quizle' ) );
        }

        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $filetype   = wp_check_filetype( basename( $file ) );

        if ( 'image' === $type && ! file_is_displayable_image( $file ) ) {
            unlink( $file );

            return new WP_Error( 'save_attachment', __( 'Unable to save invalid image file: ' . $file, 'quizle' ) );
        }

        $attachment = [
            'guid'           => $upload_dir['url'] . '/' . basename( $file ),
            'post_mime_type' => $filetype['type'],
            'post_title'     => $title,
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_parent'    => $quizle_id,
        ];

        $attachment_id = wp_insert_attachment( $attachment, $file );
        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        update_post_meta( $attachment_id, self::ATTACHMENT_HASH_META_KEY, $hash );

        $attach_data = wp_generate_attachment_metadata( $attachment_id, $file );

        wp_update_attachment_metadata( $attachment_id, $attach_data );

        return wp_get_attachment_url( $attachment_id );
    }
}
