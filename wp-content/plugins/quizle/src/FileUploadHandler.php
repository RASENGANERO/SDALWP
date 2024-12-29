<?php

namespace Wpshop\Quizle;

use WP_Error;

class FileUploadHandler {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array{'name':string,'type':string,'tmp_name':string}|null
     */
    protected $current_file;

    /**
     * @param string $name
     * @param string $action
     */
    public function __construct( $name, $action ) {
        $this->name   = $name;
        $this->action = $action;
    }

    /**
     * @param int   $post_id    The post ID of a post to attach the media item to. Required, but can
     *                          be set to 0, creating a media item that has no relationship to a post.
     * @param array $post_data  Optional. Overwrite some of the attachment.
     * @param array $overrides  Optional. Override the wp_handle_upload() behavior.
     *
     * @return int[]|WP_Error[]|null
     *
     * @see media_handle_upload()
     */
    public function handle( $post_id, $post_data = [], $overrides = [ 'test_form' => false ] ) {
        if ( empty( $_FILES[ $this->name ] ) ) {
            return null;
        }

        $overrides['action'] = $this->action;

        $result = [
            'errors' => [],
            'files'  => [],
        ];

        $count = count( $_FILES[ $this->name ]['name'] );

        $quizle_attached_files = get_option( '_quizle_attached_files_' . $post_id, [] );

        for ( $i = 0 ; $i < $count ; $i ++ ) {
            $this->current_file = [];
            foreach ( $_FILES[ $this->name ] as $key => $values ) {
                $this->current_file[ $key ] = $values[ $i ];
            }

            $upload_result = $this->handle_upload( $post_id, $post_data, $overrides );

            if ( is_wp_error( $upload_result ) ) {
                $errors = [];
                foreach ( $upload_result->errors as $code => $messages ) {
                    foreach ( $messages as $message ) {
                        $errors[] = compact( 'code', 'message' );
                    }
                }

                $result['errors'][] = $errors;
            } else {
                $filename = esc_html( wp_basename( $upload_result['file'] ) );
                $url      = $upload_result['url'];
                $size     = filesize( $upload_result['file'] );
                $type     = $upload_result['type'];
//                $file     = get_attached_file( $upload_result, true );
//                $size     = filesize( $file );
//                $url      = wp_get_attachment_url( $upload_result );
//                $filename = esc_html( wp_basename( $file ) );

                $result['files'][] = compact(
                    'url',
                    'filename',
                    'size',
                    'type'
                );

                $quizle_attached_files[] = [
                    'url'  => $url,
                    'file' => $upload_result['file'],
                ];
            }
        }

        update_option( '_quizle_attached_files_' . $post_id, $quizle_attached_files, false );

        return $result;
    }

    /**
     * @param $uploads
     *
     * @return array
     * @see _wp_upload_dir();
     */
    public function _upload_dir( $uploads ) {
        $hash = md5_file( $this->current_file['tmp_name'] );

        $subdir            = $uploads['subdir'];
        $new_subdir        = $subdir . '/' . substr( $hash, 0, 2 ) . '/' . substr( $hash, 2, 2 );
        $uploads['subdir'] = "/quizle{$new_subdir}";
        $uploads['path']   = substr( $uploads['path'], 0, - 1 * strlen( $subdir ) ) . "/quizle{$new_subdir}";
        $uploads['url']    = substr( $uploads['url'], 0, - 1 * strlen( $subdir ) ) . "/quizle{$new_subdir}";

        return $uploads;
    }

    /**
     * @param int   $post_id    The post ID of a post to attach the media item to. Required, but can
     *                          be set to 0, creating a media item that has no relationship to a post.
     * @param array $post_data  Optional. Overwrite some of the attachment.
     * @param array $overrides  Optional. Override the wp_handle_upload() behavior.
     *
     * @return array|WP_Error
     *
     * @see media_handle_upload()
     */
    protected function handle_upload( $post_id, $post_data = [], $overrides = [ 'test_form' => false ] ) {
        $time = current_time( 'mysql' );
        $post = get_post( $post_id );

//        if ( $post ) {
//            // The post date doesn't usually matter for pages, so don't backdate this upload.
//            if ( 'page' !== $post->post_type && substr( $post->post_date, 0, 4 ) > 0 ) {
//                $time = $post->post_date;
//            }
//        }

        add_filter( 'upload_dir', [ $this, '_upload_dir' ] );
        $file = wp_handle_upload( $this->current_file, $overrides, $time );
        remove_filter( 'upload_dir', [ $this, '_upload_dir' ] );

        if ( isset( $file['error'] ) ) {
            return new WP_Error( 'upload_error', $file['error'] );
        }

        return $file;

        $name = $this->current_file['name'];
        $ext  = pathinfo( $name, PATHINFO_EXTENSION );
        $name = wp_basename( $name, ".$ext" );

        $url     = $file['url'];
        $type    = $file['type'];
        $file    = $file['file'];
        $title   = sanitize_text_field( $name );
        $content = '';
        $excerpt = '';

        if ( preg_match( '#^audio#', $type ) ) {
            $meta = wp_read_audio_metadata( $file );

            if ( ! empty( $meta['title'] ) ) {
                $title = $meta['title'];
            }

            if ( ! empty( $title ) ) {

                if ( ! empty( $meta['album'] ) && ! empty( $meta['artist'] ) ) {
                    /* translators: 1: Audio track title, 2: Album title, 3: Artist name. */
                    $content .= sprintf( __( '"%1$s" from %2$s by %3$s.' ), $title, $meta['album'], $meta['artist'] );
                } elseif ( ! empty( $meta['album'] ) ) {
                    /* translators: 1: Audio track title, 2: Album title. */
                    $content .= sprintf( __( '"%1$s" from %2$s.' ), $title, $meta['album'] );
                } elseif ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: Audio track title, 2: Artist name. */
                    $content .= sprintf( __( '"%1$s" by %2$s.' ), $title, $meta['artist'] );
                } else {
                    /* translators: %s: Audio track title. */
                    $content .= sprintf( __( '"%s".' ), $title );
                }
            } elseif ( ! empty( $meta['album'] ) ) {

                if ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: Audio album title, 2: Artist name. */
                    $content .= sprintf( __( '%1$s by %2$s.' ), $meta['album'], $meta['artist'] );
                } else {
                    $content .= $meta['album'] . '.';
                }
            } elseif ( ! empty( $meta['artist'] ) ) {

                $content .= $meta['artist'] . '.';

            }

            if ( ! empty( $meta['year'] ) ) {
                /* translators: Audio file track information. %d: Year of audio track release. */
                $content .= ' ' . sprintf( __( 'Released: %d.' ), $meta['year'] );
            }

            if ( ! empty( $meta['track_number'] ) ) {
                $track_number = explode( '/', $meta['track_number'] );

                if ( is_numeric( $track_number[0] ) ) {
                    if ( isset( $track_number[1] ) && is_numeric( $track_number[1] ) ) {
                        $content .= ' ' . sprintf(
                            /* translators: Audio file track information. 1: Audio track number, 2: Total audio tracks. */
                                __( 'Track %1$s of %2$s.' ),
                                number_format_i18n( $track_number[0] ),
                                number_format_i18n( $track_number[1] )
                            );
                    } else {
                        $content .= ' ' . sprintf(
                            /* translators: Audio file track information. %s: Audio track number. */
                                __( 'Track %s.' ),
                                number_format_i18n( $track_number[0] )
                            );
                    }
                }
            }

            if ( ! empty( $meta['genre'] ) ) {
                /* translators: Audio file genre information. %s: Audio genre name. */
                $content .= ' ' . sprintf( __( 'Genre: %s.' ), $meta['genre'] );
            }

            // Use image exif/iptc data for title and caption defaults if possible.
        } elseif ( str_starts_with( $type, 'image/' ) ) {
            $image_meta = wp_read_image_metadata( $file );

            if ( $image_meta ) {
                if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
                    $title = $image_meta['title'];
                }

                if ( trim( $image_meta['caption'] ) ) {
                    $excerpt = $image_meta['caption'];
                }
            }
        }

        // Construct the attachment array.
        $attachment = array_merge(
            [
                'post_mime_type' => $type,
                'guid'           => $url,
                'post_parent'    => $post_id,
                'post_title'     => $title,
                'post_content'   => $content,
                'post_excerpt'   => $excerpt,
            ],
            $post_data
        );

        // This should never be set as it would then overwrite an existing attachment.
        unset( $attachment['ID'] );

        // Save the data.
        $attachment_id = wp_insert_attachment( $attachment, $file, $post_id, true );

        if ( ! is_wp_error( $attachment_id ) ) {
            /*
             * Set a custom header with the attachment_id.
             * Used by the browser/client to resume creating image sub-sizes after a PHP fatal error.
             */
            //if ( ! headers_sent() ) {
            //    header( 'X-WP-Upload-Attachment-ID: ' . $attachment_id );
            //}

            /*
             * The image sub-sizes are created during wp_generate_attachment_metadata().
             * This is generally slow and may cause timeouts or out of memory errors.
             */

            add_filter( 'intermediate_image_sizes_advanced', $image_sizes = function ( $new_sizes, $image_meta, $attachment_id ) {
                //leave only the previews for the admin area
                return array_filter( $new_sizes, function ( $key ) {
                    return $key === 'thumbnail';
                }, ARRAY_FILTER_USE_KEY );
            }, 10, 3 );

            $metadata = wp_generate_attachment_metadata( $attachment_id, $file );

            remove_filter( 'intermediate_image_sizes_advanced', $image_sizes );

            wp_update_attachment_metadata( $attachment_id, $metadata );
        }

        return $attachment_id;
    }
}
