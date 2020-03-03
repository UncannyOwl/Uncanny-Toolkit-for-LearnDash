import './sidebar.js';

import {
    moduleIsActive
} from '../utilities';

import {
    UncannyOwlIconColor
} from '../components/icons';

import {
    ToolkitPlaceholder
} from '../components/editor';

const {__} = wp.i18n;
const {registerBlockType} = wp.blocks;

if ( moduleIsActive( 'LearnDashResume' ) ){
    registerBlockType('uncanny-toolkit/resume-button', {
        title: __('Resume Button'),

        description: __('Displays a button that enables users to resume their learning in LearnDash courses.'),

        icon: UncannyOwlIconColor,

        category: 'uncanny-learndash-toolkit',

        keywords: [
            __('Uncanny Owl'),
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

        edit({className, attributes, setAttributes}) {

            return (
                <div className={className}>
                    <ToolkitPlaceholder>
                        {__('Resume Button')}
                    </ToolkitPlaceholder>
                </div>
            );
        },

        save({className, attributes}) {
            // We're going to render this block using PHP
            // Return null
            return null;
        },
    });
}
