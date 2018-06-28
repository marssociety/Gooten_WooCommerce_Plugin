<?php
/**
 * Created by Boro on 18-Oct-16.
 */

require_once('BaseTest.class.php');

class GootenWCAPITest extends BaseTest
{
    public function setUp()
    {
        parent::setUp();

        update_option('woocommerce_default_country', 'US:NY');
        update_option('woocommerce_currency', 'USD');
    }

    private function createWCOrderWithGootenProduct()
    {
        $wcOrder = $this->createOrder();
        $this->setAddressesToOrder($wcOrder, $this->createRealCustomer());
        $wcOrder->add_product($this->createSimpleProduct());
        $wcOrder->add_product($this->createGootenVariableProduct());
        return $wcOrder;
    }

    public function testCreateBaseGootenAPIURL()
    {
        $serverUrl = GootenWCAPI::createBaseGootenAPIURL();
        $this->assertTrue(strpos($serverUrl, "https://api.print.io/api/") === 0);
        $this->assertTrue(preg_match("[^(?=.*v\/\d)(?=.*source\/\D).*$]", $serverUrl) === 1);
    }

    public function testCreateEndpoint_validRecipeID()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID);

        $method = $this->getMethodByReflection('GootenWCAPI', 'createEndpoint');
        $endpoint = $method->invokeArgs(null, array('priceestimate'));

        $this->assertTrue(preg_match("[^(?=.*priceestimate)(?=.*recipeId\D).*$]", $endpoint) === 1);
    }

    public function testCreateEndpoint_invalidRecipeId()
    {
        $this->setRecipeId('12345');

        $method = $this->getMethodByReflection('GootenWCAPI', 'createEndpoint');
        $endpoint = $method->invokeArgs(null, array('priceestimate'));

        $this->assertFalse($endpoint);
    }

    public function testCreateEndpoint_recipeIdNotSet()
    {
        delete_option('gooten_recipe_id');

        $method = $this->getMethodByReflection('GootenWCAPI', 'createEndpoint');
        $endpoint = $method->invokeArgs(null, array('priceestimate'));

        $this->assertFalse($endpoint);
    }

    public function testPostPriceEstimateCart_invalidCustomer()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID);

        WC()->customer = $this->createPartialCustomer();
        $cart = $this->createCartMock(array($this->createGootenVariableProduct()));

        $response = GootenWCAPI::postPriceEstimateCart($cart);
        $this->assertFalse($response);
    }

    public function testPostPriceEstimateCart_RecipeIdNotSet()
    {
        delete_option('gooten_recipe_id');

        WC()->customer = $this->createRealCustomer();
        $cart = $this->createCartMock(array($this->createGootenVariableProduct()));

        $response = GootenWCAPI::postPriceEstimateCart($cart);
        $this->assertFalse($response);
    }

    public function testPostPriceEstimateCart_invalidRecipeId()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID . '-invalid');

        $cart = $this->createCartMock(array($this->createGootenVariableProduct()));
        WC()->customer = $this->createRealCustomer();

        $response = GootenWCAPI::postPriceEstimateCart($cart);
        $this->assertFalse($response);
    }

    public function testPostPriceEstimateCart_noGootenProducts()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID);

        WC()->customer = $this->createRealCustomer();
        $cart = $this->createCartMock(array($this->createSimpleProduct()));

        $response = GootenWCAPI::postPriceEstimateCart($cart);
        $this->assertFalse($response);
    }

    public function testPostPriceEstimateCart_valid_USD()
    {
        update_option('woocommerce_currency', 'USD');
        $this->setRecipeId($this->VALID_RECIPE_ID);

        $cart = $this->createCartMock(array($this->createGootenVariableProduct()));
        WC()->customer = $this->createRealCustomer();

        $response = GootenWCAPI::postPriceEstimateCart($cart);

        $this->assertTrue(isset($response));
        $response = json_decode($response, true);
        $this->assertFalse($response['HadError']);
        $this->assertTrue($response['Items']['Price'] > 0);
        $this->assertEquals($response['Items']['CurrencyCode'], 'USD');
        $this->assertTrue($response['Shipping']['Price'] > 0);
    }

    public function testPostPriceEstimateCart_valid_RSD()
    {
        update_option('woocommerce_currency', 'RSD');
        $this->setRecipeId($this->VALID_RECIPE_ID);

        $cart = $this->createCartMock(array($this->createGootenVariableProduct()));
        WC()->customer = $this->createRealCustomer();

        $response = GootenWCAPI::postPriceEstimateCart($cart);

        $this->assertTrue(isset($response));
        $response = json_decode($response, true);
        $this->assertFalse($response['HadError']);
        $this->assertTrue($response['Items']['Price'] > 0);
        $this->assertEquals($response['Items']['CurrencyCode'], 'RSD');
        $this->assertTrue($response['Shipping']['Price'] > 0);
    }

    public function testPostOrders_RecipeIdNotSet()
    {
        delete_option('gooten_recipe_id');

        $wcOrder = $this->createWCOrderWithGootenProduct();
        $response = GootenWCAPI::postOrders($wcOrder, true, '');

        $this->assertFalse($response);
    }

    public function testPostOrders_RecipeIdInvalid()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID . '-invalid');

        $wcOrder = $this->createWCOrderWithGootenProduct();
        $response = GootenWCAPI::postOrders($wcOrder, true, '');

        $this->assertFalse($response);
    }

    public function testPostOrders_noGootenProducts()
    {
        $wcOrder = $this->createOrder();
        $this->setAddressesToOrder($wcOrder, $this->createRealCustomer());
        $wcOrder->add_product($this->createSimpleProduct());
        $response = GootenWCAPI::postOrders($wcOrder, true, '');

        $this->assertFalse($response);
    }

    public function testPostOrders_invalidSKU()
    {
        // TODO (product are not identified as gooten product ???)
//        update_option('woocommerce_currency', 'USD');
//        $this->setRecipeId($this->VALID_RECIPE_ID);
//
//        $wcOrder = $this->createOrder();
//        $this->setAddressesToOrder($wcOrder, $this->createRealCustomer());
//        $wcOrder->add_product($this->createGootenVariableProductInvalidSKU());
//        $response = GootenWCAPI::postOrders($wcOrder, true, '');
//
//        $this->assertTrue(isset($response));
//        $response = json_decode($response, true);
//        $this->assertArrayHasKey('HadError', $response);
//        $this->assertTrue($response['HadError']);
    }

    public function testPostOrders_valid_USD()
    {
        // TODO (product are not identified as gooten product ???)
//        update_option('woocommerce_currency', 'USD');
//        $this->setRecipeId($this->VALID_RECIPE_ID);
//
//        $wcOrder = $this->createWCOrderWithGootenProduct();
//        $response = GootenWCAPI::postOrders($wcOrder, true, '');
//
//        $this->assertTrue(isset($response));
//        $response = json_decode($response, true);
//        $this->assertArrayHasKey('Id', $response);
    }
}
