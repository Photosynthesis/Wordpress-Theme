var path = require("path");
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const WebpackCleanupPlugin = require('webpack-cleanup-plugin');

var isProduction = process.env.NODE_ENV == 'production';

module.exports = {
  entry: {
    styles: [
      './src/styles.js',
    ],
    fa: [
      'style-loader!css-loader!./node_modules/@fortawesome/fontawesome-free/css/all.css',
    ],
    board_staff: [
      './src/board-staff.js',
    ],
    directory: [
      './src/directory.js',
    ],
    wholesale: [
      './src/wholesale.js',
    ],
    // Entries for admin scripts should all start with `admin_`.
    admin_adb: [
      './src/admin-adb.js',
    ],
    admin_board_staff: [
      './src/admin-board-staff.js',
    ],
    admin_flate_rate: [
      './src/admin-flat-rate.js',
    ],
  },

  output: {
    path: path.resolve(__dirname + '/dist'),
    filename: '[name]-[chunkHash].js',
    publicPath: '/wp-content/themes/fic-theme/dist/',
  },

  module: {
    loaders: [
      {
        test: /\.elm$/,
        exclude: [/elm-stuff/, /node_modules/,],
        loader: 'elm-webpack-loader?verbose=true' +
          (isProduction ? ',optimize=true' : ',optimize=false'),
      },
      {
        test: /\.html$/,
        exclude: /node_modules/,
        loader: 'file?name=[name].[ext]',
      },
      {
        test: /\.sass$/,
        exclude: /node_modules/,
        use: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: ['css-loader', 'sass-loader'],
        }),
      },
      {
        test: /\.css$/,
        exclude: /node_modules/,
        loaders: ['style-loader', 'css-loader']
      },
      {
        test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: "url-loader?limit=10000&mimetype=application/font-woff&name=[hash].[ext]"
      },
      { test: /\.(ttf|eot|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: "file-loader?name=[hash].[ext]"
      },
    ],
  },

  plugins: [
    new ExtractTextPlugin('[contenthash].css'),
    new webpack.DefinePlugin({ isProduction: isProduction }),
    new WebpackCleanupPlugin(),
  ],

};
