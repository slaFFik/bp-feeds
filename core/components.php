<?php

/**
 * Get the master slug for a Members component
 *
 * @return string
 */
function bpf_members_get_component_slug() {
	/** @noinspection PhpUndefinedFieldInspection */
	return apply_filters( 'bpf_members_get_component_slug', buddypress()->members->id );
}

/**
 * Create a term for BPF_TAX taxonomy
 *
 * @param string $slug Slug of a new component
 * @param array $args Includes slug, description
 *
 * @return object|WP_Error
 */
function bpf_register_component( $slug, $args = array() ) {
	if ( '' == trim( $slug ) ) {
		return new WP_Error( 'bpf_empty_component_slug', __( 'A slug is required for registering this component.' ) );
	}

	$name        = $slug;
	$description = '';

	// Do not add twice, check first
	if ( ( $term = term_exists( $slug, BPF_TAX ) ) !== null ) {
		return new WP_Error( 'bpf_component_exists', __( 'A component with the name provided already exists.', BPF_TAX ), $term );
	}

	if ( ! empty( $args['name'] ) ) {
		$name = $args['name'];
	}

	// currently, no html for description
	if ( ! empty( $args['description'] ) ) {
		$description = wp_strip_all_tags( $args['description'] );
	}

	$term_data = wp_insert_term(
		apply_filters( 'bpf_register_component_name', $name ),
		BPF_TAX,
		apply_filters( 'bpf_register_component_args', array(
			'description' => $description,
			'slug'        => $slug,
		) )
	);

	if ( ! is_wp_error( $term_data ) ) {
		return get_term( $term_data['term_id'], BPF_TAX, OBJECT );
	}

	return new WP_Error( 'bpf_component_registration_failed', __( 'There was an error while creating an associated term for this component.', BPF_TAX ), $term_data );
}

/**
 * Get the Component associated term data (object)
 *
 * @param string $slug
 *
 * @return false|object
 */
function bpf_get_component( $slug ) {

	$component = apply_filters( 'bpf_get_component', get_term_by( 'slug', $slug, BPF_TAX, OBJECT ) );

	if ( is_object( $component ) && ! is_wp_error( $component ) ) {
		return $component;
	}

	return false;
}

/**
 * Get the Component associated term_id
 *
 * @param string $slug
 *
 * @return false|int
 */
function bpf_get_component_id( $slug ) {

	$component = bpf_get_component( $slug );

	if ( is_object( $component ) && ! is_wp_error( $component ) && isset( $component->term_id ) ) {
		return $component->term_id;
	}

	return false;
}

/**
 * Delete the Component associated term_id bu slug of term_id
 *
 * @param $slug_or_id
 *
 * @return bool|int|WP_Error
 */
function bpf_delete_component( $slug_or_id ) {
	$term = term_exists( (int) $slug_or_id, BPF_TAX );

	return wp_delete_term( $term['term_id'], BPF_TAX );
}

/**
 * Delete all components from DB
 *
 * @return bool
 */
function bpf_delete_components() {
	$terms = get_terms( BPF_TAX, array(
		'hide_empty' => false,
		'fields'     => 'ids'
	) );

	if ( is_wp_error( $terms ) ) {
		return false;
	}

	foreach ( $terms as $term_id ) {
		bpf_delete_component( $term_id );
	}

	return true;
}