import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { PanelBody, TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';

export const addToolkitResumeButtonSettings = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( 'uncanny-toolkit/resume-button' === props.name && props.isSelected ) {
			return (
				<Fragment>
					<BlockEdit { ...props } />
					<InspectorControls>
						<PanelBody title={ __( 'Course Association Settings', 'uncanny-learndash-toolkit' ) }>
							<TextControl
								label={ __( 'Course ID', 'uncanny-learndash-toolkit' ) }
								value={ props.attributes.courseId }
								type="number"
								onChange={ ( value ) => {
									props.setAttributes( { courseId: value } );
								} }
							/>
						</PanelBody>
					</InspectorControls>
				</Fragment>
			);
		}

		return <BlockEdit { ...props } />;
	};
}, 'addToolkitResumeButtonSettings' );

addFilter( 'editor.BlockEdit', 'uncanny-toolkit/resume-button', addToolkitResumeButtonSettings );
