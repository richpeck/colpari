export const ControlWrap = ({ children, label, help }) => (
    <div className="components-base-control">
        <label className="components-base-control__label">{label}</label>
        {children}
        {help && (
            <p className="components-base-control__help">{help}</p>
        )}
    </div>
);
