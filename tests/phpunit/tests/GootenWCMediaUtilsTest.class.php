<?php
/**
 * Created by Vlado on 21-Dec-16.
 */

require_once('BaseTest.class.php');

class GootenWCMediaUtilsTest extends BaseTest
{

    public function testDownloadAndSaveImageToMedia_null()
    {
        $id = GootenWCMediaUtils::downloadAndSaveImageToMedia(null);
        $this->assertEquals(-1, $id);
    }

    public function testDownloadAndSaveImageToMedia_valid()
    {
        $url = 'https://upload.wikimedia.org/wikipedia/en/e/eb/Westworld_%28TV_series%29_title_card.jpg';

        $id1 = GootenWCMediaUtils::downloadAndSaveImageToMedia($url);
        $id2 = GootenWCMediaUtils::downloadAndSaveImageToMedia($url);

        $this->assertGreaterThan(0, $id1);
        $this->assertEquals($id1, $id2);
    }

    public function testSetProductImages_invalidParams()
    {
        // Test without postId
        $result = GootenWCMediaUtils::setProductImages(null, null, array());
        $this->assertArrayHasKey('HadError', $result);
        $this->assertEquals(true, $result['HadError']);

        // Test images undefined
        $result = GootenWCMediaUtils::setProductImages(0, null, array());
        $this->assertEquals(true, sizeof($result) === 0);
    }

    public function testSetProductImages_valid()
    {
        $images = array('https://upload.wikimedia.org/wikipedia/commons/c/c4/PM5544_with_non-PAL_signals.png');

        { // Only product image
            $result = GootenWCMediaUtils::setProductImages(0, $images[0], null);
            $this->assertArrayNotHasKey('HadError', $result);
            $this->assertArrayHasKey('productImageID', $result);
            $this->assertArrayHasKey('productImageHTML', $result);
            $this->assertArrayNotHasKey('productGalleryHTML', $result);
        }

        { // Only gallery
            $result = GootenWCMediaUtils::setProductImages(0, null, $images);
            $this->assertArrayNotHasKey('HadError', $result);
            $this->assertArrayNotHasKey('productImageID', $result);
            $this->assertArrayNotHasKey('productImageHTML', $result);
            $this->assertArrayHasKey('productGalleryHTML', $result);
        }

        { // Both
            $result = GootenWCMediaUtils::setProductImages(0, $images[0], $images);
            $this->assertArrayHasKey('productImageID', $result);
            $this->assertArrayHasKey('productImageHTML', $result);
            $this->assertArrayHasKey('productGalleryHTML', $result);
        }
    }

}