/**
 * Created by Vlado on 14-Oct-16.
 */

/*global
 module, require
 */

var globals = require('../globals.json');
var wrapper = require('../adminPostProductsWrapper.js');
var pageConstants = require('../pageConstants.js');
var extend = require('util')._extend;

var G = globals;
var S = pageConstants.ADMIN_POST_PRODUCT.SELECTORS;
var V = pageConstants.ADMIN_POST_PRODUCT.VALUES;

var testProduct = {
    title: 'WooCommerce PRP Automation Test',
    description: 'WooCommerce PRP Automation Test',
    variants: [
        {
            gootenCost: '13.26',
            description: 'WooCommerce PRP Automation Test',
            sku: 'woocommerce-atest-sku-1',
            options: [
                'Transparent', 'Nexus 5X'
            ]
        },
        {
            gootenCost: '13.26',
            description: 'WooCommerce PRP Automation Test',
            sku: 'woocommerce-atest-sku-2',
            options: [
                'Matte', 'Google Pixel'
            ]
        }
    ]
};

module.exports = {

    before: function (browser) {
        'use strict';
        var SS = pageConstants.SETTINGS.SELECTORS;
        wrapper.wrapBrowser(browser);

        browser
            // Ensure that valid recipe id and billing keys are set
            .openPageAsAdmin(G.pageGootenSettingsUrl)
            .clearValueSet(SS.RECIPE_ID_INPUT, G.validRecipeId)
            .clearValueSet(SS.BILLING_KEY_INPUT, G.validBillingKey)
            .submitFormAndWait(SS.SUBMIT_SETTINGS_BUTTON)

            // Reload aka load post product page
            .reloadPage();
    },

    after: function (browser) {
        'use strict';
        wrapper.unwrapBrowser(browser);
        browser.end();
    },

    'test visible product data tabs': function (browser) {
        'use strict';
        browser
            .selectGootenProductData()
            .assertProductDataTabs();
    },

    'test if PRPs are fetched': function (browser) {
        'use strict';
        browser
            .click(S.PRP_DROPDOWN)
            .keys('phone')
            .assert.visible(S.PRP_ITEM);
    },

    'test PRP auto populating values': function (browser) {
        'use strict';

        browser
            .reloadPage()
            .selectGootenProductData()
            .selectProduct(testProduct)
            .assertProductValues(testProduct)
            .publishProduct()
            .assertProductPublishedSuccessfully()
            .assertProductDataTabs()
            .waitForIndicator(S.GOOTEN_PRODUCT_DATA_CONTAINER)
            .assertProductValues(testProduct);
    },

    'test variation delete variant and adding variants': function (browser) {
        'use strict';

        browser
            .reloadPage()
            .selectGootenProductData()
            .selectProduct(testProduct)
            .waitForIndicators();

        var i, count = testProduct.variants.length, vs1, uvs1;
        vs1 = S.VARIANT_DATA + ':nth-child(1)';
        // There is some title element as 1st child, so first undefined variant is 2nd child
        uvs1 = S.UNDEFINED_VARIANT_DATA + ':nth-child(2)';

        // Test deleting variants
        for (i = 0; i < count; i += 1) {
            // Assert total number of variations
            browser.assertNumberOfElementsToBePresent(S.VARIANT_DATA, count - i);

            // Delete first variation in the list
            browser.moveToElement(vs1 + ' ' + S.VARIANT_DATA_HEADER, 100, 10, function () {
                browser.waitForElementVisible(vs1 + ' ' + S.VARIANT_DATA_DELETE_BUTTON, 1000, function () {
                    browser
                        .click(S.VARIANT_DATA_DELETE_BUTTON)
                        .pause(200);
                });
            });

            // Assert that variant is removed
            browser.assertNumberOfElementsToBePresent(S.VARIANT_DATA, count - i - 1);
        }

        // Test adding undefined variants
        for (i = 0; i < count; i += 1) {
            // Assert total number of variations
            browser.assertNumberOfElementsToBePresent(S.VARIANT_DATA, i);

            // Add first variation in the list
            browser.moveToElement(uvs1, 100, 10, function () {
                browser.waitForElementVisible(uvs1 + ' ' + S.UNDEFINED_VARIANT_DATA_ADD_BUTTON, 1000, function () {
                    browser
                        .click(S.UNDEFINED_VARIANT_DATA_ADD_BUTTON)
                        .pause(200);
                });
            });

            // Assert that variant is added
            browser.assertNumberOfElementsToBePresent(S.VARIANT_DATA, i + 1);
        }

        // Make sure that all is good
        browser
            .assertProductValues(testProduct);
    },

    'test PRP default profit': function (browser) {
        'use strict';

        var productNoProfit = extend({}, testProduct), productWithProfit = extend({}, testProduct);
        productWithProfit.variants[0].customerPrice = '17.24'; // With 30% profit
        productWithProfit.variants[1].customerPrice = '17.24'; // With 30% profit

        // Test disabling default profit
        browser
            .ensureProductProfit('-1')
            .selectProduct(productNoProfit)
            .waitForIndicators()
            .assertProductValues(productNoProfit)
            .saveChanges();

        // Test default profit with 30%
        browser
            .ensureProductProfit('30')
            .selectProduct(productWithProfit)
            .waitForIndicators()
            .assertProductValues(productWithProfit)
            .saveChanges();
    },

    'test PRP tags strategies': function (browser) {
        'use strict';

        var pvoTags = ["Nexus 5X", "Transparent"];

        // Test tags populated using 'none' strategy
        browser
            .ensureStrategy('tags', 'none')
            .selectProduct(testProduct)
            .assertTags([])
            .waitForIndicators()
            .saveChanges()
            .assertTags([]);

        // Test tags populated using 'pvo' strategy
        browser
            .ensureStrategy('tags', 'pvo')
            .selectProduct(testProduct)
            .assertTags(pvoTags)
            .waitForIndicators()
            .saveChanges()
            .assertTags(pvoTags);
    },

    'test PRP categories strategies': function (browser) {
        'use strict';

        var productTypeCategories = ['Phone Cases'], gootenCategories = ['Phone/Tablet Cases'];

        // Test categories populated using 'none' strategy
        browser
            .ensureStrategy('categories', 'none')
            .selectProduct(testProduct)
            .assertCategories([])
            .waitForIndicators()
            .saveChanges()
            .assertCategories([]);

        // Test categories populated using 'productType' strategy
        browser
            .ensureStrategy('categories', 'productType')
            .selectProduct(testProduct)
            .assertCategories(productTypeCategories)
            .waitForIndicators()
            .saveChanges()
            .assertCategories(productTypeCategories);

        // Test categories populated using 'gootenCategories' strategy
        browser
            .ensureStrategy('categories', 'gootenCategories')
            .selectProduct(testProduct)
            .assertCategories(gootenCategories)
            .waitForIndicators()
            .saveChanges()
            .assertCategories(gootenCategories);
    },

    'test PRP auto populating values (stressed)': function (browser) {
        'use strict';

        var productNames = ['14october-5', 'acrylic block cards', 'T-Shirts (8268)', 'Canvas Wrap 24 nov', 'Accessory Pouches (a156)'];

        browser
            .selectGootenProductData();

        // Simulate fast product selection
        productNames.forEach(function (element) {
            browser.selectProduct({title: element});
        });

        browser
            .selectProduct(testProduct)
            .assertProductValues(testProduct);
    }
}