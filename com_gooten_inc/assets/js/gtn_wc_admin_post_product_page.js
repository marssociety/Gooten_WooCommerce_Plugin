/**
 * Created by Vlado on 26-Aug-16.
 */

/*global
 jQuery, GTN_WC, window
 */

/**
 * Encapsulates functionality on post-product admin page for WooCommerce plugin.
 */
GTN_WC.createAdminPostProductPage = function () {
    'use strict';

    var //
        that = {},

        /** The name of currently selected PRP name. */
        currentlySelectedProduct,

        /** Response of GET /productvariants fetched for currently selected PRP. */
        productVariantsRes,

        /** Loading indicator control shown over categories view. */
        categoriesLoadingIndicator,

        /** Loading indicator control shown over tags view. */
        tagsLoadingIndicator,

        /** Loading indicator control shown over product image and product gallery views. */
        imagesLoadingIndicator,

        /** Loading indicator control shown over Gooten product data view. */
        productDataIndicator,

        /** Error message shown when setting images fails. */
        imagesErrorMessage,

        /** Grouped PRPs by PRP name. */
        groupedPRPs,

        /** Template used for generating variant views. */
        variantTemplate,

        /** Hidden input holding PRRs JSON. Value is sent to backend for the case of optimization. */
        inputPRPJSON,

        /**
         * Calculates customer price based on configuration for supplied PRP.
         *
         * @param product The PRP for who customer price is being calculated.
         * @returns {string|number} Calculated customer price if product profit was configured or empty string otherwise.
         */
        calculateCustomerPrice = function (product) {
            var price = product.Price.Price;
            if (price || price === 0) {
                if (GTN_WC.Config.productProfit) {
                    price += ((GTN_WC.Config.productProfit / 100) * price);
                    price = Math.round(price * 100) / 100;
                } else {
                    price = '';
                }
            } else {
                price = '';
            }
            return price;
        },

        /**
         * Populates product title.
         *
         * @param title The title value to be set.
         */
        setProductTitle = function (title) {
            jQuery('#title').val(title || '');
            jQuery('#title-prompt-text').hide();
        },

        /**
         * Populates product content (description).
         *
         * @param content The content value to be set.
         */
        setProductContent = function (content) {
            jQuery("#content-html").click();
            jQuery("#content").val(content || '');
            jQuery("#content-tmce").click();
        },

        /**
         * Populates short product description.
         *
         * @param description The value to be set.
         */
        setShortProductDescription = function (description) {
            jQuery("#excerpt-html").click();
            jQuery("#excerpt").val(description || '');
            jQuery("#excerpt-tmce").click();
        },

        /**
         * Populates product tags according to configured strategy.
         *
         * @param productGroup List of PRPs, or falsy value if UI should be cleared.
         */
        setProductTags = function (productGroup) {
            // Clears all entered product tags
            jQuery('.tagchecklist').children().each(function () {
                jQuery('#product_tag-check-num-0').click();
            });

            if (productGroup) {
                if (GTN_WC.Config.productTags === 'pvo') {
                    var firstItem = productGroup[0].Items[0];
                    if (firstItem) {
                        tagsLoadingIndicator.show();
                        GTN_WC.API.getProductVariants(firstItem.ProductId).done(function (res) {
                            if (currentlySelectedProduct === productGroup[0].Name) {
                                tagsLoadingIndicator.hide();
                                var tags, productVariant = GTN_WC.Utils.searchProductVariantBySKU(res.ProductVariants, firstItem.ProductVariantSku);
                                tags = jQuery.map(productVariant.Options, function (option) {
                                    return option.Value;
                                }).join();

                                jQuery('#new-tag-product_tag').val(tags).submit();
                            }
                        });
                    }
                }
            }
        },

        /**
         * Populates product categories according to configured strategy.
         *
         * @param productGroup List of PRPs, or falsy value if UI should be cleared.
         */
        setProductCategories = function (productGroup) {
            // Un-check all selected product categories
            jQuery('#product_catchecklist').find(':checkbox').each(function () {
                jQuery('#' + this.id).prop("checked", false);
            });

            if (productGroup) {
                var addProductCategory = function (category) {
                    jQuery('#newproduct_cat').val(category);
                    jQuery('#product_cat-add-submit').click();
                };
                if (GTN_WC.Config.productCategories === 'productType' || GTN_WC.Config.productCategories === 'gootenCategories') {
                    categoriesLoadingIndicator.show();
                    GTN_WC.API.getProducts().done(function (res) {
                        if (currentlySelectedProduct === productGroup[0].Name) {
                            categoriesLoadingIndicator.hide();
                            var firstItem = productGroup[0].Items[0], originalProduct;
                            if (firstItem) {
                                originalProduct = GTN_WC.Utils.searchProductById(res.Products, firstItem.ProductId);
                                if (originalProduct) {
                                    if (GTN_WC.Config.productCategories === 'productType') {
                                        addProductCategory(originalProduct.Name);
                                    } else {
                                        jQuery.each(originalProduct.Categories, function () {
                                            addProductCategory(this.Name);
                                        });
                                    }
                                }
                            }
                        }
                    });
                }
            }
        },

        /**
         * Clears product image and product gallery.
         *
         * @param callback The callback invoked after clear action has finished.
         */
        clearProductImages = function (callback) {
            jQuery('#product_image_gallery').val('');
            jQuery('#product_images_container').find('ul li').remove();
            jQuery('#remove-post-thumbnail').click();

            // Process with callback after images have been cleared
            GTN_WC.Utils.doWhenReady(function () {
                return jQuery('#set-post-thumbnail').find('img').length <= 0;
            }, callback);
        },

        /**
         * Sets product image and product gallery for supplied product group.
         *
         * @param productGroup List of PRPs.
         */
        setProductImages = function (productGroup) {
            if (imagesErrorMessage) {
                imagesErrorMessage.hide();
                imagesErrorMessage = null;
            }
            clearProductImages(function () {
                if (productGroup) {
                    imagesLoadingIndicator.show();
                    var productPreviewImage = GTN_WC.Utils.getGeneratedProductPreviewImgUrl(productGroup[0].Images),
                        galleryImages = jQuery.map(productGroup[0].Images, function (img) {
                            return img.Url;
                        });
                    GTN_WC.WP_API.uploadImages(jQuery('#post_ID').val(), productPreviewImage, galleryImages)
                        .done(function (data) {
                            if (currentlySelectedProduct === productGroup[0].Name) {
                                imagesLoadingIndicator.hide();

                                var productImageContent, productGalleryContent, galleryElement;
                                productImageContent = jQuery('#postimagediv .inside');
                                productGalleryContent = jQuery('#woocommerce-product-images .inside');
                                galleryElement = jQuery(window.atob(data.productGalleryHTML));

                                // Set product image
                                jQuery('#_thumbnail_id').val(data.productImageID);
                                jQuery('#set-post-thumbnail').text('').append(window.atob(data.productImageHTML));
                                productImageContent.append('<p class="hide-if-no-js howto" id="set-post-thumbnail-desc">Click the image to edit or update</p>');
                                productImageContent.append('<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">Remove product image</a></p>');

                                // Set gallery
                                productGalleryContent.find('.product_images').remove();
                                productGalleryContent.find('#product_images_container').append(galleryElement.find('.product_images'));
                                productGalleryContent.find('#product_image_gallery').val(galleryElement.find('#product_image_gallery').val());
                            }
                        })
                        .fail(function () {
                            if (currentlySelectedProduct === productGroup[0].Name) {
                                imagesLoadingIndicator.hide();
                                imagesErrorMessage = GTN_WC.Utils.createErrorMessage(jQuery('#post'), 'Error loading product images. <a href="#" class="callback" >Please try again</a>.', function () {
                                    setProductImages(productGroup);
                                }).show();
                            }
                        });
                }
            });
        },

        /**
         * Updates variant views ordering.
         */
        syncVariantOrder = function () {
            var i = 0;
            jQuery('#gooten_variants_holder .gtn-variant').each(function () {
                var element = jQuery(this), header;
                element.find('[name]').each(function () {
                    var input = jQuery(this);
                    input.attr('name', input.attr('name').replace(new RegExp('\\[[0-9]*\\]', 'g'), '[' + i + ']'));
                });
                header = element.find('.gtn-variant-header strong');
                header.html(header.html().replace(new RegExp('#[0-9]*', 'g'), '#' + (i + 1)));
                i += 1;
            });
        },

        /**
         * Shows messages for product variants that indicate that variants have duplicates.
         */
        showDuplicateVariantsMessage = function () {
            var variantElements, skuToVariantMap = {};
            variantElements = jQuery('#gooten_variants_holder .gtn-variant');

            variantElements.each(function (i) {
                var sku = jQuery(this).data('gtn-sku');
                if (!skuToVariantMap[sku]) {
                    skuToVariantMap[sku] = [];
                }
                skuToVariantMap[sku].push(i);
            });

            jQuery('.gtn-duplicate-variant').remove();
            jQuery.each(skuToVariantMap, function () {
                var duplicates = this;
                if (duplicates.length > 1) {
                    jQuery.each(duplicates, function () {
                        // Show message
                        var otherDuplicates, html;
                        otherDuplicates = duplicates.slice(0);
                        otherDuplicates.splice(otherDuplicates.indexOf(this), 1);
                        otherDuplicates = jQuery.map(otherDuplicates, function (element) {
                            return '#' + (element + 1);
                        });
                        html = GTN_WC.Utils.format('<span class="gtn-duplicate-variant">This variant is duplicate of variant(s): {{duplicates}}</span>', {"duplicates": otherDuplicates.join(', ')});
                        jQuery(variantElements[this]).find('.gtn-variant-header-message').append(html);
                    });
                }
            });
        },

        /**
         * Creates PRP/variant identificator for supplied PRP.
         *
         * @param product The PRP for who identificator should be created.
         * @returns {string} PRP/variant identificator.
         */
        createOptionsIdentificator = function (product) {
            if (productVariantsRes && product) {
                var options, productVariant = GTN_WC.Utils.searchProductVariantBySKU(productVariantsRes.ProductVariants, product.Items[0].ProductVariantSku);
                options = jQuery.map(productVariant.Options, function (option) {
                    return option.Value;
                }).join(' &mdash; ');
                return '(' + options + ')';
            }
            return '';
        },

        /**
         * Utility method for creating variant template data based of supplied product.
         * @param product The prp for who variant data is to be created.
         * @param ordinal The ordinal number of this variant.
         * @returns Object holding basic variant template data.
         */
        createVariantData = function (product, ordinal) {
            return {
                'inputNamePrefix': '_gooten_variants[' + ordinal + ']',
                'variablePostId': '',
                'imageSrc': GTN_WC.Utils.getGeneratedProductPreviewImgUrl(product.Images) + '?width=130',
                'isEnabledChecked': 'checked="checked"', // Set enabled by default
                'sku': product.Sku,
                'price': calculateCustomerPrice(product),
                'description': product.Description,
                'gootenSku': product.Sku,
                'gootenPrice': product.Price.Price,
                'variantTitle': '#' + (ordinal + 1)
            };
        },

        /**
         * Creates variant element for supplied variant data.
         *
         * @param productGroup The product group for this product variant.
         * @param variantData Variant data indicating default properties of created variant.
         * @returns {jQuery} Created variant element.
         */
        createVariantElement = function (productGroup, variantData) {
            // Create variant element using template
            var variantElement = jQuery(GTN_WC.Utils.format(variantTemplate, variantData));

            // Create variant selection
            GTN_WC.createVariantSelect(
                variantElement.find('.gtn-variant-selector'),
                productGroup,
                productVariantsRes,
                function (sku) {
                    if (sku) {
                        variantElement.find('.gtn-variant-not-available').hide();
                    } else {
                        variantElement.find('.gtn-variant-not-available').show();
                    }
                    variantElement.data('gtn-sku', sku || '');
                    variantElement.find('input.productSku').val(sku || '');
                    updateUndefinedVariants(productGroup);
                    showDuplicateVariantsMessage();
                }
            ).init();

            // Set delete button action
            variantElement.find('.gtn-delete-variation').click(function (e) {
                e.preventDefault();
                productDataIndicator.show();
                var element = jQuery(this);
                GTN_WC.WP_API.deleteVariation(element.data('id'))
                    .done(function () {
                        productDataIndicator.hide();
                        variantElement.remove();
                        syncVariantOrder();
                        updateUndefinedVariants(productGroup);
                        showDuplicateVariantsMessage();
                    })
                    .fail(function () {
                        productDataIndicator.hide();
                    });
            });

            GTN_WC.Utils.tipTip(variantElement);

            return variantElement;
        },

        /**
         * Updates undefined variant views.
         *
         * @param productGroup List of PRPs.
         */
        updateUndefinedVariants = function (productGroup) {
            var skusNotAdded, undefinedVariantsHolder, template;
            skusNotAdded = jQuery.map(productGroup, function (prp) {
                return prp.Sku;
            });
            jQuery('#gooten_variants_holder .gtn-variant').each(function () {
                var index = jQuery.inArray(jQuery(this).data('gtn-sku'), skusNotAdded);
                if (index !== -1) {
                    skusNotAdded.splice(index, 1);
                }
            });

            undefinedVariantsHolder = jQuery('#gooten_undefined_variants');

            // Show variants that are not added
            if (skusNotAdded.length) {
                undefinedVariantsHolder.find('.gtn-variant-undefined').remove();
                template = '<div class="gtn-variant-undefined"><h3>{{variationTitle}}<a href="#" class="gtn-variant-add" data-sku="{{gootenSku}}">Add variant</a></h3></div>';
                jQuery.each(productGroup, function () {
                    if (jQuery.inArray(this.Sku, skusNotAdded) !== -1) {
                        if (GTN_WC.Utils.searchProductVariantBySKU(productVariantsRes, this.Items[0].ProductVariantSku)) {
                            undefinedVariantsHolder.append(jQuery(GTN_WC.Utils.format(
                                template,
                                {
                                    'gootenSku': this.Sku,
                                    'variationTitle': createOptionsIdentificator(this)
                                }
                            )));
                        }
                    }
                });
                if (!undefinedVariantsHolder.find('.gtn-variant-undefined').length) {
                    undefinedVariantsHolder.hide();
                } else {
                    undefinedVariantsHolder.find('.gtn-variant-add').click(function (e) {
                        e.preventDefault();
                        var element = jQuery(this), variantsHolder = jQuery('#gooten_variants_holder'), product;
                        element.closest('.gtn-variant-undefined').remove();
                        product = GTN_WC.Utils.searchProductBySKU(productGroup, element.data('sku'));
                        variantsHolder.append(createVariantElement(productGroup, createVariantData(product, variantsHolder.find('.gtn-variant').length)));
                        if (!undefinedVariantsHolder.find('.gtn-variant-undefined').length) {
                            undefinedVariantsHolder.hide();
                        }
                        showDuplicateVariantsMessage();
                    });
                    undefinedVariantsHolder.show();
                }
            } else {
                undefinedVariantsHolder.hide();
            }
        },

        /**
         * Updates product variants UI for supplied product group.
         *
         * @param productGroup List of PRPs, or falsy value if UI should be cleared.
         */
        setProductVariants = function (productGroup) {
            productVariantsRes = null;
            var variantsHolder = jQuery('#gooten_variants_holder');
            variantsHolder.find('.gtn-variant').remove();
            if (productGroup && productGroup.length) {
                productDataIndicator.show();
                GTN_WC.API.getProductVariants(productGroup[0].Items[0].ProductId).done(function (res) {
                    productDataIndicator.hide();
                    productVariantsRes = res;
                    var numberOfUnavailableSKUs = 0;
                    jQuery.each(productGroup, function (i, product) {
                        if (GTN_WC.Utils.searchProductVariantBySKU(productVariantsRes.ProductVariants, product.Items[0].ProductVariantSku)) {
                            variantsHolder.append(createVariantElement(productGroup, createVariantData(product, i)));
                        } else {
                            numberOfUnavailableSKUs += 1;
                        }
                    });
                    if (numberOfUnavailableSKUs) {
                        GTN_WC.Utils.createErrorMessage(jQuery('#post'), 'Some of SKUs are not shown as they are not available for this shop\'s location.').show();
                    }
                    variantsHolder.show();
                    showDuplicateVariantsMessage();
                });
            }

            // Reset undefined variants area
            jQuery('#gooten_undefined_variants').hide();
            jQuery('#gooten_undefined_variants .gtn-variant-undefined').remove();
        },

        /**
         * Shows warning message next to product variants that are no longer available via Gooten API.
         *
         * @param productGroup List of PRPs, or falsy value if UI should be cleared.
         */
        showUnavailableVariantsWarning = function (productGroup) {
            var skus, skusNotAvailable = [], variantElements;
            variantElements = jQuery('#gooten_variants_holder .gtn-variant');
            skus = jQuery.map(productGroup, function (prp) {
                return prp.Sku;
            });
            variantElements.each(function () {
                var sku = jQuery(this).data('gtn-sku'), index;
                index = jQuery.inArray(sku, skus);
                if (index === -1) {
                    skusNotAvailable.push(sku);
                }
            });
            jQuery.each(skusNotAvailable, function () {
                var sku = this;
                variantElements.each(function () {
                    var element = jQuery(this), elementSku = element.data('gtn-sku');
                    if (sku === elementSku) {
                        element.find('.gtn-variant-warning').show();
                        return false;
                    }
                });
            });
        },

        /**
         * Sets initial values when page is shown for editing.
         *
         * @param prpName The name of PRP being edited.
         */
        setEditValues = function (prpName) {
            var productGroup = groupedPRPs[prpName];

            currentlySelectedProduct = prpName;
            inputPRPJSON.val(window.btoa(JSON.stringify(productGroup)));

            productDataIndicator.show();
            GTN_WC.API.getProductVariants(productGroup[0].Items[0].ProductId).done(function (res) {
                productDataIndicator.hide();
                productVariantsRes = res;

                var variantsHolder = jQuery('#gooten_variants_holder');
                jQuery.each(GTN_WC.variantData, function (i, val) {
                    var variantData = jQuery.extend({}, val, {
                        'inputNamePrefix': '_gooten_variants[' + i + ']',
                        'variantTitle': '#' + (i + 1)
                    });
                    variantsHolder.append(createVariantElement(productGroup, variantData));
                    showDuplicateVariantsMessage();
                });

                showUnavailableVariantsWarning(productGroup);
                updateUndefinedVariants(productGroup);
            });
        },

        /**
         * Updates UI for supplied product group.
         *
         * @param productGroup List of PRPs, or falsy value if UI should be cleared.
         */
        setProductValues = function (productGroup) {
            if (productGroup && productGroup.length) {
                var refProduct = productGroup[0];
                currentlySelectedProduct = refProduct.Name;

                inputPRPJSON.val(window.btoa(JSON.stringify(productGroup)));
                setProductVariants(productGroup);
                setProductTitle(refProduct.Name);
                setProductContent(refProduct.Description);
                setShortProductDescription(refProduct.Description);
                setProductTags(productGroup);
                setProductCategories(productGroup);
                setProductImages(productGroup);
            } else {
                currentlySelectedProduct = null;

                inputPRPJSON.val('');
                setProductVariants();
                setProductTitle();
                setProductContent();
                setShortProductDescription();
                setProductTags();
                setProductCategories();
                setProductImages();

                tagsLoadingIndicator.hide();
                categoriesLoadingIndicator.hide();
                imagesLoadingIndicator.hide();
            }
        },

        /**
         * Sets functionality for PRP dropdown element.
         */
        setupProductSearch = function () {
            productDataIndicator.show();
            GTN_WC.API.getPreconfiguredProducts()
                .done(function (res) {
                    productDataIndicator.hide();
                    var prpDropdownElement = jQuery('#gooten_prp_select');
                    groupedPRPs = GTN_WC.Utils.groupProducts(res.PreconfiguredProducts);

                    prpDropdownElement.select2({
                        allowClear: true,
                        placeholder: prpDropdownElement.data('placeholder'),
                        multiple: false,
                        data: jQuery.map(groupedPRPs, function (group) {
                            var gName = group[0].Name;
                            return {
                                id: gName,
                                text: gName,
                                name: gName,
                                count: group.length,
                                imageUrl: GTN_WC.Utils.getGeneratedProductPreviewImgUrl(group[0].Images)
                            };
                        }),
                        formatResult: function (data) {
                            var thumbnailUrl = data.imageUrl + "?height=60&width=60";
                            return '<div class="gtn-prp-item"><img class="gtn-lazy" data-original="' + thumbnailUrl + '" />'
                                + '<div class="gtn-prp-text-container">'
                                + '<div><b>' + data.name + '</b></div>'
                                + '<div>( ' + data.count + ' ' + (data.count === 1 ? 'SKU' : 'SKUs') + ' )</div>'
                                + '</div>'
                                + '</div>';
                        }
                    });
                    prpDropdownElement.on('change', function (e) {
                        setProductValues(groupedPRPs[e.val]);
                    });
                    prpDropdownElement.on('select2-loaded', function () {
                        jQuery('img.gtn-lazy').lazyload({
                            container: jQuery('#select2-drop .select2-results')
                        });
                    });
                    if (prpDropdownElement.val()) {
                        setEditValues(prpDropdownElement.val());
                    }
                })
                .fail(function (res) {
                    productDataIndicator.hide();
                    if (res && res.InvalidRecipeId) {
                        GTN_WC.Utils.createErrorMessage(jQuery('#post'), 'Recipe ID is not set. Please visit  <a href="' + GTN_WC.Config.pluginSettingsPage + '">Settings page</a> and set your Recipe ID.').show();
                    } else {
                        GTN_WC.Utils.createErrorMessage(jQuery('#post'), 'Error getting data from Gooten. <a href="#" class="callback" >Please try again</a>.', function () {
                            setupProductSearch();
                        }).show();
                    }
                });
        };

    /**
     * Called to initialize page UI after DOM gets ready.
     */
    that.init = function () {
        var submitPostContainer = jQuery('#submitpost');
        categoriesLoadingIndicator = GTN_WC.Utils.createLoadingIndicator(jQuery('#product_catdiv'), submitPostContainer);
        tagsLoadingIndicator = GTN_WC.Utils.createLoadingIndicator(jQuery('#tagsdiv-product_tag'), submitPostContainer);
        imagesLoadingIndicator = GTN_WC.Utils.createLoadingIndicator(jQuery('#postimagediv'), jQuery('#woocommerce-product-images'), submitPostContainer);
        productDataIndicator = GTN_WC.Utils.createLoadingIndicator(jQuery('#gooten_product_data'));
        variantTemplate = jQuery('<textarea/>').html(jQuery('#gtn-variant-template').html()).text();
        inputPRPJSON = jQuery('#_gooten_prp_json');
        setupProductSearch();
    };

    return that;
};
