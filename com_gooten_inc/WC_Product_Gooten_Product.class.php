<?php
/**
 * Created by Vlado on 9-Sep-16.
 */

/**
 * Class WC_Product_Gooten_Product
 */
class WC_Product_Gooten_Product extends WC_Product_Variable
{
    private $productName;

    public function __construct($product)
    {
        parent::__construct($product);
        $this->product_type = 'gooten_product';
    }

    public function getProductName()
    {
        if (!isset($this->productName)) {
            $this->productName = get_post_meta($this->id, '_gooten_prp_name', true);
        }
        return $this->productName;
    }

    public function getAllVariations()
    {
        $variations = array();
        $children = get_posts(array(
            'post_parent' => $this->id,
            'post_type' => 'product_variation',
            'post_status' => array('publish', 'private'),
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'fields' => 'ids',
            'numberposts' => -1
        ));
        foreach ($children as $child_id) {
            $variation = $this->get_child($child_id);
            if (has_post_thumbnail($variation->get_variation_id())) {
                $attachment_id = get_post_thumbnail_id($variation->get_variation_id());
                $attachment = wp_get_attachment_image_src($attachment_id, 'shop_single');
                $image = $attachment ? current($attachment) : '';
            } else {
                $image = '';
            }
            $variations[] = array(
                'variationId' => $variation->variation_id,
                'isEnabled' => $variation->variation_is_visible(),
                'price' => $variation->get_display_price(),
                'imageSrc' => $image,
                'sku' => $variation->get_sku(),
                'description' => $variation->get_variation_description(),
                'gootenSku' => get_post_meta($variation->variation_id, '_gooten_sku', true),
                'gootenPrice' => get_post_meta($variation->variation_id, '_gooten_price', true)
            );
        }
        return $variations;
    }

}
