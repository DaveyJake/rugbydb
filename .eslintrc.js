module.exports = {
  'env': {
    'browser': true,
    'builtin': true,
    'es6': true,
    'jquery': true,
    'node': true
  },
  'globals': {
    'window': true,
    'document': true,
    'jQuery': true,
    'wp': 'readonly'
  },
  'plugins': ['import', 'jsdoc', '@wordpress/eslint-plugin'],
  'extends': ['plugin:@wordpress/eslint-plugin/recommended-with-formatting'],
  'parser': 'babel-eslint',
  'parserOptions': {
    'ecmaVersion': 2017,
    'sourceType': 'module',
    'ecmaFeatures': {
      'modules': true,
      'impliedStrict': true
    }
  },
  'rules': {
    'block-scoped-var': 2,
    'camelcase': 2,
    'comma-style': [2, 'last'],
    'curly': [0, 'all'],
    'dot-notation': [2, { 'allowKeywords': false }],
    'eqeqeq': [2, 'allow-null'],
    'guard-for-in': 2,
    'new-cap': 2,
    'no-bitwise': 2,
    'no-caller': 2,
    'no-cond-assign': [2, 'except-parens'],
    'no-debugger': 2,
    'no-empty': 2,
    'no-eval': 2,
    'no-extend-native': 2,
    'no-extra-parens': 1,
    'no-irregular-whitespace': 2,
    'no-iterator': 2,
    'no-loop-func': 2,
    'no-mixed-spaces-and-tabs': ['error', 'smart-tabs'],
    'no-multi-str': 2,
    'no-new': 2,
    'no-plusplus': 0,
    'no-proto': 2,
    'no-script-url': 2,
    'no-sequences': 2,
    'no-shadow': 1,
    'no-undef': 2,
    'no-unused-vars': 1,
    'no-with': 2,
    'quotes': 0,
    'semi': [0, 'never'],
    'space-in-parens': ['error', 'always', { 'exceptions': ['empty'] }],
    'strict': [1, 'global'],
    'valid-typeof': 2,
    'wrap-iife': [2, 'inside']
  },
  'root': true
};