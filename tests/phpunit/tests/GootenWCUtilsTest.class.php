<?php
/**
 * Created by Boro on 17-Oct-16.
 */

require_once('BaseTest.class.php');

class GootenWCUtilsTest extends BaseTest
{

    public function setUp()
    {
        parent::setUp();

        update_option('woocommerce_default_country', 'US:NY');
        update_option('woocommerce_currency', 'USD');
    }

    public function testGetPluginVersion()
    {
        $this->assertEquals(preg_match('[(\d+\.)(\d+\.)(\d+)]', GootenWCUtils::getPluginVersion()), 1);
    }

    public function testCompareVersion()
    {
        $this->assertEquals(0, GootenWCUtils::compareVersion('1.1.1', '1.1.1'));
        $this->assertEquals(0, GootenWCUtils::compareVersion('0.1.1', '0.1.1'));
        $this->assertGreaterThan(0, GootenWCUtils::compareVersion('0.1.2', '0.1.1'));
        $this->assertGreaterThan(0, GootenWCUtils::compareVersion('0.2.1', '0.1.1'));
        $this->assertGreaterThan(0, GootenWCUtils::compareVersion('1.1.1', '0.1.1'));
        $this->assertLessThan(0, GootenWCUtils::compareVersion('0.1.1', '0.1.2'));
    }

    public function testGetAPICountryCode()
    {
        update_option('woocommerce_default_country', 'US:NY');
        $this->assertEquals('US', GootenWCUtils::getAPICountryCode());

        update_option('woocommerce_default_country', ':');
        $this->assertEquals('US', GootenWCUtils::getAPICountryCode());

        delete_option('woocommerce_default_country');
        $this->assertEquals('US', GootenWCUtils::getAPICountryCode());
    }

    public function testGetAPICurrencyCode()
    {
        update_option('woocommerce_currency', 'USD');
        $this->assertEquals('USD', GootenWCUtils::getAPICurrencyCode());

        update_option('woocommerce_currency', '');
        $this->assertEquals('USD', GootenWCUtils::getAPICurrencyCode());

        delete_option('woocommerce_currency');
        $this->assertEquals('USD', GootenWCUtils::getAPICurrencyCode());
    }

    public function testGetAPICurrencySymbol()
    {
        update_option('woocommerce_currency', 'USD');
        $this->assertEquals('&#36;', GootenWCUtils::getAPICurrencySymbol());

        update_option('woocommerce_currency', '');
        $this->assertEquals('&#36;', GootenWCUtils::getAPICurrencySymbol());

        delete_option('woocommerce_currency');
        $this->assertEquals('&#36;', GootenWCUtils::getAPICurrencySymbol());
    }

    public function testCartHasGootenProduct()
    {
        $productSimple = $this->createSimpleProduct();
        $productGooten = $this->createGootenVariableProduct();

        { // Empty cart
            $cart = $this->createCartMock();
            $this->assertFalse(GootenWCUtils::cartHasGootenProduct($cart));
        }
        { // Cart with non Gooten product
            $cart = $this->createCartMock(array($productSimple));
            $this->assertFalse(GootenWCUtils::cartHasGootenProduct($cart));
        }
        { // Cart with Gooten product
            $cart = $this->createCartMock(array($productSimple, $productGooten));
            $this->assertTrue(GootenWCUtils::cartHasGootenProduct($cart));
        }
        { // Test using global cart
            global $woocommerce;
            $woocommerce->cart = $this->createCartMock(array($productSimple, $productGooten));
            $this->assertTrue(GootenWCUtils::cartHasGootenProduct());
        }
    }

    public function testOrderHasGootenProduct_withGootenProduct()
    {
        $wcOrder = $this->createOrder();
        $wcOrder->add_product($this->createGootenVariableProduct());
        $this->assertTrue(GootenWCUtils::orderHasGootenProduct($wcOrder));
    }

    public function testOrderHasGootenProduct_nullOrder()
    {
        $this->assertFalse(GootenWCUtils::orderHasGootenProduct(null));
    }

    public function testOrderHasGootenProduct_emptyOrder()
    {
        $wcOrder = $this->createOrder();
        $this->assertFalse(GootenWCUtils::orderHasGootenProduct($wcOrder));
    }

    public function testOrderHasGootenProduct_noGootenProducts()
    {
        $wcOrder = $this->createOrder();
        $wcOrder->add_product($this->createSimpleProduct());
        $this->assertFalse(GootenWCUtils::orderHasGootenProduct($wcOrder));
    }

    public function testIsGootenVariableProduct()
    {
        { // Non Gooten products should return false
            $productSimple = $this->createSimpleProduct();
            $this->assertFalse(GootenWCUtils::isGootenVariableProduct($productSimple));
            $this->assertFalse(GootenWCUtils::isGootenVariableProduct($productSimple->id));
        }

        // TODO
        // $this->assertTrue(GootenWCUtils::isGootenVariableProduct($productGooten));
        // $this->assertTrue(GootenWCUtils::isGootenVariableProduct($productGooten->id));
    }

    public function testGetPriceEstimateForCart_valid()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID);
        $cart = $this->createCartMock(array($this->createGootenVariableProduct()));
        WC()->customer = $this->createRealCustomer();
        $result = GootenWCUtils::getPriceEstimateForCart($cart);

        $this->assertArrayHasKey('shipping_total', $result);
        $this->assertArrayHasKey('tax_total', $result);
        $this->assertGreaterThan(0, $result['shipping_total']);
    }

    public function testGetPriceEstimateForCart_noRecipeId()
    {
        delete_option('gooten_recipe_id');
        $cart = $this->createCartMock(array($this->createGootenVariableProduct()));
        WC()->customer = $this->createRealCustomer();
        $result = GootenWCUtils::getPriceEstimateForCart($cart);

        $this->assertArrayHasKey('shipping_total', $result);
        $this->assertArrayHasKey('tax_total', $result);
        $this->assertEquals(0, $result['shipping_total']);
        $this->assertEquals(0, $result['tax_total']);
    }

    public function testGetPriceEstimateForCart_invalidGootenSKU()
    {
        delete_option('gooten_recipe_id');
        $cart = $this->createCartMock(array($this->createGootenVariableProductInvalidSKU()));
        WC()->customer = $this->createRealCustomer();
        $result = GootenWCUtils::getPriceEstimateForCart($cart);

        $this->assertArrayHasKey('shipping_total', $result);
        $this->assertArrayHasKey('tax_total', $result);
        $this->assertEquals(0, $result['shipping_total']);
        $this->assertEquals(0, $result['tax_total']);
    }

    public function testMarkOrderAsSubmittedToGooten()
    {
        $wcOrder = $this->createOrder();

        $this->assertTrue(GootenWCUtils::markOrderAsSubmittedToGooten($wcOrder, '{"Id":"TestId"}'));
        $this->assertEquals('TestId', GootenWCUtils::getGootenOrderId($wcOrder));

        // Not valid params test cases
        $this->assertFalse(GootenWCUtils::markOrderAsSubmittedToGooten('', null));
        $this->assertFalse(GootenWCUtils::markOrderAsSubmittedToGooten(null, ''));
    }

    public function testWCToGTNAddress()
    {
        $wcAddress = array(
            'first_name' => 'test',
            'last_name' => 'test',
            'company' => 'test',
            'address_1' => 'test',
            'address_2' => 'test',
            'postcode' => 'test',
            'state' => 'test',
            'country' => 'test',
            'city' => 'test',
            'email' => 'test',
            'phone' => 'test'
        );

        $gtnAddress = GootenWCUtils::wcToGTNAddress($wcAddress);
        $wcAddress['company'] = true;

        // Check does Gooten address has all values
        $this->assertEquals(array_values($gtnAddress), array_values($wcAddress));

        // Check does Gooten address has correct field names
        $fields = implode(array_keys($gtnAddress), ' ');
        $this->assertTrue(preg_match('[^(?=.*\bFirstName\b)(?=.*\bLastName\b)' .
                '(?=.*\bLine1\b)(?=.*\bLine2\b)' .
                '(?=.*\bIsBusinessAddress\b)(?=.*\bPostalCode\b)' .
                '(?=.*\bState\b)(?=.*\bCountryCode\b)' .
                '(?=.*\bCity\b)(?=.*\bEmail\b)(?=.*\bPhone\b).*$]', $fields) === 1);
    }

    public function testGetWCCustomerShippingAddress_fullAddress()
    {
        WC()->customer = $this->createRealCustomer();
        $address = GootenWCUtils::getWCCustomerShippingAddress();

        // Assert correct property names
        $fields = implode(array_keys($address), ' ');
        $this->assertTrue(preg_match('[^(?=.*\bLine1\b)(?=.*\bLine2\b)(?=.*\bPostalCode\b)' .
                '(?=.*\bState\b)(?=.*\bCountryCode\b)' .
                '(?=.*\bCity\b).*$]', $fields) === 1);

        $this->assertTrue(GootenWCUtils::isFullAddress($address));
    }

    public function testGetWCCustomerShippingAddress_partialAddress()
    {
        WC()->customer = $this->createPartialCustomer();
        $address = GootenWCUtils::getWCCustomerShippingAddress();

        // Assert correct property names
        $fields = implode(array_keys($address), ' ');
        $this->assertTrue(preg_match('[^(?=.*\bLine1\b)(?=.*\bLine2\b)(?=.*\bPostalCode\b)' .
                '(?=.*\bState\b)(?=.*\bCountryCode\b)' .
                '(?=.*\bCity\b).*$]', $fields) === 1);

        $this->assertFalse(GootenWCUtils::isFullAddress($address));
    }
}
