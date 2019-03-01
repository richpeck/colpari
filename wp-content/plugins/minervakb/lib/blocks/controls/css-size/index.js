/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import { ControlWrap } from "controls/common/control-wrap";

export function CssSize (props) {
    const { size, unit, onChange } = props;
    const units = ['px', 'rem', 'em', '%'];

    const onChangeUnit = (e) => {
        onChange({
            size,
            unit: e.currentTarget.dataset.unit
        });
    };

    const onChangeSize = (e) => {
        onChange({
            size: e.currentTarget.value.trim(),
            unit
        });
    };

    return (
        <ControlWrap {...props}>
            <div className="mkb-css-size">
                <input className="mkb-css-size__input"
                       type="text"
                       value={size}
                       onChange={onChangeSize}
                />
                <ul className="mkb-css-size__units">
                    {units.map(unitItem => {
                        const unitClasses = classnames('mkb-unstyled-link', 'mkb-css-unit', {
                            'mkb-css-unit--selected': unitItem === unit
                        });

                        return (
                            <li><a href="#" className={unitClasses} data-unit={unitItem} onClick={onChangeUnit}>{unitItem}</a></li>
                        );
                    })}
                </ul>
            </div>
        </ControlWrap>
    );
}