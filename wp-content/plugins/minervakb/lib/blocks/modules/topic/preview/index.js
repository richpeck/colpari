/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Spinner } = wp.components;
const { withSelect } = wp.data;

/**
 * Internal dependencies
 */
import { getOption, pluginUrl } from 'utils';
import { articlesSelect } from 'data/resolvers';

export const TopicPreview = withSelect(articlesSelect)((props) => {
    const {
        id,
        view,
        columns,
        limit,
        posts,
        isSelected
    } = props;

    if (!posts) {
        return (
            <p>
                <Spinner />
                {__( 'Loading Articles...', 'minervakb' )}
            </p>
        );
    }

    if (0 === posts.length ) {
        return <p>{__('No articles', 'minerva-kb')}</p>;
    }

    const articlesLimit = limit > 0 ? limit : 10;

    return (
        <div>
            {_.take(posts, articlesLimit).map(post => (
                <div className="mkb-article-item mkb-article-item--simple">
                    <div className="mkb-entry-header">
                        <h2 className="mkb-entry-title">
                            <i className={classnames('mkb-article-icon fa fa-lg', getOption('article_icon'))} />
                            <a href="#" rel="bookmark">{post.title.rendered}</a>
                        </h2>
                    </div>
                </div>
            ))}
        </div>
    );
});