const path                    = require( 'path' );
const CleanWebpackPlugin      = require( 'clean-webpack-plugin' );
const MiniCssExtractPlugin    = require( 'mini-css-extract-plugin' );
const OptimizeCssAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );

module.exports = {
	watch: true,
	entry: {
		main: './src/index.js'
	},
	output: {
		path: path.resolve( __dirname, 'dist' ),
		filename: 'bundle.min.js'
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader'
				}
			},
			{
				test: /\.scss$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					'sass-loader'
				]
			}
		]
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: 'bundle.min.css'
		}),
		new CleanWebpackPlugin([
			'./dist/*.*'
		]),
		new OptimizeCssAssetsPlugin({
            assetNameRegExp: /\.css$/g,
            cssProcessor: require('cssnano'),
            cssProcessorPluginOptions: {
                preset: ['default', {
                    discardComments: {
                        removeAll: true
                    }
                }]
            },
            canPrint: true
        })
	]
};