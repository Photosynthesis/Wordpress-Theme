var path = require("path");
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');

module.exports = {
  entry: {
    styles: [
      './src/styles.js',
    ],
    fa: [
      'style-loader!css-loader!./node_modules/font-awesome/css/font-awesome.css',
    ],
  },

  output: {
    path: path.resolve(__dirname + '/dist'),
    filename: '[chunkHash].js',
    publicPath: '/wp-content/themes/fic-theme/dist/',
  },

  module: {
    loaders: [
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
    new ExtractTextPlugin('[chunkHash].css'),
    new CleanWebpackPlugin('dist'),
  ],

};
