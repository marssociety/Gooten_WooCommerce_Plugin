/**
 * Created by Vlado on 26-Dec-16.
 */

/*global
 jQuery, GTN_WC, window
 */

/**
 * Encapsulates functionality for creating dropdowns for choosing variant by specifying options.
 */
GTN_WC.createVariantSelect = function (element, prps, productVariants, onSkuChanged) {
    'use strict';

    var //
        that = {},

        selectElements = [],

        sameOptions = [],

        possibleVariants,

        createSelect = function (option, defaultValue) {
            var selectElement = jQuery('<select></select>').data('option-name', option.Name);
            jQuery.each(option.Values, function () {
                var isSelected = defaultValue === this.Value ? 'selected="selected"' : '';
                selectElement.append('<option value="' + this.Value + '" ' + isSelected + '>' + this.Value + '</option>');
            });
            return selectElement;
        },

        onSelectChanged = function () {
            var options, variant, prp;
            options = jQuery.map(selectElements, function (select) {
                return {
                    Name: select.data('option-name'),
                    Value: select.val()
                };
            });
            options = options.concat(sameOptions);
            variant = GTN_WC.Utils.searchProductVariantByOptions(possibleVariants, options);
            if (variant) {
                prp = GTN_WC.Utils.searchProductWhereItemSKU(prps, variant.Sku);
                if (prp) {
                    onSkuChanged(prp.Sku);
                } else {
                    onSkuChanged();
                }
            } else {
                onSkuChanged();
            }
        };

    that.init = function () {
        var selectedVariant, selectedPRP, possibleVariantSKUs, possibleOptions = [], possibleOptionsMap = {};
        possibleVariantSKUs = jQuery.map(prps, function (prp) {
            return prp.Items[0].ProductVariantSku;
        });
        possibleVariants = jQuery.grep(productVariants.ProductVariants, function (variant) {
            return jQuery.inArray(variant.Sku, possibleVariantSKUs) !== -1 ? variant : undefined;
        });
        jQuery.each(possibleVariants, function () {
            jQuery.each(this.Options, function () {
                if (!possibleOptionsMap[this.Name]) {
                    possibleOptionsMap[this.Name] = [];
                }
                possibleOptionsMap[this.Name].push(this.Value);
            });
        });
        jQuery.each(possibleOptionsMap, function (key, value) {
            value = GTN_WC.Utils.arrayUnique(value);
            if (value.length === 1) {
                sameOptions.push({
                    Name: key,
                    Values: value
                });
                delete possibleOptionsMap[key];
            }
        });
        jQuery.each(possibleOptionsMap, function (key, value) {
            possibleOptions.push({
                Name: key,
                Values: jQuery.map(GTN_WC.Utils.arrayUnique(value), function (e) {
                    return {
                        Name: key,
                        Value: e
                    };
                })
            });
        });

        selectedPRP = GTN_WC.Utils.searchProductBySKU(prps, element.data('sku'));
        selectedVariant = GTN_WC.Utils.searchProductVariantBySKU(possibleVariants, selectedPRP.Items[0].ProductVariantSku);
        jQuery.each(possibleOptions, function () {
            var selectElement = createSelect(this, GTN_WC.Utils.getOptionValue(selectedVariant.Options, this.Name));
            selectElement.change(onSelectChanged);
            selectElements.push(selectElement);
            element.append(selectElement);
        });
    };

    return that;
};