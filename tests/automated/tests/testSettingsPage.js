/**
 * Created by Vlado on 12-Oct-16.
 */

/*global
 module, require
 */

var globals = require('../globals.json');
var commonWrapper = require('../commonWrapper.js');
var pageConstants = require('../pageConstants.js');

var G = globals;
var S = pageConstants.SETTINGS.SELECTORS;
var V = pageConstants.SETTINGS.VALUES;

module.exports = {

    before: function (browser) {
        'use strict';
        commonWrapper.wrapBrowser(browser);
        browser
            .openPageAsAdmin(G.pageGootenSettingsUrl);
    },

    after: function (browser) {
        'use strict';
        commonWrapper.unwrapBrowser(browser);
        browser.end();
    },

    "Set recipe ID tests": function (browser) {
        'use strict';
        browser
            // Billing key is set to ensure that it is not reason for showing notice
            .clearValueSet(S.BILLING_KEY_INPUT, G.validBillingKey)

            // Set valid recipe ID test
            .clearValueSet(S.RECIPE_ID_INPUT, G.validRecipeId)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.RECIPE_ID_INPUT, G.validRecipeId)
            .assertNoNoticeWithText(V.NOTICE_EMPTY_KEYS)

            // Set invalid (empty) recipe ID test
            .clearValueSet(S.RECIPE_ID_INPUT, '')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.RECIPE_ID_INPUT, '')
            .assertHasNoticeWithText(V.NOTICE_EMPTY_KEYS);
    },

    "Set billing key tests": function (browser) {
        'use strict';
        browser
            // Recipe ID is set to ensure that it is not reason for showing notice
            .clearValueSet(S.RECIPE_ID_INPUT, G.validRecipeId)

            // Set valid billing key
            .clearValueSet(S.BILLING_KEY_INPUT, G.validBillingKey)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.BILLING_KEY_INPUT, G.validBillingKey)
            .assertNoNoticeWithText(V.NOTICE_EMPTY_KEYS)

            // Set invalid (empty) recipe ID test
            .clearValueSet(S.BILLING_KEY_INPUT, '')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.BILLING_KEY_INPUT, '')
            .assertHasNoticeWithText(V.NOTICE_EMPTY_KEYS);
    },

    "Set order testing tests": function (browser) {
        'use strict';
        browser
            // Recipe ID & billing key are set to ensure that they do not override notice for testing orders
            .clearValueSet(S.RECIPE_ID_INPUT, G.validRecipeId)
            .clearValueSet(S.BILLING_KEY_INPUT, G.validBillingKey)

            // Order testing enabled test
            .setCheckboxChecked(S.ORDER_TESTING_CHECKBOX)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assertCheckboxChecked(S.ORDER_TESTING_CHECKBOX)
            .assertNoNoticeWithText(V.NOTICE_EMPTY_KEYS)
            .assertHasNoticeWithText(V.NOTICE_ORDER_TESTING)

            // Order testing disabled test
            .setCheckboxNotChecked(S.ORDER_TESTING_CHECKBOX)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assertCheckboxNotChecked(S.ORDER_TESTING_CHECKBOX)
            .assertNoNoticeWithText(V.NOTICE_EMPTY_KEYS)
            .assertNoNoticeWithText(V.NOTICE_ORDER_TESTING);
    },

    "Set dynamic shipping": function (browser) {
        'use strict';
        browser
            // Dynamic shipping enabled
            .setCheckboxChecked(S.DYNAMIC_SHIPPING_CHECKBOX)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assertCheckboxChecked(S.DYNAMIC_SHIPPING_CHECKBOX)

            // Dynamic shipping disabled
            .setCheckboxNotChecked(S.DYNAMIC_SHIPPING_CHECKBOX)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assertCheckboxNotChecked(S.DYNAMIC_SHIPPING_CHECKBOX);
    },

    "Set dynamic tax": function (browser) {
        'use strict';
        browser
            // Dynamic shipping enabled
            .setCheckboxChecked(S.DYNAMIC_TAX_CHECKBOX)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assertCheckboxChecked(S.DYNAMIC_TAX_CHECKBOX)

            // Dynamic shipping disabled
            .setCheckboxNotChecked(S.DYNAMIC_TAX_CHECKBOX)
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assertCheckboxNotChecked(S.DYNAMIC_TAX_CHECKBOX);
    },

    "Set product tag strategy tests": function (browser) {
        'use strict';
        browser
            // Validate setting value to none
            .click(S.PRODUCT_TAG_STRATEGY_SELECT + ' option[value=none]')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_TAG_STRATEGY_SELECT, 'none')

            // Validate setting value to Product Variant Options
            .click(S.PRODUCT_TAG_STRATEGY_SELECT + ' option[value=pvo]')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_TAG_STRATEGY_SELECT, 'pvo');
    },

    "Set product categories strategy tests": function (browser) {
        'use strict';
        browser
            // Validate setting value to none
            .click(S.PRODUCT_CATEGORY_STRATEGY_SELECT + ' option[value=none]')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_CATEGORY_STRATEGY_SELECT, 'none')

            // Validate setting value to Product Type
            .click(S.PRODUCT_CATEGORY_STRATEGY_SELECT + ' option[value=productType]')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_CATEGORY_STRATEGY_SELECT, 'productType')

            // Validate setting value to Gooten Categories
            .click(S.PRODUCT_CATEGORY_STRATEGY_SELECT + ' option[value=gootenCategories]')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_CATEGORY_STRATEGY_SELECT, 'gootenCategories');
    },

    "Set product profit tests": function (browser) {
        'use strict';
        browser
            // Negative value should result as if value is not entered
            .clearValueSet(S.PRODUCT_PROFIT_INPUT, '-1')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_PROFIT_INPUT, '')

            // Zero value should result as if value is not entered
            .clearValueSet(S.PRODUCT_PROFIT_INPUT, '0')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_PROFIT_INPUT, '')

            // Not-number value should result as if value is not entered
            .clearValueSet(S.PRODUCT_PROFIT_INPUT, 'gooten')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_PROFIT_INPUT, '')

            // Not-number value should result as if value is not entered
            .clearValueSet(S.PRODUCT_PROFIT_INPUT, '40')
            .submitFormAndWait(S.SUBMIT_SETTINGS_BUTTON)
            .assert.value(S.PRODUCT_PROFIT_INPUT, '40');
    }

}