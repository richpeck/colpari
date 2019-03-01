/**
 * WordPress dependencies
 */
const {__} = wp.i18n;
const { Spinner } = wp.components;

/**
 * Internal dependencies
 */
import { ControlWrap } from "controls/common/control-wrap";

export const TermSingleSelect = function(props) {
    const { terms, value, onChange } = props;

    if (!terms) {
        return (
            <p>
                <Spinner />
                {__( 'Loading terms...', 'minervakb' )}
            </p>
        );
    }

    if (0 === terms.length ) {
        return <p>{__('No terms', 'minerva-kb')}</p>;
    }

    return (
        <ControlWrap {...props}>
            <div className="mkb-term-single-select">
                <select className="mkb-term-single-select-control" onChange={(e) => onChange(e.target.value)}>
                    {terms.map(term => <option value={term.id} selected={term.id === parseInt(value)}>{term.name}</option>)}
                </select>
            </div>
        </ControlWrap>
    )
};




