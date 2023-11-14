// @ts-nocheck
'use strict';

const fs                = require( 'node:fs' );
const path              = require( 'node:path' );
const gulp              = require( 'gulp' );
const plugins           = require( 'gulp-load-plugins' );
const imagemin          = import( 'gulp-imagemin' );
const run               = require( 'gulp-run-command' ).default;
const autoprefixer      = require( 'autoprefixer' );
const browserSync       = require( 'browser-sync' );
const colors            = require( 'ansi-colors' );
const cssnano           = require( 'cssnano' );
const dartSass          = require( 'sass' );
const extend            = require( 'lodash/extend.js' );
const log               = require( 'fancy-log' );
const named             = require( 'vinyl-named' );
const postcssImport     = require( 'postcss-easy-import' );
const postcssSortMQ     = require( 'postcss-sort-media-queries' );
const postcssStrip      = require( 'postcss-strip-inline-comments' );
const postcssReporter   = require( 'postcss-reporter' );
const purgecssWP        = require( 'purgecss-with-wordpress' );
const { rimraf }        = require( 'rimraf' );
const yaml              = require( 'js-yaml' );
const ESLintPlugin      = require( 'eslint-webpack-plugin' );
const webpackStream     = require( 'webpack-stream' );
const webpack2          = require( 'webpack' );
const webpackConfig     = require( './webpack.config.js' );

console.log( imagemin );

/**
 * Check for CLI flags.
 *
 * @since 1.0.0
 *
 * @param {string} opt Flag option found in CLI.
 *
 * @return {boolean} True if flag's present. False if not.
 */
const getFlag = opt => {
  const index = process.argv.indexOf( `--${ opt }` );

  if ( index > -1 ) {
    // opt = process.argv[ index + 1 ];
    // return opt && opt.slice( 0, 2 ) !== '--' ? opt : true;
    return true;
  }

  return false;
};

/**
 * Check if file exists synchronously
 *
 * @since 1.0.0
 *
 * @param {string} filepath File path.
 *
 * @return {boolean} True if found. False if not.
 */
const fileExists = filepath => {
  let flag = true;

  try {
    fs.accessSync( filepath, fs.constants.F_OK );
  } catch ( e ) {
    flag = false;
  }

  return flag;
};

/**
 * Load the YAML config.
 *
 * @since 1.0.0
 *
 * @return {yaml} YAML configuration options.
 */
const loadConfig = () => {
  log( 'Loading config file...' );

  let fileName = '';

  if ( fileExists( './config.yml' ) ) {
    fileName = './config.yml';
  } else if ( fileExists( './config-default.yml' ) ) {
    fileName = './config-default.yml';
  } else {
    // Exit if config.yml & config-default.yml do not exist
    log( 'Exiting process, no config file exists.' );

    process.exit( 1 );
  }

  // If the main config file exists, load it!
  log( colors.bold.cyan( fileName ), 'exists, loading', colors.bold.cyan( fileName ) );

  const ymlFile = fs.readFileSync( fileName, 'utf8' );

  return yaml.load( ymlFile );
};

// Load all Gulp plugins into one variable.
const $ = plugins({
  config: `${ __dirname }/package.json`,
  postRequireTransforms: {
    sass( sass ) {
      return sass( dartSass );
    }
  }
});

// Check for --production flag.
const PRODUCTION = !!( getFlag( 'production' ) );

// Check for --development flag unminified with sourcemaps.
const DEV = !!( getFlag( 'development' ) );

// Load settings from the config[-default].yml file.
const { PROJECT, BROWSERSYNC, COMPATIBILITY, REVISIONING, PATHS } = loadConfig();

// Create BrowserSync instance.
const browser = browserSync.create();

/**
 * Primary utility module.
 *
 * @namespace UTIL
 *
 * @type {object}
 */
const UTIL = {
  /**
   * Delete the "dist" folder everytime a build starts.
   *
   * @since 1.0.0
   *
   * @param {Function} done Signal to Gulp that task is finished.
   */
  clean( done ) {
    rimraf.native( `${ PATHS.admin }/css` );
    rimraf.native( `${ PATHS.admin }/js` );
    rimraf.native( PATHS.dist );
    rimraf.native( `${ PATHS.src }/css` );

    done();
  },

  /**
   * Delete the "dist/js" folder everytime a change is saved.
   *
   * @since 1.0.0
   *
   * @param {Function} done Signal to Gulp that task is finished.
   */
  cleanJavaScript( done ) {
    rimraf.native( `${ PATHS.dist }/js` );

    done();
  },

  /**
   * Delete the "dist/css" folder everytime a change is saved.
   *
   * @since 1.0.0
   *
   * @param {Function} done Signal to Gulp that task is finished.
   */
  cleanStyleSheets( done ) {
    rimraf.native( `${ PATHS.dist }/css` );
    rimraf.native( `${ PATHS.src }/css` );

    done();
  },

  /**
   * Copy static files out of the `src` folder. This task skips over the
   * "images", "js", and "scss" folders, which are parsed separately.
   *
   * @since 1.0.0
   *
   * @see gulp-rename()
   */
  copy() {
    const mmenuDirnames = [
      'themes', 'oncanvas', 'offcanvas', 'screenreader', 'autoheight',
      'columns', 'dropdown', 'keyboardnavigation', 'navbars', 'searchfield',
      'setselected', 'pagedim', 'popup', 'positioning', 'shadows'
    ];

    return gulp.src( PATHS.assets.all )
      .pipe( $.rename( UTIL.destination ) )
      .pipe( gulp.dest( './' ) );
  },

  /**
   * Copy CSS assets.
   *
   * @since 1.1.0
   */
  copyCss() {
    return gulp.src( PATHS.assets.css )
      .pipe( gulp.dest( `${ PATHS.dist }/css` ) )
      .pipe( browser.reload({ stream: true }) );
  },

  /**
   * Custom Destination Rules
   *
   * @since 1.0.0
   *
   * @memberof gulp-rename()
   *
   * @param {object} filepath File path.
   *
   * @return {string} Corrected file path.
   */
  destination( filepath ) {
    let dirname;

    // Begin.
    if ( '.php' === filepath.extname ) {
      dirname = 'inc/devicedetect/';
    } else if ( filepath.extname.match( /\.(css|js|map)$/ ) ) {
      dirname = filepath.basename.match( /(admin|widget|customizer)/ ) ? PATHS.admin : PATHS.dist;

      if ( PATHS.admin !== dirname ) {
        if ( '.map' === filepath.extname ) {
          if ( filepath.basename.match( /\.css/ ) ) {
            dirname += '/css/';
          } else if ( filepath.basename.match( /\.js/ ) ) {
            dirname += '/js/';
          }
        } else {
          dirname += `/${ filepath.extname.slice( 1 ) }/`;
        }
      }
    } else if ( filepath.extname.match( /\.(eot|otf|svg|ttc|ttf|woff2?)$/ ) ) {
      dirname = `${ PATHS.dist }/fonts/`;
    }

    filepath.dirname = dirname;

    return filepath;
  },

  /**
   * Copy images to the "dist" folder. Images are compressed in production.
   *
   * @since 1.0.0
   */
  images() {
    const svgOpts = {
      plugins: [
        {
          name: 'removeComments',
          active: true
        },
        {
          name: 'cleanupAttrs',
          active: true
        },
        {
          name: 'convertStyleToAttrs',
          active: true
        },
        {
          name: 'removeEmptyContainers',
          active: true
        }
      ]
    };

    return gulp.src( PATHS.images )
      .pipe( async () => {
        $.if(
          PRODUCTION,
          // Production.
          await imagemin([
            imagemin.mozjpeg({ progressive: true }),
            imagemin.optipng({ optimizationLevel: 5 }),
            imagemin.gifsicle({ interlaced: true }),
            imagemin.svgo( svgOpts )
          ]),
          // Development.
          await imagemin([ imagemin.svgo( svgOpts ) ])
        )
      })
      .pipe( gulp.dest( `${ PATHS.dist }/img` ) );
  },

  /**
   * PurgeCSS
   *
   * @since 1.0.0
   */
  purgecss() {
    if ( DEV ) {
      return gulp.src( PATHS.purgecss )
        .pipe( gulp.dest( `${ PATHS.dist }/css` ) )
        .pipe( browser.reload({ stream: true }) );
    }

    const dev = [
      /^dt-(.*)$/,
      /^dataTable/,
      /^mm-(.*)$/,
      /^wp-(.*)$/,
      /^wpclubmanager/,
      /^wpcm-(.*)$/,
      /^ua-(.*)$/
    ];

    let i = 0;

    const regs = dev.length;

    for ( i; i < regs; i++ ) {
      purgecssWP.safelist.push( dev[i] );
    }

    return gulp.src( PATHS.purgecss )
      .pipe(
        $.purgecss({
          content: PATHS.purge,
          safelist: {
            standard: purgecssWP.safelist,
            greedy: [
              /^figure(\:.*)?$/,
              /^rdb-(.*)$/
            ]
          }
        })
      )
      .pipe( $.rename( UTIL.destination ) )
      .pipe( gulp.dest( './' ) )
      .pipe( browser.reload({ stream: true }) );
  },

  /**
   * Reload the browser with BrowserSync.
   *
   * @since 1.0.0
   *
   * @param {Function} done Signal to Gulp that task is finished.
   */
  reload( done ) {
    browser.reload();
    done();
  },

  /**
   * Compile Sass into CSS -- minified in production.
   *
   * @since 1.0.0
   */
  sass() {
    return gulp.src( PATHS.sass.templates )
      .pipe( $.sourcemaps.init() )
      .pipe( $.sass({ includePaths: PATHS.sass.include }).on( 'error', $.sass.logError ) )
      .pipe(
        $.postcss([
          postcssImport,
          postcssSortMQ({ sort: 'mobile-first' }),
          postcssStrip,
          postcssReporter({ clearReportedMessages: true }),
          autoprefixer({ overrideBrowserslist: COMPATIBILITY }),
          cssnano({ autoprefixer: false })
        ])
      )
      .pipe( $.if( PRODUCTION, $.cleanCss({ compatibility: 'edge' }) ) )
      .pipe( $.if( DEV, $.sourcemaps.write( '.' ) ) )
      .pipe( gulp.dest( `${ PATHS.src }/css` ) );
  },

  /**
   * Start BrowserSync to preview the site in.
   *
   * @since 1.0.0
   *
   * @param {Function} done Signal to Gulp that task is finished.
   */
  server( done ) {
    browser.init({
      proxy: BROWSERSYNC.url.join( '' ),
      ui: { port: 8080 }
    });

    done();
  },

  /**
   * Watch for changes to static assets, pages, Sass, and JavaScript.
   *
   * @since 1.0.0
   */
  watch() {
    gulp.watch( PATHS.assets.all, UTIL.copy );

    // Stylesheets.
    gulp.watch( 'theme.json', gulp.series( 'json2scss', 'lint:scss', 'sass:build' ) )
      .on( 'change', path => log( `File ${ colors.bold.magenta( path ) } changed.` ) )
      .on( 'unlink', path => log( `File ${ colors.bold.magenta( path ) } was removed.` ) );
    gulp.watch( PATHS.sass.watch, gulp.series( 'lint:scss', 'sass:build' ) )
      .on( 'change', path => log( `File ${ colors.bold.magenta( path ) } changed.` ) )
      .on( 'unlink', path => log( `File ${ colors.bold.magenta( path ) } was removed.` ) );

    // PHP.
    gulp.watch( '**/*.php', gulp.series( 'lint:php', 'lint:wpcs', UTIL.reload ) )
      .on( 'change', path => log( `File ${ colors.bold.magenta( path ) } changed.` ) )
      .on( 'unlink', path => log( `File ${ colors.bold.magenta( path ) } was removed.` ) );

    // Images.
    gulp.watch( 'src/img/**/*', gulp.series( UTIL.images, UTIL.reload ) );
  },

  /**
   * Combine JavaScript into one file (minified in production).
   *
   * @since 1.0.0
   *
   * @type Object
   */
  webpack: {
    config: extend( webpackConfig, {
      mode: 'development',
      target: 'web',
      stats: 'errors-warnings',
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
      },
      output: {
        devtoolNamespace: 'rugbydb',
        filename: '[name].js',
        hashFunction: 'sha256',
        path: path.resolve( __dirname, `${ PATHS.dist }/js` ),
        publicPath: 'https://www.rugbydb.com/wp-content/themes/rugbydb/dist/js',
        environment: {
          dynamicImport: true,
          module: true
        }
      },
      plugins: [
        new ESLintPlugin({
          context: `${ PATHS.src }/js`,
          exclude: '_vendor',
          files: PATHS.js.entries
        })
      ],
      resolve: {
        alias: {
          Modules: path.resolve( __dirname, 'src/js/modules' ),
          UI: path.resolve( __dirname, 'src/js/ui' ),
          Utils: path.resolve( __dirname, 'src/js/utils' ),
          Vendor: path.resolve( __dirname, 'src/js/_vendor' )
        },
        extensions: ['.js', '.json']
      }
    }),
    changeHandler( err, stats ) {
      log( '[webpack]', err );
      log( '[webpack]', stats.toString({ colors: true }) );

      browser.reload();
    },
    build() {
      return gulp.src( PATHS.js.entries )
        .pipe( named() )
        .pipe( webpackStream( UTIL.webpack.config, webpack2 ) )
        .pipe( $.rename( UTIL.destination ) )
        .pipe( gulp.dest( './' ) );
    },
    watch() {
      const watchConfig = Object.assign( UTIL.webpack.config, {
        watch: true,
        devtool: 'source-map'
      });

      return gulp.src( PATHS.js.entries )
        .pipe( named() )
        .pipe(
          webpackStream( watchConfig, webpack2, UTIL.webpack.changeHandler )
            .on( 'error', err => {
              log( '[webpack:error]', err.toString({ colors: true }) );
            })
        )
        .pipe( $.rename( UTIL.destination ) )
        .pipe( gulp.dest( './' ) );
    }
  },

  /**
   * Tags and Schemas for JS-YAML.
   *
   * @since 1.0.0
   * @access private
   *
   * @see loadConfig()
   *
   * @param {object} args See `defaults` property.
   *
   * @return {object} Custom tags and schemas.
   */
  ymlSchema( args = {} ) {
    const { Type, Schema } = yaml;

    const defaults = {
      path: 'wp-content/themes/rugbydb',
      sep: '/'
    };

    args = extend( defaults, args );

    const Path2Theme = new Type( '!path2theme', {
      kind: 'sequence',
      construct( data ) {
        if ( typeof data === 'string' ) {
          return args.path + args.sep + data;
        }

        return data.map( string => {
          if ( '!' === string.charAt( 0 ) ) {
            return `${ string.charAt( 0 ) + args.path + args.sep + string.slice( 1 ) }`;
          }

          return args.path + args.sep + string;
        });
      }
    });

    const Join = new Type( '!join', {
      kind: 'sequence',
      construct( data ) {
        return data.join( '' );
      }
    });

    return Schema.create([ Path2Theme, Join ]);
  }
};

// JSON-to-SCSS & lint sass, javascript and PHP.
gulp.task( 'json2scss', async () => await run( 'npm run json2scss' )() );
gulp.task( 'lint:scss', async () => await run( 'npm run lint:scss' )() );
gulp.task( 'lint:js', async () => await run( 'npm run lint:js' )() );
gulp.task( 'lint:php', async () => await run( 'composer run-script lint:php' )() );
gulp.task( 'lint:wpcs', async () => await run( 'composer run-script lint:wpcs' )() );

// Task actions.
const { clean, copy, images, reload, server, watch, webpack, cleanStyleSheets, cleanJavaScript, sass, copyCss, purgecss } = UTIL;

// Stylesheets.
gulp.task( 'sass:build', gulp.series( cleanStyleSheets, sass, gulp.parallel( copyCss, purgecss ) ) );

// Webpack.
gulp.task( 'webpack:build', gulp.series( cleanJavaScript, webpack.build ) );
gulp.task( 'webpack:watch', webpack.watch );

// Build task maintenance.
const phpLint = ['lint:php', 'lint:wpcs'],
      staLint = ['lint:js', 'lint:scss'],
      stBuild = ['sass:build', 'webpack:build'];

// Build the 'dist' folder by running all of the below tasks.
gulp.task( 'build',
  gulp.series(
    clean,
    ...phpLint,
    gulp.parallel( ...staLint ),
    gulp.parallel( ...stBuild, copy ),
    purgecss,
    reload
  )
);

// Build the site, run the server, and watch for file changes
gulp.task( 'default', gulp.series( 'build', server, gulp.parallel( 'webpack:watch', watch ) ) );

// Package task
gulp.task( 'package', gulp.series( 'build' ) );
