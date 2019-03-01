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
import { getOption, pluginUrl } from 'utils';

export const SearchPreview = (props) => {
    const {
        showSearchResultsPreview,
        title,
        titleSize,
        titleColor,
        theme,
        topPadding,
        bottomPadding,
        resultsMultiline,
        showTip,
        tip,
        tipColor,
        minWidth,
        borderColor,
        topicBg,
        topicColor,
        placeholder,
        imageBg,
        addGradient,
        gradientFrom,
        gradientTo,
        gradientOpacity,
        addPattern,
        pattern,
        patternOpacity,
        bg,
        iconsLeft,
        showSearchIcon,
        searchIcon,
        showTopic,
        clearIcon
    } = props;

    const bgImageStyle = imageBg.url ? `url(${imageBg.url})` : '';

    const containerStyle = {
        backgroundColor: bg,
        paddingTop: topPadding.size + topPadding.unit,
        paddingBottom: bottomPadding.size + bottomPadding.unit,
        backgroundImage: bgImageStyle,
        backgroundSize: 'cover',
        backgroundPosition: 'center center'
    };

    const gradientStyle = addGradient ? {
        background: `linear-gradient(45deg, ${gradientFrom} 0%, ${gradientTo} 100%)`,
        opacity: gradientOpacity
    } : {};

    const patternStyle = addPattern && pattern.url ? {
        background: `url(${pattern.url})`,
        opacity: patternOpacity
    } : {};

    const titleStyle = {
        fontSize: titleSize.size + titleSize.unit,
        color: titleColor
    };

    const wrapStyle = {
        borderColor: borderColor,
        backgroundColor: borderColor,
        minWidth: minWidth.size + minWidth.unit
    };

    const tipStyle = {
        color: tipColor
    };

    const previewResults = [
        { title: 'First result', topic: 'Topic 1', excerpt: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' },
        { title: 'Second result', topic: 'Topic 1', excerpt: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' },
        { title: 'Third result', topic: 'Topic 2', excerpt: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' },
    ];

    return (
        <div className="kb-header" style={containerStyle}>
            {addGradient && (
                <div className="kb-search-gradient" style={gradientStyle}/>
            )}
            {addPattern && (
                <div className="kb-search-pattern" style={patternStyle}/>
            )}
            <div className="kb-search">
                {title && (
                    <div className="kb-search__title" style={titleStyle}>
                        {title}
                    </div>
                )}
                <form className="kb-search__form" action="/" method="get">
                    <div className={classnames('kb-search__input-wrap', 'mkb-search-theme__' + theme, {
                            'kb-search__input-wrap--icons-left': iconsLeft,
                            'kb-search__input-wrap--multiline-results': resultsMultiline,
                            'kb-search__input-wrap--with-excerpt': getOption('live_search_show_excerpt'),
                            'kb-search__input-wrap--has-content kb-search__input-wrap--has-results': showSearchResultsPreview,
                        })}
                         style={wrapStyle}>
                        <input className="kb-search__input"
                           name="s"
                           placeholder={placeholder}
                           type="text"
                           value={showSearchResultsPreview ? 'search' : ''}
                        />
                        <span className="kb-search__results-summary">
                            <i className={classnames('kb-search-request-indicator fa fa-spin fa-fw', getOption('search_request_icon'))}/>
                            <span className="kb-summary-text-holder">{showSearchResultsPreview ? '3 results' : ''}</span>
                        </span>
                        {showSearchIcon && (
                            <span className="kb-search__icon-holder">
                                <i className={classnames('kb-search__icon fa', searchIcon)}/>
                            </span>
                        )}
                        <a href="#" className="kb-search__clear" title={getOption('search_clear_icon_tooltip')}>
                            <i className={classnames('kb-search__clear-icon fa', clearIcon)}/>
                        </a>
                        <div className={classnames('kb-search__results', { 'kb-search__results--with-topics': showTopic })}>
                            {showSearchResultsPreview ? (
                                <ul>
                                    {previewResults.map(preview => (
                                        <li>
                                            <a href="#">
                                                <span className="kb-search__result-header">
                                                    <span className="kb-search__result-title">{preview.title}</span>
                                                    {showTopic && (
                                                        <span className="kb-search__result-topic">
                                                            <span className="kb-search__result-topic-label">{getOption('search_result_topic_label')}</span>
                                                            <span className="kb-search__result-topic-name" style={{
                                                                backgroundColor: topicBg,
                                                                color: topicColor,
                                                            }}>{preview.topic}</span>
                                                        </span>
                                                    )}
                                                </span>
                                                {getOption('live_search_show_excerpt') && (
                                                    <span className="kb-search__result-excerpt">{preview.excerpt}</span>
                                                )}
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                        </div>
                    </div>
                    {showTip && (
                        <div className="kb-search__tip" style={tipStyle}>
                            {tip}
                        </div>
                    )}
                </form>
            </div>
        </div>
    );
};
