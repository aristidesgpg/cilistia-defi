/* eslint-disable */
const path = require("path");
const {CleanWebpackPlugin} = require("clean-webpack-plugin");
const {BundleAnalyzerPlugin} = require("webpack-bundle-analyzer");
const ESLintPlugin = require("eslint-webpack-plugin");
const mix = require("laravel-mix");

mix.webpackConfig({
    output: {
        filename: "[name].js",
        chunkFilename: "chunks/[name].[contenthash].js"
    },

    module: {
        rules: [
            {
                test: /\.(ogg|mp3)$/i,
                include: path.resolve(__dirname, "./resources"),
                use: [
                    {
                        loader: "file-loader",
                        options: {
                            outputPath: "sounds"
                        }
                    }
                ]
            }
        ]
    },

    resolve: {
        modules: [
            "node_modules",
            path.resolve(__dirname, "./resources/js"),
            path.resolve(__dirname, "./resources")
        ],
        alias: {
            "@material-ui/core": "@mui/material",
            "@ziggy": path.resolve('vendor/tightenco/ziggy/src/js'),
            "@material-ui/styles": "@mui/styles",
        }
    },

    devServer: {
        allowedHosts: 'all',
    },

    devtool: mix.inProduction() ? false : "cheap-source-map",
});


mix.options({
    postCss: [
        require("postcss-at-rules-variables"),
        require("postcss-each")
    ]
});

if (!Mix.inProduction()) {
    mix.webpackConfig({
        plugins: [
            new ESLintPlugin({
                lintDirtyModulesOnly: true,
                fix: true
            }),
        ]
    });

    if (!Mix.isWatching()) {
        mix.webpackConfig({
            plugins: [
                new BundleAnalyzerPlugin({
                    analyzerMode: "static",
                }),
            ]
        });
    }
} else {
    mix.webpackConfig({
        plugins: [
            new CleanWebpackPlugin({
                cleanOnceBeforeBuildPatterns: ["+(js|css|sounds|chunks|images|fonts)/**/*"],
                cleanStaleWebpackAssets: false
            })
        ]
    });
}

if (Mix.isWatching()) {
    const url = new URL(process.env.APP_URL);

    const hmrHost = "app." + url.hostname;
    const hmrPort = 8080;

    mix.options({
        hmrOptions: {
            host: hmrHost,
            port: hmrPort
        }
    });

    mix.setResourceRoot(`http://${hmrHost}:${hmrPort}/`);

    mix.webpackConfig({
        devServer: {
            host: "0.0.0.0",
            port: hmrPort
        }
    });
}

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/scss/global.scss', 'css')

mix.js("resources/js/admin.js", "js").react({extractStyles: false});
mix.js("resources/js/commercePayment.js", "js").react({extractStyles: false});
mix.js("resources/js/main.js", "js").react({extractStyles: false});

mix.js("resources/vendor/socket.js", "vendor");
mix.js("resources/vendor/pusher.js", "vendor");

if (mix.inProduction()) {
    mix.version();
}