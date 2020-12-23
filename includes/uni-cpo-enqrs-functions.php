<?php
function uni_enqrs_get_nice_options_data( $filtered_form_data ) {
	if ( ! function_exists( 'uni_cpo_get_posts_by_slugs' ) ) {
		return;
	}

	$formatted = [];

	if ( ! empty( $filtered_form_data ) ) {
		$posts = uni_cpo_get_posts_by_slugs( array_keys( $filtered_form_data ) );

		if ( ! empty( $posts ) ) {
			$posts_ids = wp_list_pluck( $posts, 'ID' );
			foreach ( $posts_ids as $post_id ) {
				$option = uni_cpo_get_option( $post_id );

				if ( is_object( $option ) ) {
					$slug             = $option->get_slug();
					$display_key      = uni_cpo_sanitize_label( $option->cpo_order_label() );
					$calculate_result = $option->calculate( $filtered_form_data );

					if ( 'extra_cart_button' === $option->get_type() ) {
						continue;
					}

					$display_value = '';
					if ( ! empty( $calculate_result ) ) {
						foreach ( $calculate_result as $k => $v ) {
							if ( $slug === $k ) { // excluding special vars

								if ( is_array( $v['order_meta'] ) ) {
									$v['order_meta'] = array_map(
										function ( $item ) {
											if ( ! is_numeric( $item ) ) {
												return esc_html__( $item );
											} else {
												return $item;
											}
										},
										$v['order_meta']
									);
									$display_value   = implode( ', ', $v['order_meta'] );
								} else {
									if ( ! is_numeric( $v['order_meta'] ) ) {
										$display_value = esc_html__( $v['order_meta'] );
									} else {
										$display_value = $v['order_meta'];
									}
								}
								break;
							}
						}

						$name               = apply_filters( 'uni_cpo_order_item_display_meta_key', $display_key, $v );
						$formatted[ $name ] = apply_filters( 'uni_cpo_order_item_display_meta_value', $display_value, $v );
					}
				}
			}
		}
	}

	return $formatted;
}

function uni_enqrs_send_email( $data ) {
	$admin_email   = get_bloginfo( 'admin_email' );
	$admin_subject = __( 'new inquiry', 'uni-cpo-enqrs' );
	$email_vars    = [
		'name_desc'    => __( 'Name', 'uni-cpo-enqrs' ),
		'email_desc'   => __( 'Email', 'uni-cpo-enqrs' ),
		'phone_desc'   => __( 'Phone', 'uni-cpo-enqrs' ),
		'notes_desc'   => __( 'Notes', 'uni-cpo-enqrs' ),
		'options_desc' => __( 'Options', 'uni-cpo-enqrs' )
	];

	foreach ( $data as $k => $v ) {
		if ( is_array( $v ) ) {
			$opts = [];
			foreach ( $v as $sk => $sv ) {
				$opts[] = "$sk: $sv";
			}
			$email_vars['options'] = $opts;
		} else {
			$email_vars[ $k ] = $v;
		}
	}

	$email = WP_Mail::init()
	                ->to( $admin_email )
	                ->subject( $admin_subject )
	                ->template( UniCpoEnqrs()->plugin_path() . '/includes/enquiry.php', $email_vars )
	                ->send();

	return true;
}