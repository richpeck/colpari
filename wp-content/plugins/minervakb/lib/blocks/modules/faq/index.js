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
import { FaqPreview } from './preview';
import { PreviewNote } from 'components/preview-note';

registerBlockType('minervakb/faq', {

    title: __('KB FAQ', 'minerva-kb'),

    description: __('FAQ section with live filter', 'minerva-kb'),

    category: 'minervakb',

    icon: {
        foreground: '#009adf',
        src: 'editor-help',
    },

    keywords: [
        __('KB', 'minerva-kb'),
        __('FAQ', 'minerva-kb'),
        __('Q&A', 'minerva-kb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const { attributes, className, setAttributes, isSelected } = props;
        const settingsFactory = getInspectorControlsFactory('faq', setAttributes);

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody
                        title={ __( 'General FAQ Settings', 'minerva-kb' ) }>
                        {settingsFactory(_.pick(attributes,
                            'title',
                            'titleSize'
                        ))}
                    </PanelBody>
                    {settingsFactory(_.pick(attributes,
                        'titleColor'
                    ))}
                    <PanelBody
                        title={ __( 'FAQ layout', 'minerva-kb' ) }
                        initialOpen={false}>
                        {settingsFactory(_.pick(attributes,
                            'marginTop',
                            'marginBottom',
                            'limitWidth',
                            'width'
                        ))}
                    </PanelBody>
                    <PanelBody
                        title={ __( 'FAQ Controls Settings', 'minerva-kb' ) }
                        initialOpen={false}>
                        {settingsFactory(_.pick(attributes,
                            'controlsMarginTop',
                            'controlsMarginBottom',
                            'showFilter',
                            'showToggleAll'
                        ))}
                    </PanelBody>
                    <PanelBody
                        title={ __( 'FAQ Categories Settings', 'minerva-kb' ) }
                        initialOpen={false}>
                        {settingsFactory(_.pick(attributes,
                            'categories',
                            'showCategories',
                            'showCount'
                        ))}
                    </PanelBody>
                </InspectorControls>
                {isSelected
                    ? <PreviewNote text={__('This block uses preview questions instead of actual FAQ content', 'minervakb')} />
                    : null
                }
                <div className={classnames('mkb-shortcode-container', className)}>
                    <FaqPreview {...attributes}/>
                </div>
            </Fragment>
        );
    },

    save: () => null
});
