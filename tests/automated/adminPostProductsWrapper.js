/**
 * Created by Vlado on 19-Oct-16.
 */

/*global
 module, require
 */

var wrapper = require('./browserWrapper.js');
var commonWrapper = require('./commonWrapper.js');
var globals = require('./globals.json');
var pageConstants = require('./pageConstants.js');

var G = globals;
var S = pageConstants.ADMIN_POST_PRODUCT.SELECTORS;
var V = pageConstants.ADMIN_POST_PRODUCT.VALUES;

module.exports = {
    wrapBrowser: function (browser) {
        'use strict';

        var extension = {

            /**
             * Waits until all indicator are dismissed.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            waitForIndicators: function () {
                return browser
                    .waitForIndicator(S.PRODUCT_CATEGORY_CONTAINER)
                    .waitForIndicator(S.PRODUCT_TAGS_CONTAINER)
                    .waitForIndicator(S.PRODUCT_IMAGE_CONTAINER)
                    .waitForIndicator(S.PRODUCT_GALLERY_CONTAINER)
                    .waitForIndicator(S.SUBMIT_POST)
                    .pause(500);
            },

            /**
             * Asserts that page has values populated according to supplied product.
             *
             * @param product The product that should resemble rendered page.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertProductValues: function (product) {
                browser
                    .waitForIndicators(S.GOOTEN_PRODUCT_DATA_CONTAINER)
                    .pause(500);

                if (product.title !== undefined) {
                    browser.assert.value(S.PRODUCT_TITLE_INPUT, product.title);
                }
                if (product.description !== undefined) {
                    browser
                        .click("#content-html")
                        .assert.value("#content", product.description)
                        .click("#excerpt-html")
                        .assert.value("#excerpt", product.description);
                }
                if (product.variants) {
                    var i, variant, variantSelector;
                    for (i = 0; i < product.variants.length; i += 1) {
                        variant = product.variants[i];
                        variantSelector = S.VARIANT_DATA + ':nth-child(' + (i + 1) + ')';

                        browser.expect.element(variantSelector).to.be.present;
                        browser
                            .assert.value(variantSelector + ' input[id="_gooten_variants[' + i + '][sku]"]', variant.sku)
                            .assert.value(variantSelector + ' textarea[id="_gooten_variants[' + i + '][description]"]', variant.description);
                        if (variant.customerPrice) {
                            browser.assert.value(variantSelector + ' input[id="_gooten_variants[' + i + '][price]"]', variant.customerPrice);
                        }

                        // Assert select values
                        if (variant.options) {
                            var j, selectSelector;
                            for (j = 0; j < variant.options.length; j += 1) {
                                selectSelector = variantSelector + ' ' + S.VARIANT_DATA_OPTIONS_CONTAINER + ' select:nth-child(' + (j + 1) + ')';
                                browser.expect.element(selectSelector).to.be.present;
                                browser.assert.value(selectSelector, variant.options[j]);
                            }
                        }
                    }
                }

                // Wait for categories and tags to be populated
                // We intentionally do not assert these values now
                browser
                    .waitForIndicator(S.PRODUCT_CATEGORY_CONTAINER)
                    .waitForIndicator(S.PRODUCT_TAGS_CONTAINER);

                // Wait for images to be populated
                browser
                    .waitForIndicator(S.PRODUCT_IMAGE_CONTAINER)
                    .waitForIndicator(S.PRODUCT_GALLERY_CONTAINER);

                if (product.images !== undefined) {
                    browser
                        .pause(2000) // Give some time for images to be rendered
                        .assert.visible(S.PRODUCT_IMAGE_ITEM)
                        .assertHasSrcAttribute(S.PRODUCT_IMAGE_ITEM)
                        .assert.visible(S.PRODUCT_GALLERY_ITEM);
                }

                return browser;
            },

            /**
             * Performs series of actions needed to select supplied product in PRP dropdown.
             *
             * @param product The product that should be selected from PRP dropdown.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            selectProduct: function (product) {
                return browser
                    .click(S.PRP_DROPDOWN)
                    .clearValueSet(S.PRP_DROPDOWN_INPUT, product.title)
                    .assert.visible(S.PRP_ITEM)
                    .click(S.PRP_ITEM)
                    .pause(500); // Give some time click action to finish
            },

            /**
             * Performs series of actions needed to publish product entered on this page.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            publishProduct: function () {
                return browser
                    .click('#publish')
                    .pause(500)
                    .waitForBody();
            },

            /**
             * Performs series of actions needed to save current changes entered on this page.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            saveChanges: function () {
                return browser
                    .click('#save-post')
                    .pause(500)
                    .waitForBody();
            },

            /**
             * Selects Gooten product from the list of product data.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            selectGootenProductData: function () {
                return browser
                    .click('#product-type option[value=gooten_product]')
                    .pause(100); // Give some time for click to complete
            },

            /**
             * Reloads post product page.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            reloadPage: function () {
                return browser
                    .url(G.url + G.pageAdminPostProductUrl)
                    .pause(500)
                    .waitForBody()
                    .waitForIndicator(S.GOOTEN_PRODUCT_DATA_CONTAINER)
                    .selectGootenProductData();
            },

            /**
             * Asserts that correct product data tabs are visible for Gooten product.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertProductDataTabs: function () {
                return browser
                    .assert.visible('.advanced_options')
                    .assert.hidden('.attribute_options')
                    .assert.visible('.linked_product_options')
                    .assert.visible('.gooten_product_config_options')
                    .assert.hidden('.inventory_options')
                    .assert.hidden('.general_options')
                    .assert.hidden('.variations_options');
            },

            /**
             * Asserts that product was published successfully.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertProductPublishedSuccessfully: function () {
                return browser
                    .assertHasNoticeWithText(V.NOTICE_PRODUCT_PUBLISHED);
            },

            /**
             * Ensures that default product profit is set to supplied value
             *
             * @param profit The product profit value to set
             * @returns  @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            ensureProductProfit: function (profit) {
                var SS = pageConstants.SETTINGS.SELECTORS;
                return browser
                    .url(G.url + G.pageGootenSettingsUrl)
                    .acceptAlert() // TODO Y U NO WORK ??
                    .pause(500)
                    .waitForBody()
                    .clearValueSet(SS.PRODUCT_PROFIT_INPUT, profit)
                    .submitFormAndWait(SS.SUBMIT_SETTINGS_BUTTON)
                    .reloadPage();
            },

            /**
             * Ensures that correct strategy is selected for tags/categories.
             *
             * @param type The type of strategy. 'tags' for tags, otherwise it's categories.
             * @param strategy The strategy to select.
             * @returns  @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            ensureStrategy: function (type, strategy) {
                var SS = pageConstants.SETTINGS.SELECTORS;
                return browser
                    .url(G.url + G.pageGootenSettingsUrl)
                    .acceptAlert() // TODO Y U NO WORK ??
                    .pause(500)
                    .waitForBody()
                    .click((type === 'tags' ? SS.PRODUCT_TAG_STRATEGY_SELECT : SS.PRODUCT_CATEGORY_STRATEGY_SELECT) + ' option[value=' + strategy + ']')
                    .submitFormAndWait(SS.SUBMIT_SETTINGS_BUTTON)
                    .reloadPage();
            },

            /**
             * Asserts that supplied tags are entered on page.
             *
             * @param tags The list of tags that should be visible on page.
             * @returns  @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertTags: function (tags) {
                if (tags && tags.length) {
                    return browser
                        .waitForIndicator(S.PRODUCT_TAGS_CONTAINER)
                        .assert.elementPresent('.tagchecklist a');
                    // TODO assert correct items
                }
                return browser
                    .waitForIndicator(S.PRODUCT_TAGS_CONTAINER)
                    .assert.elementNotPresent('.tagchecklist a');
            },

            /**
             * Asserts that supplied categories are entered on page.
             *
             * @param categories The list of categories that should be visible on page.
             * @returns  @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertCategories: function (categories) {
                if (categories && categories.length) {
                    return browser
                        .waitForIndicator(S.PRODUCT_CATEGORY_CONTAINER)
                        .assert.elementPresent('#product_catchecklist input:checked');
                    // TODO assert correct items
                }
                return browser
                    .waitForIndicator(S.PRODUCT_CATEGORY_CONTAINER)
                    .assert.elementNotPresent('#product_catchecklist input:checked');
            }
        };

        commonWrapper.wrapBrowser(browser);
        wrapper.wrapBrowser(browser, 'adminPostProduct', extension);
        return browser;
    },

    unwrapBrowser: function (browser) {
        'use strict';

        commonWrapper.unwrapBrowser(browser);
        wrapper.unwrapBrowser(browser, 'adminPostProduct');
        return browser;
    }
}