/**
 * Created by Vlado on 19-Oct-16.
 */

/*global
 module, require
 */

var G = require('./globals.json');
var wrapper = require('./browserWrapper.js');


/**
 * Encapsulates helper functionalities common to all test cases. In order to use them base browser object must be
 * wrapped calling exposed {@code wrapBrowser} function.
 */
module.exports = {
    wrapBrowser: function (browser) {
        'use strict';

        var extension = {

            /**
             * Common functionality used to wait for body to be loaded.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            waitForBody: function () {
                return browser.waitForElementVisible('body', 100000);
            },

            /**
             * Waits until indicator is dismissed.
             *
             * @param selector The selector of element with indicator.
             *  @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            waitForIndicator: function (selector) {
                // Loading indicators have bigger timeout intentionally
                return browser.waitForElementNotPresent(selector + ' .gtn-loading-indicator-overlay', 100000);
            },


            /**
             * Common functionality used to submit form by clicking on submit button. Method blocks until
             * page is reloaded.
             *
             * @param submitButtonSelector Selector of submit button.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            submitFormAndWait: function (submitButtonSelector) {
                return browser.click(submitButtonSelector)
                    .pause(1000)
                    .waitForBody();
            },

            /**
             * Setts new value to element. This utility vas made as original .setValue actually appends value.
             *
             * @param selector The selector for the element
             * @param value The value to be set on the element
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            clearValueSet: function (selector, value) {
                return browser
                    .clearValue(selector)
                    .setValue(selector, value);
            },

            /**
             * Asserts that supplied element has 'src' property set.
             *
             * @param selector The selector of the element.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertHasSrcAttribute: function (selector) {
                return browser.getAttribute(selector, 'src', function (result) {
                    this.assert.equal(typeof result, 'object');
                    this.assert.equal(result.status, 0);
                    this.assert.ok(result.value);
                });
            },

            /**
             * Performs set of action necessary to login to site as admin.
             *
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            loginToAdmin: function () {
                browser
                    .url(G.url + G.pageLoginUrl)
                    .waitForElementVisible('#loginform', 20000)
                    .clearValueSet('[name="log"]', G.adminUsername)
                    .clearValueSet('[name="pwd"]', G.adminPassword)
                    .click('#wp-submit')
                    .pause(1000)
                    .waitForElementVisible('body.wp-admin', 20000);
                return browser;
            },

            /**
             * Opens specified page with admin privileges.
             *
             * @param pageUrl The url of the page.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            openPageAsAdmin: function (pageUrl) {
                return browser
                    .loginToAdmin()
                    .url(G.url + pageUrl)
                    .pause(1000)
                    .waitForBody();
            },

            /**
             * Asserts that current page has notice with specified text.
             *
             * @param text The text of notice to search for.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertHasNoticeWithText: function (text) {
                browser
                    .useXpath()
                    .expect.element("//p[contains(text(), '" + text + "')]").to.be.present;
                return browser.useCss();
            },

            /**
             * Asserts that current page does NOT have notice with specified text.
             *
             * @param text The text of notice to search for.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertNoNoticeWithText: function (text) {
                browser
                    .useXpath()
                    .expect.element("//p[contains(text(), '" + text + "')]").to.not.be.present;
                return browser.useCss();
            },

            /**
             * Asserts that checkbox is checked.
             *
             * @param selector The selector of checkbox.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertCheckboxChecked: function (selector) {
                return browser.assert.attributeEquals(selector, 'checked', 'true');
            },

            /**
             * Asserts that checkbox is NOT checked.
             *
             * @param selector The selector of checkbox.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertCheckboxNotChecked: function (selector) {
                browser.expect.element(selector + ':checked').to.not.be.present;
                return browser;
            },

            /**
             * Ensures that checkbox is NOT checked.
             *
             * @param selector The selector of checkbox.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            setCheckboxNotChecked: function (selector) {
                return browser.execute("jQuery('" + selector + ":checked').click();");
            },

            /**
             * Ensures that checkbox is checked.
             *
             * @param selector The selector of checkbox.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            setCheckboxChecked: function (selector) {
                return browser.execute("jQuery('" + selector + ":not(:checked)').click();");
            },

            /**
             * Asserts that supplied number of elements are present in the DOM.
             *
             * @param selector The selector of elements.
             * @param count The count of elements expected to be in the DOM.
             * @returns {browser} Reruns this object for convenience of concatenating method calls.
             */
            assertNumberOfElementsToBePresent: function (selector, count) {
                if (count === 0) {
                    return browser.expect.element(selector).to.not.be.present;
                }
                return browser.expect.element(selector + ':nth-child(' + count + ')').to.be.present;
            }
        };
        return wrapper.wrapBrowser(browser, 'common', extension);
    },

    unwrapBrowser: function (browser) {
        'use strict';

        return wrapper.unwrapBrowser(browser, 'common');
    }
}