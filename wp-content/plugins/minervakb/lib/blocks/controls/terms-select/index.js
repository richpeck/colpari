/**
 * External dependencies
 */
import classnames from 'classnames';
const _ = window.lodash;

/**
 * WordPress dependencies
 */
const {__} = wp.i18n;
const { Spinner } = wp.components;
const { Component } = wp.element;

/**
 * Internal dependencies
 */
import { ControlWrap } from "controls/common/control-wrap";
import { getOption } from 'utils';
import Sortable from 'controls/common/sortable';

const getTermsTree = (termsList = []) => {
    let terms = termsList.map(term => Object.assign({}, term)); // just a copy to prevent original array mutation

    if (!terms.length) {
        return terms;
    }

    const rootTerms = terms.filter(term => term.parent === 0);

    terms.forEach((term, index, allTerms) => term.parent === 0 && delete allTerms[index]);

    if (!rootTerms.length) { // some broken structure, exit
        return terms;
    }

    const findParentAndMove = (childTerm, index, termsArray) => {
        rootTerms.some(function termsWalker(term) {
            if (childTerm.parent === term.id) {
                term.children = term.children || [];
                term.children.push(childTerm);

                delete termsArray[index];
                return true;
            } else if (term.children && term.children.length) {
                return term.children.some(termsWalker);
            }
        });
    };

    while((terms = terms.filter(Boolean)).length) {
        terms.forEach(findParentAndMove);
    }

    return rootTerms;
};

const Terms = (props) => {
    const { children, selected, onChange } = props;

    return (
        <ul>
            {children.map(term => {
                const isSelected = selected.includes(term.id);

                return (
                    <li key={term.id}>
                        <span onClick={(e) => {
                            if (isSelected) {
                                _.pull(selected, term.id);
                            } else {
                                selected.push(term.id);
                            }

                            onChange(selected.join(','));
                        }}>
                            <i className="fa fa-folder"/>
                            {term.name}{term.count ? ` (${term.count})` : ''}
                            <input id={'term_select_' + term.id}
                                   type="checkbox"
                                   checked={isSelected || null}
                            />
                            <label htmlFor={'term_select_' + term.id}/>
                        </span>

                        {term.children && <Terms {...props} children={term.children}/>}
                    </li>
                );
            })}
        </ul>
    )
};

const getTermPath = (term, allTerms) => {
    let parents = [];
    let nextParent = term;
    let limit = 0;

    while (nextParent.parent && ++limit < 20) {
        nextParent = _.find(allTerms, { id: nextParent.parent });
        parents.push(nextParent.name);
    }

    return parents ? parents.join(' / ') : '';
};

const TABS = {
    SELECT: 'SELECT',
    REORDER: 'REORDER'
};

let tabsCachedState = {};

export class TermsList extends Component {

    constructor() {
        super( ...arguments );

        this.state = {
            selectedTab: tabsCachedState[this.props.optionId] || TABS.SELECT
        };
    }

    render() {
        const { terms, value, onChange, optionId } = this.props;

        if (!terms) {
            return (
                <div className="mkb-terms-selected">
                    <p>
                        <Spinner />
                        {__( 'Loading terms...', 'minervakb' )}
                    </p>
                </div>
            );
        }

        if (0 === terms.length ) {
            return (
                <div className="mkb-terms-selected">
                    <p>{__('No terms', 'minerva-kb')}</p>
                </div>
            );
        }

        const selected = value.split(',').filter(Boolean).map(Number);
        const termsTree = getTermsTree(terms);

        const tabs = [
            { label: __( 'Select', 'minervakb' ), id: TABS.SELECT },
            { label: __( 'Reorder', 'minervakb' ), id: TABS.REORDER },
        ];

        return (
            <ControlWrap {...this.props}>
                <div className="mkb-term-select-tabs">
                    {tabs.map(tab => (
                        <span className={classnames('mkb-term-select-tab', {'mkb-term-select-tab--active': this.state.selectedTab === tab.id})}
                              onClick={() => {
                                  this.setState({ selectedTab: tab.id });
                                  tabsCachedState[optionId] = tab.id;
                              }}>
                            {tab.label}
                        </span>
                    ))}
                </div>
                {this.state.selectedTab === TABS.SELECT ? (
                    <div className="mkb-terms-tree">
                        <Terms onChange={onChange} selected={selected} children={termsTree} />
                    </div>
                ) : (
                    <div className="mkb-terms-selected">
                        <Sortable tag="ul" onChange={(reorderedItems) => {
                            onChange(reorderedItems.join(','));
                        }}>
                            {selected.map(termId => {
                                const term = _.find(terms, { id: termId });

                                return (
                                    <li key={_.uniqueId()} data-id={termId}>
                                        <span>{getTermPath(term, terms)}</span>
                                        {term.name}
                                    </li>
                                )
                            })}
                        </Sortable>
                    </div>
                )}
            </ControlWrap>
        )
    }
}
