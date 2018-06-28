<?php
/**
 * Created by Vlado on 30-Nov-16.
 */


/**
 * Holds utilities needed for creating variations for Gooten product.
 */
class GootenWCProductVariationUtils
{

    /**
     * Searches array of PRPs by SKU.
     *
     * @param $prps The array of PRPs.
     * @param $sku The SKU of PRP being searched.
     * @return PRP or null if PRP with specified SKU was not found.
     */
    private static function findPrpBySku($prps, $sku)
    {
        if (isset($prps) && is_array($prps) && is_string($sku)) {
            foreach ($prps as $prp) {
                if ($prp['Sku'] === $sku) {
                    return $prp;
                }
            }
        }
        return null;
    }

    /**
     * Searches array of product variants by SKU.
     *
     * @param $variants The array of product variants.
     * @param $sku The SKU of product variant being searched.
     * @return Product variant or null if product variant with specified SKU was not found.
     */
    private static function findProductVariantBySku($variants, $sku)
    {
        // Data have same structure
        return self::findPrpBySku($variants, $sku);
    }

    /**
     * Gets option 'Value' property for specified product variant and option name.
     *
     * @param $variants The array of product variants.
     * @param $sku Specifies product variant within $variants array.
     * @param $optionName Specifies option name
     * @return Value of specified option or null
     */
    private static function getOptionValue($variants, $sku, $optionName)
    {
        $variant = self::findProductVariantBySku($variants, $sku);
        if (isset($variant) && is_array($variant) && array_key_exists('Options', $variant) && is_string($sku) && is_string($optionName)) {
            foreach ($variant['Options'] as $option) {
                if ($option['Name'] === $optionName) {
                    return $option['Value'];
                };
            }
        }
        return null;
    }

    /**
     * Searches for URL of 'generated-preview' image.
     *
     * @param array $images Array of Gooten PRP images.
     * @return (string|null) The URL of 'generated-preview' image or null image was not found.
     */
    private static function getGeneratedPreviewImageUrl($images)
    {
        if (isset($images) && is_array($images)) {
            foreach ($images as $image) {
                if (isset($image['Id']) && $image['Id'] === 'generated-preview') {
                    return $image['Url'];
                }
            }
        }
        return null;
    }

    /**
     * Creates WooCommerce product attributes. Attributes are created based on supplied variations
     * (PRPs that are enabled by user).
     *
     * @param $variants The variants configured by user for current product.
     * @param $gootenVariants Variants from Gooten's GET /productvariants response fetched for current product.
     * @param $prps Array of all Gooten PRPs.
     * @return array WooCommerce product attributes.
     */
    private static function createWCAttributes($variants, $gootenVariants, $prps)
    {
        $attributes = array();
        if (isset($variants) && is_array($variants)) {
            foreach ($variants as $variant) {
                $prp = self::findPrpBySku($prps, $variant['productSku']);
                if (isset($prp)) {
                    $gtnVariant = self::findProductVariantBySku($gootenVariants, $prp['Items'][0]['ProductVariantSku']);
                    if (isset($gtnVariant)) {
                        foreach ($gtnVariant['Options'] as $option) {
                            $n = $option['Name'];
                            if (!isset($attributes[$n])) {
                                $attributes[$n] = array();
                            }
                            $attributes[$n][] = $option['Value'];
                        }
                    }
                }
            }
            foreach ($attributes as $key => $val) {
                $val = array_unique($val);
                if (sizeof($val) === 1) {
                    unset($attributes[$key]);
                } else {
                    $attributes[$key] = implode(WC_DELIMITER, $val);
                }
            }
        }
        return $attributes;
    }

    /**
     * Saves product attributes for product.
     *
     * @param $post_id Post ID of the product.
     * @param $wc_attributes The product attributes to be saved.
     */
    private static function saveProductAttributes($post_id, $wc_attributes)
    {
        $attributes = array();
        $i = 0;
        foreach ($wc_attributes as $key => $value) {
            // Text based, possibly separated by pipes (WC_DELIMITER). Preserve line breaks in non-variation attributes.
            $values = wc_clean($value);
            $values = implode(' ' . WC_DELIMITER . ' ', wc_get_text_attributes($values));

            // Custom attribute - Add attribute to array and set the values
            $attributes[sanitize_title($key)] = array(
                'name' => wc_clean($key),
                'value' => $values,
                'position' => $i++, // Currently position is not important
                'is_visible' => 1,
                'is_variation' => 1,
                'is_taxonomy' => 0
            );
        }
        uasort($attributes, 'wc_product_attribute_uasort_comparison');

        // Unset removed attributes by looping over previous values and unsetting the terms.
        $old_attributes = array_filter((array)maybe_unserialize(get_post_meta($post_id, '_product_attributes', true)));

        if (!empty($old_attributes)) {
            foreach ($old_attributes as $key => $value) {
                if (empty($attributes[$key]) && !empty($value['is_taxonomy']) && taxonomy_exists($key)) {
                    wp_set_object_terms($post_id, array(), $key);
                }
            }
        }

        // After removed attributes are unset, we can set the new attribute data.
        update_post_meta($post_id, '_product_attributes', $attributes);
    }

    /**
     * Crates/updates WooCommerce variants based on data described in $variants array.
     *
     * @param integer $post_id The post ID of product currently being edited/created.
     * @param array $variants Data sent from client describing product variants.
     * @param array $prpItems Array of all Gooten PRPs associated to $variants array.
     */
    public static function saveVariations($post_id, $variants, $prpItems)
    {
        // Get Gooten variants for this product
        $gtnProductVariantsResponse = GootenWCAPI::getProductVariants($prpItems[0]['Items'][0]['ProductId']);
        $gtnProductVariantsResponse = json_decode($gtnProductVariantsResponse, true);
        $gtnProductVariantsResponse = $gtnProductVariantsResponse['ProductVariants'];

        // Create and save attributes used on this product
        $wc_attributes = self::createWCAttributes($variants, $gtnProductVariantsResponse, $prpItems);
        self::saveProductAttributes($post_id, $wc_attributes);
        $attributes = (array)maybe_unserialize(get_post_meta($post_id, '_product_attributes', true));
        $default_attributes = array();

        global $wpdb;

        // Create variations
        for ($i = 0; $i < sizeof($variants); $i++) {
            $variation = $variants[$i];
            $prp = self::findPrpBySku($prpItems, $variation['productSku']);

            $variation_id = isset($variation['variablePostId']) && strlen($variation['variablePostId']) > 0 ? absint($variation['variablePostId']) : null;
            $variable_sku = $variation['sku'];
            $variable_regular_price = $variation['price'];
            $post_status = isset($variation['isEnabled']) && $variation['isEnabled'] === 'true' ? 'publish' : 'private';
            $variable_menu_order = $i; // Position is not important for now

            // Generate a useful post title
            $variation_post_title = sprintf(__('Variation #%s of %s', 'woocommerce'), $variation_id, esc_html(get_the_title($post_id)));

            // Add or Update post
            if (!isset($variation_id)) {
                $variation_id = wp_insert_post(array(
                    'post_title' => $variation_post_title,
                    'post_content' => '',
                    'post_status' => $post_status,
                    'post_author' => get_current_user_id(),
                    'post_parent' => $post_id,
                    'post_type' => 'product_variation',
                    'menu_order' => $variable_menu_order
                ));
                do_action('woocommerce_create_product_variation', $variation_id);
            } else {
                $modified_date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));
                $wpdb->update($wpdb->posts, array(
                    'post_status' => $post_status,
                    'post_title' => $variation_post_title,
                    'menu_order' => $variable_menu_order,
                    'post_modified' => $modified_date,
                    'post_modified_gmt' => get_gmt_from_date($modified_date)
                ), array('ID' => $variation_id));
                clean_post_cache($variation_id);
                do_action('woocommerce_update_product_variation', $variation_id);
            }

            // Only continue if we have a variation ID
            if (!$variation_id) {
                continue;
            }

            {// Set SKU
                $sku = get_post_meta($variation_id, '_sku', true);
                $new_sku = wc_clean($variable_sku);

                if ('' == $new_sku) {
                    update_post_meta($variation_id, '_sku', '');
                } elseif ($new_sku !== $sku) {
                    if (!empty($new_sku)) {
                        $unique_sku = wc_product_has_unique_sku($variation_id, $new_sku);
                        if (!$unique_sku) {
                            WC_Admin_Meta_Boxes::add_error(sprintf(__('#%s &ndash; Variation SKU must be unique.', 'woocommerce'), $variation_id));
                        } else {
                            update_post_meta($variation_id, '_sku', $new_sku);
                        }
                    } else {
                        update_post_meta($variation_id, '_sku', '');
                    }
                }
            }

            // Update Gooten meta
            update_post_meta($variation_id, '_gooten_sku', $prp['Sku']);
            update_post_meta($variation_id, '_gooten_price', $prp['Price']['Price']);

            { // Update variation thumbnail
                $imgUrl = self::getGeneratedPreviewImageUrl($prp['Images']);
                if (isset($imgUrl)) {
                    $imageId = GootenWCMediaUtils::downloadAndSaveImageToMedia($imgUrl);
                    if (isset($imageId)) {
                        update_post_meta($variation_id, '_thumbnail_id', $imageId);
                    }
                }
            }

            // Virtual
            update_post_meta($variation_id, '_virtual', 'no');

            // Downloadable
            update_post_meta($variation_id, '_downloadable', 'no');
            update_post_meta($variation_id, '_download_limit', '');
            update_post_meta($variation_id, '_download_expiry', '');
            update_post_meta($variation_id, '_downloadable_files', '');

            // Stock handling
            update_post_meta($variation_id, '_manage_stock', 'no');
            delete_post_meta($variation_id, '_backorders');
            delete_post_meta($variation_id, '_stock');

            // Price handling
            _wc_save_product_price($variation_id, $variable_regular_price, '', '', '');

            // Description
            update_post_meta($variation_id, '_variation_description', wp_kses_post($variation['description']));

            // Save shipping class
            wp_set_object_terms($variation_id, '', 'product_shipping_class');

            // Tax class (parent's)
            delete_post_meta($variation_id, '_tax_class');

            { // Update Attributes
                $updated_attribute_keys = array();
                foreach ($attributes as $attribute) {
                    if ($attribute['is_variation']) {
                        $attribute_key = 'attribute_' . sanitize_title($attribute['name']);
                        $optionValue = self::getOptionValue($gtnProductVariantsResponse, $prp['Items'][0]['ProductVariantSku'], $attribute['name']);
                        if ($optionValue) {
                            $updated_attribute_keys[] = $attribute_key;
                            if ($attribute['is_taxonomy']) {
                                // Don't use wc_clean as it destroys sanitized characters
                                $value = sanitize_title(stripslashes($optionValue));
                            } else {
                                $value = wc_clean(stripslashes($optionValue));
                            }
                            update_post_meta($variation_id, $attribute_key, $value);

                            // Default attributes are created from first variant
                            if ($i === 0) {
                                $default_attributes[sanitize_title($attribute['name'])] = $value;
                            }
                        }
                    }
                }

                // Remove old taxonomies attributes so data is kept up to date - first get attribute key names
                $delete_attribute_keys = $wpdb->get_col($wpdb->prepare("SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'attribute_%%' AND meta_key NOT IN ( '" . implode("','", $updated_attribute_keys) . "' ) AND post_id = %d;", $variation_id));
                foreach ($delete_attribute_keys as $key) {
                    delete_post_meta($variation_id, $key);
                }
            }

            do_action('woocommerce_save_product_variation', $variation_id, $i);
        }

        // Update parent if variable so price sorting works and stays in sync with the cheapest child
        WC_Product_Variable::sync($post_id);

        // Update default attribute options setting
        update_post_meta($post_id, '_default_attributes', $default_attributes);
    }

}
