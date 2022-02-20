'use strict';

import fs            from 'fs';
import path          from 'path';
import plugins       from 'gulp-load-plugins';
import yargs         from 'yargs';
import browser       from 'browser-sync';
import gulp          from 'gulp';
import run           from 'gulp-run-command';
import autoprefixer  from 'autoprefixer';
import cssnano       from 'cssnano';
import rimraf        from 'rimraf';
import yaml          from 'js-yaml';
import dateFormat    from 'dateformat';
import webpackStream from 'webpack-stream';
import webpack2      from 'webpack';
import named         from 'vinyl-named';
import log           from 'fancy-log';
import colors        from 'ansi-colors';

// Load all Gulp plugins into one variable
const $ = plugins();

// Check for --production flag
const PRODUCTION = !!( yargs.argv.production );

// Check for --development flag unminified with sourcemaps
const DEV = !!( yargs.argv.dev );

// Load settings from the config[-default].yml file.
const { PROJECT, STATUS, BROWSERSYNC, COMPATIBILITY, REVISIONING, PATHS } = loadConfig();

/**
 * Check if file exists synchronously
 *
 * @since 1.0.0
 *
 * @param {string} filepath File path.
 *
 * @return {bool} True if found. False if not.
 */
function checkFileExists( filepath ) {
    let flag = true;

    try {
        fs.accessSync( filepath, fs.F_OK );
    } catch( e ) {
        flag = false;
    }

    return flag;
}

/**
 * Load the YAML config.
 *
 * @since 1.0.0
 *
 * @return {yaml} YAML configuration options.
 */
function loadConfig() {
    log( 'Loading config file...' );

    if ( checkFileExists( 'config.yml' ) ) {
        // config.yml exists, load it
        log( colors.bold.cyan( 'config.yml' ), 'exists, loading', colors.bold.cyan( 'config.yml' ) );
        let ymlFile = fs.readFileSync( 'config.yml', 'utf8' );
        return yaml.load( ymlFile );

    } else if ( checkFileExists( 'config-default.yml' ) ) {
        // config-default.yml exists, load it
        log( colors.bold.cyan( 'config.yml' ), 'does not exist, loading', colors.bold.cyan( 'config-default.yml' ) );
        let ymlFile = fs.readFileSync( 'config-default.yml', 'utf8' );
        return yaml.load( ymlFile );

    } else {
        // Exit if config.yml & config-default.yml do not exist
        log( 'Exiting process, no config file exists.' );
        log( 'Error Code:', err.code );
        process.exit(1);
    }
}

/**
 * Delete the "dist" folder everytime a build starts.
 *
 * @param {callback} done Signal to Gulp that task is finished.
 */
function clean( done ) {
    rimraf( PATHS.admin, done );
    rimraf( PATHS.dist, done );
}

/**
 * Copy static files out of the `src` folder. This task skips over the
 * "images", "js", and "scss" folders, which are parsed separately.
 */
function copy() {
    return gulp.src( PATHS.assets )
        .pipe( $.rename( function( path ) {
            if ( path.extname.match( /\.php$/ ) ) {
                path.dirname = 'inc/libs/';
            } else if ( path.basename.match( /foundation/ ) ) {
                path.dirname = 'src/js/utils/foundation/' + path.dirname + '/';
            } else if ( path.extname.match( /\.(js|css)/ ) ) {
                path.dirname = PATHS.dist + '/' + path.extname.slice(1) + '/';
            } else {
                path.dirname = PATHS.dist + '/' + path.dirname + '/';
            }
        }))
        .pipe( gulp.dest( './' ) );
}

/**
 * Dynamic CSS destinations.
 *
 * @param {object} path The path value from $.rename().
 */
function cssDestination( path ) {
    if ( path.basename.match( /style/ ) ) {
        path.dirname = '.';
    } else {
        if ( path.basename.match( /(-admin)/ ) ) {
            path.dirname = PATHS.admin;
        } else {
            path.dirname = PATHS.dist;
        }

        path.dirname += '/css/';
    }

    return path;
}

/**
 * Dynamic JavaScript destinations.
 *
 * @since 1.0.0
 *
 * @param {object} path The path value from $.rename().
 */
function jsDestination( path ) {
    if ( path.basename.match( /(-admin|widget-)/ ) ) {
        path.dirname = PATHS.admin;
    } else {
        path.dirname = PATHS.dist;
    }

    path.dirname += '/js/';

    return path;
}

/**
 * Compile Sass into CSS -- minified in production.
 *
 * @since 1.0.0
 */
function sass() {
    return gulp.src( PATHS.templates )
        .pipe( $.sourcemaps.init() )
        .pipe( $.sass({ includePaths: PATHS.sass }).on( 'error', $.sass.logError ) )
        .pipe( $.postcss([ autoprefixer({ overrideBrowserslist: COMPATIBILITY }), cssnano() ]) )
        .pipe( $.if( PRODUCTION, $.cleanCss({ compatibility: 'edge' }) ) )
        .pipe( $.if( !PRODUCTION, $.sourcemaps.write( '.' ) ) )
        // .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev() ) )
        .pipe( $.rename( cssDestination ) ).pipe( gulp.dest( './' ) )
        .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev.manifest() ) )
        .pipe( $.rename( cssDestination ) ).pipe( gulp.dest( './' ) )
        .pipe( browser.reload({ stream: true }) );
}

// Lint Sass, JavaScript and PHP.
gulp.task( 'lint:scss', async () => run( 'npm run lint:scss' )() );
gulp.task( 'lint:js', async () => run( 'npm run lint:js' )() );
gulp.task( 'lint:php', async () => run( 'composer run-script lint:php' )() );
gulp.task( 'lint:wpcs', async () => run( 'composer run-script lint:wpcs' )() );

/**
 * Combine JavaScript into one file (minified in production).
 *
 * @since 1.0.0
 *
 * @type Object
 */
const webpack = {
    config: {
        mode: STATUS,
        target: 'web',
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /(^_*$|node_modules(?![\\\/]foundation-sites))/,
                    use: ['babel-loader', 'eslint-loader']
                }
            ]
        },
        output: {
            path: path.resolve( __dirname, PATHS.dist + '/js' ),
            filename: '[name].js'
        },
        externals: {
            jquery: 'jQuery',
            lodash: {
                commonjs: 'lodash',
                amd: 'lodash',
                root: '_'
            },
            moment: 'moment',
            DataTable: 'DataTable',
            yadcf: 'yadcf'
        }
    },
    changeHandler( err, stats ) {
        log( '[webpack]', stats.toString({ colors: true }) );

        browser.reload();
    },
    build() {
        return gulp.src( PATHS.entries )
            .pipe( named() )
            .pipe( webpackStream( webpack.config, webpack2 ) )
            .pipe( $.if( PRODUCTION, $.uglify().on( 'error', e => { console.log( e ); }) ) )
            // .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev() ) )
            .pipe( $.rename( jsDestination ) )
            .pipe( gulp.dest( './' ) )
            .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev.manifest() ) )
            .pipe( $.rename( jsDestination ) )
            .pipe( gulp.dest( './' ) );
    },
    watch() {
        const watchConfig = Object.assign( webpack.config, {
            watch: true,
            devtool: 'source-map'
        });

        return gulp.src( PATHS.entries )
            .pipe( named() )
            .pipe( webpackStream( watchConfig, webpack2, webpack.changeHandler )
                .on( 'error', err => {
                    log( '[webpack:error]', err.toString({ colors: true }) )
                })
            )
            .pipe( $.rename( jsDestination ) )
            .pipe( gulp.dest( './' ) );
    }
};

gulp.task( 'webpack:build', webpack.build );
gulp.task( 'webpack:watch', webpack.watch );

/**
 * Copy images to the "dist" folder. Images are compressed in production.
 *
 * @since 1.0.0
 */
function images() {
    return gulp.src( PATHS.images )
        .pipe(
            $.if(
                PRODUCTION,
                // Production.
                $.imagemin([
                    $.imagemin.jpegtran({
                        progressive: true
                    }),
                    $.imagemin.optipng({
                        optimizationLevel: 5
                    }),
                    $.imagemin.gifsicle({
                        interlaced: true
                    }),
                    $.imagemin.svgo({
                        plugins: [
                            { cleanupAttrs: true },
                            { removeComments: true }
                        ]
                    })
                ]),
                // Development.
                $.imagemin([
                    $.imagemin.svgo({
                        plugins: [
                            { cleanupAttrs: true },
                            { removeComments: true }
                        ]
                    })
                ])
            )
        )
        .pipe( gulp.dest( PATHS.dist + '/img' ) );
}

// Create a .zip archive of the theme
function archive() {
    var time = dateFormat( new Date(), "yyyy-mm-dd_HH-MM" );
    var pkg = JSON.parse( fs.readFileSync( './package.json' ) );
    var title = pkg.name + '_' + time + '.zip';

    return gulp.src( PATHS.package )
        .pipe( $.zip( title ) )
        .pipe( gulp.dest( 'packaged' ) );
}

// Local cache purge.
gulp.task( 'cache:clear', function( callback ) {
    return cache.clearAll( callback )
});

// Start BrowserSync to preview the site in
function server( done ) {
    browser.init({
        proxy: BROWSERSYNC.url.join( '' ),
        ui: { port: 8080 }
    });

    done();
}

// Reload the browser with BrowserSync
function reload( done ) {
    browser.reload();
    done();
}

// Watch for changes to static assets, pages, Sass, and JavaScript
function watch() {
    gulp.watch( PATHS.assets, copy );

    gulp.watch( 'src/sass/**/*.scss', gulp.series( 'lint:scss', sass ) )
        .on( 'change', path => log( 'File ' + colors.bold.magenta( path ) + ' changed.' ) )
        .on( 'unlink', path => log( 'File ' + colors.bold.magenta( path ) + ' was removed.' ) );

    gulp.watch( 'src/js/**/*.js', gulp.series( 'lint:js', 'webpack:build', reload ) )
        .on( 'change', path => log( 'File ' + colors.bold.magenta( path ) + ' changed.' ) )
        .on( 'unlink', path => log( 'File ' + colors.bold.magenta( path ) + ' was removed.' ) );

    gulp.watch( '**/*.php', gulp.series( 'lint:php', 'lint:wpcs', reload ) )
        .on( 'change', path => log( 'File ' + colors.bold.magenta( path ) + ' changed.' ) )
        .on( 'unlink', path => log( 'File ' + colors.bold.magenta( path ) + ' was removed.' ) );

    gulp.watch( 'src/img/**/*', gulp.series( images, reload ) );
}

// Build the "dist" folder by running all of the below tasks
gulp.task( 'build',
    gulp.series( clean, 'lint:php', 'lint:wpcs', 'lint:js', 'lint:scss', gulp.parallel( sass, 'webpack:build', images, copy ), reload ) );

// Build the site, run the server, and watch for file changes
gulp.task( 'default',
    gulp.series( 'build', server, gulp.parallel( 'webpack:watch', watch ) ) );

// Package task
gulp.task( 'package',
    gulp.series( 'build', archive ) );
