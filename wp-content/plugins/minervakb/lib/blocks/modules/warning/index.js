/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText } = wp.editor;

/**
 * Internal dependencies
 */
import { getOption } from 'utils';

registerBlockType('minervakb/warning', {

    title: __('KB Warning', 'minervakb'),

    description: __('Highlight warning points in your text', 'minervakb'),

    category: 'minervakb',

    icon: {
        foreground: '#a94442',
        src: 'warning',
    },

    keywords: [
        __('KB', 'minervakb'),
        __('Warning', 'minervakb'),
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

        const iconClasses = classnames('fa fa-lg', getOption('warning_icon'));

        return (
            <div className={classnames('mkb-shortcode-container', className)}>
                <div className="mkb-warning">
                    <div className="mkb-warning__icon">
                        <i className={iconClasses} />
                    </div>
                    <div className="mkb-warning__content">
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
