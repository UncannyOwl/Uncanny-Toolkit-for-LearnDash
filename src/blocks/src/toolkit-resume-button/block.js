// Import Uncanny Owl icon
import {
	UncannyOwlIconColor
} from '../components/icons';

import {
	ToolkitPlaceholder
} from '../components/editor';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Sidebar
 */

const {
	assign
} = lodash;

const {
	addFilter
} = wp.hooks;

const {
	PanelBody,
	TextControl
} = wp.components;

const {
	Fragment
} = wp.element;

const {
	createHigherOrderComponent
} = wp.compose;

const {
    InspectorControls
} = wp.editor;

registerBlockType( 'uncanny-toolkit/resume-button', {
	title: __( 'Resume Button' ),

	description: __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Autem, dolores.' ),

	icon: UncannyOwlIconColor,

	category: 'uncanny-learndash-toolkit',

	keywords: [
		__( 'Uncanny Owl' ),
	],

	supports: {
		html: false
	},

	attributes: {
		courseId: {
			type: 'string',
			default: ''
		}
	},

	edit({ className, attributes, setAttributes }){
		console.log( attributes );

		return (
			<div className={ className }>
				<ToolkitPlaceholder>
					{ __( 'Resume Button' ) }
				</ToolkitPlaceholder>
			</div>
		);
	},

	save({ className, attributes }){
		// We're going to render this block using PHP
		// Return null
		return null;
	},
});

/**
 * Sidebar
 */

export const addTookitResumeButtonSettings = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        // Check if we have to do something
        if ( props.name == 'uncanny-toolkit/resume-button' && props.isSelected ){
            return (
                <Fragment>
                    <BlockEdit { ...props } />
                    <InspectorControls>

                        <PanelBody title={ __( 'Course Association Settings' ) }>
				            <TextControl
				                label={ __( 'Course ID' ) }
				                value={ props.attributes.courseId }
				                type="number"
				                onChange={ ( value ) => {
				                    props.setAttributes({
				                    	courseId: value
				                    });
				                }}
				            />
				        </PanelBody>

                    </InspectorControls>
                </Fragment>
            );
        }

        return <BlockEdit { ...props } />;
    };
}, 'addTookitResumeButtonSettings' );

addFilter( 'editor.BlockEdit', 'uncanny-toolkit/resume-button', addTookitResumeButtonSettings );