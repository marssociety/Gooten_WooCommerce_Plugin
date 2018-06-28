<?php
/**
 * Created by Vlado on 30-Nov-16.
 */


/**
 * Holds utilities for manipulating with media - images.
 */
class GootenWCMediaUtils
{

    /**
     * Downloads and saves image with supplied URL to media.
     * (image will not be downloaded if image was previously downloaded)
     *
     * @param string $imageUrl The URL to image.
     * @return int Attachment ID of downloaded image or -1 on failure.
     */
    public static function downloadAndSaveImageToMedia($imageUrl)
    {
        if (isset($imageUrl)) {
            $tmp = explode('.', $imageUrl);
            $localImageName = md5($imageUrl) . '.' . end($tmp);
            $uploadDir = wp_upload_dir();
            $uploadFilePath = $uploadDir['path'] . '/' . $localImageName;
            if (file_exists($uploadFilePath)) {
                $attachments = get_posts(array(
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'numberposts' => -1,
                    'meta_key' => '_gooten_image_url_key',
                    'meta_value' => $imageUrl
                ));
                return count($attachments) > 0 ? $attachments[0]->ID : -1;
            } else {
                // Download and save image to upload dir
                $contents = file_get_contents($imageUrl . '?height=1024');
                $saveFile = fopen($uploadFilePath, 'w');
                fwrite($saveFile, $contents);
                fclose($saveFile);

                // Insert attachment
                $fileType = wp_check_filetype($localImageName, null);
                $attachment = array(
                    'post_mime_type' => $fileType['type'],
                    'post_status' => 'inherit',
                );
                $attachmentId = wp_insert_attachment($attachment, $uploadFilePath);
                $imageNew = get_post($attachmentId);
                $fullSizePath = get_attached_file($imageNew->ID);

                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachData = wp_generate_attachment_metadata($attachmentId, $fullSizePath);
                wp_update_attachment_metadata($attachmentId, $attachData);
                update_post_meta($attachmentId, '_gooten_image_url_key', $imageUrl);
                return $attachmentId;
            }
        }
        return -1;
    }

    /**
     * Sets product image and product gallery images for specified product.
     *
     * @param int $postId The post ID of product.
     * @param string $productImage URL of product image (may be null).
     * @param array(string) $productGalleryImages Array of URLs used as product gallery images (may be null).
     * @return array Array holding data needed to render product image and product gallery images on client.
     */
    public static function setProductImages($postId, $productImage, $productGalleryImages)
    {
        if (!isset($postId)) {
            return array(
                'HadError' => true
            );
        }

        $result = array();

        // Set post thumbnail
        if (isset($productImage)) {
            $productImageId = self::downloadAndSaveImageToMedia($productImage);
            set_post_thumbnail($postId, $productImageId);

            $result['productImageID'] = $productImageId;
            $result['productImageHTML'] = base64_encode(get_the_post_thumbnail($postId));
        }

        // Set gallery images
        if (isset($productGalleryImages) && sizeof($productGalleryImages) > 0) {
            $galleryPostIds = [];
            foreach ($productGalleryImages as $image) {
                $attachmentId = self::downloadAndSaveImageToMedia($image);
                if ($attachmentId !== -1) {
                    $galleryPostIds[] = $attachmentId;
                }
            }

            // Add images to Product Gallery
            update_post_meta($postId, '_product_image_gallery', implode(',', $galleryPostIds));


            { // Create Product Gallery HTML
                ob_start();
                if (!is_null(get_post($postId))) {
                    WC_Meta_Box_Product_Images::output(get_post($postId));
                }
                $productGalleryHTML = ob_get_contents();
                ob_end_clean();
            }

            $result['productGalleryHTML'] = base64_encode($productGalleryHTML);
        }

        return $result;
    }

}