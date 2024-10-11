<?php

namespace ACP\Admin;

use AC;
use AC\Admin\Page\Columns;
use AC\Admin\Tooltip;
use AC\Asset;
use AC\Asset\Location;
use AC\ListScreen;
use AC\ListScreenCollection;
use AC\ListScreenPost;
use AC\ListScreenRepository\Sort\ManualOrder;
use AC\ListScreenRepository\Storage;
use AC\Registerable;
use AC\Type\ListScreenId;
use AC\Type\Url;
use AC\View;
use ACP\Admin\ScriptFactory\SettingsFactory;
use ACP\Bookmark\SegmentRepository;
use ACP\ListScreen\Comment;
use ACP\ListScreen\Media;
use ACP\ListScreen\User;
use ACP\Search\TableScreenFactory;
use ACP\Settings\ListScreen\HideOnScreen;
use ACP\Settings\ListScreen\HideOnScreenCollection;
use ACP\Sorting;
use ACP\Type\HideOnScreen\Group;
use LogicException;
use WP_User;

class Settings implements Registerable {

	private $storage;

	private $location;

	private $segment_repository;

	public function __construct(
		Storage $storage,
		Location\Absolute $location,
		SegmentRepository $segment_repository
	) {
		$this->storage = $storage;
		$this->location = $location;
		$this->segment_repository = $segment_repository;
	}

	public function register() {
		add_action( 'ac/settings/before_columns', [ $this, 'render_title' ] );
		add_action( 'ac/settings/sidebox', [ $this, 'render_sidebar' ] );
		add_action( 'ac/settings/sidebox', [ $this, 'render_sidebar_help' ] );
		add_action( 'ac/admin_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'ac/settings/after_title', [ $this, 'render_submenu_view' ] );
		add_action( 'ac/settings/after_columns', [ $this, 'render_settings' ] );
	}

	public function render_submenu_view( ListScreen $current_list_screen ): void {
		if ( ! apply_filters( 'acp/admin/enable_submenu', false ) ) {
			return;
		}

		$list_screens = $this->get_list_screens( $current_list_screen->get_key() );

		if ( $list_screens->count() <= 1 ) {
			return;
		}

		ob_start();
		foreach ( $list_screens as $list_screen ) : ?>
			<li data-screen="<?= esc_attr( $list_screen->get_layout_id() ) ?>">
				<a class="<?= $list_screen->get_layout_id() === $current_list_screen->get_layout_id() ? 'current' : '' ?>" href="<?= add_query_arg( [ 'layout_id' => $list_screen->get_layout_id() ], $current_list_screen->get_edit_link() ) ?>"><?php echo esc_html( $list_screen->get_title() ?: __( '(no name)', 'codepress-admin-columns' ) ); ?></a>
			</li>
		<?php endforeach;

		$items = ob_get_clean();

		$menu = new View( [
			'items' => $items,
		] );

		echo $menu->set_template( 'admin/edit-submenu' );
	}

	private function get_list_screens( string $key ): ListScreenCollection {
		static $list_screen_types;

		if ( null === $list_screen_types ) {
			$list_screen_types = $this->storage->find_all_by_key( $key, new ManualOrder() );
		}

		return $list_screen_types;
	}

	public function render_title( ListScreen $list_screen ): void {
		$list_screens = $this->get_list_screens( $list_screen->get_key() );

		if ( $list_screens->count() <= 1 ) {
			return;
		}

		$view = new View( [
			'title' => $list_screen->get_title(),
		] );

		$view->set_template( 'admin/list-screen-title' );

		echo $view->render();
	}

	public function render_sidebar( ListScreen $current_list_screen ): void {
		$list_screens = $this->get_list_screens( $current_list_screen->get_key() );

		$sidebar = new View( [
			'list_screen'  => $current_list_screen,
			'list_screens' => $list_screens,
		] );

		$sidebar->set_template( 'admin/list-screens-sidebar' );

		echo $sidebar->render();
	}

	public function admin_scripts( $main ): void {
		if ( ! $main instanceof Columns ) {
			return;
		}

		wp_deregister_script( 'select2' ); // try to remove any other version of select2

		$style = new Asset\Style( 'acp-layouts', $this->location->with_suffix( 'assets/core/css/layouts.css' ), [ 'ac-utilities' ] );
		$style->enqueue();

		// Select2
		wp_enqueue_style( 'ac-select2' );
		wp_enqueue_script( 'ac-select2' );

		$list_screen = $main->get_list_screen_from_request();

		if ( ! $list_screen ) {
			throw new LogicException( 'Missing list screen.' );
		}

		$factory = new SettingsFactory(
			$this->location,
			$this->get_hide_on_screen_collection( $list_screen ),
			$list_screen
		);

		$factory
			->create()
			->enqueue();
	}

	private function tooltip_horizontal_scrolling(): Tooltip {
		$content = new View( [
			'location' => $this->location,
		] );

		$content->set_template( 'admin/tooltip/horizontal-scrolling' );

		return new Tooltip( 'horizontal_scrolling', [
			'content'    => $content,
			'link_label' => '<img src="' . AC()->get_url() . 'assets/images/question.svg" alt="?" class="ac-setbox__row__th__info">',
			'title'      => __( 'Horizontal Scrolling', 'codepress-admin-columns' ),
			'position'   => 'right_bottom',
		] );
	}

	private function tooltip_filters(): Tooltip {
		$content = new View( [
			'location' => $this->location,
		] );

		$content->set_template( 'admin/tooltip/preferred-segment' );

		return new Tooltip( 'preferred_segment', [
			'content'    => $content,
			'link_label' => '<img src="' . AC()->get_url() . 'assets/images/question.svg" alt="?" class="ac-setbox__row__th__info">',
			'title'      => __( 'Filters', 'codepress-admin-columns' ),
			'position'   => 'right_bottom',
		] );
	}

	private function tooltip_primary_column(): Tooltip {
		$content = new View( [
			'location' => $this->location,
		] );

		$content->set_template( 'admin/tooltip/primary-column' );

		return new Tooltip( 'primary_column', [
			'content'    => $content,
			'link_label' => sprintf( '<img src="%s" alt="?" class="ac-setbox__row__th__info">', AC()->get_url() . 'assets/images/question.svg' ),
			'title'      => __( 'Primary Column', 'codepress-admin-columns' ),
			'position'   => 'right_bottom',
		] );
	}

	private function can_bookmark( ListScreen $list_screen ): bool {
		return null !== TableScreenFactory::get_table_screen_reference( $list_screen );
	}

	public function render_settings( ListScreen $list_screen ): void {
		$roles = $list_screen->get_preference( 'roles' );

		if ( empty( $roles ) || ! is_array( $roles ) ) {
			$roles = [];
		}

		$users = $list_screen->get_preference( 'users' );

		if ( empty( $users ) || ! is_array( $users ) ) {
			$users = [];
		}

		$view = new View( [
			'list_screen'            => $list_screen,
			'preferences'            => $list_screen->get_preferences(),
			'select_roles'           => $this->select_roles( $roles, $list_screen->is_read_only() ),
			'select_users'           => $this->select_users( $users, $list_screen->is_read_only() ),
			'tooltip_hs'             => $this->tooltip_horizontal_scrolling(),
			'tooltip_filters'        => $this->tooltip_filters(),
			'segments'               => $list_screen->has_id() ? $this->get_segments_for_list_screen_id( $list_screen->get_id() ) : [],
			'can_horizontal_scroll'  => true,
			'can_sort'               => $list_screen instanceof Sorting\ListScreen,
			'can_bookmark'           => $this->can_bookmark( $list_screen ),
			'can_primary_column'     => true,
			'primary_column'         => $list_screen->get_preference( 'primary_column' ) ?: '',
			'primary_columns'        => $this->get_primary_column_options( $list_screen ),
			'tooltip_primary_column' => $this->tooltip_primary_column(),
		] );

		$view->set_template( 'admin/list-screen-settings' );

		echo $view->render();
	}

	private function get_segments_for_list_screen_id( ListScreenId $list_screen_id ): array {
		$result = [];

		$segments = $this->segment_repository->find_all( [
			SegmentRepository::FILTER_LIST_SCREEN => $list_screen_id,
			SegmentRepository::FILTER_GLOBAL      => true,
			SegmentRepository::ORDER_BY           => 'name',
			SegmentRepository::ORDER              => 'ASC',
		] );

		foreach ( $segments as $segment ) {
			$result[ $segment->get_id()->get_id() ] = $segment->get_name();
		}

		return $result;
	}

	public function get_hide_on_screen_collection( ListScreen $list_screen ): HideOnScreenCollection {
		$collection = new HideOnScreenCollection();

		$collection->add( new HideOnScreen\Filters(), new Group( Group::ELEMENT ), 30 )
		           ->add( new HideOnScreen\Search(), new Group( Group::ELEMENT ), 90 )
		           ->add( new HideOnScreen\BulkActions(), new Group( Group::ELEMENT ), 100 )
		           ->add( new HideOnScreen\ColumnResize(), new Group( Group::FEATURE ), 110 )
		           ->add( new HideOnScreen\ColumnOrder(), new Group( Group::FEATURE ), 120 )
		           ->add( new HideOnScreen\RowActions(), new Group( Group::ELEMENT ), 130 );

		switch ( true ) {
			case $list_screen instanceof ListScreenPost :
				$collection->add( new HideOnScreen\FilterPostDate(), new Group( Group::ELEMENT ), 32 );

				// Exclude Media, but make sure to include all other post types
				if ( 'attachment' !== $list_screen->get_post_type() ) {
					$collection->add( new HideOnScreen\SubMenu\PostStatus(), new Group( Group::ELEMENT ), 80 );
				}

				if ( is_object_in_taxonomy( $list_screen->get_post_type(), 'category' ) ) {
					$collection->add( new HideOnScreen\FilterCategory(), new Group( Group::ELEMENT ), 34 );
				}

				if ( post_type_supports( $list_screen->get_post_type(), 'post-formats' ) ) {
					$collection->add( new HideOnScreen\FilterPostFormat(), new Group( Group::ELEMENT ), 36 );
				}

				if ( $list_screen instanceof Media ) {
					$collection->add( new HideOnScreen\FilterMediaItem(), new Group( Group::ELEMENT ), 31 );
				}

				break;
			case $list_screen instanceof User:
				$collection->add( new HideOnScreen\SubMenu\Roles(), new Group( Group::ELEMENT ), 80 );

				break;
			case $list_screen instanceof Comment:
				$collection->add( new HideOnScreen\FilterCommentType(), new Group( Group::ELEMENT ), 31 );
				$collection->add( new HideOnScreen\SubMenu\CommentStatus(), new Group( Group::ELEMENT ), 80 );

				break;
		}

		do_action( 'acp/admin/settings/hide_on_screen', $collection, $list_screen );

		return $collection;
	}

	public function render_sidebar_help(): void {
		?>
		<template id="layout-help" class="hidden">
			<h3><?php _e( 'Sets', 'codepress-admin-columns' ); ?></h3>

			<p>
				<?php _e( "Sets allow users to switch between different column views.", 'codepress-admin-columns' ); ?>
			</p>
			<p>
				<?php _e( "Available sets are selectable from the overview screen. Users can have their own column view preference.", 'codepress-admin-columns' ); ?>
			<p>
			<p>
				<img src="<?= esc_url( $this->location->with_suffix( 'assets/core/images/layout-selector.png' )->get_url() ) ?>" alt=""/>
			</p>
			<p>
				<a href="<?= esc_url( ( new Url\Documentation( Url\Documentation::ARTICLE_COLUMN_SETS ) )->get_url() ) ?>" target="_blank"><?php _e( 'Online documentation', 'codepress-admin-columns' ); ?></a>
			</p>
		</template>
		<?php
	}

	private function select_roles( array $roles = [], bool $is_disabled = false ): AC\Form\Element\MultiSelect {
		$select = new AC\Form\Element\MultiSelect( 'roles', $this->get_grouped_role_names() );

		$roles = array_map( 'strval', array_filter( $roles ) );

		$select->set_value( $roles )
		       ->set_attribute( 'multiple', true )
		       ->set_attribute( 'class', 'roles' )
		       ->set_attribute( 'style', 'width: 100%;' )
		       ->set_attribute( 'id', 'listscreen_roles' );

		if ( $is_disabled ) {
			$select->set_attribute( 'disabled', 'dsiabled' );
		}

		return $select;
	}

	private function get_grouped_role_names(): array {
		if ( ! function_exists( 'get_editable_roles' ) ) {
			return [];
		}

		$roles = [];

		foreach ( get_editable_roles() as $name => $role ) {
			$group = __( 'Other', 'codepress-admin-columns' );

			// Core roles
			if ( in_array( $name, [ 'super_admin', 'administrator', 'editor', 'author', 'contributor', 'subscriber' ] ) ) {
				$group = __( 'Default', 'codepress-admin-columns' );
			}

			/**
			 * @param string $group Role group
			 * @param string $name  Role name
			 *
			 * @since 4.0
			 */
			$group = (string) apply_filters( 'ac/editing/role_group', $group, $name );

			if ( ! isset( $roles[ $group ] ) ) {
				$roles[ $group ]['title'] = $group;
				$roles[ $group ]['options'] = [];
			}

			$roles[ $group ]['options'][ $name ] = $role['name'];
		}

		return $roles;
	}

	private function get_primary_column_options( ListScreen $list_screen ): array {
		$options = [];

		foreach ( $list_screen->get_columns() as $column ) {
			if ( 'column-actions' === $column->get_type() ) {
				return [];
			}

			$options[ $column->get_name() ] = trim( strip_tags( $column->get_custom_label() ) );
		}

		return [ '' => __( 'Default', 'codepress-admin-columns' ) ] + $options;
	}

	private function select_users( array $user_ids = [], bool $is_disabled = false ): AC\Form\Element\MultiSelect {
		$options = [];

		$user_ids = array_map( 'intval', array_filter( $user_ids ) );

		foreach ( $user_ids as $user_id ) {
			$user = get_userdata( $user_id );

			if ( ! $user instanceof WP_User ) {
				continue;
			}

			$options[ (string) $user_id ] = ac_helper()->user->get_display_name( $user_id );
		}

		$select = new AC\Form\Element\MultiSelect( 'users', $options );

		$select->set_value( $user_ids )
		       ->set_attribute( 'class', 'users' )
		       ->set_attribute( 'style', 'width: 100%;' )
		       ->set_attribute( 'multiple', true )
		       ->set_attribute( 'id', 'listscreen_users' );

		if ( $is_disabled ) {
			$select->set_attribute( 'disabled', 'dsiabled' );
		}

		return $select;
	}

	public function read_only_message( string $message, ListScreen $list_screen ): string {
		if ( $list_screen->is_read_only() ) {
			$message .= '<br/>' . sprintf( __( 'You can make an editable copy of this set by clicking %s on the right.', 'codepress-admin-columns' ), '"<strong>' . __( '+ Add set', 'codepress-admin-columns' ) . '</strong>"' );
		}

		return $message;
	}

}