/**
 * WordPress dependencies
 */
const {__} = wp.i18n;

export const PreviewNote = ({text}) => (
    <div className="mkb-editor-preview-note">
        <strong>{__('Preview note:', 'minervakb')}</strong> {text}
    </div>
);




