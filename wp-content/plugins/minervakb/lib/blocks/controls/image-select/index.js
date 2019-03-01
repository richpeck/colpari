/**
 * External dependencies
 */
import classnames from 'classnames';
const _ = window.lodash;

/**
 * Internal dependencies
 */
import { ControlWrap } from "controls/common/control-wrap";

export function ImageSelect (props) {
    const { value, options, onChange } = props;

    return (
        <ControlWrap  {...props}>
            <div className="mkb-image-select">
                <ul>
                    {_.map(options, ({ label, img }, key) => {
                        return (
                            <li className={classnames('mkb-image-select__item', {'mkb-image-selected': key === value })}
                                data-value={key}
                                onClick={e => onChange(e.currentTarget.dataset.value)}>
                                <span className="mkb-image-wrap">
                                    <img src={img} className="mkb-image-select__image"/>
                                    <span className="mkb-image-selected__checkmark">
                                        <i className="fa fa-lg fa-check-circle" />
                                    </span>
                                </span>
                                <span className="mkb-image-select__item-label">
                                    {label}
                                </span>
                            </li>
                        );
                    })}
                </ul>
            </div>
        </ControlWrap>
    );
}