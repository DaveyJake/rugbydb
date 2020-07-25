/**
 * Gulp Task Runner
 *
 * @version 1.0.0
 */

import fs            from 'fs';
import path          from 'path';
import gulp          from 'gulp';
import plugins       from 'gulp-load-plugins';
import browser       from 'browser-sync';
import colors        from 'ansi-colors';
import log           from 'fancy-log';
import rimraf        from 'rimraf';
import named         from 'vinyl-named';
import webpack2      from 'webpack';
import webpackStream from 'webpack-stream';
import yaml          from 'js-yaml';

const $ = plugins();

// Load all Gulp plugins into one variable
const $ = plugins();

// Check for --production flag
const PRODUCTION = !!yargs.argv.production;

// Check for --development flag unminified with sourcemaps
const DEV = !!yargs.argv.dev;

// Load settings from settings.yml
const { BROWSERSYNC, COMPATIBILITY, REVISIONING, PATHS } = loadConfig();

// Check if file exists synchronously
function checkFileExists( filepath ) {
    let flag = true;

    try {
        fs.accessSync( filepath, fs.F_OK );
    } catch ( e ) {
        flag = false;
    }

    return flag;
}

// Load default or custom YML config file
function loadConfig() {
    log( 'Loading config file...' );

    if ( checkFileExists( 'config.yml' ) ) {
        // config.yml exists, load it
        log( colors.bold( colors.cyan( 'config.yml' ) ), 'exists, loading', colors.bold( colors.cyan( 'config.yml' ) ) );
        let ymlFile = fs.readFileSync( 'config.yml', 'utf8' );

        return yaml.load( ymlFile );
    } else if ( checkFileExists( 'config-default.yml' ) ) {
        // config-default.yml exists, load it
        log( colors.bold( colors.cyan( 'config.yml' ) ), 'does not exist, loading', colors.bold( colors.cyan( 'config-default.yml' ) ) );
        let ymlFile = fs.readFileSync( 'config-default.yml', 'utf8' );

        return yaml.load( ymlFile );
    } else {
        // Exit if config.yml & config-default.yml do not exist
        log( 'Exiting process, no config file exists.' );
        log( 'Error Code:', err.code );

        process.exit( 1 );
    }
}

// Delete the "dist" folder
// This happens every time a build starts
function clean( done ) {
    rimraf( PATHS.dist, done );
}

// Copy files out of the assets folder
// This task skips over the "images", "js", and "scss" folders, which are parsed separately
function copy() {
    return gulp.src( PATHS.assets ).pipe( gulp.dest( PATHS.dist ) );
}

// Compile Sass into CSS
// In production, the CSS is compressed
function sass() {
    return gulp.src( ['src/sass/usardb.scss'] )
        .pipe( $.sourcemaps.init() )
        .pipe( $.sass({ includePaths: PATHS.sass }).on( 'error', $.sass.logError ) )
        .pipe( $.autoprefixer({ overrideBrowserslist: COMPATIBILITY }) )
        .pipe( $.if( PRODUCTION, $.cleanCss({ compatibility: 'edge' }) ) )
        .pipe( $.if( ! PRODUCTION, $.sourcemaps.write() ) )
        .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev() ) )
        .pipe( gulp.dest( PATHS.dist + '/css' ) )
        .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev.manifest() ) )
        .pipe( gulp.dest( PATHS.dist + '/css' ) )
        .pipe( browser.reload({ stream: true }) );
}

// Combine JavaScript into one file
// In production, the file is minified
const webpack = {
    config: {
        module: {
            rules: [
                {
                    test: /\.js$/,
                    loader: 'babel-loader',
                    exclude: /node_modules/
                }
            ]
        },
        mode: 'development',
        externals: {
            jquery: 'jQuery',
            lodash: {
                commonjs: 'lodash',
                amd: 'lodash',
                root: '_'
            },
            modernizr: 'Modernizr',
            moment: 'moment'
        }
    },
    changeHandler( err, stats ) {
        log( '[webpack]', stats.toString( {
            colors: true
        } ) );

        browser.reload();
    },
    admin() {
        return gulp.src( ['src/js/admin/usardb-admin.js'] )
            .pipe( named() )
            .pipe( webpackStream( webpack.config, webpack2 ) )
            .pipe( $.if( PRODUCTION, $.uglify().on( 'error', e => console.log( e ) ) ) )
            .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev() ) )
            .pipe( gulp.dest( 'admin' ) )
            .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev.manifest() ) )
            .pipe( gulp.dest( 'admin' ) );
    },
    build() {
        this.admin();

        return gulp.src( PATHS.entries )
            .pipe( named() )
            .pipe( webpackStream( webpack.config, webpack2 ) )
            .pipe( $.if( PRODUCTION, $.uglify().on( 'error', e => console.log( e ) ) ) )
            .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev() ) )
            .pipe( gulp.dest( PATHS.dist + '/js' ) )
            .pipe( $.if( REVISIONING && PRODUCTION || REVISIONING && DEV, $.rev.manifest() ) )
            .pipe( gulp.dest( PATHS.dist + '/js' ) );
    },
    watch() {
        const watchConfig = Object.assign( webpack.config, {
            watch: true,
            devtool: 'source-map'
        } );

        return gulp.src( PATHS.entries )
            .pipe( named() )
            .pipe( webpackStream( watchConfig, webpack2, webpack.changeHandler )
                .on( 'error', err => log( '[webpack:error]', err.toString({ colors: true }) ) ),
            )
            .pipe( gulp.dest( PATHS.dist + '/js' ) );
    }
};

gulp.task( 'webpack:build', webpack.build );
gulp.task( 'webpack:watch', webpack.watch );

// Copy images to the "dist" folder
// In production, the images are compressed
function images() {
    return gulp.src( [ 'src/img/*', 'src/img/**/*' ] )
        .pipe( $.if( PRODUCTION, $.imagemin( [
            $.imagemin.jpegtran( {
                progressive: true
            } ),
            $.imagemin.optipng( {
                optimizationLevel: 5
            } ),
            $.imagemin.gifsicle( {
                interlaced: true
            } ),
            $.imagemin.svgo( {
                plugins: [
                    { cleanupAttrs: true },
                    { removeComments: true }
                ]
            } )
        ] ) ) )
        .pipe( gulp.dest( PATHS.dist + '/img' ) );
}

// Lint PHP
gulp.task( 'phpl', function() {
    return gulp.src( PATHS.phpcs )
        .pipe( $.runCommand( 'composer run-scripts lint:php' ) )
        .on( 'error', log )
        .pipe( gulp.dest( '.' ) );
});

// Sniff code.
gulp.task( 'phpcs', function() {
    return gulp.src( PATHS.phpcs )
        .pipe( $.runCommand( 'composer run-scripts lint:wpcs' ) )
        .on( 'error', log )
        .pipe( gulp.dest( '.' ) );
});

// Start BrowserSync to preview the site in
function server( done ) {
    browser.init({
        proxy: BROWSERSYNC.url,
        ui: {
            port: 8080
        }
    });

    done();
}

// Reload the browser with BrowserSync
function reload(done) {
    browser.reload();
    done();
}

// Watch for changes to static assets, pages, Sass, and JavaScript
function watch() {
    gulp.watch( PATHS.assets, copy );

    gulp.watch( 'src/assets/scss/**/*.scss', sass )
        .on( 'change', path => log( 'File ' + colors.bold.magenta( path ) ) + ' changed.' )
        .on( 'unlink', path => log( 'File ' + colors.bold.magenta( path ) ) + ' was removed.' );

    gulp.watch( '**/*.php', reload )
        .on( 'change', path => log( 'File ' + colors.bold.magenta( path ) ) + ' changed.' )
        .on( 'unlink', path => log( 'File ' + colors.bold.magenta( path ) ) + ' was removed.' );

    gulp.watch( 'src/assets/img/**/*', gulp.series( images, reload ) );
}

// Build the "dist" folder by running all of the below tasks
gulp.task( 'build', gulp.series( clean, gulp.parallel( sass, 'webpack:build', images, copy ) ) );

// Build the site, run the server, and watch for file changes
gulp.task( 'default', gulp.series( 'build', 'phpl', 'phpcs', server, gulp.parallel( 'webpack:watch', watch ) ) );