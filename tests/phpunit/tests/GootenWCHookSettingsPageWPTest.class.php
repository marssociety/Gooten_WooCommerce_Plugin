<?php
/**
 * Created by Vlado on 3-Jan-17.
 */

require_once('BaseTest.class.php');


class GootenWCHookSettingsPageWPTest extends BaseTest
{
    private $hook;

    public function setUp()
    {
        parent::setUp();
        $this->hook = new GootenWCHookSettingsPageWP($this->GootenWC);
    }

    public function testMethodsHaveOutput()
    {
        $methods = array('plugin_section_text_api',
            'gooten_order_dynamic_tax_string',
            'plugin_section_text_orders',
            'plugin_section_product_settings',
            'gooten_recipe_id_string',
            'gooten_billing_key_string',
            'gooten_order_testing_string',
            'gooten_product_tags_string',
            'gooten_product_categories_string',
            'gooten_product_profit_string',
            'gooten_order_dynamic_shipping_string',
            'gooten_order_dynamic_tax_string');
        $this->assertMethodsHaveOutput($this->hook, $methods);
    }
}