var path = require("path");

module.exports = {
  entry: {
    styles: [
      './src/styles.js',
    ],
    base: [
      './src/base.js',
    ],
  },

  output: {
    path: path.resolve(__dirname + '/dist'),
    filename: '[name].js'
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
        loaders: ['style-loader', 'css-loader', 'sass-loader'],
      },
      {
        test: /\.css$/,
        exclude: /node_modules/,
        loaders: ['style-loader', 'css-loader']
      },
      {
        test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: "url-loader?limit=10000&minetype=application/font-woff"
      },
      { test: /\.(ttf|eot|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: "file-loader"
      },
    ],
  },

  // devServer: {
  //   inline: true,
  //   host: '0.0.0.0',
  //   stats: {
  //     colors: true,
  //     chunks: false,
  //   },
  //   proxy: {
  //     '/api/*': {
  //       target: 'http://localhost:8080',
  //       changeOrigin: true,
  //       pathRewrite: { "^/api/": "" },
  //     }
  //   }
  // },

};
