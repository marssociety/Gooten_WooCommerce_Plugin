<?php
/**
 * Created by Vlado on 02-Jan-17.
 */

/**
 * Holds hooks needed for .js scripts inclusion.
 */
class GootenWCHookScriptInclusion
{
    private $gootenWC;

    public function __construct($gootenWC)
    {
        $this->gootenWC = $gootenWC;
    }

    public function initHooks()
    {
        add_action('admin_head', array($this, 'wp_gooten_admin_head'));
    }

    function wp_gooten_admin_head()
    {
        $assetsDir = plugin_dir_url(__FILE__) . 'assets/';

        // Include assets based on currently active screen
        $screen = get_current_screen();
        if ($screen->base === 'post' && $screen->post_type === 'product') {
            wp_enqueue_script('jquery.lazyload', $assetsDir . 'js/libs/jquery.lazyload.min.js', array('jquery'), '1.9.3', false);
            echo '<script type="text/javascript" src="' . $assetsDir . 'js/gtn_wc.js' . '"></script>';
            $this->print_gooten_config();
            echo '<script type="text/javascript" src="' . $assetsDir . 'js/gtn_utils.js' . '"></script>';
            echo '<script type="text/javascript" src="' . $assetsDir . 'js/gtn_api.js' . '"></script>';
            echo '<script type="text/javascript" src="' . $assetsDir . 'js/gtn_wp_api.js' . '"></script>';
            echo '<script type="text/javascript" src="' . $assetsDir . 'js/gtn_variant_select.js' . '"></script>';
            echo '<script type="text/javascript" src="' . $assetsDir . 'js/gtn_wc_admin_post_product_page.js' . '"></script>';
            echo '<script type="text/javascript">jQuery(document).ready(GTN_WC.createAdminPostProductPage().init);</script>';
            echo '<link rel="stylesheet" type="text/css" href="' . $assetsDir . 'css/gooten.css' . '" />';
        }
    }

    function print_gooten_config()
    {
        echo '<script type="text/javascript">';
        echo 'GTN_WC.Config.recipeId = "' . $this->gootenWC->getRecipeId() . '";';
        echo 'GTN_WC.Config.productTags = "' . $this->gootenWC->getProductTagsStrategy() . '";';
        echo 'GTN_WC.Config.productCategories = "' . $this->gootenWC->getProductCategoriesStrategy() . '";';
        echo 'GTN_WC.Config.productProfit = "' . $this->gootenWC->getProductProfit() . '";';
        echo 'GTN_WC.Config.apiUrl = "' . GootenWCAPI::createBaseGootenAPIURL() . '";';
        echo 'GTN_WC.Config.countryCode = "' . GootenWCUtils::getAPICountryCode() . '";';
        echo 'GTN_WC.Config.currencyCode = "' . GootenWCUtils::getAPICurrencyCode() . '";';
        echo 'GTN_WC.Config.pluginSettingsPage = "' . admin_url('admin.php?page=gooten_settings') . '";';
        echo 'GTN_WC.Config.productImagesEndpoint = "' . rest_url('/gooten/setProductImages') . '";';
        echo 'GTN_WC.Config.productImagesEndpointFallback = "' . site_url() . '/?rest_route=/gooten/setProductImages";';
        echo 'GTN_WC.Config.nonce = "' . wp_create_nonce('wp_rest') . '";';
        echo '</script>';
    }
}