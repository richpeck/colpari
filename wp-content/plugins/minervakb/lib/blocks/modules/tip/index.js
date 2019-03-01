/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const {__} = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText } = wp.editor;

/**
 * Internal dependencies
 */
import { getOption } from 'utils';

registerBlockType('minervakb/tip', {

    title: __('KB Tip', 'minervakb'),

    description: __('Highlight useful points in your text', 'minervakb'),

    category: 'minervakb',

    icon: {
        foreground: '#8a6d3b',
        src: 'lightbulb',
    },

    keywords: [
        __('KB', 'minervakb'),
        __('Tip', 'minervakb'),
        __('Highlight', 'minervakb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const {attributes: { message }, className, setAttributes, isSelected} = props;

        const onChangeMessage = message => {
            setAttributes( { message } )
        };

        const iconClasses = classnames('fa fa-lg', getOption('tip_icon'));

        return (
            <div className={classnames('mkb-shortcode-container', className)}>
                <div className="mkb-tip">
                    <div className="mkb-tip__icon">
                        <i className={iconClasses}/>
                    </div>
                    <div className="mkb-tip__content">
                        <RichText
                            tagName="div"
                            format="string"
                            multiline="br"
                            placeholder={ __( 'Add your custom message', 'minervakb' ) }
                            onChange={ onChangeMessage }
                            value={ message }
                        />
                    </div>
                </div>
            </div>
        );
    },

    save: () => null
});
