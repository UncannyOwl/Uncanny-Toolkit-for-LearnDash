import {
    UncannyOwlIconColor
} from '../components/icons';

import {
    moduleIsActive
} from '../utilities';

import {
    ToolkitPlaceholder
} from '../components/editor';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

if ( moduleIsActive( 'FrontendLoginPlus' ) ){

    registerBlockType( 'uncanny-toolkit/frontend-login', {
        title: __( 'Front End Login' ),

        description: __( 'Displays the Uncanny Toolkit front end login form.' ),

        icon: UncannyOwlIconColor,

        category: 'uncanny-learndash-toolkit',

        keywords: [
            __( 'Uncanny Owl' ),
        ],

        supports: {
            html: false
        },

        attributes: {},

        edit({ className, attributes, setAttributes }) {
            return (
                <div className={className}>
                    <ToolkitPlaceholder>
                        { __( 'Front End Login' ) }
                    </ToolkitPlaceholder>
                </div>
            );
        },

        save({ className, attributes }) {
            // We're going to render this block using PHP
            // Return null
            return null;
        },
    });
}
