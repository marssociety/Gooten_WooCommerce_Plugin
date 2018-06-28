/**
 * Created by Boro on 31-Oct-16.
 */
/*global
  QUnit, GTN_WC, console, jQuery
*/
var GTNJSTestBase = new function () {
    'use strict';

    var mockElement = function (type, id) {
        jQuery('<' + type + ' id="' + id + '"/>').appendTo('#qunit-fixture');
        return jQuery('#' + id);
    };

    this.mockInput = function (id) {
        return mockElement('input', id);
    };

    this.mockLabel = function (id) {
        return mockElement('label', id);
    };

    this.mockButton = function (id) {
        return mockElement('button', id);
    };

    this.mockTextArea = function (id) {
        return mockElement('textarea', id);
    };

    this.insertHtml = function (html) {
        jQuery(html).appendTo('#qunit-fixture');
    };
};
