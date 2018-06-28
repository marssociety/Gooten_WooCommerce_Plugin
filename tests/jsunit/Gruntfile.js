/**
 * Created by Boro on 26-Oct-16.
 */
module.exports = function(grunt) {
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.initConfig({
        qunit: {
          all: {
          options: {
            urls: [
              'http://local.wordpress.dev/wordpress-default/wp-content/plugins/gooten/tests/jsunit/tests/gooten_js_tests.html',
            ]
          }
        }
        }
    });
}
