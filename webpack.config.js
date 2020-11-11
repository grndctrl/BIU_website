const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CleanWebpackPlugin = require('clean-webpack-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')

module.exports = (env, options) => {
  console.log('env: ')
  console.log(env)
  console.log('options: ')
  console.log(options)

  return {
    entry: {
      bundle: './theme/src/index.js',
      fonts: './theme/src/fonts.js',
    },
    output: {
      path: path.resolve(__dirname, 'server/wp-content/themes', 'beamitup/dist'),
      filename: '[name].js',
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
          },
        },
        {
          test: /\.css$/,
          use: [
            {
              loader: MiniCssExtractPlugin.loader,
            },
            'css-loader',
            {
              loader: 'postcss-loader',
              options: {
                config: {
                  ctx: {
                    purgecss: options.mode === 'production' ? { content: ['./**/*.twig'] } : false,
                    cssnano: options.mode === 'production' ? {} : false,
                  },
                },
              },
            },
          ],
        },
        {
          test: /\.(ttf|eot|woff|woff2)$/,
          use: {
            loader: 'file-loader',
            options: {
              name: 'fonts/[name].[ext]',
            },
          },
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: '[name].css',
        chunkFilename: '[id].css',
      }),
      new CleanWebpackPlugin(path.resolve(__dirname, 'server/wp-content/themes', 'beamitup')),
      new CopyWebpackPlugin([
        {
          from: 'theme/include',
          to: path.resolve(__dirname, 'server/wp-content/themes', 'beamitup'),
        },
        {
          from: 'plugins',
          to: path.resolve(__dirname, 'server/wp-content/plugins'),
        },
        {
          from: 'theme/templates',
          to: path.resolve(__dirname, 'server/wp-content/themes', 'beamitup/templates'),
        },
      ]),
    ],
  }
}
