/**
 * Created by Vlado on 25-Nov-16.
 */

/**
 * Holds .js utility methods.
 */
GTN_WC.Utils = (function () {
    'use strict';

    var //

        /**
         * Searches array of PRPs by SKU.
         *
         * @param preconfiguredProducts The array of PRPs.
         * @param sku The SKU of PRP being searched.
         * @returns First PRP matching search terms or null if PRP with specified SKU was not found.
         */
        searchProductBySKU = function (preconfiguredProducts, sku) {
            if (sku && preconfiguredProducts && preconfiguredProducts.length) {
                var res = jQuery.grep(preconfiguredProducts, function (element) {
                    return element.Sku === sku;
                });
                return res.length ? res[0] : null;
            }
            return null;
        },

        /**
         * Searches array of PRPs whose `Item` have specified product variant SKU.
         *
         * @param preconfiguredProducts The array of PRPs.
         * @param sku The product variant SKU.
         * @returns First PRP matching search terms or null if PRP with specified SKU was not found.
         */
        searchProductWhereItemSKU = function (preconfiguredProducts, sku) {
            if (sku && preconfiguredProducts && preconfiguredProducts.length) {
                var res = jQuery.grep(preconfiguredProducts, function (element) {
                    return element.Items[0].ProductVariantSku === sku;
                });
                return res.length ? res[0] : null;
            }
            return null;
        },

        /**
         * Groups PRPs by PRP name.
         *
         * @param preconfiguredProducts The array of PRPs.
         * @returns Object were property names correspond to all unique PRP names and
         * property values correspond to array of all PRPs having same name.
         */
        groupProducts = function (preconfiguredProducts) {
            var grouped = {};
            jQuery.each(preconfiguredProducts, function () {
                if (!grouped[this.Name]) {
                    grouped[this.Name] = [];
                }
                grouped[this.Name].push(this);
            });
            return grouped;
        },

        /**
         * Gets option 'Value' property form option specified by option name.
         *
         * @param options The array of options.
         * @param optionName Option name of option being searched.
         * @returns The value of 'Value' property of null if option was not found.
         */
        getOptionValue = function (options, optionName) {
            if (optionName && options && options.length) {
                var res = jQuery.grep(options, function (option) {
                    return option.Name === optionName;
                });
                return res.length ? res[0].Value : null;
            }
            return null;
        },

        /**
         * Searches array of product variants by SKU.
         *
         * @param productVariants The array of product variants.
         * @param sku The SKU of product variant being searched.
         * @returns First product variant matching search terms or null if product variant with specified SKU was not found.
         */
        searchProductVariantBySKU = function (productVariants, sku) {
            if (sku && productVariants && productVariants.length) {
                var res = jQuery.grep(productVariants, function (element) {
                    return element.Sku === sku;
                });
                return res.length ? res[0] : null;
            }
            return null;
        },

        /**
         * Searches for product variant where product variant options matches specified options.
         *
         * @param productVariants The array of product variants.
         * @param options The array of options being searched in product variant.
         * @returns First product variant matching search terms or null if product variant with specified options was not found.
         */
        searchProductVariantByOptions = function (productVariants, options) {
            if (productVariants && productVariants.length && options && options.length) {
                var res = jQuery.grep(productVariants, function (variant) {
                    var i, option;
                    for (i = 0; i < variant.Options.length; i += 1) {
                        option = variant.Options[i];
                        if (option.Value !== getOptionValue(options, option.Name)) {
                            return null;
                        }
                    }
                    return variant;
                });
                return res.length ? res[0] : null;
            }
            return null;
        },

        /**
         * Searches array of products by ID.
         *
         * @param products Array of Gooten products.
         * @param id The ID of the product being searched.
         * @returns First product matching search terms or null if product with specified Id was not found.
         */
        searchProductById = function (products, id) {
            if (products && products.length) {
                var res = jQuery.grep(products, function (element) {
                    return element.Id === id;
                });
                return res.length ? res[0] : null;
            }
            return null;
        },

        /**
         * Returns image URL with 'generated-preview' id.
         *
         * @param {array} images Array of Gooten image JSONs.
         * @returns {string} The URL of 'generated-preview' image or null image was not found.
         */
        getGeneratedProductPreviewImgUrl = function (images) {
            if (images && images.length) {
                var res = jQuery.grep(images, function (img) {
                    return img.Id === 'generated-preview';
                });
                return res.length ? res[0].Url : null;
            }
            return null;
        },

        /**
         * Waits until conditions are meet before executing callback.
         *
         * @param {function} testReady Function used check if conditions are meet before executing callback.
         * @param {function} callback Function to execute when testReady returns true.
         */
        doWhenReady = function (testReady, callback) {
            if (testReady()) {
                callback();
            } else {
                window.setTimeout(function () {
                    doWhenReady(testReady, callback);
                }, 50);
            }
        },

        /**
         * Formats given string by replacing placeholder with values from supplied format array.
         *
         * @param {string} string The string to be formatted.
         * @param {object} format Object holding key-value pares used to format input string.
         * @returns {string} Formatted string.
         */
        format = function (string, format) {
            jQuery.each(format, function (key, value) {
                string = string.replace(new RegExp('{{' + key + '}}', 'g'), value);
            });
            return string;
        },

        /**
         * Creates loading indicator covering supplied elements.
         *
         * @arguments Array of jQuery elements.
         * @returns {{hide: Function, show: Function}} New loading indicator control object.
         */
        createLoadingIndicator = function () {
            var elements = arguments;
            return {
                hide: function () {
                    jQuery.each(elements, function () {
                        var count = this.data('gtn-indicators');
                        if (count === 1) {
                            this.find('.gtn-loading-indicator-overlay').remove();
                        }
                        this.data('gtn-indicators', count - 1);
                    });
                    return this;
                },
                show: function () {
                    jQuery.each(elements, function () {
                        if (this.find('.gtn-loading-indicator-overlay').length === 0) {
                            this.append(jQuery('<div class="blockUI blockOverlay gtn-loading-indicator-overlay"/>'));
                            this.data('gtn-indicators', 1);
                        } else {
                            this.data('gtn-indicators', parseInt(this.data('gtn-indicators'), 10) + 1);
                        }
                    });
                    return this;
                }
            };
        },

        /**
         * Creates error message control object.
         *
         * @param container Element where notice will be attached.
         * @param msg The message that should be shown. Could be HTML.
         * @param callback The callback function to be invoked when clicked on '.callback' element.
         * @returns {{show: Function, hide: Function}} New error message control object.
         */
        createErrorMessage = function (container, msg, callback) {
            var id = 'gtn-msg-' + parseInt(Math.random() * 100000, 10);
            return {
                show: function () {
                    var html = '<div class="notice notice-error" id="' + id + '"><p>' + msg + '</p></div>';
                    container.before(html);
                    if (callback) {
                        jQuery('#' + id + ' .callback').click(function (e) {
                            e.preventDefault();
                            jQuery('#' + id).remove();
                            callback();
                        });
                    }
                    return this;
                },
                hide: function () {
                    jQuery('#' + id).remove();
                    return this;
                }
            };
        },

        /**
         * Adds tooltip functionality to elements found in supplied parent element.
         *
         * @param element The parent element where tool tips elements are searched.
         */
        tipTip = function (element) {
            element.find('.tips, .help_tip, .woocommerce-help-tip').tipTip({
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            });
        },

        arrayUnique = function (array) {
            var u = {}, a = [], i, l;
            for (i = 0, l = array.length; i < l; i += 1) {
                if (!u.hasOwnProperty(array[i])) {
                    a.push(array[i]);
                    u[array[i]] = 1;
                }
            }
            return a;
        };

    return {
        searchProductBySKU: searchProductBySKU,
        searchProductWhereItemSKU: searchProductWhereItemSKU,
        groupProducts: groupProducts,
        getOptionValue: getOptionValue,
        searchProductVariantBySKU: searchProductVariantBySKU,
        searchProductVariantByOptions: searchProductVariantByOptions,
        searchProductById: searchProductById,
        getGeneratedProductPreviewImgUrl: getGeneratedProductPreviewImgUrl,
        doWhenReady: doWhenReady,
        format: format,
        createLoadingIndicator: createLoadingIndicator,
        createErrorMessage: createErrorMessage,
        tipTip: tipTip,
        arrayUnique: arrayUnique
    };
}());
