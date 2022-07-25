module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        uglify: {
            build: {
                files: [{
                    expand: true,
                    src: 'assets/**/*.js',
                    dest: 'dist',
                    cwd: '.',
                    rename: function (dst, src) {
                        return dst + '/' + src.replace('.js', '.min.js');
                    }
                }]
            },
            dev: {
                files: [{
                    expand: true,
                    src: 'assets/**/*.js',
                    dest: 'dist',
                    cwd: '.',
                    rename: function (dst, src) {
                        return dst + '/' + src.replace('.js', '.min.js');
                    }
                }],
                options: {
                    sourceMap: true,
                }
            },
        },
        postcss: {
            build: {
                files: [{
                    expand: true,
                    src: 'assets/**/*.css',
                    dest: 'dist',
                    cwd: '.',
                    rename: function (dst, src) {
                        return dst + '/' + src.replace('.css', '.min.css');
                    }
                }],
                options: {
                    processors: [
                        require('autoprefixer')(),
                        require('cssnano')()
                    ]
                }
            },
            dev: {
                options: {
                    map: true
                },
                files: [{
                    expand: true,
                    src: 'assets/**/*.css',
                    dest: 'dist',
                    cwd: '.',
                    rename: function (dst, src) {
                        return dst + '/' + src.replace('.css', '.min.css');
                    }
                }],
            }
        },
        watch: {
            scripts: {
                files: ['assets/**/*.js', 'assets/**/*.css'],
                tasks: ['uglify:dev', 'postcss:dev'],
                options: {
                    spawn: false,
                },
            },
        },
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Load the plugin that provides the "watch" task.
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Load the plugin that provides the "postcss" task.
    grunt.loadNpmTasks('@lodder/grunt-postcss');

    // Default task(s).
    grunt.registerTask('default', ['uglify:build', 'postcss:build']);

};