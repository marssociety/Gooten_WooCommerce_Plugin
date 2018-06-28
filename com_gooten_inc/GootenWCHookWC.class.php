<?php
/**
 * Created by Vlado on 02-Jan-17.
 */

/**
 * Hooks for integration with WooCommerce.
 */
class GootenWCHookWC
{
    private $gootenWC;

    public function __construct($gootenWC)
    {
        $this->gootenWC = $gootenWC;
    }

    public function initHooks()
    {
        // WordPress Hook
        add_action('rest_api_init', array($this, 'wp_gooten_rest_api_init'));

        // WooCommerce hooks
        add_filter('product_type_selector', array($this, 'wc_gooten_product_type_selector'));
        add_action('woocommerce_system_status_report', array($this, 'wc_gooten_woocommerce_system_status_report'));
        add_action('woocommerce_process_product_meta_gooten_product', array($this, 'wc_gooten_woocommerce_process_product_meta_gooten_product'));
        add_filter('woocommerce_product_data_tabs', array($this, 'wc_gooten_woocommerce_product_data_tabs'));
        add_filter('woocommerce_product_data_panels', array($this, 'wc_gooten_woocommerce_product_data_panels'));
        add_filter('woocommerce_cart_needs_shipping_address', array($this, 'wc_gooten_woocommerce_cart_needs_shipping_address'));
        add_filter('woocommerce_cart_ready_to_calc_shipping', array($this, 'wc_gooten_woocommerce_cart_ready_to_calc_shipping'));
        add_filter('woocommerce_calculate_totals', array($this, 'wc_gooten_woocommerce_calculate_totals'));
        add_filter('wc_tax_enabled', array($this, 'wc_gooten_wc_tax_enabled'));
        add_filter('woocommerce_cart_taxes_total', array($this, 'wc_gooten_woocommerce_cart_taxes_total'), 10, 4);
        add_filter('woocommerce_payment_complete', array($this, 'wc_gooten_woocommerce_payment_complete'));
        add_filter('woocommerce_admin_order_data_after_order_details', array($this, 'wc_gooten_woocommerce_admin_order_data_after_order_details'));
        add_filter('woocommerce_product_class', array($this, 'wc_gooten_woocommerce_product_class'), 10, 4);
        add_filter('woocommerce_add_to_cart_handler', array($this, 'wc_gooten_woocommerce_add_to_cart_handler'), 10, 2);
        add_action('woocommerce_gooten_product_add_to_cart', array($this, 'wc_gooten_woocommerce_gooten_product_add_to_cart'));

        // Include products once WC plugin is loaded
        require_once(plugin_dir_path(__FILE__) . '/WC_Product_Gooten_Product.class.php');
        require_once(plugin_dir_path(__FILE__) . '/WC_Product_Gooten_Product_Simple.class.php');
    }

    function wp_gooten_rest_api_init()
    {
        register_rest_route('gooten/', '/setProductImages', array(
            'methods' => 'POST',
            'callback' => function (WP_REST_Request $request) {
                return GootenWCMediaUtils::setProductImages($request->get_param('postId'), $request->get_param('productImage'), $request->get_param('productGalleryImages'));
            },
            'permission_callback' => function () {
                return current_user_can('edit_others_posts');
            }
        ));
    }

    function wc_gooten_woocommerce_system_status_report()
    {
        $billingKeySet = strlen($this->gootenWC->getBillingKey()) > 10;
        $recipeIdSet = strlen($this->gootenWC->getRecipeId()) > 10;
        $recipeIdValid = preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', strtoupper($this->gootenWC->getRecipeId()));
        $productProfit = $this->gootenWC->getProductProfit();

        echo '<table class="wc_status_table widefat" cellspacing="0">';
        echo '<thead><tr><th colspan="3" data-export-label="Gooten Dropshipping for WooCommerce"><h2>Gooten Dropshipping for WooCommerce</h2></th></tr></thead>';
        echo '<tbody>';
        $this->print_report_item('Version', GootenWCUtils::getPluginVersion());
        $this->print_report_item('Recipe ID', $recipeIdSet ? ($recipeIdValid ? 'Valid' : 'Not valid') : 'Not set', $recipeIdValid && $recipeIdSet);
        $this->print_report_item('Billing configured', $billingKeySet ? 'Yes' : 'No', $billingKeySet);
        $this->print_report_item('Order testing', $this->gootenWC->isOrderTesting() ? 'Enabled' : 'Disabled');
        $this->print_report_item('Dynamic shipping', $this->gootenWC->isOrderWithDynamicShipping() ? 'Enabled' : 'Disabled');
        $this->print_report_item('Dynamic tax', $this->gootenWC->isOrderWithDynamicTax() ? 'Enabled' : 'Disabled');
        $this->print_report_item('Product tags strategy', $this->gootenWC->getProductTagsStrategy());
        $this->print_report_item('Product categories strategy', $this->gootenWC->getProductCategoriesStrategy());
        $this->print_report_item('Product profit', is_numeric($productProfit) ? $productProfit : 'Disabled');
        $this->print_report_item('API country code', GootenWCUtils::getAPICountryCode());
        $this->print_report_item('API currency code', GootenWCUtils::getAPICurrencyCode());
        echo '</tbody></table>';
    }

    function print_report_item($label, $value, $mark = null)
    {
        if ($mark === true) {
            $value = '<mark class="yes">' . $value . '</mark>';
        } else if ($mark === false) {
            $value = '<mark class="error">' . $value . '</mark>';
        }
        echo '<tr><td data-export-label="' . $label . '">' . $label . ':</td><td>&nbsp</td><td>' . $value . '</td></tr>';
    }

    function wc_gooten_woocommerce_product_class($classname, $product_type, $post_type, $product_id)
    {
        if ($product_type === 'gooten_product') {
            if (!GootenWCUtils::isGootenVariableProduct($product_id)) {
                return 'WC_Product_Gooten_Product_Simple';
            }
        }
        return $classname;
    }

    function wc_gooten_product_type_selector($product_types)
    {
        $product_types['gooten_product'] = 'Gooten product';
        return $product_types;
    }

    function wc_gooten_woocommerce_product_data_tabs($product_data_tabs)
    {
        $tabs = array();

        $tabs['gooten_product_config'] = array(
            'label' => 'Choose product',
            'target' => 'gooten_product_data',
            'class' => array('show_if_gooten_product', 'hide_if_simple', 'hide_if_grouped', 'hide_if_external', 'hide_if_variable'),
        );
        foreach ($product_data_tabs as $key => $tab) {
            $tabs[$key] = $tab;
        }
        $tabs['general']['class'][] = 'hide_if_gooten_product';
        $tabs['attribute']['class'][] = 'hide_if_gooten_product';
        if ($this->gootenWC->isOrderWithDynamicShipping()) {
            $tabs['shipping']['class'][] = 'hide_if_gooten_product';
        }
        $tabs['variations']['class'][] = 'hide_if_gooten_product';

        return $tabs;
    }

    function wc_gooten_woocommerce_product_data_panels()
    {
        $prpName = '';
        $product = (new WC_Product_Factory())->get_product();
        if (isset($product) && $product->is_type('gooten_product')) {
            if (!GootenWCUtils::isGootenVariableProduct($product)) {
                echo '<div id="gooten_product_data" class="panel woocommerce_options_panel hidden">';
                echo '<p>This product was crated with older version of Gooten plugin. Format of this product has became obsolete as new Gooten products have support for variations. It is recommended to permanently delete and recreate this product.</p>';
                echo '<p>Shop visitors are still able to purchase this product.</p>';
                echo '</div>';
                return;
            }
            $prpName = $product->getProductName();
            $variations = $product->getAllVariations();
        }

        // Pass variation data in js
        if (isset($variations)) {
            $variantData = array();
            foreach ($variations as $variation) {
                $variantData[] = array(
                    'variablePostId' => $variation['variationId'],
                    'imageSrc' => $variation['imageSrc'],
                    'isEnabledChecked' => $variation['isEnabled'] ? 'checked="checked"' : '',
                    'sku' => $variation['sku'],
                    'price' => $variation['price'],
                    'description' => isset($variation['description']) ? esc_textarea(GootenWCUtils::wpautop_inverse($variation['description'])) : '',
                    'gootenSku' => $variation['gootenSku'],
                    'gootenPrice' => $variation['gootenPrice']
                );
            }
            echo '<script type="text/javascript">GTN_WC.variantData = ' . json_encode($variantData) . ';</script>';
        }

        ?>
        <div id="gooten_product_data" class="panel woocommerce_options_panel hidden">
            <div id="gtn-variant-template"
                 class="hidden"><?php echo htmlentities($this->create_variant_template()) ?></div>
            <input type="hidden" id="_gooten_prp_json" name="_gooten_prp_json">

            <div class="options_group">
                <p class="form-field">
                    <label for="gooten_prp_select">Product</label>
                    <input type="text" id="gooten_prp_select" class="gtn-w50p" value="<?php echo $prpName ?>"
                           data-placeholder="Search for a product..."/>
                </p>
            </div>
            <div class="options_group">
                <div class="gtn-w100p" id="gooten_variants_holder"></div>
                <div class="gtn-w100p hidden" id="gooten_undefined_variants">
                    <h3>Undefined variants</h3>
                </div>
            </div>
        </div>
    <?php
    }

    function create_variant_template()
    {
        $currSymbol = GootenWCUtils::getAPICurrencySymbol();
        $template = '<div class="gtn-variant" data-gtn-sku="{{gootenSku}}">'

            . '<div class="gtn-variant-header"><div><strong>{{variantTitle}}</strong><div class="gtn-variant-selector" data-sku="{{gootenSku}}"></div><a href="#" class="gtn-delete-variation" data-id="{{variablePostId}}">Remove</a></div><div class="gtn-variant-header-message"><span class="gtn-variant-warning hidden">This variant is no longer available on Gooten.</span><span class="gtn-variant-not-available hidden">Variant with specified options is not defined on Gooten admin.</span></div></div>'
            . '<div class="gtn-variant-attributes">'

            // Left - Image
            . '<div class="gtn-variant-left"><img src="{{imageSrc}}" alt="Variant Preview" class="gtn-w100p"></div>'

            // Right
            . '<div class="gtn-variant-right">'
            // SKU & enabled
            . '<div class="gtn-variant-row">'
            . '<div class="gtn-variant-ib gtn-w70p"><label for="{{inputNamePrefix}}[sku]">SKU: </label><input type="text" value="{{sku}}" id="{{inputNamePrefix}}[sku]" name="{{inputNamePrefix}}[sku]"/>' . wc_help_tip('SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.') . '</div>'
            . '<div class="gtn-variant-ib gtn-w30p"><label for="{{inputNamePrefix}}[isEnabled]"><input type="checkbox" value="true" name="{{inputNamePrefix}}[isEnabled]" id="{{inputNamePrefix}}[isEnabled]" {{isEnabledChecked}} />&nbsp;Enabled</label></div>'
            . '</div>'
            // Price
            . '<div class="gtn-variant-row">'
            . '<div class="gtn-variant-ib gtn-w70p"><label for="{{inputNamePrefix}}[price]">Customer price (' . $currSymbol . '): </label><input type="text" value="{{price}}" name="{{inputNamePrefix}}[price]" id="{{inputNamePrefix}}[price]" />' . wc_help_tip('Defines how much will you charge customer for this product.') . '</div>'
            . '<div class="gtn-variant-ib gtn-w30p"><label class="gtn-wauto">Gooten price (' . $currSymbol . '): </label>{{gootenPrice}} ' . wc_help_tip('Gooten cost is production cost for this product.') . '</div>'
            . '</div>'
            // Description
            . '<div class="gtn-variant-row"><label for="{{inputNamePrefix}}[description]">Description: </label><textarea class="gtn-w70p" name="{{inputNamePrefix}}[description]" id="{{inputNamePrefix}}[description]" rows="4">{{description}}</textarea></div>'
            // End Right
            . '</div>'

            . '<input type="hidden" value="{{gootenSku}}" name="{{inputNamePrefix}}[productSku]" class="productSku"/>'
            . '<input type="hidden" value="{{variablePostId}}" name="{{inputNamePrefix}}[variablePostId]"/>'
            . '<div class="clear" ></div>'
            . '</div>'
            . '</div>';
        return $template;
    }

    function wc_gooten_woocommerce_add_to_cart_handler($product_type, $product)
    {
        if ($product_type === 'gooten_product') {
            return GootenWCUtils::isGootenVariableProduct($product) ? 'variable' : 'simple';
        }
        return $product_type;
    }

    function wc_gooten_woocommerce_gooten_product_add_to_cart()
    {
        global $product;
        if (GootenWCUtils::isGootenVariableProduct($product)) {
            do_action('woocommerce_variable_add_to_cart');
        } else {
            do_action('woocommerce_simple_add_to_cart');
        }
    }

    function wc_gooten_woocommerce_process_product_meta_gooten_product($post_id)
    {
        $variants = $_POST['_gooten_variants'];

        if (isset($_POST['_gooten_prp_json']) && strlen($_POST['_gooten_prp_json']) > 0) {
            $temp = base64_decode($_POST['_gooten_prp_json']);
            $temp = utf8_encode($temp);
            $prpItems = json_decode($temp, true);
        }

        if (!isset($prpItems) || !isset($variants)) {
            WC_Admin_Meta_Boxes::add_error('Ooops... An error has occurred while saving product, please try again later.');
            return;
        }

        { // Gooten product attributes
            update_post_meta($post_id, '_gooten_version', GootenWCUtils::getPluginVersion());
            update_post_meta($post_id, '_gooten_prp_name', $prpItems[0]['Name']);
        }

        { // WooCommerce - enforce these values for gooten product
            update_post_meta($post_id, '_downloadable', 'no');
            update_post_meta($post_id, '_virtual', 'no');
            update_post_meta($post_id, '_tax_status', 'taxable');
            delete_post_meta($post_id, '_tax_class');
            update_post_meta($post_id, '_regular_price', '');
            update_post_meta($post_id, '_sale_price', '');
            update_post_meta($post_id, '_sale_price_dates_from', '');
            update_post_meta($post_id, '_sale_price_dates_to', '');
            update_post_meta($post_id, '_sold_individually', '');
            update_post_meta($post_id, '_manage_stock', 'no');
            update_post_meta($post_id, '_backorders', 'no');
            update_post_meta($post_id, '_stock', '');
            wc_update_product_stock_status($post_id, 'instock');
        }

        { // WooCommerce - Save variations
            GootenWCProductVariationUtils::saveVariations($post_id, $variants, $prpItems);
        }
    }

    function wc_gooten_woocommerce_cart_needs_shipping_address($needs_shipping_address)
    {
        return $needs_shipping_address || GootenWCUtils::cartHasGootenProduct();
    }

    function wc_gooten_woocommerce_cart_ready_to_calc_shipping($show_shipping)
    {
        if ($this->gootenWC->isOrderWithDynamicShipping()) {
            return false;
        }
        return $show_shipping;
    }

    function wc_gooten_woocommerce_calculate_totals($cart)
    {
        if (GootenWCUtils::cartHasGootenProduct($cart)) {
            $isDynamicTax = $this->gootenWC->isOrderWithDynamicTax();
            $isDynamicShipping = $this->gootenWC->isOrderWithDynamicShipping();
            if ($isDynamicTax || $isDynamicShipping) {
                $pe = GootenWCUtils::getPriceEstimateForCart($cart);

                // Include tax to order total
                if ($isDynamicTax) {
                    $tax = $pe['tax_total'];
                    $cart->gooten_tax = $tax;
                    $cart->total += $tax;
                    $cart->tax_total += $tax;

                    // This is tax display hack when tax is set to display as 'itemized'
                    if ('itemized' === get_option('woocommerce_tax_total_display')) {
                        $cart->add_fee(__('Tax', 'woocommerce'), $tax);
                    }
                }

                // Include shipping to order total
                if ($isDynamicShipping) {
                    $shipping = $pe['shipping_total'];
                    $cart->gooten_shipping = $shipping;
                    $cart->shipping_total += $shipping;
                    $cart->total += $shipping;

                    // This is shipping display hack
                    $cart->add_fee(__('Shipping', 'woocommerce'), $shipping);
                }
            }
        }
    }

    function wc_gooten_wc_tax_enabled($isEnabled)
    {
        return $isEnabled || $this->gootenWC->isOrderWithDynamicTax();
    }

    function wc_gooten_woocommerce_cart_taxes_total($total, $compound, $display, $cart)
    {
        return $total + (isset($cart->gooten_tax) ? $cart->gooten_tax : 0);
    }

    function wc_gooten_woocommerce_payment_complete($orderId)
    {
        $order = new WC_Order($orderId);
        if (GootenWCUtils::orderHasGootenProduct($order)) {
            $response = GootenWCAPI::postOrders($order, $this->gootenWC->isOrderTesting(), $this->gootenWC->getBillingKey());
            GootenWCUtils::markOrderAsSubmittedToGooten($order, $response);
        }
    }

    function wc_gooten_woocommerce_admin_order_data_after_order_details($order)
    {
        if (GootenWCUtils::orderHasGootenProduct($order)) {
            $id = GootenWCUtils::getGootenOrderId($order);
            $isSubmitted = isset($id) && strlen($id) > 0;
            echo '<p class="form-field form-field-wide">';
            echo '<div id="gooten-order-status" >Gooten status: <b>' . ($isSubmitted ? 'Submitted' : 'Not submitted') . '</b></div>';
            if ($isSubmitted) {
                echo '<div id="gooten-order-id" >Gooten order id: <b> ' . $id . '</b></div>';
            }
            echo '</p>';
        }
    }
}