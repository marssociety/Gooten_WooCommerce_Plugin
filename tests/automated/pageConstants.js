/**
 * Created by Vlado on 14-Oct-16.
 */

/*global
 module, require
 */

/**
 * Enumeration of page constants.
 */
module.exports = {

    /** Settings page constants */
    SETTINGS: {

        /** Settings page selectors enumeration */
        SELECTORS: {
            RECIPE_ID_INPUT: 'input[name="gooten_recipe_id"]',
            BILLING_KEY_INPUT: 'input[name="gooten_billing_key"]',
            ORDER_TESTING_CHECKBOX: 'input[name="gooten_order_testing"]',
            DYNAMIC_SHIPPING_CHECKBOX: 'input[name="gooten_order_dynamic_shipping"]',
            DYNAMIC_TAX_CHECKBOX: 'input[name="gooten_order_dynamic_tax"]',
            SUBMIT_SETTINGS_BUTTON: 'input[name="update_gooten_settings"]',
            PRODUCT_TAG_STRATEGY_SELECT: '#gooten_product_tags',
            PRODUCT_CATEGORY_STRATEGY_SELECT: '#gooten_product_categories',
            PRODUCT_PROFIT_INPUT: 'input[name="gooten_product_profit"]'
        },

        /** Settings page various values enumeration */
        VALUES: {
            NOTICE_EMPTY_KEYS: 'Recipe ID and/or Billing key is not set. Please set these keys in order to enable plugin.',
            NOTICE_ORDER_TESTING: 'Order testing mode is enabled - orders will not be sent to production.'
        }
    },

    /** Admin post product page constants */
    ADMIN_POST_PRODUCT: {

        /** Admin post product selectors enumeration */
        SELECTORS: {
            SUBMIT_POST: '#submitpost',
            GOOTEN_PRODUCT_DATA_CONTAINER: '#gooten_product_data',
            PRP_DROPDOWN: '#s2id_gooten_prp_select',
            PRP_DROPDOWN_INPUT: '#s2id_autogen4_search',
            PRP_ITEM: '.gtn-prp-item',
            PRODUCT_TITLE_INPUT: '#title',
            PRODUCT_IMAGE_ITEM: '.attachment-post-thumbnail',
            PRODUCT_GALLERY_ITEM: '.product_images img',
            PRODUCT_IMAGE_CONTAINER: '#postimagediv',
            PRODUCT_GALLERY_CONTAINER: '#woocommerce-product-images',
            PRODUCT_CATEGORY_CONTAINER: '#product_catdiv',
            PRODUCT_TAGS_CONTAINER: '#tagsdiv-product_tag',
            UNDEFINED_VARIANT_DATA: '.gtn-variant-undefined',
            UNDEFINED_VARIANT_DATA_ADD_BUTTON: '.gtn-variant-add',
            VARIANT_DATA: '.gtn-variant',
            VARIANT_DATA_HEADER: '.gtn-variant-header',
            VARIANT_DATA_OPTIONS_CONTAINER: '.gtn-variant-selector',
            VARIANT_DATA_DELETE_BUTTON: '.gtn-delete-variation'
        },

        /** Admin post product values enumeration */
        VALUES: {
            NOTICE_PRODUCT_PUBLISHED: 'Product published.'
        }
    }
}