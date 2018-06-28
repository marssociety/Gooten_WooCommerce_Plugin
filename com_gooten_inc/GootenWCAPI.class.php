<?php
/**
 * Created by Vlado on 9-Sep-16.
 */

/**
 * Gooten API access.
 */
class GootenWCAPI
{
    /**
     * Returns Gooten API url based on current settings.
     *
     * @return string
     */
    public static function createBaseGootenAPIURL()
    {
        return 'https://api.print.io/api/'
        . 'v/' . GootenWCConstants::API_VERSION . '/'
        . 'source/' . GootenWCConstants::API_SOURCE . '/';
    }

    /**
     * Creates Gooten API URL for supplied endpoint.
     *
     * @param $endpoint The endpoint identifier.
     * @return string
     */
    private static function createEndpoint($endpoint)
    {
        // Do not access recipe Id via global $GootenWC as it might not be initialized
        $recipeId = get_option(GootenWCOptionNames::RECIPE_ID);
        if (!isset($recipeId) || !is_string($recipeId) || strlen($recipeId) < 10) {
            return false;
        }
        return self::createBaseGootenAPIURL() . $endpoint . '/?recipeId=' . $recipeId;
    }

    private static function executeCurl($method, $url, $postData = null)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60, // Use same timeout as in JS
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        return array('response' => $response, 'error' => $error);
    }

    /**
     * Posts report to Gooten API.
     *
     * @param $data The report data
     * @return bool True on success.
     */
    public static function postReport($data)
    {
        $url = self::createEndpoint('ErrorReport');
        if ($url === false) {
            return false;
        }

        $curlResult = self::executeCurl('POST', $url, $data);
        $response = $curlResult['response'];
        $error = $curlResult['error'];

        // Posting report should not post report on error
        if ($error) {
            return false;
        } else {
            return strlen($response) === 0;
        }
    }

    /**
     * Executes POST request to supplied URL with supplied request data.
     *
     * @param string $url The request URL.
     * @param $postData The request data.
     * @return mixed Non false value on success holding request result.
     */
    private static function executePOST($url, $postData)
    {
        if ($url === false) {
            return false;
        }

        $curlResult = self::executeCurl('POST', $url, $postData);
        $response = $curlResult['response'];
        $error = $curlResult['error'];

        if ($error) {
            GootenWCErrorReport::postAPIIssue($error, $response, $url, $postData);
            return false;
        } else {
            $arr = json_decode($response, true);
            if (isset($arr['Message']) && strpos($arr['Message'], 'Invalid recipeId') !== false) {
                return false;
            }
            if (isset($arr['HadError']) && $arr['HadError'] === true) {
                GootenWCErrorReport::postAPIIssue(null, $response, $url, $postData);
            }
            return $response;
        }
    }

    /**
     * Executes GET request to supplied URL.
     *
     * @param string $url The request URL.
     * @return mixed Non false value on success holding request result.
     */
    private static function executeGET($url)
    {
        if ($url === false) {
            return false;
        }

        $curlResult = self::executeCurl('GET', $url);
        $response = $curlResult['response'];
        $error = $curlResult['error'];

        if ($error) {
            GootenWCErrorReport::postAPIIssue($error, $response, $url, null);
            return false;
        } else {
            $arr = json_decode($response, true);
            if (isset($arr['Message']) && strpos($arr['Message'], 'Invalid recipeId') !== false) {
                return false;
            }
            if (isset($arr['HadError']) && $arr['HadError'] === true) {
                GootenWCErrorReport::postAPIIssue(null, $response, $url, null);
            }
            return $response;
        }
    }

    /**
     * Issues POST /priceestimate request for supplied WC cart.
     *
     * @param WC_Cart $wcCart The cart.
     * @return mixed Non false value on success holding request result.
     */
    public static function postPriceEstimateCart($wcCart)
    {
        $shipToAddress = GootenWCUtils::getWCCustomerShippingAddress();
        if (!GootenWCUtils::isFullAddress($shipToAddress)) {
            return false;
        }

        $items = array();
        {
            $cartItems = $wcCart->get_cart();
            foreach ($cartItems as $item) {
                $data = $item['data'];
                if (isset($data)) {
                    $sku = '';
                    if ($data->is_type('gooten_product')) {
                        $sku = $data->getProductVariantSKU();
                    } else if (isset($data->parent) && $data->parent->is_type('gooten_product')) {
                        $sku = get_post_meta($item['variation_id'], '_gooten_sku', true);
                    }
                    if (isset($sku) && !empty($sku)) {
                        $items[] = array(
                            'SKU' => $sku,
                            'ShipCarrierMethodId' => 1,
                            'Quantity' => absint($item['quantity'])
                        );
                    }
                }
            }
        }
        if (count($items) === 0) {
            return false;
        }

        $postData = json_encode(array(
            'Items' => $items,
            'Payment' => array(
                'CurrencyCode' => GootenWCUtils::getAPICurrencyCode()
            ),
            'ShipToAddress' => $shipToAddress
        ));

        return self::executePOST(self::createEndpoint('priceestimate'), $postData);
    }

    /**
     * Issues POST /orders request for supplied WC order.
     *
     * @param WC_Order $wcOrder The WC order.
     * @param bool $isOrderTestMode Boolean telling if order testing is enabled.
     * @param string $billingKey Partners billing key.
     * @return mixed Non false value on success holding request result.
     */
    public static function postOrders($wcOrder, $isOrderTestMode, $billingKey)
    {
        $items = array();
        if (isset($wcOrder)) {
            $_pf = new WC_Product_Factory();
            foreach ($wcOrder->get_items() as $item) {
                $product = $_pf->get_product($item['product_id']);
                if (isset($product) && $product instanceof WC_Product && $product->is_type('gooten_product')) {
                    if (GootenWCUtils::isGootenVariableProduct($product)) {
                        $sku = get_post_meta($item['variation_id'], '_gooten_sku', true);
                    } else if (method_exists($product, 'getProductVariantSKU')) {
                        $sku = $product->getProductVariantSKU();
                    }
                    if (!empty($sku)) {
                        $items[] = array(
                            'SKU' => $sku,
                            'ShipCarrierMethodId' => 1,
                            'Quantity' => absint($item['qty'])
                        );
                    }
                }
            }
        }
        if (count($items) === 0) {
            return false;
        }

        $postData = json_encode(array(
            'ShipToAddress' => GootenWCUtils::wcToGTNAddress($wcOrder->get_address()),
            'BillingAddress' => GootenWCUtils::wcToGTNAddress($wcOrder->get_address('billing')),
            'IsInTestMode' => $isOrderTestMode,
            'Items' => $items,
            'Payment' => array(
                'PartnerBillingKey' => $billingKey
            ),
            'Meta' => array(
                'Source' => 'gooten-woocommerce-plugin',
                'Version' => GootenWCUtils::getPluginVersion()
            )
        ));

        return self::executePOST(self::createEndpoint('orders'), $postData);
    }

    /**
     * Issues GET /productvariants request for supplied product ID.
     *
     * @param int $productId The product ID.
     * @return mixed Non false value on success holding request result.
     */
    public static function getProductVariants($productId)
    {
        $url = self::createEndpoint('productvariants')
            . '&currencyCode=' . GootenWCUtils::getAPICurrencyCode()
            . '&countryCode=' . GootenWCUtils::getAPICountryCode()
            . '&productId=' . $productId;
        return self::executeGET($url);
    }
}