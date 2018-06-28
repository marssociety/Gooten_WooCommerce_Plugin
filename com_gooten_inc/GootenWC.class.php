<?php
/**
 * Created by Vlado on 26-Aug-16.
 */

if (!class_exists('GootenWC')) {

    $class_path = plugin_dir_path(__FILE__);
    require_once("{$class_path}/GootenWCConstants.php");
    require_once("{$class_path}/GootenWCUtils.class.php");
    require_once("{$class_path}/GootenWCAPI.class.php");
    require_once("{$class_path}/GootenWCErrorReport.class.php");
    require_once("{$class_path}/GootenWCHookWC.class.php");
    require_once("{$class_path}/GootenWCHookSettingsPageWP.class.php");
    require_once("{$class_path}/GootenWCHookScriptInclusion.class.php");
    require_once("{$class_path}/GootenWCProductVariationUtils.class.php");
    require_once("{$class_path}/GootenWCMediaUtils.class.php");

    /**
     * The main Gooten plugin class.
     */
    class GootenWC
    {

        public function __construct()
        {
            $this->addDefaultOptions();
            $this->initHooks();
        }

        /**
         * Returns currently entered recipe ID.
         *
         * @return string Non blank value if recipe ID is set.
         */
        function getRecipeId()
        {
            $recipeID = get_option(GootenWCOptionNames::RECIPE_ID);
            return isset($recipeID) && is_string($recipeID) ? $recipeID : '';
        }

        /**
         * Returns currently entered billing key.
         *
         * @return string Non blank value if billing key is set.
         */
        function getBillingKey()
        {
            $billingKey = get_option(GootenWCOptionNames::BILLING_KEY);
            return isset($billingKey) && is_string($billingKey) ? $billingKey : '';
        }

        /**
         * Returns true if order testing mode is enabled.
         *
         * @return bool true if order testing mode is enabled.
         */
        function isOrderTesting()
        {
            return get_option(GootenWCOptionNames::ORDER_TESTING) === '1';
        }

        /**
         * Returns identifier of selected product tags strategy.
         *
         * @return string Identifier of selected product tags strategy.
         */
        function getProductTagsStrategy()
        {
            $strategy = get_option(GootenWCOptionNames::PRODUCT_TAGS);
            return isset($strategy) && is_string($strategy) ? $strategy : 'none';
        }

        /**
         * Returns identifier of selected product categories strategy.
         *
         * @return string Identifier of selected product categories strategy.
         */
        function getProductCategoriesStrategy()
        {
            $strategy = get_option(GootenWCOptionNames::PRODUCT_CATEGORIES);
            return isset($strategy) && is_string($strategy) ? $strategy : 'none';
        }

        /**
         * Returns currently entered product profit value.
         *
         * @return int Currently entered product profit value, or '' if not set.
         */
        function getProductProfit()
        {
            $profit = floatval(get_option(GootenWCOptionNames::PRODUCT_PROFIT));
            return $profit < 0 ? '' : $profit;
        }

        /**
         * Returns true if dynamic shipping is used.
         *
         * @return bool true if dynamic shipping is used.
         */
        function isOrderWithDynamicShipping()
        {
            return get_option(GootenWCOptionNames::ORDER_DYNAMIC_SHIPPING) === '1';
        }

        /**
         * Returns true if dynamic tax is used.
         *
         * @return bool true if dynamic tax is used.
         */
        function isOrderWithDynamicTax()
        {
            return get_option(GootenWCOptionNames::ORDER_DYNAMIC_TAX) === '1';
        }

        /**
         * Adds default option values.
         */
        function addDefaultOptions()
        {
            add_option(GootenWCOptionNames::RECIPE_ID, '');
            add_option(GootenWCOptionNames::BILLING_KEY, '');
            add_option(GootenWCOptionNames::ORDER_TESTING, '1');
            add_option(GootenWCOptionNames::PRODUCT_TAGS, 'none');
            add_option(GootenWCOptionNames::PRODUCT_CATEGORIES, 'none');
            add_option(GootenWCOptionNames::PRODUCT_PROFIT, '');
            add_option(GootenWCOptionNames::ORDER_DYNAMIC_SHIPPING, '1');
            add_option(GootenWCOptionNames::ORDER_DYNAMIC_TAX, '1');
        }

        /**
         * Initializes necessary hooks for WordPress and WooCommerce.
         */
        function initHooks()
        {
            $temp = new GootenWCHookScriptInclusion($this);
            $temp->initHooks();
            $temp = new GootenWCHookSettingsPageWP($this);
            $temp->initHooks();
            $temp = new GootenWCHookWC($this);
            $temp->initHooks();
        }
    }

}

/**
 * Load GootenWC plugin.
 */
add_action('plugins_loaded', function () {
    global $GootenWC;
    $GootenWC = new GootenWC();
});
