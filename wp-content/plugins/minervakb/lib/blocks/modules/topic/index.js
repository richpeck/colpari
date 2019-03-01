/**
 * External dependencies
 */
const _ = window.lodash;
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { PanelBody } = wp.components;
const { InspectorControls } = wp.editor;

/**
 * Internal dependencies
 */
import { getOption } from 'utils';
import { getInspectorControlsFactory } from 'controls';
import { TopicPreview } from './preview';

registerBlockType('minervakb/topic', {

    title: __('KB Topic', 'minerva-kb'),

    description: __('Single KB topic', 'minerva-kb'),

    category: 'minervakb',

    icon: {
        foreground: '#009adf',
        src: 'category',
    },

    keywords: [
        __('KB', 'minerva-kb'),
        __('Topic', 'minerva-kb'),
        __('Articles list', 'minerva-kb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const { attributes, className, setAttributes, isSelected } = props;
        const settingsFactory = getInspectorControlsFactory('topic', setAttributes);

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody
                        title={ __( 'Topic Settings', 'minerva-kb' ) }>
                        {settingsFactory(_.pick(attributes,
                            'id',
                            'view',
                            'columns',
                            'limit'
                        ))}
                    </PanelBody>
                </InspectorControls>
                <div className={classnames('mkb-shortcode-container', className)}>
                    <TopicPreview {...attributes}/>
                </div>
            </Fragment>
        );
    },

    save: () => null
});
