module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        clean: ['scss/compiled'],
        copy: {
            css: {
                files: [
                    {
                        cwd: 'scss/compiled/',
                        src: ['*'],
                        dest: '../public_html/css/',
                        filter: 'isFile'
                    }
                ]
            }
        },
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
            options: {
                livereload: true
            },
            html: {
                files: ['../public_html/cms/index.html']
            },
            js: {
                files: ['js/**.js'],
                tasks: ['']
            },
            scss: {
                files: ['scss/**.scss'],
                tasks: ['clean', 'sass:dev', 'copy']
            }
        }
    });

    grunt.registerTask('default', ['sass:dev']);
};