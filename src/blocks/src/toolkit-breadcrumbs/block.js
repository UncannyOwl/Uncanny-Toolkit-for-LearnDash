// Import css
import './css/editor.scss';
import './css/style.scss';

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

if ( moduleIsActive( 'Breadcrumbs' ) ){
    registerBlockType('uncanny-toolkit/breadcrumbs', {
        title: __('Breadcrumbs'),
        description: __('Displays breadcrumb links that understand the course > lesson > topic hierarchy of LearnDash.'),

        icon: UncannyOwlIconColor,

        category: 'uncanny-learndash-toolkit',

        keywords: [
            __('Uncanny Owl'),
        ],

        supports: {
            html: false
        },

        attributes: {},

        edit({className, attributes, setAttributes}) {
            return (
                <div className={className}>
                    <ToolkitPlaceholder>
                        {__('Breadcrumbs')}
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
