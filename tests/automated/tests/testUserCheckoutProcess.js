/**
 * Created by Vlado on 21-Oct-16.
 */

/*global
 module, require
 */

var globals = require('../globals.json');
var commonWrapper = require('../commonWrapper.js');
var pageConstants = require('../pageConstants.js');

var G = globals;

module.exports = {

    before: function (browser) {
        'use strict';
        commonWrapper.wrapBrowser(browser);
    },

    after: function (browser) {
        'use strict';
        commonWrapper.unwrapBrowser(browser);
        browser.end();
    },

    /**
     * This test case assumes that:
     * 1. there is WooCommerce Dummy Gateway plugin installed & activated
     * 2. there is Storefront theme for WordPress activated
     * 3. there is at least one product defined in the store
     */
    "Test user checkout process": function (browser) {
        'use strict';

        var SS = pageConstants.SETTINGS.SELECTORS;

        // Ensure that:
        // - valid recipe id and billing keys are set
        // - order testing is selected
        browser
            .openPageAsAdmin(G.pageGootenSettingsUrl)
            .clearValueSet(SS.RECIPE_ID_INPUT, G.validRecipeId)
            .clearValueSet(SS.BILLING_KEY_INPUT, G.validBillingKey)
            .setCheckboxChecked(SS.ORDER_TESTING_CHECKBOX)
            .submitFormAndWait(SS.SUBMIT_SETTINGS_BUTTON);

        // Ensure that dummy payment gateway is set
        browser
            .openPageAsAdmin(G.pageDummyGatewaySettings)
            .setCheckboxChecked('#woocommerce_wc_dummy_gateway_enabled')
            .click('#woocommerce_wc_dummy_gateway_user_role option[value=everyone]')
            .pause(200)
            .click('input.woocommerce-save-button')
            .pause(200)
            .waitForBody();

        // Logout current user (admin)
        browser
            .url(G.url + 'wp-login.php?action=logout')
            .pause(200)
            .waitForBody()
            .click('a');

        // Add gooten product to cart
        browser
            .url(G.url + G.pageShopUrl)
            .click('a.product_type_gooten_product')
            .pause(200)
            .waitForBody()
            .click('button.single_add_to_cart_button');

        // Go to checkout page
        browser
            .url(G.url + G.pageCheckoutUrl)
            .waitForBody();

        // 1. Fill billing info
        browser
            .clearValueSet('input[id="billing_first_name"]', 'Woocommerce')
            .clearValueSet('input[id="billing_last_name"]', 'Test')
            .clearValueSet('input[id="billing_email"]', 'test@gooten.com')
            .clearValueSet('input[id="billing_phone"]', '1220555444231')
            .clearValueSet('input[id="billing_address_1"]', 'Madison Avenue 79')
            .clearValueSet('input[id="billing_address_2"]', 'Suite 230')
            .clearValueSet('input[id="billing_city"]', 'New York')
            .click('#billing_state option[value=NY]')
            .clearValueSet('input[id="billing_postcode"]', '10016')
            .setCheckboxNotChecked('input[id="ship-to-different-address-checkbox"]');

        // 2. Place order
        browser
            .setCheckboxChecked('input[id="payment_method_wc_dummy_gateway"]')
            .pause(200)
            .waitForElementNotPresent('#payment .blockOverlay', 1000000)
            .click('#place_order')
            .pause(500)
            .waitForElementNotPresent('form.checkout .blockOverlay', 100000)
            .pause(200)
            .waitForBody();

        // 3. Assert order is accepted by site & that order is submitted to Gooten
        browser
            .assert.elementPresent('.woocommerce-thankyou-order-received')
            .openPageAsAdmin(G.pageAdminOrdersUrl)
            .click('.order_title a.row-title:nth-of-type(1)')
            .assert.elementPresent('#gooten-order-status')
            .assert.elementPresent('#gooten-order-id');
    }

}