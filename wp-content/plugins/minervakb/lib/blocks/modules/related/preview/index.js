/**
 * External dependencies
 */
const _ = window.lodash;

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

const articlesSelect = (select) => {
    return {
        posts: select('core').getEntityRecords('postType', getOption('article_cpt'), { per_page: -1 })
    };
};

export const RelatedPreview = withSelect(articlesSelect)(({ posts, ids}) => {
    let selected = ids.split(',').filter(Boolean).map(Number);

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

    selected = selected.map(articleId => _.find(posts, { id: articleId })).filter(Boolean);

    if (0 === selected.length ) {
        return <p>{__('No articles selected', 'minerva-kb')}</p>;
    }

    return selected.length && (
        <div className="mkb-related-content">
            <div className="mkb-related-content-title">{getOption('related_content_label')}</div>
            <ul className="mkb-related-content-list">
                {selected.map(article => <li><a>{article.title.rendered}</a></li>)}
            </ul>
        </div>
    );
});