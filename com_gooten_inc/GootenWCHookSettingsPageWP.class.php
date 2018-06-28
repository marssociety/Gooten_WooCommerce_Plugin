<?php
/**
 * Created by Vlado on 02-Jan-17.
 */

/**
 * Holds hooks for settings page.
 */
class GootenWCHookSettingsPageWP
{
    private $gootenWC;

    public function __construct($gootenWC)
    {
        $this->gootenWC = $gootenWC;
    }

    public function initHooks()
    {
        // WP Admin hooks
        add_action('admin_notices', array($this, 'wp_gooten_admin_notices'));
        add_action('admin_menu', array($this, 'wp_gooten_admin_menu'));
        add_action('admin_init', array($this, 'wp_gooten_admin_init'));
    }

    function showMessage($type, $message)
    {
        echo "<div class='notice $type'><p>$message</p></div>";
    }

    function wp_gooten_admin_notices($isAfterUpdate = false)
    {
        $screen = get_current_screen();
        if ($screen->base === 'toplevel_page_gooten_settings' && (!isset($_POST['update_gooten_settings']) || $isAfterUpdate)) {
            $hasErrorMessages = false;

            // Error notices
            { // RecipeID/Billing key not set notice
                if (strlen($this->gootenWC->getRecipeId()) <= 10 || strlen($this->gootenWC->getBillingKey()) <= 10) {
                    $this->showMessage(GootenNoticeType::ERROR, 'Recipe ID and/or Billing key is not set. Please set these keys in order to enable plugin.');
                    $hasErrorMessages = true;
                }
            }

            // Warning notices - shown only when there are no error notice
            if (!$hasErrorMessages) {
                // Order Testing notice
                if ($this->gootenWC->isOrderTesting()) {
                    $this->showMessage(GootenNoticeType::WARNING, 'Order testing mode is enabled - orders will not be sent to production.');
                }
            }
        }
    }

    function wp_gooten_admin_menu()
    {
        add_menu_page('Gooten Settings', 'Gooten', 'manage_options', 'gooten_settings', array($this, 'gooten_settings_page'), plugin_dir_url(__FILE__) . 'assets/images/logo.png', 58);
    }

    function wp_gooten_admin_init()
    {
        register_setting('gooten_recipe_id', 'gooten_recipe_id', array($this, 'gooten_validate_option'));
        register_setting('gooten_billing_key', 'gooten_billing_key', array($this, 'gooten_validate_option'));
        register_setting('gooten_order_testing', 'gooten_order_testing', array($this, 'gooten_validate_option'));
        register_setting('gooten_product_tags', 'gooten_product_tags', array($this, 'gooten_validate_option'));
        register_setting('gooten_product_categories', 'gooten_product_categories', array($this, 'gooten_validate_option'));
        register_setting('gooten_product_profit', 'gooten_product_profit', array($this, 'gooten_validate_option'));
        register_setting('gooten_order_dynamic_shipping', 'gooten_order_dynamic_shipping', array($this, 'gooten_validate_option'));
        register_setting('gooten_order_dynamic_tax', 'gooten_order_dynamic_tax', array($this, 'gooten_validate_option'));

        add_settings_section('gooten_api', 'Gooten API', array($this, 'plugin_section_text_api'), 'gooten_settings');
        add_settings_field('gooten_recipe_id', 'Recipe ID', array($this, 'gooten_recipe_id_string'), 'gooten_settings', 'gooten_api');
        add_settings_field('gooten_billing_key', 'Billing key', array($this, 'gooten_billing_key_string'), 'gooten_settings', 'gooten_api');

        add_settings_section('gooten_orders', 'Orders', array($this, 'plugin_section_text_orders'), 'gooten_settings');
        add_settings_field('gooten_order_testing', 'Orders testing', array($this, 'gooten_order_testing_string'), 'gooten_settings', 'gooten_orders');
        add_settings_field('gooten_order_dynamic_shipping', 'Dynamic shipping', array($this, 'gooten_order_dynamic_shipping_string'), 'gooten_settings', 'gooten_orders');
        add_settings_field('gooten_order_dynamic_tax', 'Dynamic tax', array($this, 'gooten_order_dynamic_tax_string'), 'gooten_settings', 'gooten_orders');

        add_settings_section('gooten_product_settings', 'Product settings', array($this, 'plugin_section_product_settings'), 'gooten_settings');
        add_settings_field('gooten_product_tags', 'Product tags strategy', array($this, 'gooten_product_tags_string'), 'gooten_settings', 'gooten_product_settings');
        add_settings_field('gooten_product_categories', 'Product categories strategy', array($this, 'gooten_product_categories_string'), 'gooten_settings', 'gooten_product_settings');
        add_settings_field('gooten_product_profit', 'Product profit', array($this, 'gooten_product_profit_string'), 'gooten_settings', 'gooten_product_settings');
    }

    function gooten_settings_page()
    {
        if (isset($_POST['update_gooten_settings'])) {
            $possible_settings = array(GootenWCOptionNames::RECIPE_ID, GootenWCOptionNames::BILLING_KEY, GootenWCOptionNames::ORDER_TESTING, GootenWCOptionNames::PRODUCT_TAGS,
                GootenWCOptionNames::PRODUCT_CATEGORIES, GootenWCOptionNames::PRODUCT_PROFIT, GootenWCOptionNames::ORDER_DYNAMIC_SHIPPING, GootenWCOptionNames::ORDER_DYNAMIC_TAX);
            foreach ($possible_settings as $key) {
                $val = array_key_exists($key, $_POST) ? $_POST[$key] : '';
                update_option($key, $val);
            }
            $this->wp_gooten_admin_notices(true);
            $this->showMessage(GootenNoticeType::SUCCESS, 'Settings Updated');
        }

        echo '<div><h2>Gooten Settings</h2><form method="post">';
        echo settings_fields('plugin_options') . do_settings_sections('gooten_settings');
        echo '<br /><br /><input name="update_gooten_settings" type="submit" value="Save Settings" /><br /><br />';
        echo '</form></div>';
    }

    function plugin_section_text_api()
    {
        echo 'Configuration for Gooten API';
    }

    function plugin_section_text_orders()
    {
        echo 'Configuration for Gooten Orders';
    }

    function plugin_section_product_settings()
    {
        echo 'Configuration for Products';
    }

    function gooten_validate_option($input)
    {
        return trim($input);
    }

    function gooten_recipe_id_string()
    {
        $recipeId = $this->gootenWC->getRecipeId();
        echo '<input id="gooten_recipe_id" name="gooten_recipe_id" size="55" type="text" value="' . $recipeId . '" />';
        echo '<p class="description" id="tagline-description">Recipe ID can be obtained from <a href="https://www.gooten.com/admin#/settings/keys/">Gooten Admin Panel</a>.</p>';
    }

    function gooten_billing_key_string()
    {
        $key = $this->gootenWC->getBillingKey();
        echo '<input id="gooten_billing_key" name="gooten_billing_key" size="55" type="text" value="' . $key . '" />';
        echo '<p class="description" id="tagline-description">Billing key can be obtained from <a href="https://www.gooten.com/admin#/settings/keys/">Gooten Admin Panel</a>.</p>';
    }

    function gooten_order_testing_string()
    {
        $isTestingOrders = $this->gootenWC->isOrderTesting();
        echo '<input id="gooten_order_testing" name="gooten_order_testing" type="checkbox" value="1" ' . checked(1, $isTestingOrders, false) . ' />';
        echo '<p class="description" id="tagline-description">When enabled, orders sent to Gooten will not be submitted to production.</p>';
    }

    function gooten_product_tags_string()
    {
        $strategy = $this->gootenWC->getProductTagsStrategy();
        echo '<select name="gooten_product_tags" id="gooten_product_tags">';
        echo '<option value="none" ' . selected($strategy, 'none', false) . ' >None</option>';
        echo '<option value="pvo" ' . selected($strategy, 'pvo', false) . ' >Product Variant Options</option>';
        echo '</select>';
        echo '<p class="description" id="tagline-description">Defines strategy for creating product tags.</p>';
    }

    function gooten_product_categories_string()
    {
        $strategy = $this->gootenWC->getProductCategoriesStrategy();
        echo '<select name="gooten_product_categories" id="gooten_product_categories">';
        echo '<option value="none" ' . selected($strategy, 'none', false) . ' >None</option > ';
        echo '<option value="productType" ' . selected($strategy, 'productType', false) . ' >Product Type</option>';
        echo '<option value="gootenCategories" ' . selected($strategy, 'gootenCategories', false) . ' >Gooten Categories</option>';
        echo '</select>';
        echo '<p class="description" id="tagline-description">Defines strategy for creating product categories.</p>';
    }

    function gooten_product_profit_string()
    {
        $profit = $this->gootenWC->getProductProfit();
        echo '<input id="gooten_product_profit" name="gooten_product_profit" size="5" type="text" value="' . $profit . '" />&nbsp;%';
        echo '<p class="description" id="tagline-description">Used to preset default customer price with specified profit. Valid value is any non-negative number.</p>';
    }

    function gooten_order_dynamic_shipping_string()
    {
        $isEnabled = $this->gootenWC->isOrderWithDynamicShipping();
        echo '<input id="gooten_order_dynamic_shipping" name="gooten_order_dynamic_shipping" type="checkbox" value="1" ' . checked(1, $isEnabled, false) . ' />';
        echo '<p class="description" id="tagline-description">When enabled, order totals will include shipping calculated by Gooten.</p>';
    }

    function gooten_order_dynamic_tax_string()
    {
        $isEnabled = $this->gootenWC->isOrderWithDynamicTax();
        echo '<input id="gooten_order_dynamic_tax" name="gooten_order_dynamic_tax" type="checkbox" value="1" ' . checked(1, $isEnabled, false) . ' />';
        echo '<p class="description" id="tagline-description">When enabled, order totals will include tax calculated by Gooten.</p>';
    }
}