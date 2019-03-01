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
const { PanelBody, ToggleControl } = wp.components;
const { InspectorControls } = wp.editor;

/**
 * Internal dependencies
 */
import { getOption } from 'utils';
import { getInspectorControlsFactory } from 'controls';
import { SearchPreview } from './preview';

registerBlockType('minervakb/search', {

    title: __('KB Search', 'minerva-kb'),

    description: __('Live KB search with custom themes', 'minerva-kb'),

    category: 'minervakb',

    icon: {
        foreground: '#009adf',
        src: 'search',
    },

    keywords: [
        __('KB', 'minerva-kb'),
        __('Search', 'minerva-kb'),
        __('Ajax search', 'minerva-kb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const { attributes, className, setAttributes, isSelected } = props;
        const settingsFactory = getInspectorControlsFactory('search', setAttributes);

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody
                        title={ __( 'Preview Settings', 'minerva-kb' ) }>
                        <ToggleControl
                            label={ 'Show search results preview?' }
                            help={ 'You can enable this to see how your search results will look like' }
                            checked={ attributes.showSearchResultsPreview }
                            onChange={ (newValue) => setAttributes({ showSearchResultsPreview: newValue }) }
                        />
                    </PanelBody>
                    <PanelBody
                        title={ __( 'General Search Settings', 'minerva-kb' ) }>
                        {settingsFactory(_.pick(attributes,
                            'title',
                            'titleSize',
                            'theme',
                            'minWidth',
                            'topPadding',
                            'bottomPadding',
                            'placeholder',
                            'topics',
                            'noFocus',
                            'showTip',
                            'tip',
                            'showTopic',
                            'resultsMultiline',
                            'topicLabel',
                        ))}
                    </PanelBody>
                    {settingsFactory(_.pick(attributes,
                        'titleColor',
                        'borderColor',
                        'bg',
                        'tipColor',
                        'topicBg',
                        'topicColor',
                    ))}
                    <PanelBody
                        title={ __( 'Search style settings', 'minerva-kb' ) }
                        initialOpen={false}>
                        {settingsFactory(_.pick(attributes,
                            'imageBg',
                            'addGradient',
                            'gradientFrom',
                            'gradientTo',
                            'gradientOpacity',
                            'addPattern',
                            'pattern',
                            'patternOpacity',
                            'topicCustomColors'
                        ))}
                    </PanelBody>
                    <PanelBody
                        title={ __( 'Icon settings', 'minerva-kb' ) }
                        initialOpen={false}>
                        {settingsFactory(_.pick(attributes,
                            'iconsLeft',
                            'showSearchIcon',
                            'searchIcon',
                            'clearIcon',
                        ))}
                    </PanelBody>
                </InspectorControls>
                <div className={classnames('mkb-shortcode-container', className)}>
                    <SearchPreview {...attributes}/>
                </div>
            </Fragment>
        );
    },

    save: () => null
});
