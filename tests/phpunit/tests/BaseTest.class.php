<?php

/**
 * Created by Vlado on 21-Dec-16.
 */
class BaseTest extends WP_UnitTestCase
{
    protected $VALID_RECIPE_ID = '1AB4E1F8-DBCB-4D6C-829F-EE0B2A60C0B3';
    protected $GootenWC;

    public function setUp()
    {
        global $GootenWC;
        $this->GootenWC = $GootenWC;
    }

    public function getFunctionOutput($args, $callback)
    {
        ob_start();
        call_user_func_array($callback, $args);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    public function assertMethodsHaveOutput($object, $methodNames)
    {
        foreach ($methodNames as $methodName) {
            $output = $this->getFunctionOutput(array($object, $methodName), function ($object, $methodName) {
                call_user_func(array($object, $methodName));
            });
            $this->assertNotEmpty($output);
        }
    }

    public function getMethodByReflection($className, $methodName)
    {
        $reflection = new \ReflectionClass($className);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    public function setRecipeId($recipeId)
    {
        update_option(GootenWCOptionNames::RECIPE_ID, $recipeId);
    }

    public function createSimpleProduct()
    {
        $productId = $this->addWCProduct(array(
            'product_type' => 'simple',
            '_sku' => 'test-' . uniqid(),
            '_price' => '10.0'));
        $_pf = new WC_Product_Factory();
        $product = $_pf->get_product($productId);
        return $product;
    }

    public function createGootenProductPost()
    {
        $id = $this->addWCProduct(array('product_type' => 'gooten_product'));
        return $id;
    }

    public function createGootenVariableProduct()
    {
        $id = $this->addWCProduct(array('product_type' => 'gooten_product'));
        $variationId = $this->addWCVariation($id, array(
            '_price' => '15.5',
            '_sku' => 'test-' . uniqid(),
            '_gooten_sku' => 'woocommerce-atest-sku-1',
            '_gooten_price' => '13.5'));

        $variation = new WC_Product_Variation($variationId, array('parent_id' => $id));

        return $variation;
    }

    public function createGootenVariableProductInvalidSKU()
    {
        $id = $this->addWCProduct(array('product_type' => 'gooten_product'));
        $variationId = $this->addWCVariation($id, array(
            '_price' => '15.5',
            '_sku' => 'test-' . uniqid(),
            '_gooten_sku' => 'woocommerce-atest-sku-1-invalid-sku',
            '_gooten_price' => '13.5'));

        $variation = new WC_Product_Variation($variationId, array('parent_id' => $id));

        return $variation;
    }

    public function createCartMock($products = array())
    {
        $cartItems = array();
        foreach ($products as $product) {
            $item = array('data' => $product, 'quantity' => 1);
            if (isset($product->variation_id)) {
                $item['variation_id'] = $product->variation_id;
            }
            $cartItems[] = $item;
        }

        $cart = $this->getMockBuilder(WC_Cart::class)
            ->setMethods(array('get_cart'))
            ->getMock();
        $cart->method('get_cart')->willReturn($cartItems);

        return $cart;
    }

    public function createOrder()
    {
        $order_date = new DateTime();
        $order_data = array(
            'post_name' => 'order-' . date_format($order_date, 'M-d-Y-hi-a'),
            'post_type' => 'shop_order',
            'post_title' => 'Order &ndash; ' . date_format($order_date, 'F d, Y @ h:i A'),
            'post_status' => 'wc-completed',
            'ping_status' => 'closed',
            'post_excerpt' => 'Testing order',
            'post_author' => 1,
            'post_date' => date_format($order_date, 'Y-m-d H:i:s e'),
            'comment_status' => 'open'
        );
        $order_id = wp_insert_post($order_data, true);
        $wcOrder = new WC_Order($order_id);

        return $wcOrder;
    }

    public function setAddressesToOrder($wcOrder, $customer)
    {
        $wcOrder->billing_first_name = $wcOrder->shipping_first_name = 'Test';
        $wcOrder->billing_last_name = $wcOrder->shipping_last_name = 'Test';
        $wcOrder->billing_company = $wcOrder->shipping_company = 'GootenTest';
        $wcOrder->billing_address_1 = $wcOrder->shipping_address_1 = $customer->get_shipping_address();
        $wcOrder->billing_address_2 = $wcOrder->shipping_address_2 = $customer->get_shipping_address_2();
        $wcOrder->billing_city = $wcOrder->shipping_city = $customer->get_shipping_city();
        $wcOrder->billing_state = $wcOrder->shipping_state = $customer->get_shipping_state();
        $wcOrder->billing_postcode = $wcOrder->shipping_postcode = $customer->get_shipping_postcode();
        $wcOrder->billing_country = $wcOrder->shipping_country = $customer->get_shipping_country();
        $wcOrder->billing_email = $wcOrder->shipping_email = 'test@test.test';
        $wcOrder->billing_phone = $wcOrder->shipping_phone = '1111111111111111';
    }

    private function addWCProduct($params = array())
    {
        $postID = wp_insert_post(array(
            'post_title' => 'Test Product',
            'post_content' => 'Test Product',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'product',
            'menu_order' => 0
        ));
        wp_set_object_terms($postID, self::getArrayValue($params, 'product_type', 'gooten_product'), 'product_type');
        $this->setPostMeta($postID, $params);
        return $postID;
    }

    private function addWCVariation($parentID, $params = array())
    {
        $postID = wp_insert_post(array(
            'post_title' => 'Test Variation',
            'post_content' => 'Test Variation',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_parent' => $parentID,
            'post_type' => 'product_variation',
            'menu_order' => 0
        ));
        $this->setPostMeta($postID, $params);
        update_post_meta($postID, '_gooten_sku', $this->getArrayValue($params, '_gooten_sku', ''));
        update_post_meta($postID, '_gooten_price', $this->getArrayValue($params, '_gooten_price', ''));
        return $postID;
    }

    private function setPostMeta($postID, $params)
    {
        update_post_meta($postID, '_price', $this->getArrayValue($params, '_price', ''));
        update_post_meta($postID, '_sku', $this->getArrayValue($params, '_sku', ''));
        update_post_meta($postID, '_visibility', 'visible');
        update_post_meta($postID, 'total_sales', '0');
        update_post_meta($postID, '_downloadable', 'no');
        update_post_meta($postID, '_virtual', 'no');
        update_post_meta($postID, '_regular_price', '');
        update_post_meta($postID, '_sale_price', '');
        update_post_meta($postID, '_sale_price_dates_from', '');
        update_post_meta($postID, '_sale_price_dates_to', '');
        update_post_meta($postID, '_purchase_note', '');
        update_post_meta($postID, '_featured', 'no');
        update_post_meta($postID, '_weight', '');
        update_post_meta($postID, '_length', '');
        update_post_meta($postID, '_width', '');
        update_post_meta($postID, '_height', '');
        update_post_meta($postID, '_sold_individually', '');
        update_post_meta($postID, '_stock_status', 'instock');
        update_post_meta($postID, '_manage_stock', 'no');
        update_post_meta($postID, '_backorders', 'no');
        update_post_meta($postID, '_stock', '');
    }

    private function getArrayValue($array, $key, $default)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }

    private function getMockCustomer()
    {
        $customer = $this->getMockBuilder(WC_Customer::class)
            ->setConstructorArgs(array(null))
            ->setMethods(array('get_shipping_address', 'get_shipping_address_2',
                'get_shipping_postcode', 'get_shipping_state', 'get_shipping_country',
                'get_shipping_city'))
            ->getMock();
        return $customer;
    }

    public function createRealCustomer()
    {
        $customer = $this->getMockCustomer();
        $customer->method('get_shipping_address')->willReturn('3655 Las Vegas Blvd S');
        $customer->method('get_shipping_address_2')->willReturn('');
        $customer->method('get_shipping_postcode')->willReturn('89109');
        $customer->method('get_shipping_state')->willReturn('NV');
        $customer->method('get_shipping_country')->willReturn('US');
        $customer->method('get_shipping_city')->willReturn('Las Vegas');
        return $customer;
    }

    public function createPartialCustomer()
    {
        $customer = $this->getMockCustomer();
        $customer->method('get_shipping_address')->willReturn(null);
        $customer->method('get_shipping_address_2')->willReturn(null);
        $customer->method('get_shipping_postcode')->willReturn('89109');
        $customer->method('get_shipping_state')->willReturn('NV');
        $customer->method('get_shipping_country')->willReturn('US');
        $customer->method('get_shipping_city')->willReturn('Las Vegas');
        return $customer;
    }

}
