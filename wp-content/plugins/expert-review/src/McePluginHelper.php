<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Settings\ExpertOptions;

class McePluginHelper {

    use TemplateRendererTrait;

    /**
     * @var array
     */
    protected $like_icons;

    /**
     * McePluginHelper constructor.
     *
     * @param array $like_icons
     */
    public function __construct( array $like_icons ) {
        $this->like_icons = $like_icons;
    }

    /**
     * @return void
     */
    public function init() {
        $this->prepare_media_templates();

        do_action( __METHOD__, $this );
    }

    /**
     * @return void
     */
    protected function prepare_media_templates() {

        $users_and_experts_deferred = function () {
            static $result;

            if ( null === $result ) {
                $options   = new ExpertOptions();
                $experts   = $options->experts ? json_decode( $options->experts, true ) : [];
                $users     = [];
                $all_users = apply_filters( 'expert_review:get_users', null );
                if ( ! is_array( $all_users ) ) {
                    $all_users = $this->get_users();
                }
                foreach ( $all_users as $user ) {
                    /** @var \WP_User $user */
                    $users[] = [
                        'id'          => $user->ID,
                        'name'        => "{$user->display_name}",
                        'avatar'      => apply_filters( 'expert_review:expert_avatar_url:preview', $user ) ?: get_avatar_url( $user->ID ),
                        'email'       => $user->user_email,
                        'link'        => get_author_posts_url( $user->ID, $user->user_nicename ),
                        'description' => $user->user_description,
                    ];
                }

                $result = [ $users, $experts ];
            }

            return $result;
        };

        if ( is_admin() ) {
            add_action( 'admin_footer', function () use ( $users_and_experts_deferred ) {
                list( $users, $experts ) = $users_and_experts_deferred();
                echo $this->render( 'tmpl-expert-review-popup', [
                    'experts' => $experts,
                    'users'   => $users,
                ] );
            } );

            add_action( 'print_media_templates', function () use ( $users_and_experts_deferred ) {
                list( $users, $experts ) = $users_and_experts_deferred();
                echo $this->render( 'tmlp-expert-review-live-preview', [
                        'experts' => $experts,
                        'users'   => $users,
                    ] ) .
                     $this->render( 'tmpl-expert-review-likes-live-preview', [
                         'icons' => $this->like_icons,
                     ] ) .
                     $this->render( 'tmpl-expert-review-likes-rate-live-preview', [
                         'icons' => $this->like_icons,
                     ] ) .
                     $this->render( 'tmpl-expert-review-faq-popup' ) .
                     $this->render( 'tmpl-expert-review-faq-live-preview' ) .
                     $this->render( 'tmpl-expert-review-poll-popup' ) .
                     $this->render( 'tmpl-expert-review-poll-live-preview' );
            } );
        }
    }

    /**
     * @return array
     */
    protected function get_users() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT ID, user_email, display_name, user_nicename, meta1.meta_value as user_description FROM {$wpdb->users}
LEFT JOIN {$wpdb->usermeta} as meta1 on {$wpdb->users}.ID = meta1.user_id AND meta1.meta_key = 'description'"
        );
    }
}
