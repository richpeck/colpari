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
import { TopicsPreview } from './preview';

registerBlockType('minervakb/topics', {

    title: __('KB Topics', 'minerva-kb'),

    description: __('A set of KB topics', 'minerva-kb'),

    category: 'minervakb',

    icon: {
        foreground: '#009adf',
        src: 'screenoptions',
    },

    keywords: [
        __('KB', 'minerva-kb'),
        __('Topics', 'minerva-kb'),
        __('Knowledge Base', 'minerva-kb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const { attributes, className, setAttributes, isSelected } = props;
        const settingsFactory = getInspectorControlsFactory('topics', setAttributes);

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody
                        title={ __( 'General Topics Settings', 'minerva-kb' ) }>
                        {settingsFactory(_.pick(attributes,
                            'title',
                            'titleSize',
                            'view',
                            'columns',
                            'topics',
                            'limit',
                            'hideChildren',
                            'articlesLimit',
                            'showDescription',
                            'showAll',
                            'showAllLabel',
                            'showCount'
                        ))}
                    </PanelBody>
                    {settingsFactory(_.pick(attributes,
                        'topicColor',
                        'titleColor',
                        'boxItemBg',
                        'boxItemHoverBg',
                        'countBg',
                        'countColor'
                    ))}
                    <PanelBody
                        title={ __( 'Topic style settings', 'minerva-kb' ) }
                        initialOpen={false}>
                        {settingsFactory(_.pick(attributes,
                            'forceTopicColor',
                            'showTopicIcons',
                            'topicIcon',
                            'forceTopicIcon',
                            'useTopicImage',
                            'imageSize',
                            'iconPaddingTop',
                            'iconPaddingBottom'
                        ))}
                    </PanelBody>
                    <PanelBody
                        title={ __( 'Articles settings', 'minerva-kb' ) }
                        initialOpen={false}>
                        {settingsFactory(_.pick(attributes,
                            'showArticleIcons',
                            'articleIcon'
                        ))}
                    </PanelBody>
                </InspectorControls>
                <div className={classnames('mkb-shortcode-container', className)}>
                    <TopicsPreview {...attributes} isSelected={isSelected}/>
                </div>
            </Fragment>
        );
    },

    save: () => null
});
