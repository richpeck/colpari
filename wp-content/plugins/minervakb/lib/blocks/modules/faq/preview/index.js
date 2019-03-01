/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import { getOption } from 'utils';

export const FaqPreview = (props) => {
    const { title, titleSize, titleColor, marginTop, marginBottom, limitWidth, width, controlsMarginTop, controlsMarginBottom, showFilter, showToggleAll, showCategories, showCount } = props;
    const questions = [
        {
            title: 'First question',
            content: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            isOpen: true
        },
        {
            title: 'Second question',
            content: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.'
        },
        {
            title: 'Third question',
            content: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.'
        }
    ];

    const faqStyle = {
        marginTop: marginTop.size + marginTop.unit,
        marginBottom: marginBottom.size + marginBottom.unit,
        width: limitWidth ? width.size + width.unit : 'auto'
    };
    const titleStyle = {
        fontSize: titleSize.size + titleSize.unit,
        color: titleColor
    };
    const controlsStyle = {
        marginTop: controlsMarginTop.size + controlsMarginTop.unit,
        marginBottom: controlsMarginBottom.size + controlsMarginBottom.unit,
    };

    return (
        <div className="mkb-home-faq kb-faq mkb-container" style={faqStyle}>
            {title && (
                <div className="mkb-section-title">
                    <h3 style={titleStyle}>{title}</h3>
                </div>
            )}
            <div className="kb-faq__controls mkb-clearfix" style={controlsStyle}>
                {showFilter && (
                    <div className={classnames('kb-faq__filter kb-faq__filter--empty', `kb-faq__filter--${getOption('faq_filter_theme')}-theme`)}>
                        <form className="kb-faq__filter-form" action="" noValidate>
                            <input type="text" className="kb-faq__filter-input" placeholder={getOption('faq_filter_placeholder')} />
                            <a href="#" className="kb-faq__filter-clear">
                                <i className={classnames('fa', getOption('faq_filter_clear_icon'))} />
                            </a>
                            {getOption('show_faq_filter_icon') && (
                                <span className="kb-faq__filter-icon">
                                    <i className={classnames('fa', getOption('faq_filter_icon'))} />
                                </span>
                            )}
                        </form>
                    </div>
                )}
                {showToggleAll && (
                    <div className="kb-faq__toggle-all">
                        <a href="#" className="kb-faq__toggle-all-link">
                            <span className="kb-faq__toggle-all-label">
                                {getOption('show_faq_toggle_all_icon') && (
                                    <i className={classnames('kb-faq__toggle-all-icon fa ', getOption('faq_toggle_all_icon'))} />
                                )}
                                <span className="kb-faq__toggle-all-text">
                                    {getOption('faq_toggle_all_open_text')}
                                </span>
                            </span>
                            <span className="kb-faq__toggle-all-label-open">
                                {getOption('show_faq_toggle_all_icon') && (
                                    <i className={classnames('kb-faq__toggle-all-icon fa ', getOption('faq_toggle_all_icon_open'))} />
                                )}
                                <span className="kb-faq__toggle-all-text">
                                    {getOption('faq_toggle_all_close_text')}
                                </span>
                            </span>
                        </a>
                    </div>
                )}
            </div>
            <div className="kb-faq__category">
                <div className="kb-faq__category-inner">
                    {showCategories && (
                        <div className="kb-faq__category-title">
                            {__('Preview category', 'minerva-kb')}
                            {showCount && (
                                <span className="kb-faq__count">{`${questions.length} ${getOption('questions_text')}`}</span>
                            )}
                        </div>
                    )}
                    <div className="kb-faq__questions">
                        <ul className={classnames('kb-faq__questions-list', {'kb-faq__questions-list--with-shadow': getOption('faq_question_shadow')})}>
                            {questions.map(({ title, content, isOpen = false }) => (
                                <li className={classnames('kb-faq__questions-list-item', {'kb-faq__questions-list-item--open': isOpen})}>
                                    <a href="#">
                                        <span className="kb-faq__question-title">
                                            {getOption('show_faq_question_icon') && (
                                                <i className={classnames('kb-faq__question-toggle-icon fa ', getOption('faq_question_icon'))} />
                                            )}
                                            {title}
                                        </span>
                                    </a>
                                    <div className="kb-faq__answer" style={{maxHeight: isOpen ? '9999px' : '0'}}>
                                        <div className="kb-faq__answer-content">{content}</div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    );
};
