<?php

namespace Wpshop\Quizle;

use Wpshop\Quizle\Admin\MetaBoxes;
use Wpshop\Quizle\Admin\Settings;

class Quizle {

    const POST_TYPE = 'quizle';

    const TYPE_CONTACTS = 'contacts';
    const TYPE_VARIABLE = 'variable';
    const TYPE_TEST     = 'test';

    const VIEW_TYPE_LIST   = 'list';
    const VIEW_TYPE_SLIDES = 'slides';

    /**
     * @var AssetsProvider
     */
    protected $plugin;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * Quizle constructor.
     *
     * @param AssetsProvider $plugin
     * @param Settings       $settings
     */
    public function __construct( AssetsProvider $plugin, Settings $settings ) {
        $this->plugin   = $plugin;
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function init() {
        $this->register_post_type();

        add_action( 'save_post_quizle', function ( $post_ID, $post, $update ) {
            if ( $update ) {
                container()->get( MetaBoxes::class )->set_post( $post )->update_metadata();
            }
        }, 10, 3 );

        add_action( 'edit_form_after_title', function () {
            $screen = get_current_screen();

            if ( ! $screen || $screen->id != 'quizle' ) {
                return;
            }
            container()->get( MetaBoxes::class )->set_post( get_post( get_the_ID() ) )->output();
        } );
    }

    /**
     * @return array
     */
    public function get_types() {
        return [
            Quizle::TYPE_CONTACTS => [
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16"><path d="M256 32a96 96 0 1096 96 96 96 0 00-96-96zm0 144a48 48 0 1148-48 48.05 48.05 0 01-48 48zm64 112H192A128 128 0 0064 416v96h384v-96a128 128 0 00-128-128zm80 176H112v-48a80.09 80.09 0 0180-80h128a80.09 80.09 0 0180 80z" fill="#006DEE"></path></svg>',
                'title'       => __( 'Gathering contacts', QUIZLE_TEXTDOMAIN ),
                'description' => __( 'Not tied to points and options, the main purpose is to get the user\'s contact information.', QUIZLE_TEXTDOMAIN ),
            ],
            Quizle::TYPE_VARIABLE => [
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="16" height="16"><path d="M29.56 22.94c.59.59.59 1.54 0 2.12l-5 5c-.29.29-.68.44-1.06.44s-.77-.15-1.06-.44a1.49 1.49 0 0 1 0-2.12l2.44-2.44H20c-.19 0-.38-.04-.55-.11h-.02c-.18-.08-.35-.19-.49-.33l-7.56-7.56H3.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5h7.88l7.56-7.56c.14-.14.31-.25.49-.33h.02c.17-.07.35-.11.55-.11h4.88l-2.44-2.44c-.59-.59-.59-1.54 0-2.12s1.54-.59 2.12 0l5 5c.59.59.59 1.54 0 2.12l-5 5c-.29.29-.68.44-1.06.44s-.77-.15-1.06-.44a1.49 1.49 0 0 1 0-2.12l2.44-2.44h-4.26l-6.5 6.5 6.5 6.5h4.26l-2.44-2.44c-.59-.59-.59-1.54 0-2.12s1.54-.59 2.12 0l5 5Z" fill="#006DEE" /></svg>',
                'title'       => __( 'Variable', QUIZLE_TEXTDOMAIN ),
                'description' => __( 'When each answer is linked to a different result and at the end of the output is the result that scored the most points.', QUIZLE_TEXTDOMAIN ),
            ],
            Quizle::TYPE_TEST     => [
                'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16"><path d="M432 0H80A80.09 80.09 0 000 80v352a80.09 80.09 0 0080 80h352a80.09 80.09 0 0080-80V80a80.09 80.09 0 00-80-80zm32 432a32 32 0 01-32 32H80a32 32 0 01-32-32V80a32 32 0 0132-32h352a32 32 0 0132 32zm-54.21-281.65a24 24 0 01-.11 34l-161 160a24 24 0 01-16.92 7h-.26a24 24 0 01-17-7.34L135 261.4a24 24 0 1134.56-33.31l62.61 65 143.68-142.84a24 24 0 0133.94.1z" fill="#006DEE"></path></svg>',
                'title'       => __( 'Testing', QUIZLE_TEXTDOMAIN ),
                'description' => __( 'Each answer is followed by the correct option. The results are derived from the final total score.', QUIZLE_TEXTDOMAIN ),
            ],
        ];
    }

    /**
     * @return void
     */
    protected function register_post_type() {
        add_action( 'init', function () {
            if ( ! $this->settings->verify() ) {
                return;
            }

            $public = $this->settings->get_value( 'is_quizle_public' );
            $labels = [
                'name'          => __( 'Quizle', QUIZLE_TEXTDOMAIN ),
                'singular_name' => __( 'Quiz', QUIZLE_TEXTDOMAIN ),
                'menu_name'     => __( 'Quizle', QUIZLE_TEXTDOMAIN ),
                'all_items'     => __( 'All Quizzes', QUIZLE_TEXTDOMAIN ),
                'add_new'       => __( 'Add new', QUIZLE_TEXTDOMAIN ),
                'add_new_item'  => __( 'Add new Quiz', QUIZLE_TEXTDOMAIN ),
                'edit_item'     => __( 'Edit Quiz', QUIZLE_TEXTDOMAIN ),
            ];

            /**
             * @since 1.4
             */
            $post_type_args = apply_filters( 'quizle/register/post_type_args', [
                'label'               => __( 'Quizle', QUIZLE_TEXTDOMAIN ),
                'menu_icon'           => admin_icon_url(),
                'menu_position'       => 120,
                'labels'              => $labels,
                'description'         => '',
                'public'              => $public,
                'publicly_queryable'  => $public,
                'show_ui'             => true,
                'delete_with_user'    => false,
                'show_in_rest'        => false,
                'has_archive'         => self::POST_TYPE,
                //                'show_in_menu'          => 'edit.php?post_type=' . self::POST_TYPE,
                //                'show_in_menu'          => self::POST_TYPE,
                'show_in_nav_menus'   => $public,
                'exclude_from_search' => true,
                'capability_type'     => 'post',
                'map_meta_cap'        => true,
                'hierarchical'        => false,
                'rewrite'             => [ 'slug' => 'quizle', 'with_front' => true ],
                'query_var'           => true,
                'supports'            => [
                    'title',
                ],
                'feed'                => false,
            ] );

            register_post_type( self::POST_TYPE, $post_type_args );
        } );
    }
}
