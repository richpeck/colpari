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
import { RelatedPreview } from './preview';

registerBlockType('minervakb/related', {

    title: __('KB Related', 'minerva-kb'),

    description: __('Block with related content links', 'minerva-kb'),

    category: 'minervakb',

    icon: {
        foreground: '#2ab77b',
        src: 'networking',
    },

    keywords: [
        __('KB', 'minerva-kb'),
        __('Related', 'minerva-kb'),
        __('Articles', 'minerva-kb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const { attributes, className, setAttributes, isSelected } = props;
        const settingsFactory = getInspectorControlsFactory('related', setAttributes);

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody
                        title={ __( 'General Settings', 'minerva-kb' ) }>
                        {settingsFactory(_.pick(attributes,
                            'ids'
                        ))}
                    </PanelBody>
                </InspectorControls>
                <div className={classnames('mkb-shortcode-container', className)}>
                    <RelatedPreview {...attributes}/>
                </div>
            </Fragment>
        );
    },

    save: () => null
});
