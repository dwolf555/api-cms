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
            scss: {
                files: ['scss/**.scss'],
                tasks: ['clean', 'sass:dev', 'copy']
            },
            js: {
                files: ['js/**.js'],
                tasks: ['']
            }
        }
    });

    grunt.registerTask('default', ['sass:dev']);
};