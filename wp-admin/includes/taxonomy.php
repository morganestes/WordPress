<?php
/**
 * WordPress Taxonomy Administration API.
 *
 * @package WordPress
 * @subpackage Administration
 */

//
// Category
//

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $cat_name
 * @return unknown
 */
function category_exists($cat_name, $parent = 0) {
	$id = term_exists($cat_name, 'category', $parent);
	if ( is_array($id) )
		$id = $id['term_id'];
	return $id;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $id
 * @return unknown
 */
function get_category_to_edit( $id ) {
	$category = get_term( $id, 'category', OBJECT, 'edit' );
	_make_cat_compat( $category );
	return $category;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $cat_name
 * @param unknown_type $parent
 * @return unknown
 */
function wp_create_category( $cat_name, $parent = 0 ) {
	if ( $id = category_exists($cat_name, $parent) )
		return $id;

	return wp_insert_category( array('cat_name' => $cat_name, 'category_parent' => $parent) );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $categories
 * @param unknown_type $post_id
 * @return unknown
 */
function wp_create_categories($categories, $post_id = '') {
	$cat_ids = array ();
	foreach ($categories as $category) {
		if ($id = category_exists($category))
			$cat_ids[] = $id;
		else
			if ($id = wp_create_category($category))
				$cat_ids[] = $id;
	}

	if ( $post_id )
		wp_set_post_categories($post_id, $cat_ids);

	return $cat_ids;
}

/**
 * Updates an existing Category or creates a new Category.
 *
 * @since 2.0.0
 * @since 2.5.0 $wp_error parameter was added.
 * @since 3.0.0 The 'taxonomy' argument was added.
 *
 * @param array $catarr {
 *     Array of arguments for inserting a new category.
 *
 *     @type int        $cat_ID               Categoriy ID. A non-zero value updates an existing category.
 *                                            Default 0.
 *     @type string     $taxonomy             Taxonomy slug. Defualt 'category'.
 *     @type string     $cat_nam              Category name. Default empty.
 *     @type string     $category_description Category description. Default empty.
 *     @type string     $category_nicename    Category nice (display) name. Default empty.
 *     @type int|string $category_parent      Category parent ID. Default empty.
 * }
 * @param bool  $wp_error Optional. Default false.
 * @return int|object The ID number of the new or updated Category on success. Zero or a WP_Error on failure,
 *                    depending on param $wp_error.
 */
function wp_insert_category( $catarr, $wp_error = false ) {
	$cat_defaults = array( 'cat_ID' => 0, 'taxonomy' => 'category', 'cat_name' => '', 'category_description' => '', 'category_nicename' => '', 'category_parent' => '' );
	$catarr = wp_parse_args( $catarr, $cat_defaults );

	if ( trim( $catarr['cat_name'] ) == '' ) {
		if ( ! $wp_error ) {
			return 0;
		} else {
			return new WP_Error( 'cat_name', __( 'You did not enter a category name.' ) );
		}
	}

	$catarr['cat_ID'] = (int) $catarr['cat_ID'];

	// Are we updating or creating?
	$update = ! empty ( $catarr['cat_ID'] );

	$name = $catarr['cat_name'];
	$description = $catarr['category_description'];
	$slug = $catarr['category_nicename'];
	$parent = (int) $catarr['category_parent'];
	if ( $parent < 0 ) {
		$parent = 0;
	}

	if ( empty( $parent )
		|| ! term_exists( $parent, $catarr['taxonomy'] )
		|| ( $catarr['cat_ID'] && term_is_ancestor_of( $catarr['cat_ID'], $parent, $catarr['taxonomy'] ) ) ) {
		$parent = 0;
	}

	$args = compact('name', 'slug', 'parent', 'description');

	if ( $update ) {
		$catarr['cat_ID'] = wp_update_term( $catarr['cat_ID'], $catarr['taxonomy'], $args );
	} else {
		$catarr['cat_ID'] = wp_insert_term( $catarr['cat_name'], $catarr['taxonomy'], $args );
	}

	if ( is_wp_error( $catarr['cat_ID'] ) ) {
		if ( $wp_error ) {
			return $catarr['cat_ID'];
		} else {
			return 0;
		}
	}
	return $catarr['cat_ID']['term_id'];
}

/**
 * Aliases wp_insert_category() with minimal args.
 *
 * If you want to update only some fields of an existing category, call this
 * function with only the new values set inside $catarr.
 *
 * @since 2.0.0
 *
 * @param array $catarr The 'cat_ID' value is required. All other keys are optional.
 * @return int|bool The ID number of the new or updated Category on success. Zero or FALSE on failure.
 */
function wp_update_category($catarr) {
	$cat_ID = (int) $catarr['cat_ID'];

	if ( isset($catarr['category_parent']) && ($cat_ID == $catarr['category_parent']) )
		return false;

	// First, get all of the original fields
	$category = get_term( $cat_ID, 'category', ARRAY_A );
	_make_cat_compat( $category );

	// Escape data pulled from DB.
	$category = wp_slash($category);

	// Merge old and new fields with new fields overwriting old ones.
	$catarr = array_merge($category, $catarr);

	return wp_insert_category($catarr);
}

//
// Tags
//

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.3.0
 *
 * @param unknown_type $tag_name
 * @return unknown
 */
function tag_exists($tag_name) {
	return term_exists($tag_name, 'post_tag');
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.3.0
 *
 * @param unknown_type $tag_name
 * @return unknown
 */
function wp_create_tag($tag_name) {
	return wp_create_term( $tag_name, 'post_tag');
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.3.0
 *
 * @param unknown_type $post_id
 * @return unknown
 */
function get_tags_to_edit( $post_id, $taxonomy = 'post_tag' ) {
	return get_terms_to_edit( $post_id, $taxonomy);
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.8.0
 *
 * @param unknown_type $post_id
 * @return unknown
 */
function get_terms_to_edit( $post_id, $taxonomy = 'post_tag' ) {
	$post_id = (int) $post_id;
	if ( !$post_id )
		return false;

	$tags = wp_get_post_terms($post_id, $taxonomy, array());

	if ( !$tags ) {
		return false;
	}
	if ( is_wp_error($tags) ) {
		return $tags;
	}
	$tag_names = array();
	foreach ( $tags as $tag ) {
		$tag_names[] = $tag->name;
	}

	$tags_to_edit = esc_attr( join( ',', $tag_names ) );

	/**
	 * Filter the comma-separated list of terms available to edit.
	 *
	 * @since 2.8.0
	 *
	 * @see get_terms_to_edit()
	 *
	 * @param array  $tags_to_edit An array of terms.
	 * @param string $taxonomy     The taxonomy for which to retrieve terms. Default 'post_tag'.
	 */
	$tags_to_edit = apply_filters( 'terms_to_edit', $tags_to_edit, $taxonomy );

	return $tags_to_edit;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.8.0
 *
 * @param unknown_type $tag_name
 * @return unknown
 */
function wp_create_term($tag_name, $taxonomy = 'post_tag') {
	if ( $id = term_exists($tag_name, $taxonomy) )
		return $id;

	return wp_insert_term($tag_name, $taxonomy);
}
