<?php
/**
 * Created by Vlado on 9-Sep-16.
 */

/**
 * Class WC_Product_Gooten_Product_Simple
 */
class WC_Product_Gooten_Product_Simple extends WC_Product_Simple
{

    public function __construct($product)
    {
        parent::__construct($product);
        $this->product_type = 'gooten_product';
    }

    public function getProductVariantSKU()
    {
        $prpMeta = get_post_meta($this->id, '_gooten_prp_json', true);
        if (!empty($prpMeta)) {
            $prp = json_decode(base64_decode($prpMeta), true);
            return $prp['Sku'];
        }
        return null;
    }

}
