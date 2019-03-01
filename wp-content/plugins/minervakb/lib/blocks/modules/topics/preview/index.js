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
import { PreviewNote } from 'components/preview-note';

export const TopicsPreview = (props) => {
    const {
        title,
        titleSize,
        titleColor,
        view,
        columns,
        topics,
        showDescription,
        forceTopicColor,
        topicColor,
        showTopicIcons,
        forceTopicIcon,
        useTopicImage,
        imageSize,
        iconPaddingTop,
        iconPaddingBottom,
        boxItemBg,
        countBg,
        countColor,
        topicIcon,
        showCount,
        showAll,
        showAllLabel,
        showArticleIcons,
        articleIcon,
        isSelected
    } = props;

    const titleStyle = {
        fontSize: titleSize.size + titleSize.unit,
        color: titleColor
    };

    const topicTitleStyle = {
        color: forceTopicColor && topicColor || ''
    };

    const countStyle = {
        color: countColor,
        backgroundColor: countBg,
    };

    const iconHolderStyle = {
        paddingTop: iconPaddingTop.size + iconPaddingTop.unit,
        paddingBottom: iconPaddingBottom.size + iconPaddingBottom.unit,
    };

    const boxItemStyle = {
        backgroundColor: boxItemBg
    };

    const imageStyle = {
        width: imageSize.size + imageSize.unit,
    };

    const columnsMap = {
        '2col': 2,
        '3col': 3,
        '4col': 4,
    };

    const articlesPool = [
        { title: 'Lorem Ipsum is simply', likes: 1, views: 2 },
        { title: 'Dummy text of the printing', likes: 3, views: 4 },
        { title: 'Unknown printer took a galley', likes: 5, views: 6 },
        { title: 'A type specimen book', likes: 3, views: 5 },
        { title: 'It was popularised', likes: 2, views: 3 },
    ];

    const topicsPool = [
        { title: 'Topic 1', description: 'This is a description', articles: _.take(articlesPool, 5) },
        { title: 'Topic 2', description: 'This is a description', articles: _.take(articlesPool, 5) },
        { title: 'Topic 3', description: 'This is a description', articles: _.take(articlesPool, 5) },
        { title: 'Topic 4', description: 'This is a description', articles: _.take(articlesPool, 5) }
    ];

    const previewTopics = _.take(topicsPool, columnsMap[columns]);

    return (
        <div>
            {isSelected
                ? <PreviewNote text={__('This block uses preview topics instead of actual KB content', 'minervakb')} />
                : null
            }
            {title && (
                <div className="mkb-container mkb-section-title" style={titleStyle}>
                    {title}
                </div>
            )}
            <div className={classnames('mkb-home-topics mkb-container mkb-columns', 'mkb-columns-' + columns)}>
                <div className="mkb-row">
                    {view === 'list' ? (
                        (previewTopics.map(({ title, articles }) => {
                            return (
                                <div className="kb-topic">
                                    <div className="kb-topic__inner">
                                        <h3 className="kb-topic__title" style={topicTitleStyle}>
                                            <a className="kb-topic__title-link" href="#" style={topicTitleStyle}>
                                                {showTopicIcons && (
                                                    <span className="kb-topic__title-icon">
                                                        <i className={classnames('kb-topic__list-icon fa', topicIcon)} />&nbsp;
                                                    </span>
                                                )}
                                                {title}
                                                {showCount && (
                                                    <span className="kb-topic__count" style={countStyle}>
                                                        {articles.length} {getOption('articles_text')}
                                                    </span>
                                                )}
                                            </a>
                                        </h3>

                                        <div className={classnames('kb-topic__articles', { 'kb-topic__articles--with-icons': showArticleIcons })}>
                                            <ul>
                                                {articles.map(({ title, likes, views }) => {
                                                    return (
                                                        <li>
                                                            <a href="#">
                                                                {showArticleIcons && (
                                                                    <i className={classnames('kb-topic__list-article-icon fa', articleIcon)} />
                                                                )}
                                                                <span className="kb-topic__list-article-title">{title}</span>
                                                                {getOption('show_article_views') && (
                                                                    <span className="kb-topic__list-article-views">
                                                                        <i className="fa fa-eye kb-topic__list-article-meta-icon" />{likes}
                                                                    </span>
                                                                )}
                                                                {getOption('show_article_likes') && (
                                                                    <span className="kb-topic__list-article-likes">
                                                                        <i className="fa fa-heart-o kb-topic__list-article-meta-icon" />{views}
                                                                    </span>
                                                                )}
                                                            </a>
                                                        </li>
                                                    );
                                                })}
                                            </ul>
                                            {showAll && (
                                                <a className="kb-topic__show-all" href="#">
                                                    {showAllLabel}
                                                </a>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            );
                        }))

                    ) : (previewTopics.map(({ title, description, articles }) => {
                            return (
                                <div className="kb-topic kb-topic--box-view">
                                    <a href="#">
                                        <div className="kb-topic__inner" style={boxItemStyle}>
                                            <div className="kb-topic__box-header" style={topicTitleStyle}>
                                                {showTopicIcons && (
                                                    <div className="kb-topic__icon-holder" style={iconHolderStyle}>
                                                        {useTopicImage ? (
                                                            <img className="kb-topic__icon-image"
                                                                 style={imageStyle}
                                                                 src={pluginUrl('assets/img/topics-preview-image.png')}/>
                                                        ) : (
                                                            <i className={classnames('kb-topic__box-icon fa', topicIcon)} />
                                                        )}
                                                    </div>
                                                )}
                                                <h3 className="kb-topic__title" style={topicTitleStyle}>
                                                    {title}
                                                </h3>
                                            </div>

                                            <div className="kb-topic__articles">
                                                {showDescription && (
                                                    <div className="kb-topic__description">{description}</div>
                                                )}
                                                {showCount && (
                                                    <div className="kb-topic__box-count">
                                                        {articles.length} {getOption('articles_text')}
                                                    </div>
                                                )}
                                                {showAll && (
                                                    <a className="kb-topic__show-all" href="#">
                                                        {showAllLabel}
                                                    </a>
                                                )}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        </div>
    );
};
