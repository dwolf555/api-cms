module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        sass: {
            dev: {
                options: {
                    outputStyle: 'expanded',
                    sourceMap: true,
                    sourceComments: true
                },
                files: {
                    'scss/compiled/main.css': 'scss/main.scss'
                }
            }
        },
        watch: {
            scss: {
                files: ['scss/**.scss'],
                tasks: ['sass:dev']
            },
            js: {
                files: ['js/**.js'],
                tasks: ['']
            }
        }
    });

    grunt.registerTask('default', ['sass:dev']);
};