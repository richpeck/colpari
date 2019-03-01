/**
 * External dependencies
 */
import classnames from 'classnames';
const _ = window.lodash;

/**
 * WordPress dependencies
 */
const {
    Button,
    ToggleControl
} = wp.components;
const {
    MediaUpload,
    MediaUploadCheck,
    MediaPlaceholder
} = wp.editor;

/**
 * Internal dependencies
 */
import { ControlWrap } from "controls/common/control-wrap";

const ALLOWED_MEDIA_TYPES = [ 'image' ];
const IMAGE_BACKGROUND_TYPE = 'image';

export function MediaUploadControl (props) {
    const { value, options, onChange } = props;
    const { isUrl, img, url } = value;

    const onSelectMedia = ( media ) => {
        if (!media || !media.url) {
            onChange( { url: null, img: null, isUrl } );

            return;
        }

        if (media.media_type !== IMAGE_BACKGROUND_TYPE && media.type !== IMAGE_BACKGROUND_TYPE) {
            return;
        }

        onChange( { url: media.url, img: media.id, isUrl } );
    };

    const previewStyle = {
        display: url ? 'block' : 'none',
        backgroundImage: url ? `url(${url})` : ''
    };

    return (
        <ControlWrap  {...props}>
            <div className="mkb-media-upload">
                <ToggleControl
                    label={'Use URL?'}
                    checked={ isUrl }
                    onChange={(isUrl) => onChange({ img, url, isUrl })}
                />

                <div className="mkb-media-upload__preview" style={previewStyle}>
                    <a className="mkb-remove-media-img" href="#" onClick={() => onChange({ img: null, url: null, isUrl })}>
                        <i className="fa fa-lg fa-times-circle"/>
                    </a>
                </div>

                {isUrl ? (
                    <input type="text" onChange={(e) => onChange({ isUrl, img: e.currentTarget.value, url: e.currentTarget.value })} value={url || ''} />
                ) : (
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={ onSelectMedia }
                            allowedTypes={ ALLOWED_MEDIA_TYPES }
                            value={ img }
                            render={ ({ open }) => (
                                <Button
                                    onClick={ open }
                                    isLarge
                                >Select Media
                                </Button>
                            ) }
                        />
                    </MediaUploadCheck>
                )}
            </div>
        </ControlWrap>
    );
}