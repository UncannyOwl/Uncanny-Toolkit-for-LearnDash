const path                    = require( 'path' );
const webpack                 = require( 'webpack' );
const CleanWebpackPlugin      = require( 'clean-webpack-plugin' );
const MiniCssExtractPlugin    = require( 'mini-css-extract-plugin' );
const OptimizeCssAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const CopyWebpackPlugin       = require( 'copy-webpack-plugin' );

module.exports = ( env, options ) => {

	let fileWatcher = true;
	let _mode       = `${options.mode}`;

	if ( 'production' === _mode ){
		fileWatcher = false;
	}

	return {
		watch: fileWatcher,
		devtool: 'source-map',
		entry: {
			//backend: './src/assets/backend/index.js',
			frontend: './src/assets/frontend/src/index.js',
		},
		output: {
			path: path.join( __dirname, './src/assets/frontend/dist/' ),
			filename: '[name]/bundle.min.js'
		},
		externals: {
			jquery: 'jQuery'
		},
		// resolve: {
		// 	alias: {
		// 		fonts: path.resolve( __dirname, './src/assets/common/fonts' ),
		// 	},
		// },
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: ['@babel/preset-env']
						}
					}
				},
				{
					test: /\.scss$/,
					use: [
						MiniCssExtractPlugin.loader,
						'css-loader',
						'sass-loader',
					]
				},
				{
					test: /\.(png|jpe?g|gif|svg)(\?.*)?$/,
					include: [
						path.join( __dirname, './src/assets/frontend/img/' ),
						//path.join( __dirname, './src/assets/backend/img/' ),
					],
					loader: 'url-loader',
					options: {
						limit: 10, // Convert images < 10kb to base64 strings
						name: 'img/[name].[ext]',
					},
				},
				{
					test: /\.(woff|woff2|eot|ttf|svg)$/,
					include: [
						path.join( __dirname, './src/assets/common/fonts/' ),
					],
					loader: 'url-loader',
					options: {
						limit: 10000,
						name: '[name].[ext]',
					},
				}
			],
		},
		plugins: [
			new webpack.ProvidePlugin({
				$: 'jquery',
				jQuery: 'jquery'
			}),
			new MiniCssExtractPlugin( {
				filename: '[name]/bundle.min.css'
			} ),
			new CleanWebpackPlugin( [
				'./src/assets/frontend/dist/*'
			] ),
			new CopyWebpackPlugin( [
				// {
				// 	from: './src/assets/backend/img',
				// 	to: './backend/img'
				// },
				{
					from: './src/assets/frontend/img',
					to: './frontend/img'
				}
				// {
				// 	from: './src/assets/common/img',
				// 	to: './common/img'
				// },
				// {
				// 	from: './src/assets/common/fonts',
				// 	to: './common/fonts'
				// }
			] ),
			new OptimizeCssAssetsPlugin( {
				assetNameRegExp: /\.css$/g,
				cssProcessor: require( 'cssnano' ),
				cssProcessorOptions: {
					map: {
						inline: false
					}
				},
				cssProcessorPluginOptions: {
					preset: ['default', {
						discardComments: {
							removeAll: true
						}
					}]
				},
				canPrint: true
			} ),
		]
	};
}