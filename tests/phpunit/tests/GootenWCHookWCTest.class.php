<?php
/**
 * Created by Vlado on 3-Jan-17.
 */

require_once('BaseTest.class.php');

class GootenWCHookWCTest extends BaseTest
{
    private $hook;

    public function setUp()
    {
        parent::setUp();
        $this->hook = new GootenWCHookWC($this->GootenWC);
    }

    public function testWCGootenProductTypeSelector()
    {
        $result = $this->hook->wc_gooten_product_type_selector(array());
        $this->assertArrayHasKey('gooten_product', $result);
    }

    public function testWCGootenWoocommerceProductDataTabs()
    {
        $result = $this->hook->wc_gooten_woocommerce_product_data_tabs(array("shipping" => array("class" => ""), "general" => array("class" => "")));
        $this->assertArrayHasKey('gooten_product_config', $result);
        $this->assertEquals($result['gooten_product_config']['target'], 'gooten_product_data');
    }

    public function testCartNeedsShippingHook()
    {
        global $woocommerce;

        { // Empty cart
            $woocommerce->cart = $this->createCartMock();
            $this->assertFalse($this->hook->wc_gooten_woocommerce_cart_needs_shipping_address(false));
            $this->assertTrue($this->hook->wc_gooten_woocommerce_cart_needs_shipping_address(true));
        }
        { // Cart with Gooten product
            $woocommerce->cart = $this->createCartMock(array($this->createGootenVariableProduct(), $this->createSimpleProduct()));
            $this->assertTrue($this->hook->wc_gooten_woocommerce_cart_needs_shipping_address(false));
        }
    }

    public function testCartCalculateTotalsHook_withGootenProducts()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID);
        WC()->customer = $this->createRealCustomer();


        { // With Gooten shipping and tax
            update_option('gooten_order_dynamic_shipping', '1');
            update_option('gooten_order_dynamic_tax', '1');

            $cart = $this->createCartMock(array($this->createGootenVariableProduct()));
            $this->hook->wc_gooten_woocommerce_calculate_totals($cart);

            $this->assertGreaterThan(0, $cart->gooten_shipping);
            $this->assertGreaterThan(0, $cart->shipping_total);
            $this->assertGreaterThan(0, $cart->total);
        }

        { // Without Gooten shipping and tax
            update_option('gooten_order_dynamic_shipping', '');
            update_option('gooten_order_dynamic_tax', '');

            $cart = $this->createCartMock(array($this->createGootenVariableProduct()));
            $this->hook->wc_gooten_woocommerce_calculate_totals($cart);

            $this->assertEquals(null, $cart->gooten_shipping);
            $this->assertEquals(null, $cart->gooten_tax);
        }
    }

    public function testCartCalculateTotalsHook_noGootenProducts()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID);
        $cart = $this->createCartMock(array($this->createSimpleProduct()));
        WC()->customer = $this->createRealCustomer();
        $this->hook->wc_gooten_woocommerce_calculate_totals($cart);

        $this->assertEquals(0, $cart->gooten_shipping);
        $this->assertEquals(0, $cart->shipping_total);
        $this->assertEquals(0, $cart->total);
    }

    public function testPaymentCompleteHook()
    {
        // TODO test wc_gooten_woocommerce_payment_complete
    }

    public function testOrderData_submitted()
    {
        $wcOrder = $this->createOrder();
        $wcOrder->add_product($this->createSimpleProduct());
        $wcOrder->add_product($this->createGootenVariableProduct());

        $this->assertTrue(GootenWCUtils::markOrderAsSubmittedToGooten($wcOrder, '{"Id":"TestId"}'));

        $html = $this->getFunctionOutput(array($this->hook, $wcOrder), function ($hook, $wcOrder) {
            $hook->wc_gooten_woocommerce_admin_order_data_after_order_details($wcOrder);
        });

        $this->assertTrue(strpos($html, 'Submitted') !== false);
        $this->assertTrue(strpos($html, 'TestId') !== false);
    }

    public function testOrderData_notSubmitted()
    {
        $wcOrder = $this->createOrder();
        $wcOrder->add_product($this->createSimpleProduct());
        $wcOrder->add_product($this->createGootenVariableProduct());

        $html = $this->getFunctionOutput(array($this->hook, $wcOrder), function ($hook, $wcOrder) {
            $hook->wc_gooten_woocommerce_admin_order_data_after_order_details($wcOrder);
        });

        $this->assertTrue(strpos($html, 'Not submitted') !== false);
        $this->assertFalse(strpos($html, 'Gooten order id'));
    }

    public function testOrderData_noGootenProducts()
    {
        $wcOrder = $this->createOrder();
        $wcOrder->add_product($this->createSimpleProduct());

        $html = $this->getFunctionOutput(array($this->hook, $wcOrder), function ($hook, $wcOrder) {
            $hook->wc_gooten_woocommerce_admin_order_data_after_order_details($wcOrder);
        });

        $this->assertFalse(strpos($html, 'Not submitted'));
        $this->assertFalse(strpos($html, 'Submitted'));
        $this->assertFalse(strpos($html, 'Gooten order id'));
    }
}