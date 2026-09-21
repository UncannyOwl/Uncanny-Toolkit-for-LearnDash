import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { UncannyOwlIconColor } from '../components/icons';
import { ToolkitPlaceholder } from '../components/editor';
import '../common.scss';

registerBlockType( metadata.name, {
	icon: UncannyOwlIconColor,
	edit: () => {
		const blockProps = useBlockProps();
		return (
			<div { ...blockProps }>
				<ToolkitPlaceholder>
					{ __( 'Breadcrumbs', 'uncanny-learndash-toolkit' ) }
				</ToolkitPlaceholder>
			</div>
		);
	},
	save: () => null,
} );
