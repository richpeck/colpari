/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import { getOption } from 'utils';

registerBlockType('minervakb/article-content', {

    title: __('KB Article Content', 'minervakb'),

    description: __('For cases when you need to build article template with page builder', 'minervakb'),

    category: 'minervakb',

    icon: {
        foreground: '#009adf',
        src: 'media-text',
    },

    keywords: [
        __('KB', 'minervakb'),
        __('Article', 'minervakb'),
        __('Template', 'minervakb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const {className} = props;

        return (
            <div className={classnames('mkb-shortcode-container', className)}>
                {__('Article Content (this item does not have preview)', 'minervakb')}
            </div>
        );
    },

    save: () => null
});
