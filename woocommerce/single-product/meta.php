<?php
/**
 * Single Product Meta
 *
 * This template overrides WooCommerce's default meta.php to hide the SKU line
 * while keeping the category and tag lists untouched.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>
<div class="product_meta">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php
	/**
	 * Filters the ordering mode for product categories in the single product meta.
	 *
	 * @param string     $orderby Ordering mode passed to wc_get_product_category_list().
	 * @param WC_Product $product Product object.
	 *
	 * @since 11.2.0
	 */
	$product_category_orderby = apply_filters( 'woocommerce_product_meta_category_orderby', 'breadcrumb', $product );

	$product_category_list = wc_get_product_category_list(
		$product->get_id(),
		', ',
		'<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ',
		'</span>',
		is_string( $product_category_orderby ) ? $product_category_orderby : ''
	);
	echo is_string( $product_category_list ) ? $product_category_list : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>

	<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' ); ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>