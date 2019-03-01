/**
 * External dependencies
 */
const _ = window.lodash;

/**
 * WordPress dependencies
 */
const {
    TextControl,
    ToggleControl,
    RangeControl,
    SelectControl
} = wp.components;
const {
    InspectorControls,
    PanelColorSettings
} = wp.editor;
const { withSelect } = wp.data;

/**
 * Internal dependencies
 */
import { CssSize } from 'controls/css-size';
import { ImageSelect } from 'controls/image-select';
import { IconSelect } from 'controls/icon-select';
import { MediaUploadControl } from 'controls/media';
import { ArticlesList } from 'controls/articles-list';
import { TermsList } from 'controls/terms-select';
import { TermSingleSelect } from 'controls/term-single-select';
import { getTaxonomySelect } from 'data/resolvers';

/**
 * Control markup builder for blocks
 */
function inspectorControlsFactory(options, blockOptionsData, setAttributes) {
    return _.map(options, (value, key) => {
        const option = blockOptionsData[key];

        switch (option.type) {
            case 'input_text':
            case 'input':
                return (
                    <TextControl
                        label={ option.label }
                        value={ value }
                        help={ option.description }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'css_size':
                return (
                    <CssSize {...value}
                             label={ option.label }
                             help={ option.description }
                             onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'image_select':
                return (
                    <ImageSelect
                        value={value}
                        label={ option.label }
                        help={ option.description }
                        options={ option.options }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'icon_select':
                return (
                    <IconSelect
                        value={value}
                        label={ option.label }
                        help={ option.description }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'color':
                return (
                    <PanelColorSettings
                        title={ option.label }
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: value,
                                onChange: (newValue) => setAttributes({ [key]: newValue }),
                                label: option.label,
                                help: option.description
                            }
                        ]}
                    />
                );

            case 'checkbox':
                return (
                    <ToggleControl
                        label={ option.label }
                        help={ option.description }
                        checked={ value }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'select':
                return (
                    <SelectControl
                        label={ option.label }
                        value={ value }
                        help={ option.description }
                        options={_.map(option.options, (optionLabel, optionKey) => { return { value: optionKey, label: optionLabel } })}
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'media':
                return (
                    <MediaUploadControl
                        label={ option.label }
                        value={ value }
                        help={ option.description }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'range':
                return (
                    <RangeControl
                        label={ option.label }
                        value={ value }
                        help={ option.description }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                        min={ parseFloat(option.min) }
                        max={ parseFloat(option.max) }
                        step={ parseFloat(option.step) }
                    />
                );

            case 'articles_list':
                return (
                    <ArticlesList
                        label={ option.label }
                        value={ value }
                        help={ option.description }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'term_select':
                const TermSelectControl = withSelect(getTaxonomySelect(option.tax))(TermsList);

                return (
                    <TermSelectControl
                        optionId={ option.id }
                        label={ option.label }
                        value={ value }
                        help={ option.description }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            case 'term_single_select':
                const TermSingleSelectControl = withSelect(getTaxonomySelect(option.tax))(TermSingleSelect);

                return (
                    <TermSingleSelectControl
                        label={ option.label }
                        value={ value }
                        help={ option.description }
                        onChange={ (newValue) => setAttributes({ [key]: newValue }) }
                    />
                );

            default:
                return 'Unknown control type';
        }
    });
}

export const getInspectorControlsFactory = (id, setAttributes) => {
    return (options) => inspectorControlsFactory(options, window.MinervaKBBlocksInfo[id], setAttributes);
};