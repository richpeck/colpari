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
import { ControlWrap } from "controls/common/control-wrap";
import { getOption } from 'utils';
import { articlesSelect } from 'data/resolvers';
import Sortable from 'controls/common/sortable';

export const ArticlesList = withSelect(articlesSelect)(function(props) {
    const { posts, value, onChange } = props;

    const selected = value.split(',').filter(Boolean).map(Number);

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

    return (
        <ControlWrap {...props}>
            <div className="mkb-related-articles">
                {selected.length ? (
                    <Sortable onChange={(reorderedItems) => onChange(reorderedItems.join(','))}>
                        {selected.map((articleId, index) => (
                            <div className="mkb-related-articles__item" key={_.uniqueId()} data-id={articleId}>
                                <select className="mkb-related-articles__select" onChange={(e) => {
                                    selected[index] = e.currentTarget.value;
                                    onChange(selected.join(','));
                                }}>
                                    {posts.map(post => (
                                        <option value={post.id} selected={post.id === articleId}>{post.title.rendered}</option>
                                    ))}
                                </select>
                                <a className="mkb-related-articles__item-remove mkb-unstyled-link" onClick={() => {
                                    _.pullAt(selected, [index]);
                                    onChange(selected.join(','));
                                }}>
                                    <i className="fa fa-close"/>
                                </a>
                            </div>
                        ))}
                    </Sortable>
                ) : (
                    <div className="mkb-no-related-message">
                        <p>{__('No related articles selected', 'minerva-kb')}</p>
                    </div>
                )}
            </div>
            <div className="mkb-related-actions">
                <a className="button button-primary button-large"
                   onClick={() => onChange([...selected, posts[0].id].join(','))}
                   title={__('Add article', 'minerva-kb')}>
                    {__('Add article', 'minerva-kb')}
                </a>
            </div>
        </ControlWrap>
    )
});




