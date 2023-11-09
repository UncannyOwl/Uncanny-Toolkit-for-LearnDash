const { __ } = wp.i18n;

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