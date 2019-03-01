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
import { PreviewNote } from 'components/preview-note';

registerBlockType('minervakb/guestpost', {

    title: __('KB Guest Posting Form', 'minervakb'),

    description: __('Allows guests to submit articles from the client side', 'minervakb'),

    category: 'minervakb',

    icon: {
        foreground: '#009adf',
        src: 'editor-table',
    },

    keywords: [
        __('KB', 'minervakb'),
        __('Guest Post', 'minervakb'),
        __('Form', 'minervakb')
    ],

    supports: {
        html: false,
    },

    edit: props => {
        const {className, isSelected} = props;

        return (
            <div className={classnames('mkb-shortcode-container', className)}>
                {isSelected
                    ? <PreviewNote text={__('Guest Post block uses theme form styles and may look very different on the client side', 'minervakb')} />
                    : null
                }
                <div className="mkb-client-submission">
                    <div className="mkb-client-submission__heading">
                        {getOption('submit_form_heading_label')}
                    </div>
                    <div className="mkb-client-submission__subheading">
                        {getOption('submit_form_subheading_label')}
                    </div>
                    <form className="mkb-client-submission-form" action="" noValidate>
                        <div className="mkb-submission-title-wrap">
                            <div className="mkb-form-input-label">
                                {getOption('submit_article_title_label')}
                            </div>
                            <input type="text" name="mkb-submission-title"/>
                        </div>
                        <br/>
                        <div className="mkb-submission-content-wrap">
                            <div className="mkb-form-input-label">
                                {getOption('submit_content_label')}
                            </div>
                            <div id="mkb-client-editor">
                                <p>{getOption('submit_content_default_text')}</p>
                            </div>
                        </div>
                        <br/>
                        {getOption('submit_allow_topics_select') ? (
                            <div className="mkb-submission-topic-wrap">
                                <div className="mkb-form-input-label">
                                    {getOption('submit_topic_select_label')}
                                </div>
                                <select name="mkb-submission-topic">
                                    <option value="one">Topic 1</option>
                                    <option value="one">Topic 2</option>
                                    <option value="one">Topic 3</option>
                                </select>
                            </div>
                        ) : null}
                        <br/>
                        {getOption('antispam_quiz_enable') ? (
                            <p>{getOption('antispam_quiz_question')}
                                <input name="mkb-submission-answer" className="mkb-real-human-answer" type="text"/>
                            </p>
                        ) : null}
                        <div>
                            <span className="mkb-client-submission-send">
                                {getOption('submit_send_button_label')}
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        );
    },

    save: () => null
});
