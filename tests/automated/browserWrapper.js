/**
 * Created by Vlado on 13-Oct-16.
 */

/*global
 module
 */

module.exports = {
    wrapBrowser: function (browser, name, extension) {
        'use strict';

        if (!browser[name]) {
            browser[name] = {};
            browser[name].extendedProperties = [];

            var prop;
            // Extend browser object
            for (prop in extension) {
                if (extension.hasOwnProperty(prop)) {
                    if (!browser[prop]) {
                        browser[prop] = extension[prop];
                        browser[name].extendedProperties.push(prop);
                    } else {
                        browser.assert.equal(1, 0, 'Failed to wrap browser object for [' + name + ']. Attribute with same name [' + prop + '] already defined.');
                        return null;
                    }
                }
            }
        }
        return browser;
    },

    unwrapBrowser: function (browser, name) {
        'use strict';

        if (browser[name]) {
            var i, props = browser[name].extendedProperties;
            // Extend browser object
            for (i = 0; i < props.length; i += 1) {
                browser[props[i]] = null;
            }
            browser[name] = null;
        }
        return browser;
    }
}