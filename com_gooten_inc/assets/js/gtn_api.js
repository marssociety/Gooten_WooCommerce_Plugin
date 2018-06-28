/**
 * Created by Vlado on 29-Aug-16.
 */

/**
 * Encapsulates Gooten API functionality.
 */
GTN_WC.API = (function () {
    'use strict';

    var //
        cache = {},

        baseEndpointConfig = {
            countryCode: GTN_WC.Config.countryCode,
            currencyCode: GTN_WC.Config.currencyCode
        },

        urlFactory = function (endpoint, queryParams) {
            var url = GTN_WC.Config.apiUrl + endpoint + '/?recipeid=' + GTN_WC.Config.recipeId;
            jQuery.each(queryParams, function (key, value) {
                url += '&' + key + '=' + value;
            });
            return url;
        },

        execute = function (method, reqUrl, dfd, saveCache) {
            if (!GTN_WC.Config.recipeId || GTN_WC.Config.recipeId.length < 10) {
                dfd.reject({InvalidRecipeId: true});
            } else {
                var settings = {
                    'async': true,
                    'crossDomain': true,
                    'url': reqUrl,
                    'method': method,
                    'timeout': 60000, // Use same timeout as in PHP
                    'headers': {
                        'content-type': 'application/json'
                    }
                };
                jQuery.ajax(settings)
                    .done(function (response) {
                        if (response && response.Message && response.Message.contains('Invalid recipeId')) {
                            dfd.reject({InvalidRecipeId: true});
                        } else {
                            if (saveCache && response && !response.HadError) {
                                cache[reqUrl] = response;
                            }
                            dfd.resolve(response);
                        }
                    })
                    .fail(function () {
                        dfd.reject();
                    });
            }
        },

        /**
         * Issues GET /preconfiguredproducts request.
         *
         * @returns Promise that will be notified with request outcome.
         */
        getPreconfiguredProducts = function () {
            var dfd = jQuery.Deferred(), reqUrl = urlFactory('preconfiguredproducts', baseEndpointConfig);
            if (cache[reqUrl]) {
                dfd.resolve(cache[reqUrl]);
            } else {
                execute('GET', reqUrl, dfd, true);
            }
            return dfd.promise();
        },

        /**
         * Issues GET /products request.
         *
         * @returns Promise that will be notified with request outcome.
         */
        getProducts = function () {
            var dfd = jQuery.Deferred(), reqUrl = urlFactory('products', baseEndpointConfig);
            if (cache[reqUrl]) {
                dfd.resolve(cache[reqUrl]);
            } else {
                execute('GET', reqUrl, dfd, true);
            }
            return dfd.promise();
        },

        /**
         * Issues GET /productvariants request.
         *
         * @returns Promise that will be notified with request outcome.
         */
        getProductVariants = function (productId) {
            var dfd = jQuery.Deferred(), reqUrl = urlFactory('productvariants', jQuery.extend({}, baseEndpointConfig, {productId: productId}));
            if (cache[reqUrl]) {
                dfd.resolve(cache[reqUrl]);
            } else {
                execute('GET', reqUrl, dfd, true);
            }
            return dfd.promise();
        };

    return {
        getPreconfiguredProducts: getPreconfiguredProducts,
        getProductVariants: getProductVariants,
        getProducts: getProducts
    };
}());
