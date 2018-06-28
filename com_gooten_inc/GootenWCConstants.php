<?php
/**
 * Created by Vlado on 9-Sep-16.
 */


/**
 * General plugin constants.
 */
abstract class GootenWCConstants
{
    const API_VERSION = '5';
    const API_SOURCE = 'api';
}

/**
 * Enumeration of GootenWC option names.
 */
abstract class GootenWCOptionNames
{
    const RECIPE_ID = 'gooten_recipe_id';
    const BILLING_KEY = 'gooten_billing_key';
    const ORDER_TESTING = 'gooten_order_testing';
    const PRODUCT_TAGS = 'gooten_product_tags';
    const PRODUCT_CATEGORIES = 'gooten_product_categories';
    const PRODUCT_PROFIT = 'gooten_product_profit';
    const ORDER_DYNAMIC_SHIPPING = 'gooten_order_dynamic_shipping';
    const ORDER_DYNAMIC_TAX = 'gooten_order_dynamic_tax';
}

/**
 * Enumeration of notice class names.
 */
abstract class GootenNoticeType
{
    const SUCCESS = 'notice-success';
    const ERROR = 'notice-error';
    const WARNING = 'notice-warning';
}