/**
 * Internal dependencies
 */
import { getOption } from 'utils';

export const articlesSelect = (select) => {
    return {
        posts: select('core').getEntityRecords('postType', getOption('article_cpt'), { per_page: -1 })
    };
};

export const topicsSelect = (select) => {
    return {
        topics: select('core').getEntityRecords('taxonomy', getOption('article_cpt_category'), { per_page: -1 })
    };
};

export const getTaxonomySelect = (tax) => (select) => {
    return {
        terms: select('core').getEntityRecords('taxonomy', tax, { per_page: -1 })
    };
};