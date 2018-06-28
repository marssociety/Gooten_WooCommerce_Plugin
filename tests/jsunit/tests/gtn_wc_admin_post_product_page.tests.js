/**
 * Created by Boro on 28-Oct-16.
 */
/*global
  QUnit, GTN_WC, console, jQuery, GTNJSTestBase
*/
(function () {
    'use strict';

    QUnit.module("Configure Products Page", function (hooks) {
        var productPage;

        hooks.before(function () {
            jQuery('#qunit-fixture').empty();

            GTN_WC.Config.recipeId = "1AB4E1F8-DBCB-4D6C-829F-EE0B2A60C0B3";
            GTN_WC.Config.apiUrl = "https://api.print.io/api/v/5/source/api/";
            GTN_WC.API.baseEndpointConfig.countryCode = "US";
            GTN_WC.API.baseEndpointConfig.currencyCode = "USD";

            productPage = new GTN_WC.createAdminPostProductPage().init();
        });

        // Test search product by SKU
        QUnit.test("testSearchProductBySKU", function (assert) {
            var products = [{Sku : "test_sku_1"}, {Sku : "test_sku"}, {Sku : "test_sku_2"}],
                result = productPage.searchProductBySKU(products, "test_sku");
            assert.deepEqual(result.Sku, "test_sku");
        });

        // Test search product variant by SKU
        QUnit.test("testSearchProductVariantBySKU", function (assert) {
            var variants = [{Sku : "test_sku_1"}, {Sku : "test_sku"}, {Sku : "test_sku_2"}],
                result = productPage.searchProductVariantBySKU(variants, "test_sku");
            assert.deepEqual(result.Sku, "test_sku");
        });

        // Test search product by Id
        QUnit.test("testSearchProductById", function (assert) {
            var products = [{Id : "test_id_1"}, {Id : "test_id"}, {Id : "test_id_2"}],
                result = productPage.searchProductById(products, "test_id");
            assert.deepEqual(result.Id, "test_id");
        });

        // Test getting generated image preview
        QUnit.test("testGetGeneratedProductPreviewImgUrl", function (assert) {
            var images = [{Id : "image_id", Url : "image_url"}, {Id : "generated-preview", Url : "image_test_url"}],
                result = productPage.getGeneratedProductPreviewImgUrl(images);
            assert.deepEqual(result, "image_test_url");
        });

        // Test setting product title
        QUnit.test("testSetProductTitle", function (assert) {
            // mock elements
            var input = GTNJSTestBase.mockInput('title'),
                lbl = GTNJSTestBase.mockLabel('title-prompt-text');

            // test
            productPage.setProductTitle('test_title');
            assert.deepEqual('test_title', input.val());
            assert.notOk(lbl.is(":visible"));
        });

        // Test set product content
        QUnit.test("testSetProductContent", function (assert) {
            // mock
            var btn = GTNJSTestBase.mockButton('content-html'),
                btn1 = GTNJSTestBase.mockButton('content-tmce'),
                txtArea = GTNJSTestBase.mockTextArea('content');

            assert.expect(3);

            btn.on("click", function () {
                assert.ok(true);
            });

            btn1.on("click", function () {
                assert.ok(true);
            });

            productPage.setProductContent('test_content');
            assert.deepEqual('test_content', txtArea.val());
        });

        // Test setting product short description
        QUnit.test("testSetShortProductDescription", function (assert) {
            // mock
            var btn = GTNJSTestBase.mockButton('excerpt-html'),
                btn1 = GTNJSTestBase.mockButton('excerpt-tmce'),
                txtArea = GTNJSTestBase.mockTextArea('excerpt');

            assert.expect(3);

            btn.on("click", function () {
                assert.ok(true);
            });

            btn1.on("click", function () {
                assert.ok(true);
            });

            productPage.setShortProductDescription('test_short_desc');
            assert.deepEqual('test_short_desc', txtArea.val());
        });

        // Test clear product tags
        QUnit.test("testClearProductTags", function (assert) {
            assert.expect(4);

            var html = '<div class="tagchecklist"><span><a id="product_tag-check-num-0" class="ntdelbutton" ' +
                'tabindex="0">X</a>&nbsp;as</span><span><a id="product_tag-check-num-1" class="ntdelbutton" ' +
                'tabindex="0">X</a>&nbsp;asd</span><span><a id="product_tag-check-num-2" class="ntdelbutton" ' +
                'tabindex="0">X</a>&nbsp;aasd</span></div>';

            GTNJSTestBase.insertHtml(html);

            // check if check list has been inserted
            assert.ok(jQuery('.tagchecklist').children().length > 0);

            jQuery('#product_tag-check-num-0').on("click", function () {
                assert.ok(true);
            });

            productPage.clearProductTags();
        });

        // Test setting product tags
        QUnit.test("testSetProductTags", function (assert) {
            assert.expect(1);
            // mock Product and create input field
            var product = {Items : [{ProductId : 186, ProductVariantSku : 'Mug11oz-White-BlackAccent'}], Sku : 'Mug11oz-White-BlackAccent'},
                done = assert.async(),
                input = GTNJSTestBase.mockInput('new-tag-product_tag');

            // cover loading and error dialog
            GTNJSTestBase.insertHtml('<div id="tagsdiv-product_tag" class="postbox "></div>');
            GTNJSTestBase.insertHtml('<div id="gooten-error-msg"></div>');

            productPage.currentlySelectedSKU = "Mug11oz-White-BlackAccent";
            productPage.setProductTags('pvo', product, function () {
                assert.ok(input.val().length > 1);
                done();
            });
        });

        // Test adding product category
        QUnit.test("testAddProductCategory", function (assert) {
            assert.expect(2);

            var input = GTNJSTestBase.mockInput('newproduct_cat'),
                btn = GTNJSTestBase.mockButton('product_cat-add-submit');

            btn.on('click', function () {
                assert.ok(true);
            });

            productPage.addProductCategory('test_cat');
            assert.deepEqual('test_cat', input.val());
        });

        // Test clear product categories
        QUnit.test("testClearProductCategories", function (assert) {
            var html = '<ul id="product_catchecklist" data-wp-lists="list:product_cat" class="categorychecklist form-no-clear">' +
                       '<li id="product_cat-79"><label class="selectit"><input value="79" type="checkbox" name="tax_input[product_cat][]" id="in-product_cat-79" checked="checked"> Cat1</label></li>' +
                       '<li id="product_cat-77"><label class="selectit"><input value="77" type="checkbox" name="tax_input[product_cat][]" id="in-product_cat-77" checked="checked"> Cat2</label></li>' +
                       '<li id="product_cat-78"><label class="selectit"><input value="78" type="checkbox" name="tax_input[product_cat][]" id="in-product_cat-78" checked="checked"> Cat3</label></li></ul>';

            GTNJSTestBase.insertHtml(html);
            productPage.clearProductCategories();

            assert.notOk(jQuery('#in-product_cat-79').prop('checked'));
            assert.notOk(jQuery('#in-product_cat-77').prop('checked'));
            assert.notOk(jQuery('#in-product_cat-78').prop('checked'));
        });

        // Test set product categories
        QUnit.test("testSetProductCategories", function (assert) {
            assert.expect(2);

            var product = {Items : [{ProductId : 186, ProductVariantSku : 'Mug11oz-White-BlackAccent'}], Sku : 'Mug11oz-White-BlackAccent'},
                input = GTNJSTestBase.mockInput('newproduct_cat'),
                btn = GTNJSTestBase.mockButton('product_cat-add-submit'),
                done = assert.async();

            productPage.currentlySelectedSKU = "Mug11oz-White-BlackAccent";

            // strategy 'productType'
            productPage.setProductCategories('productType', product, function () {
                assert.deepEqual('Accent Mugs', input.val());

                // strategy 'gootenCategories'
                productPage.setProductCategories('gootenCategories', product, function () {
                    assert.deepEqual('Photo Gifts', input.val());
                    done();
                });
            });
        });

        // Test clear product images
        QUnit.test("testClearProductImages", function (assert) {
            assert.expect(3);

            var input = GTNJSTestBase.mockInput('product_image_gallery'),
                btn = GTNJSTestBase.mockButton('remove-post-thumbnail'),
                html = '<div id="product_images_container"><ul><li>Test1</li><li>Test2</li><li>Test3</li></ul></div>',
                done = assert.async(),
                done1 = assert.async();

            GTNJSTestBase.insertHtml(html);
            input.val('test');

            btn.on('click', function () {
                assert.ok(true);
                done();
            });

            productPage.clearProductImages(function () {
                assert.equal(jQuery('#product_images_container').find('ul li').children().length, 0);
                assert.deepEqual(input.val(), '');
                done1();
            });
        });

        // Test set product images
        // IMPORTANT - to run this test, uncoment 'data = JSON.parse(data);'
        // in gtn_wc_admin_post_product_page.js
        QUnit.test("testSetProductImages", function (assert) {
            assert.expect(5);

            // mock clear image elements
            GTNJSTestBase.mockButton('remove-post-thumbnail');

            var product = {Images: ["test_image_1", "test_image_2"], Sku : "Mug11oz-White-BlackAccent"},
                thumbnailId = GTNJSTestBase.mockInput('_thumbnail_id'),
                done = assert.async();

            // mock 'Product Image' and 'Product Gallery'
            GTNJSTestBase.insertHtml('<div id="postimagediv"><div class="inside"></div></div>');
            GTNJSTestBase.insertHtml('<div id="woocommerce-product-images"><div class="inside"><input id="product_image_gallery"></input><div id="product_images_container"><ul><li>Test1</li><li>Test2</li><li>Test3</li></ul></div></div></div>');
            GTNJSTestBase.insertHtml('<a id="set-post-thumbnail"></a>');

            // set test values
            GTN_WC.Config.nonce = "test_nonce";
            GTN_WC.Config.productImagesEndpoint = 'gooten_js_test_response.php';
            productPage.currentlySelectedSKU = "Mug11oz-White-BlackAccent";

            productPage.setProductImages(product, function () {
                assert.deepEqual(thumbnailId.val(), "test_product_image_id");
                assert.deepEqual(jQuery('#set-post-thumbnail').html(), "test_image_html");
                assert.deepEqual(jQuery('#postimagediv .inside').html(), '<p class="hide-if-no-js howto" id="set-post-thumbnail-desc">Click the image to edit or update</p>' +
                                                                         '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">Remove product image</a></p>');
                assert.deepEqual(jQuery('#product_image_gallery').val(), "test_val");
                assert.equal(jQuery('#product_images_container').find('ul li').children().length, 0);
                done();
            });
        });
    });

})();
