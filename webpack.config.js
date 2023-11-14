// @ts-nocheck
// Terser `sourceMap`.
const _sourceMap = {
  filename: '[name].js',
  url: '[name].js.map'
};

// Terser `minifyOptons`.
const _minifyOptions = {
  compress: {
    defaults: true,
    arrows: true,
    arguments: true,
    booleans: true,
    booleans_as_integers: false,
    collapse_vars: true,
    comparisons: true,
    computed_props: true,
    conditionals: true,
    dead_code: true,
    directives: true,
    drop_console: false,
    drop_debugger: true,
    ecma: 2023,
    evaluate: true,
    expression: false,
    global_defs: {},
    hoist_funs: false,
    hoist_props: true,
    hoist_vars: false,
    if_return: true,
    inline: true,
    join_vars: true,
    keep_classnames: false,
    keep_fargs: true,
    keep_fnames: false,
    keep_infinity: false,
    loops: true,
    module: true,
    negate_iife: true,
    passes: 2,
    properties: true,
    pure_funcs: null,
    pure_getters: 'strict',
    reduce_vars: true,
    reduce_funcs: true,
    sequences: true,
    side_effects: true,
    switches: true,
    toplevel: false,
    top_retain: null,
    typeofs: true,
    unsafe: false,
    unsafe_arrows: false,
    unsafe_comps: false,
    unsafe_Function: false,
    unsafe_math: false,
    unsafe_methods: false,
    unsafe_proto: false,
    unsafe_regexp: false,
    unsafe_symbols: false,
    unsafe_undefined: false,
    unused: true,
  },
  ecma: 2022,
  enclose: true,
  module: true,
  parse: {
    bare_returns: false,
    html5_comments: true,
    shebang: true,
    spidermonkey: false
  },
  sourceMap: _sourceMap,
};

module.exports = {
  experiments: {
    asyncWebAssembly: true,
    backCompat: true,
    layers: true,
    lazyCompilation: {
      imports: false,
      entries: false
    },
    outputModule: true,
    syncWebAssembly: true,
    topLevelAwait: true
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /\/(node_modules|foundation-sites)\//,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.js$/,
        enforce: 'pre',
        use: ['source-map-loader']
      }
    ]
  },
  optimization: {
    minimize: false,
    minimizer: [
      ( compiler ) => {
        const TerserPlugin = require( 'terser-webpack-plugin' );
        new TerserPlugin({
          minify: TerserPlugin.swcMinify,
          parallel: true,
          terserOptions: {
            compress: false,
            ecma: _minifyOptions.ecma,
            parse: _minifyOptions.parse,
            mangle: {
              properties: {
                keep_quoted: 'strict',
                reserved: ['__', '_n', '_nx', '_x']
              },
              reserved: ['__', '_n', '_nx', '_x']
            },
            module: _minifyOptions.module,
            sourceMap: _sourceMap
          },
          extractComments: false
        }).apply( compiler );
      }
    ],
    runtimeChunk: 'single'
  }
};
