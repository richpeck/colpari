/**
 * External dependencies
 */
import classnames from 'classnames';
const _ = window.lodash;

/**
 * WordPress dependencies
 */
const { Component } = wp.element;

/**
 * Internal dependencies
 */
import { ControlWrap } from "controls/common/control-wrap";

export class IconSelect extends Component {

    constructor(props) {
        super(props);

        this.state = { isOpen: false, filter: '' };
    }

    render() {
        const { value, onChange } = this.props;
        const { isOpen, filter } = this.state;

        const iconsList = window.MinervaKB.fontAwesomeIcons;
        const icons = filter ? _.pickBy(iconsList, (icon => icon.includes(filter))) : iconsList;

        return (
            <ControlWrap {...this.props}>
                <div className="mkb-icon-select-wrap">
                    <div className="mkb-icon-button">
                        <a href="#"
                           className="mkb-icon-button__link mkb-button mkb-unstyled-link"
                           onClick={() => this.setState({ isOpen: !isOpen })}
                        >
                            <i className={classnames('mkb-icon-button__icon fa fa-lg', value)}/>&nbsp;
                            <span className="mkb-icon-button__text">{value}</span>
                        </a>
                    </div>
                    <div className={classnames('mkb-icon-select-filter', { 'mkb-hidden': !isOpen })}>
                        <input placeholder="Type keyword to filter"
                               type="text"
                               value={filter}
                               onChange={(e) => this.setState({ filter: e.currentTarget.value.trim() })}
                        />
                    </div>
                    <div className={classnames('mkb-icon-select', { 'mkb-hidden': !isOpen })}>
                        {_.map(icons, (label, key) => {
                            return (
                                <span data-mkb-icon={key}
                                      className={classnames('mkb-icon-select__item', { 'mkb-icon-selected': key === value } )}
                                      onClick={e => onChange(e.currentTarget.dataset.mkbIcon)}
                                >
						        <i className={classnames('fa fa-lg', key)}/>
					        </span>
                            );
                        })}
                    </div>
                </div>
            </ControlWrap>
        );
    }
}
