const webpackConfig = {
    mode: 'development',
    target: 'web',
    stats: {
        errorDetails: true
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules(?![\\\/]foundation-sites))/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            }
        ]
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
};

export default webpackConfig;
