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

registerBlockType('minervakb/info', {

    title: __('KB Info', 'minervakb'),

    description: __('Highlight interesting points in your text', 'minervakb'),

    category: 'minervakb',

    icon: {
        foreground: '#31708f',
        src: 'info',
    },

    keywords: [
        __('KB', 'minervakb'),
        __('Info', 'minervakb'),
        __('Highlight', 'minervakb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const {attributes: { message }, className, setAttributes} = props;

        const onChangeMessage = message => {
            setAttributes( { message } )
        };

        const iconClasses = classnames('fa fa-lg', getOption('info_icon'));

        return (
            <div className={classnames('mkb-shortcode-container', className)}>
                <div className="mkb-info">
                    <div className="mkb-info__icon">
                        <i className={iconClasses}/>
                    </div>
                    <div className="mkb-info__content">
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
