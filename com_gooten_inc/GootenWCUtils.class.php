<?php
/**
 * Created by Vlado on 9-Sep-16.
 */

/**
 * Holds utilities needed by Gooten plugin.
 */
class GootenWCUtils
{

    /**
     * Gets plugin version by parsing Wordpress plugin meta data.
     *
     * @return string The plugin version.
     */
    public static function getPluginVersion()
    {
        $content = $file = file_get_contents(plugin_dir_path(__FILE__) . '../gooten-woo.php', true);
        $start = strpos($content, 'Version:') + 8;
        $end = strpos($content, PHP_EOL, $start);
        return trim(substr($content, $start, $end - $start));
    }

    /**
     * Compares two supplied plugin versions.
     *
     * @param string $aVersion First plugin version.
     * @param string $bVersion Second plugin version.
     * @return int Positive number if first plugin version is later then second,
     *         or negative number if second plugin version is later then first.
     *         If both versions are the same function returns {@code 0}.
     */
    public static function compareVersion($aVersion, $bVersion)
    {
        $aVer = explode('.', $aVersion);
        $bVer = explode('.', $bVersion);
        for ($i = 0; $i < 3; $i++) {
            $a = absint($aVer[$i]);
            $b = absint($bVer[$i]);
            if ($a > $b || $a < $b) {
                return $a - $b;
            }
        }
        return 0;
    }

    /**
     * Gets country coded used for Gooten API requests.
     *
     * @return string
     */
    public static function getAPICountryCode()
    {
        $baseLocation = wc_get_base_location();
        if ($baseLocation && array_key_exists('country', $baseLocation)) {
            $code = $baseLocation['country'];
        }
        if (!isset($code) || !is_string($code) || strlen($code) == 0) {
            $code = 'US'; // Default to US
        }
        return $code;
    }

    /**
     * Gets currency coded used for Gooten API requests.
     *
     * @return string
     */
    public static function getAPICurrencyCode()
    {
        $code = get_woocommerce_currency();
        if (!isset($code) || !is_string($code) || strlen($code) == 0) {
            $code = 'USD'; // Default to USD
        }
        return $code;
    }

    /**
     * Gets currency symbol appropriate for currency code returned by getAPICurrencyCode()
     *
     * @return string
     */
    public static function getAPICurrencySymbol()
    {
        $symbol = get_woocommerce_currency_symbol(self::getAPICurrencyCode());
        if (!isset($symbol) || !is_string($symbol) || strlen($symbol) == 0) {
            $symbol = slef::getAPICurrencyCode(); // Default to currency code
        }
        return $symbol;
    }

    /**
     * Returns true if current WooCommerce cart has one or more gooten products.
     *
     * @param WC_Cart $cart The WooCommerce cart object. If not supplied, global cart object will be used.
     * @return bool
     */
    public static function cartHasGootenProduct($cart = null)
    {
        if (!isset($cart)) {
            global $woocommerce;
            $cart = $woocommerce->cart;
        }
        $items = $cart->get_cart();
        foreach ($items as $item) {
            $data = $item['data'];
            if (isset($data)) {
                // Old (non variable) Gooten product
                if ($data->is_type('gooten_product')) {
                    return true;
                }
                // New (variable) Gooten product
                if (isset($data->parent) && $data->parent->is_type('gooten_product')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns true if supplied WooCommerce order has one or more gooten products.
     *
     * @param WC_Order $wcOrder The WooCommerce order object.
     * @return bool
     */
    public static function orderHasGootenProduct($wcOrder)
    {
        if (isset($wcOrder)) {
            $_pf = new WC_Product_Factory();
            foreach ($wcOrder->get_items() as $item) {
                $product = $_pf->get_product($item['product_id']);
                if (isset($product) && $product instanceof WC_Product && $product->is_type('gooten_product')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Checks if product is Gooten variable product or not.
     *
     * @param int|WC_Product $product The ID of product or WC_Product itself.
     * @return bool True if supplied product is Gooten variable product.
     */
    public static function isGootenVariableProduct($product)
    {
        if (is_numeric($product)) {
            $version = get_post_meta($product, '_gooten_version', true);
            return isset($version) && self::compareVersion($version, '1.2.0') >= 0;
        } else if ($product instanceof WC_Product) {
            return $product instanceof WC_Product_Gooten_Product;
        }
        return false;
    }

    /**
     * Returns ID of Gooten order for supplied WooCommerce order (if any).
     *
     * @param WC_Order $wcOrder The WooCommerce order object.
     * @return string
     */
    public static function getGootenOrderId($wcOrder)
    {
        return get_post_meta($wcOrder->id, '_gooten_order_id', true);
    }

    /**
     * Marks supplied WooCommerce order as submitted to Gooten.
     *
     * @param WC_Order $wcOrder The WooCommerce order object.
     * @param array $postOrderResponse JSON decoded POST /orders response.
     * @return bool True on success, false on failure.
     */
    public static function markOrderAsSubmittedToGooten($wcOrder, $postOrderResponse)
    {
        if (isset($postOrderResponse) && isset($wcOrder)) {
            $arr = json_decode($postOrderResponse, true);
            if (isset($arr) && isset($arr['Id']) && strlen($arr['Id']) > 0) {
                return update_post_meta($wcOrder->id, '_gooten_order_id', $arr['Id']) !== false;
            }
        }
        return false;
    }

    /**
     * Converts address from WooCommerce's to Gooten's notation.
     *
     * @param $wcAddress
     * @return array
     */
    public static function wcToGTNAddress($wcAddress)
    {
        return array(
            'FirstName' => $wcAddress['first_name'],
            'LastName' => $wcAddress['last_name'],
            'IsBusinessAddress' => isset($wcAddress['company']),
            'Line1' => $wcAddress['address_1'],
            'Line2' => $wcAddress['address_2'],
            'PostalCode' => $wcAddress['postcode'],
            'State' => $wcAddress['state'],
            'CountryCode' => $wcAddress['country'],
            'City' => $wcAddress['city'],
            'Email' => $wcAddress['email'],
            'Phone' => $wcAddress['phone']
        );
    }

    /**
     * Gets customers shipping address in Gooten's notation.
     *
     * @return array
     */
    public static function getWCCustomerShippingAddress()
    {
        $customer = WC()->customer;
        return array(
            'Line1' => $customer->get_shipping_address(),
            'Line2' => $customer->get_shipping_address_2(),
            'PostalCode' => $customer->get_shipping_postcode(),
            'State' => $customer->get_shipping_state(),
            'CountryCode' => $customer->get_shipping_country(),
            'City' => $customer->get_shipping_city()
        );
    }

    /**
     * Check if supplied address is fully entered (has all mandatory fields).
     *
     * @param array $address The address to check
     * @return bool True if address is fully entered, false otherwise.
     */
    public static function isFullAddress($address)
    {
        return
            isset($address['Line1']) && strlen(trim($address['Line1'])) > 0
            && isset($address['PostalCode']) && strlen(trim($address['PostalCode'])) > 0
            && isset($address['CountryCode']) && strlen(trim($address['CountryCode'])) > 0
            && isset($address['City']) && strlen(trim($address['City'])) > 0;
    }

    /**
     * Issues price estimate request for supplied WooCommerce cart.
     *
     * @param WC_Cart $cart The cart.
     * @return array Associative array holding info about shipping and tax totals.
     */
    public static function getPriceEstimateForCart($cart)
    {
        $response = GootenWCAPI::postPriceEstimateCart($cart);
        if ($response) {
            $arr = json_decode($response, true);
            if (!isset($arr['HadError']) || $arr['HadError'] === false) {
                return array(
                    'shipping_total' => (double)$arr['Shipping']['Price'],
                    'tax_total' => (double)$arr['Tax']['Price']
                );
            }
        }
        return array(
            'shipping_total' => 0,
            'tax_total' => 0
        );
    }

    /**
     * Inverse function of wpautop.
     *
     * @param $text The text formatted with wpautop.
     * @return string The text before wpautop format.
     */
    public static function wpautop_inverse($text)
    {
        $text = str_ireplace(array("<p>"), "", $text);
        $text = str_ireplace(array("</p>"), "\r\n", $text);
        $text = str_ireplace(array("<br />","<br>","<br/>"), "", $text);
        return rtrim($text);
    }
}
