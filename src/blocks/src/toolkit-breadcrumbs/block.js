// Import Uncanny Owl icon
import {
	UncannyOwlIconColor
} from '../components/icons';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { ServerSideRender } = wp.components;

registerBlockType( 'uncanny-toolkit/breadcrumbs', {
	title: __( 'Breadcrumbs' ),

	description: __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Autem, dolores.' ),

	icon: UncannyOwlIconColor,

	category: 'uncanny-learndash-toolkit',

	keywords: [
		__( 'Uncanny Owl' ),
	],

	supports: {
		html: false
	},

	attributes: {},

	edit({ className, attributes, setAttributes }){
		return (
			<div className={ className }>
				<ServerSideRender
					block="uncanny-toolkit/breadcrumbs"
				/>
			</div>
		);
	},

	save({ className, attributes }){
		// We're going to render this block using PHP
		// Return null
		return null;
	},
});