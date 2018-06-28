<?php
/**
 * Created by Boro on 25-Oct-16.
 */

require_once('BaseTest.class.php');

class GootenWCTest extends BaseTest
{
    public function testGetRecipeId()
    {
        update_option(GootenWCOptionNames::RECIPE_ID, 'test_recipe_id');
        $this->assertEquals($this->GootenWC->getRecipeId(), 'test_recipe_id');

        update_option(GootenWCOptionNames::RECIPE_ID, '');
        $this->assertEquals($this->GootenWC->getRecipeId(), '');

        delete_option(GootenWCOptionNames::RECIPE_ID);
        $this->assertEquals($this->GootenWC->getBillingKey(), '');
    }

    public function testGetBillingKey()
    {
        update_option(GootenWCOptionNames::BILLING_KEY, 'test_billing_key');
        $this->assertEquals($this->GootenWC->getBillingKey(), 'test_billing_key');

        update_option(GootenWCOptionNames::BILLING_KEY, '');
        $this->assertEquals($this->GootenWC->getBillingKey(), '');

        delete_option(GootenWCOptionNames::BILLING_KEY);
        $this->assertEquals($this->GootenWC->getBillingKey(), '');
    }

    public function testIsOrderTesting()
    {
        update_option(GootenWCOptionNames::ORDER_TESTING, '1');
        $this->assertEquals($this->GootenWC->isOrderTesting(), true);

        update_option(GootenWCOptionNames::ORDER_TESTING, 'anything');
        $this->assertEquals($this->GootenWC->isOrderTesting(), false);

        delete_option(GootenWCOptionNames::ORDER_TESTING);
        $this->assertEquals($this->GootenWC->isOrderTesting(), false);
    }

    public function testGetProductTagsStrategy()
    {
        update_option(GootenWCOptionNames::PRODUCT_TAGS, 'test_tag');
        $this->assertEquals($this->GootenWC->getProductTagsStrategy(), 'test_tag');

        delete_option(GootenWCOptionNames::PRODUCT_TAGS);
        $this->assertEquals($this->GootenWC->getProductTagsStrategy(), 'none');
    }

    public function testGetProductCategoriesStrategy()
    {
        update_option(GootenWCOptionNames::PRODUCT_CATEGORIES, 'test_category');
        $this->assertEquals($this->GootenWC->getProductCategoriesStrategy(), 'test_category');

        delete_option(GootenWCOptionNames::PRODUCT_CATEGORIES);
        $this->assertEquals($this->GootenWC->getProductCategoriesStrategy(), 'none');
    }

    public function testGetProductProfit()
    {
        update_option(GootenWCOptionNames::PRODUCT_PROFIT, '0');
        $this->assertTrue($this->GootenWC->getProductProfit() === '');

        update_option(GootenWCOptionNames::PRODUCT_PROFIT, '-1');
        $this->assertTrue($this->GootenWC->getProductProfit() === '');

        update_option(GootenWCOptionNames::PRODUCT_PROFIT, 'thisisnotnumber');
        $this->assertTrue($this->GootenWC->getProductProfit() === '');

        update_option(GootenWCOptionNames::PRODUCT_PROFIT, '10');
        $this->assertTrue($this->GootenWC->getProductProfit() === 10);

        delete_option(GootenWCOptionNames::PRODUCT_PROFIT);
        $this->assertTrue($this->GootenWC->getProductProfit() === '');
    }

    public function testIsOrderWithDynamicShipping()
    {
        update_option(GootenWCOptionNames::ORDER_DYNAMIC_SHIPPING, '1');
        $this->assertEquals($this->GootenWC->isOrderWithDynamicShipping(), true);

        update_option(GootenWCOptionNames::ORDER_DYNAMIC_SHIPPING, 'anything');
        $this->assertEquals($this->GootenWC->isOrderWithDynamicShipping(), false);

        delete_option(GootenWCOptionNames::ORDER_DYNAMIC_SHIPPING);
        $this->assertEquals($this->GootenWC->isOrderWithDynamicShipping(), false);
    }

    public function testIsOrderWithDynamicTax()
    {
        update_option(GootenWCOptionNames::ORDER_DYNAMIC_TAX, '1');
        $this->assertEquals($this->GootenWC->isOrderWithDynamicTax(), true);

        update_option(GootenWCOptionNames::ORDER_DYNAMIC_TAX, 'anything');
        $this->assertEquals($this->GootenWC->isOrderWithDynamicTax(), false);

        delete_option(GootenWCOptionNames::ORDER_DYNAMIC_TAX);
        $this->assertEquals($this->GootenWC->isOrderWithDynamicTax(), false);
    }
}
