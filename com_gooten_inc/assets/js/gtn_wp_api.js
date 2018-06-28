/**
 * Created by Vlado on 14-Dec-16.
 */

/*global
 jQuery, GTN_WC, woocommerce_admin_meta_boxes_variations
 */

/**
 * Holds methods for accessing API defined by wordpress/woocommerce.
 */
GTN_WC.WP_API = (function () {
    'use strict';

    var //

        /**
         * Uploads and sets product and gallery images to specified product.
         * (Calls GootenWCMediaUtils::setProductImages)
         *
         * @param postId The post ID of product.
         * @param productImage URL of product image.
         * @param productGalleryImages Array of URLs used as product gallery images.
         * @returns Promise that will be notified with request outcome.
         */
        uploadImages = function (postId, productImage, productGalleryImages) {
            var dfd = jQuery.Deferred(), ajaxParams;
            ajaxParams = {
                method: 'POST',
                url: GTN_WC.Config.productImagesEndpoint,
                data: {
                    postId: postId,
                    productImage: productImage,
                    productGalleryImages: productGalleryImages
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', GTN_WC.Config.nonce);
                }
            };
            jQuery.ajax(ajaxParams)
                .done(function (data) {
                    dfd.resolve(data);
                })
                .fail(function (jqXHR) {
                    if (jqXHR.status === 404) {
                        // Attempt again with fallback URL
                        jQuery.ajax(jQuery.extend({}, ajaxParams, {url: GTN_WC.Config.productImagesEndpointFallback}))
                            .done(function (data) {
                                dfd.resolve(data);
                            })
                            .fail(function () {
                                dfd.reject(arguments);
                            });
                    } else {
                        dfd.reject(arguments);
                    }
                });
            return dfd.promise();
        },

        /**
         * Issues request for removing product variant.
         *
         * @param id The ID of the product variant to be deleted.
         * If falsy value is supplied resolved promise will be returned.
         * @returns Promise that will be notified with request outcome.
         */
        deleteVariation = function (id) {
            if (id) {
                return jQuery.ajax({
                    method: 'POST',
                    url: woocommerce_admin_meta_boxes_variations.ajax_url,
                    data: {
                        action: 'woocommerce_remove_variations',
                        variation_ids: arguments,
                        security: woocommerce_admin_meta_boxes_variations.delete_variations_nonce
                    }
                });
            }
            var dfd = jQuery.Deferred();
            dfd.resolve();
            return dfd.promise();
        };

    return {
        uploadImages: uploadImages,
        deleteVariation: deleteVariation
    };
}());