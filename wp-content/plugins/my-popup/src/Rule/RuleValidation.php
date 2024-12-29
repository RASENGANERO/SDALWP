<?php

namespace Wpshop\PluginMyPopup\Rule;

use WP_Post;
use WP_Term;

class RuleValidation {

    /**
     * @param WP_Post     $post
     * @param PageContext $context
     *
     * @return bool
     */
    public function can_output( WP_Post $post, PageContext $context ) {
        $rules = get_post_meta( $post->ID, 'rules', true );
        if ( is_array( $rules ) && isset( $rules['value'] ) ) {
            $rules = json_decode( $rules['value'], true ) ?: [];

            return $this->validate( $rules, $context );
        }

        return true;
    }

    /**
     * @param array       $rules
     * @param PageContext $context
     *
     * @return bool
     */
    protected function validate( $rules, $context ) {
        $rules = array_reverse( $rules );

        $allow = null;

        $allow = apply_filters( 'my_popup:validate_rules_before', $allow );

        foreach ( $rules as $rule ) {
            $do_show = $rule['show'] === 'show';
            switch ( $rule['type'] ) {
                case 'all':
                    $allow = $do_show;
                    break;
                case 'home':
                    if ( $context->is_home ) {
                        $allow = $do_show;
                    }
                    break;
                case 'posts':
                    if ( $context->is_single &&
                         $context->is_object_type( PageContext::OBJ_TYPE_POST ) &&
                         $context->is_object_subtype( 'post' )
                    ) {
                        $is_valued = $this->is_valued( $context->get_object_id(), $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'pages':
                    if ( $context->is_object_type( PageContext::OBJ_TYPE_POST ) &&
                         $context->is_object_subtype( 'page' )
                    ) {
                        $is_valued = $this->is_valued( $context->get_object_id(), $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'post_types':
                    if ( $context->is_object_type( PageContext::OBJ_TYPE_POST ) &&
                         in_array( $context->get_object_subtype(), wp_parse_list( $rule['subtype'] ?? [] ) )
                    ) {
                        $is_valued = $this->is_valued( $context->get_object_id(), $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'categories':
                    if ( $context->is_category && $context->is_object_type( PageContext::OBJ_TYPE_TERM ) ) {
                        $is_valued = $this->is_valued( $context->get_object_id(), $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'tags':
                    if ( $context->is_tag && $context->is_object_type( PageContext::OBJ_TYPE_TERM ) ) {
                        $is_valued = $this->is_valued( $context->get_object_id(), $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'taxonomies':
                    if ( $context->is_tax &&
                         $context->is_object_type( PageContext::OBJ_TYPE_TERM ) &&
                         in_array( $context->get_object_subtype(), wp_parse_list( $rule['subtype'] ?? [] ) )
                    ) {
                        $is_valued = $this->is_valued( $context->get_object_id(), $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'search':
                    if ( $context->is_search ) {
                        $allow = $do_show;
                    }
                    break;
                case '404':
                    if ( $context->is_404 ) {
                        $allow = $do_show;
                    }
                    break;
                case 'posts_cat':
                    if ( $context->is_single ) {
                        $cat_ids = array_map( function ( WP_Term $item ) {
                            return $item->term_id;
                        }, get_the_category( $context->get_object_id() ) );

                        $is_valued = $this->is_valued( $cat_ids, $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'posts_tag':
                    if ( $context->is_single ) {
                        $terms = wp_get_post_terms( $context->get_object_id() );
                        if ( is_wp_error( $terms ) ) {
                            break;
                        }

                        $cat_ids = array_map( function ( WP_Term $item ) {
                            return $item->term_id;
                        }, $terms );

                        $is_valued = $this->is_valued( $cat_ids, $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'posts_taxonomies':
                    if ( $context->is_single ) {
                        $taxonomies = wp_parse_list( $rules['subtype'] ?? [] );
                        if ( ! $taxonomies ) {
                            break;
                        }
                        $terms = wp_get_post_terms( $context->get_object_id(), $taxonomies );
                        if ( is_wp_error( $terms ) ) {
                            break;
                        }

                        $term_ids = array_map( function ( WP_Term $item ) {
                            return $item->term_id;
                        }, $terms );

                        $is_valued = $this->is_valued( $term_ids, $rule );
                        if ( null === $is_valued ) {
                            $allow = $do_show;
                        } else {
                            if ( $is_valued ) {
                                $allow = $do_show;
                            }
                        }
                    }
                    break;
                case 'url_match':
                    $pattern = apply_filters(
                        'my_popup:validation_url_match_pattern',
                        '#' . ( $rule['value'] ?? '' ) . '#u'
                    );
                    if ( @preg_match( $pattern, $context->get_relative_url() ) ) {
                        $allow = $do_show;
                    }
                    break;
                default:
                    break;
            }

            $allow = apply_filters( 'my_popup:validate_rule', $allow, $rule );
        }

        $allow = apply_filters( 'my_popup:validate_rules_after', $allow, $rules );

        if ( null === $allow ) {
            $allow = false;
        }

        return $allow;
    }

    /**
     * @param int|int[] $id_or_ids or array of ids
     * @param array     $rule
     *
     * @return bool|null if $rule has value then return boolean
     */
    protected function is_valued( $id_or_ids, $rule ) {
        if ( $values = wp_parse_id_list( $rule['value'] ?? [] ) ) {
            if ( is_array( $id_or_ids ) ) {
                return (bool) array_intersect( $values, $id_or_ids );
            }

            return in_array( $id_or_ids, $values );
        }

        return null;
    }
}
