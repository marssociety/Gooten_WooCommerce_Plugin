<?php
/**
 * Created by Vlado on 22-Dec-16.
 */

require_once('BaseTest.class.php');

class GootenWCProductVariationUtilsTest extends BaseTest
{

    /** @var string GET /preconfiguredproducts sample response string */
    private static $prpResponseStr = '{"PreconfiguredProducts":[{"Sku":"Canvas5x7","Name":"Canvastest","Description":"Canvas5x7","Price":{"Price":6,"CurrencyCode":"USD","FormattedPrice":"$6.00","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":43,"ProductVariantSku":"CanvsWrp-ImgWrp-5x7","Preconfigurations":[{"SpaceId":"D2749","Url":"http://cdn.print.io/temp/ba3fd0b33c154754a115c51e3a9d58911moto3.jpg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/ba3fd0b33c154754a115c51e3a9d58911moto3.jpg","Index":0},{"Url":"https://az412349.vo.msecnd.net/img-manip/cf6c5768997379457e8c727c389ca043.png","Index":0,"Id":"generated-preview"}]},{"Sku":"Canvas10x10","Name":"Canvastest","Description":"Canvas5x7","Price":{"Price":6,"CurrencyCode":"USD","FormattedPrice":"$6.00","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":43,"ProductVariantSku":"CanvsWrp-ImgWrp-10x10","Preconfigurations":[{"SpaceId":"D2749","Url":"http://cdn.print.io/temp/ba3fd0b33c154754a115c51e3a9d58911moto3.jpg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/ba3fd0b33c154754a115c51e3a9d58911moto3.jpg","Index":0},{"Url":"https://az412349.vo.msecnd.net/img-manip/cf6c5768997379457e8c727c389ca043.png","Index":0,"Id":"generated-preview"}]},{"Sku":"PhoneCase","Name":"PhoneCase","Description":"PhoneCase","Price":{"Price":10,"CurrencyCode":"USD","FormattedPrice":"$10.00","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":57,"ProductVariantSku":"PhoneCase-GalaxyNote2-Gloss","Preconfigurations":[{"SpaceId":"35F4F","Url":"http://cdn.print.io/temp/b8da16ce45a043dc8f9343e972429a7711.jpg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/b8da16ce45a043dc8f9343e972429a7711.jpg","Index":0},{"Url":"http://cdn.print.io/img-manip/dcdb338d9f11d710a6ab383abb055299.png","Index":0,"Id":"generated-preview"}]},{"Sku":"Prints","Name":"Prints","Description":"prints photo","Price":{"Price":7.65,"CurrencyCode":"USD","FormattedPrice":"$7.65","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":122,"ProductVariantSku":"Print-Glossy-20x24","Preconfigurations":[{"SpaceId":"3F649","Url":"http://cdn.print.io/temp/92541ecb876e4b7dbb2e7a98bd8ba7331moto3.jpg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/92541ecb876e4b7dbb2e7a98bd8ba7331moto3.jpg","Index":0},{"Url":"http://cdn.print.io/img-manip/7f8c28525fbb4655b6e8c7d115619e5b.png","Index":0,"Id":"generated-preview"}]},{"Sku":"testtest","Name":"testtest","Description":"testtest","Price":{"Price":0.59,"CurrencyCode":"USD","FormattedPrice":"$0.59","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":39,"ProductVariantSku":"Posters-8x8-Matte","Preconfigurations":[{"SpaceId":"4B6C2","Url":"http://cdn.print.io/temp/1adc12c2a53e439f83ee33cea913f7e4desktopwallpapers.org.ua_19513.jpg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/1adc12c2a53e439f83ee33cea913f7e4desktopwallpapers.org.ua_19513.jpg","Index":0},{"Url":"http://cdn.print.io/img-manip/e4ff61be7ca039509d8a6bfc84bfcb7e.png","Index":0,"Id":"generated-preview"}]},{"Sku":"Canv","Name":"Test Print","Description":"Test Print","Price":{"Price":15.44,"CurrencyCode":"USD","FormattedPrice":"$15.44","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":43,"ProductVariantSku":"CanvsWrp-WhtWrp-11x14","Preconfigurations":[{"SpaceId":"19C01","Url":"http://cdn.print.io/temp/92cfc2d11d2349458ae0c8b86abbcf98Desktop-Wallpaper-HD4.jpeg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/92cfc2d11d2349458ae0c8b86abbcf98Desktop-Wallpaper-HD4.jpeg","Index":0},{"Url":"http://cdn.print.io/img-manip/c30def89b738ce30184b5819c797e91c.png","Index":0,"Id":"generated-preview"}]},{"Sku":"FramedPrint8x10","Name":"Framed Print 8x10","Description":"8x10","Price":{"Price":21.84,"CurrencyCode":"USD","FormattedPrice":"$21.84","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":41,"ProductVariantSku":"Framed_8x10_Black_Gloss","Preconfigurations":[{"SpaceId":"89128","Url":"http://cdn.print.io/temp/f9b07c5e75e247379c958cfd2ed12ccd1moto4.jpg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/f9b07c5e75e247379c958cfd2ed12ccd1moto4.jpg","Index":0},{"Url":"http://cdn.print.io/img-manip/f0e6ccd547cff437e0efbb330c4b20a9.png","Index":0,"Id":"generated-preview"}]},{"Sku":"AcrylicBlock4x6","Name":"AcrylicBlock","Description":"AcrylicBlock","Price":{"Price":27.17,"CurrencyCode":"USD","FormattedPrice":"$27.17","CurrencyFormat":"${1}","CurrencyDigits":2},"Items":[{"ProductId":47,"ProductVariantSku":"AcrBlck-3/4in-4x6","Preconfigurations":[{"SpaceId":"05508","Url":"http://cdn.print.io/temp/eb27c71e14c140bebb75659cf8f846981moto1.jpg"}]}],"Images":[{"Url":"http://cdn.print.io/temp/eb27c71e14c140bebb75659cf8f846981moto1.jpg","Index":0},{"Url":"http://cdn.print.io/img-manip/b1fd04610c59ddc2fb6d8df6fe8df6f7.png","Index":0,"Id":"generated-preview"}]}],"HadError":false}';

    /** @var string GET /productvariants sample response string */
    private static $productVariantsResponseStr = '{"ProductVariants":[{"Options":[{"OptionId":"2debb07fffe64d82bbd840091a49b95c","ValueId":"bbed8bf4436346b987168d72a2bbec34","Name":"Print Type","Value":"Glossy","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/Print-Glossy.png","ImageType":"WebImg","SortValue":"1"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"0c832ab93d9b4b1c8642d97d46a630ea","Name":"Print Size","Value":"4x4 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-4x4.png","ImageType":"WebImg","CmValue":"10x10 cm","SortValue":"1"}],"PriceInfo":{"Price":0.16,"CurrencyCode":"USD","FormattedPrice":"$0.16","CurrencyFormat":"${1}","CurrencyDigits":2},"Sku":"Print-Glossy-4x4","MaxImages":1,"HasTemplates":true},{"Options":[{"OptionId":"2debb07fffe64d82bbd840091a49b95c","ValueId":"bbed8bf4436346b987168d72a2bbec34","Name":"Print Type","Value":"Glossy","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/Print-Glossy.png","ImageType":"WebImg","SortValue":"1"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"c58c11bee9bf4ad696b64e3e070f3397","Name":"Print Size","Value":"4x6 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-4x6.png","ImageType":"WebImg","CmValue":"10x15 cm","SortValue":"2"}],"PriceInfo":{"Price":0.15,"CurrencyCode":"USD","FormattedPrice":"$0.15","CurrencyFormat":"${1}","CurrencyDigits":2},"Sku":"Print-Glossy-4x6","MaxImages":1,"HasTemplates":true},{"Options":[{"OptionId":"2debb07fffe64d82bbd840091a49b95c","ValueId":"bbed8bf4436346b987168d72a2bbec34","Name":"Print Type","Value":"Glossy","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/Print-Glossy.png","ImageType":"WebImg","SortValue":"1"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"7d2de6a203214f59a9735a65f0ab2780","Name":"Print Size","Value":"5x5 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-5x5.png","ImageType":"WebImg","CmValue":"13x13 cm","SortValue":"3"}],"PriceInfo":{"Price":0.39,"CurrencyCode":"USD","FormattedPrice":"$0.39","CurrencyFormat":"${1}","CurrencyDigits":2},"Sku":"Print-Glossy-5x5","MaxImages":1,"HasTemplates":true},{"Options":[{"OptionId":"2debb07fffe64d82bbd840091a49b95c","ValueId":"bbed8bf4436346b987168d72a2bbec34","Name":"Print Type","Value":"Glossy","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/Print-Glossy.png","ImageType":"WebImg","SortValue":"1"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"1eec9ea0c86a4401983086269755369c","Name":"Print Size","Value":"5x7 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-5x7.png","ImageType":"WebImg","CmValue":"13x18 cm","SortValue":"4"}],"PriceInfo":{"Price":0.39,"CurrencyCode":"USD","FormattedPrice":"$0.39","CurrencyFormat":"${1}","CurrencyDigits":2},"Sku":"Print-Glossy-5x7","MaxImages":1,"HasTemplates":true},{"Options":[{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"762e3b65410543f4ac20e1a2e24f50a2","Name":"Print Size","Value":"8x10 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-8x10.png","ImageType":"WebImg","CmValue":"20x25.4 cm","SortValue":"5"},{"OptionId":"2debb07fffe64d82bbd840091a49b95c","ValueId":"bbed8bf4436346b987168d72a2bbec34","Name":"Print Type","Value":"Glossy","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/Print-Glossy.png","ImageType":"WebImg","SortValue":"1"}],"PriceInfo":{"Price":1.64,"CurrencyCode":"USD","FormattedPrice":"$1.64","CurrencyFormat":"${1}","CurrencyDigits":2},"Sku":"Print-Glossy-8x10","MaxImages":1,"HasTemplates":true}],"Options":[{"Name":"Print Type","Values":[{"OptionId":"2debb07fffe64d82bbd840091a49b95c","ValueId":"bbed8bf4436346b987168d72a2bbec34","Name":"Print Type","Value":"Glossy","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/Print-Glossy.png","ImageType":"WebImg","SortValue":"1"},{"OptionId":"2debb07fffe64d82bbd840091a49b95c","ValueId":"48452003bb4044a6ac6b6553f2ada54a","Name":"Print Type","Value":"Lustre","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/Print-Lustre.png","ImageType":"WebImg","SortValue":"2"}]},{"Name":"Print Size","Values":[{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"0c832ab93d9b4b1c8642d97d46a630ea","Name":"Print Size","Value":"4x4 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-4x4.png","ImageType":"WebImg","CmValue":"10x10 cm","SortValue":"1"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"c58c11bee9bf4ad696b64e3e070f3397","Name":"Print Size","Value":"4x6 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-4x6.png","ImageType":"WebImg","CmValue":"10x15 cm","SortValue":"2"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"7d2de6a203214f59a9735a65f0ab2780","Name":"Print Size","Value":"5x5 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-5x5.png","ImageType":"WebImg","CmValue":"13x13 cm","SortValue":"3"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"1eec9ea0c86a4401983086269755369c","Name":"Print Size","Value":"5x7 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-5x7.png","ImageType":"WebImg","CmValue":"13x18 cm","SortValue":"4"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"762e3b65410543f4ac20e1a2e24f50a2","Name":"Print Size","Value":"8x10 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-8x10.png","ImageType":"WebImg","CmValue":"20x25.4 cm","SortValue":"5"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"8f810f72bd1b4d57972d46ac613edee6","Name":"Print Size","Value":"11x14 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-11x14.png","ImageType":"WebImg","CmValue":"28x35.5 cm","SortValue":"7"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"8da04b2c2f8d4950ba2cf31294ccdd88","Name":"Print Size","Value":"12x12 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-12x12.png","ImageType":"WebImg","CmValue":"30x30 cm","SortValue":"8"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"1f52333b92f742f7afc7f0e121f42c4e","Name":"Print Size","Value":"12x18 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-12x18.png","ImageType":"WebImg","CmValue":"30x45 cm","SortValue":"9"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"cf4b0dbf1e174fad9dc55773544e0120","Name":"Print Size","Value":"16x20 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-16x20.png","ImageType":"WebImg","CmValue":"40x50 cm","SortValue":"10"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"04d325fd2bd94738903faf6f43ab2654","Name":"Print Size","Value":"20x24 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-20x24.png","ImageType":"WebImg","CmValue":"50x60 cm","SortValue":"11"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"87cbefea6cc0404c9e886a3d299d2757","Name":"Print Size","Value":"20x30 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-20x30.png","ImageType":"WebImg","CmValue":"50x76 cm","SortValue":"12"},{"OptionId":"8f093c04ef7c4c8e8acacf40f8190543","ValueId":"bdc6a2d8912c45dd95cbd0f9f41761ab","Name":"Print Size","Value":"24x36 inch","ImageUrl":"https://printmeeappassets.blob.core.windows.net/product-prints/prints-SizeIcon-24x36.png","CmValue":"60x90 cm","SortValue":"13"}]}]}';

    public function testFindPrpBySku()
    {
        $method = $this->getMethodByReflection('GootenWCProductVariationUtils', 'findPrpBySku');
        $prps = json_decode(self::$prpResponseStr, true);
        $prps = $prps['PreconfiguredProducts'];

        { // Invalid params
            $this->assertNull($method->invokeArgs(null, array(array(), null)));
            $this->assertNull($method->invokeArgs(null, array(null, 'test')));
            $this->assertNull($method->invokeArgs(null, array('test', 'test')));
        }
        {  // Valid params
            $this->assertNotNull($method->invokeArgs(null, array($prps, 'Canvas5x7')));
            $this->assertNotNull($method->invokeArgs(null, array($prps, 'AcrylicBlock4x6')));
            $this->assertNull($method->invokeArgs(null, array($prps, 'waldo-are-you-there')));
        }
    }


    public function testFindProductVariantBySku()
    {
        $method = $this->getMethodByReflection('GootenWCProductVariationUtils', 'findProductVariantBySku');
        $productVariants = json_decode(self::$productVariantsResponseStr, true);
        $productVariants = $productVariants['ProductVariants'];

        { // Invalid params
            $this->assertNull($method->invokeArgs(null, array(array(), null)));
            $this->assertNull($method->invokeArgs(null, array(null, 'test')));
            $this->assertNull($method->invokeArgs(null, array('test', 'test')));
        }
        {  // Valid params
            $this->assertNotNull($method->invokeArgs(null, array($productVariants, 'Print-Glossy-4x4')));
            $this->assertNotNull($method->invokeArgs(null, array($productVariants, 'Print-Glossy-4x6')));
            $this->assertNotNull($method->invokeArgs(null, array($productVariants, 'Print-Glossy-5x5')));
            $this->assertNotNull($method->invokeArgs(null, array($productVariants, 'Print-Glossy-5x7')));
            $this->assertNotNull($method->invokeArgs(null, array($productVariants, 'Print-Glossy-8x10')));
            $this->assertNull($method->invokeArgs(null, array($productVariants, 'waldo-are-you-there')));
        }
    }


    public function testGetOptionValue()
    {
        $method = $this->getMethodByReflection('GootenWCProductVariationUtils', 'getOptionValue');
        $productVariants = json_decode(self::$productVariantsResponseStr, true);
        $productVariants = $productVariants['ProductVariants'];

        { // Invalid params
            $this->assertNull($method->invokeArgs(null, array(array(), null, null)));
            $this->assertNull($method->invokeArgs(null, array(array(), 'test', null)));
            $this->assertNull($method->invokeArgs(null, array(null, 'test', 'test')));
            $this->assertNull($method->invokeArgs(null, array('test', 'test', 'test')));
        }
        {  // Valid params
            $this->assertEquals('Glossy', $method->invokeArgs(null, array($productVariants, 'Print-Glossy-4x4', 'Print Type')));
            $this->assertEquals('4x4 inch', $method->invokeArgs(null, array($productVariants, 'Print-Glossy-4x4', 'Print Size')));
            $this->assertNotNull('Glossy', $method->invokeArgs(null, array($productVariants, 'Print-Glossy-4x6', 'Print Type')));
            $this->assertEquals('4x6 inch', $method->invokeArgs(null, array($productVariants, 'Print-Glossy-4x6', 'Print Size')));
            $this->assertNull($method->invokeArgs(null, array($productVariants, 'waldo-are-you-there', 'waldo')));
            $this->assertNull($method->invokeArgs(null, array($productVariants, 'Print-Glossy-4x6', 'waldo')));
        }
    }


    public function testGetGeneratedPreviewImageUrl()
    {
        $method = $this->getMethodByReflection('GootenWCProductVariationUtils', 'getGeneratedPreviewImageUrl');
        $prps = json_decode(self::$prpResponseStr, true);
        $prps = $prps['PreconfiguredProducts'];

        { // Invalid params
            $this->assertNull($method->invokeArgs(null, array(null)));
            $this->assertNull($method->invokeArgs(null, array('test')));
        }
        {  // Valid params
            $this->assertNotNull($method->invokeArgs(null, array($prps[0]['Images'])));
            $this->assertNotNull($method->invokeArgs(null, array($prps[1]['Images'])));
            $this->assertNotNull($method->invokeArgs(null, array($prps[2]['Images'])));
            $this->assertNotNull($method->invokeArgs(null, array($prps[3]['Images'])));
            $this->assertNull($method->invokeArgs(null, array(array())));
            $this->assertNull($method->invokeArgs(null, array(array(array()))));
        }
    }

    public function testCreateWCAttributes()
    {
        $attributes = $this->createAttributes();

        $this->assertNotNull($attributes);
        $this->assertArrayHasKey('Canvas Size', $attributes);
        $this->assertArrayHasKey('Shape', $attributes);
        $this->assertEquals('Rectangle' . WC_DELIMITER . 'Square', $attributes['Shape']);
        $this->assertArrayHasKey('Wrap Type', $attributes);
        $this->assertEquals('Image Wrap', $attributes['Wrap Type']);
    }

    public function testSaveProductAttributes()
    {
        $method = $this->getMethodByReflection('GootenWCProductVariationUtils', 'saveProductAttributes');

        $attributes = $this->createAttributes();
        $postID = $this->createGootenProductPost();

        $method->invokeArgs(null, array($postID, $attributes));

        $savedAttributes = (array)maybe_unserialize(get_post_meta($postID, '_product_attributes', true));

        $this->assertEquals(array('shape', 'wrap-type', 'canvas-size'), array_keys($savedAttributes));
    }

    public function testSaveVariations()
    {
        // TODO
    }

    private function createAttributes()
    {
        $this->setRecipeId($this->VALID_RECIPE_ID);
        $method = $this->getMethodByReflection('GootenWCProductVariationUtils', 'createWCAttributes');

        $prps = json_decode(self::$prpResponseStr, true);
        $prps = $prps['PreconfiguredProducts'];

        $variants = array(
            array('productSku' => $prps[0]['Sku']),
            array('productSku' => $prps[1]['Sku'])
        );

        $gootenVariants = GootenWCAPI::getProductVariants($prps[0]['Items'][0]['ProductId']);
        $gootenVariants = json_decode($gootenVariants, true);
        $gootenVariants = $gootenVariants['ProductVariants'];

        $attributes = $method->invokeArgs(null, array($variants, $gootenVariants, $prps));
        return $attributes;
    }
}