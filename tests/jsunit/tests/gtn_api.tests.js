/**
 * Created by Boro on 26-Oct-16.
 */
/*global
  QUnit, GTN_WC, console
*/

(function () {
    'use strict';
    var NO_ERROR = "passed without error";

    QUnit.module("API", function (hooks) {
        hooks.before(function () {
            GTN_WC.Config.recipeId = "1AB4E1F8-DBCB-4D6C-829F-EE0B2A60C0B3";
            GTN_WC.Config.apiUrl = "https://api.print.io/api/v/5/source/api/";
            GTN_WC.API.baseEndpointConfig.countryCode = "US";
            GTN_WC.API.baseEndpointConfig.currencyCode = "USD";
        });

        // Test get all products
        QUnit.test("testGetProducts", function (assert) {
            assert.expect(2);
            var done = assert.async();

            GTN_WC.API.getProducts(function (res) {
                assert.ok(res.HadError === undefined || res.HadError === false, NO_ERROR);
                assert.ok(res.Products.length > 0, "successfully returned products");
                done();
            });
        });

        // Test get preconfigured products
        QUnit.test("testGetPRPProducts", function (assert) {
            assert.expect(2);
            var done = assert.async();

            GTN_WC.API.getPreconfiguredProducts(function (res) {
                assert.ok(res.HadError === undefined || res.HadError === false, NO_ERROR);
                assert.ok(res.PreconfiguredProducts.length > 0, "successfully returned products");
                done();
            });
        });

        // Test get product variants
        QUnit.test("testGetProductVariants", function (assert) {
            assert.expect(3);
            var done = assert.async();

            GTN_WC.API.getProductVariants(41, function (res){
                assert.ok(res.HadError === undefined || res.HadError === false, NO_ERROR);
                assert.ok(res.ProductVariants.length > 0, "successfully returned product variants");
                assert.ok(res.Options.length > 0, "successfully returned product options");
                done();
            });
        });

        // Test creating gooten url
        QUnit.test("testUrlFactory", function (assert) {
            var url = GTN_WC.API.urlFactory("test_endpoint", {testConfig : "test_config"});
            var re = /^(?=.*test_endpoint)(?=.*recipeid\D)(?=.*testConfig)(?=.*test_config).*$/;
            assert.ok(url.match(re), "valid url");
        });
    });
}());
