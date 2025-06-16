import { registerBlockType } from '@wordpress/blocks';
import { 
    useBlockProps, 
    MediaUpload, 
    MediaUploadCheck,
    InspectorControls,
    RichText,
    BlockControls
} from '@wordpress/block-editor';
import { 
    PanelBody, 
    SelectControl, 
    ToggleControl,
    TextControl,
    Button,
    ToolbarGroup,
    ToolbarButton,
    Placeholder
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { image as imageIcon, link, linkOff } from '@wordpress/icons';

const aspectRatioOptions = [
    { label: 'Original', value: 'original' },
    { label: 'Square (1:1)', value: '1-1' },
    { label: 'Landscape (16:9)', value: '16-9' },
    { label: 'Portrait (9:16)', value: '9-16' },
    { label: 'Wide (21:9)', value: '21-9' },
    { label: 'Classic (4:3)', value: '4-3' },
    { label: 'Tall (3:4)', value: '3-4' }
];

const objectFitOptions = [
    { label: 'Cover', value: 'cover' },
    { label: 'Contain', value: 'contain' },
    { label: 'Fill', value: 'fill' },
    { label: 'None', value: 'none' }
];

const imageEffectOptions = [
    { label: 'None', value: 'none' },
    { label: 'Grayscale', value: 'grayscale' },
    { label: 'Sepia', value: 'sepia' },
    { label: 'Blur', value: 'blur' },
    { label: 'Brightness', value: 'brightness' },
    { label: 'Contrast', value: 'contrast' }
];

const hoverEffectOptions = [
    { label: 'None', value: 'none' },
    { label: 'Zoom In', value: 'zoom-in' },
    { label: 'Zoom Out', value: 'zoom-out' },
    { label: 'Rotate', value: 'rotate' },
    { label: 'Blur to Focus', value: 'blur-focus' },
    { label: 'Color to Grayscale', value: 'color-grayscale' }
];

const borderRadiusOptions = [
    { label: 'None', value: 'none' },
    { label: 'Small', value: 'small' },
    { label: 'Medium', value: 'medium' },
    { label: 'Large', value: 'large' },
    { label: 'Round', value: 'round' }
];

const captionPositionOptions = [
    { label: 'Below Image', value: 'below' },
    { label: 'Overlay Bottom', value: 'overlay-bottom' },
    { label: 'Overlay Top', value: 'overlay-top' },
    { label: 'Overlay Center', value: 'overlay-center' }
];

const captionStyleOptions = [
    { label: 'Default', value: 'default' },
    { label: 'Dark Background', value: 'dark-bg' },
    { label: 'Light Background', value: 'light-bg' },
    { label: 'Gradient', value: 'gradient' }
];

const linkToOptions = [
    { label: 'None', value: 'none' },
    { label: 'Media File', value: 'media' },
    { label: 'Custom URL', value: 'custom' }
];

registerBlockType('studio/image', {
    edit: function Edit({ attributes, setAttributes }) {
        const {
            url,
            id,
            alt,
            caption,
            aspectRatio,
            objectFit,
            imageEffect,
            hoverEffect,
            borderRadius,
            captionPosition,
            captionStyle,
            linkTo,
            href,
            linkTarget,
            rel,
            lightbox
        } = attributes;

        const blockProps = useBlockProps({
            className: `studio-image studio-image--aspect-${aspectRatio} studio-image--radius-${borderRadius} studio-image--effect-${imageEffect} studio-image--hover-${hoverEffect}`
        });

        const onSelectImage = (media) => {
            setAttributes({
                url: media.url,
                id: media.id,
                alt: media.alt
            });
        };

        const onRemoveImage = () => {
            setAttributes({
                url: undefined,
                id: undefined,
                alt: ''
            });
        };

        const ImageWrapper = ({ children }) => {
            if (linkTo === 'none' || !url) {
                return children;
            }

            const linkUrl = linkTo === 'media' ? url : href;
            
            return (
                <a 
                    href={linkUrl}
                    target={linkTarget}
                    rel={rel}
                    onClick={(e) => e.preventDefault()}
                >
                    {children}
                </a>
            );
        };

        return (
            <>
                <BlockControls>
                    <ToolbarGroup>
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={onSelectImage}
                                allowedTypes={['image']}
                                value={id}
                                render={({ open }) => (
                                    <ToolbarButton
                                        icon={imageIcon}
                                        label={__('Replace Image', 'studio')}
                                        onClick={open}
                                        disabled={!url}
                                    />
                                )}
                            />
                        </MediaUploadCheck>
                        <ToolbarButton
                            icon={linkTo !== 'none' ? link : linkOff}
                            label={__('Link', 'studio')}
                            onClick={() => {
                                setAttributes({
                                    linkTo: linkTo === 'none' ? 'custom' : 'none'
                                });
                            }}
                            isActive={linkTo !== 'none'}
                        />
                    </ToolbarGroup>
                </BlockControls>

                <InspectorControls>
                    <PanelBody title={__('Image Settings', 'studio')}>
                        <TextControl
                            label={__('Alt Text', 'studio')}
                            value={alt}
                            onChange={(value) => setAttributes({ alt: value })}
                            help={__('Describe the image for screen readers', 'studio')}
                        />
                        <SelectControl
                            label={__('Aspect Ratio', 'studio')}
                            value={aspectRatio}
                            options={aspectRatioOptions}
                            onChange={(value) => setAttributes({ aspectRatio: value })}
                        />
                        {aspectRatio !== 'original' && (
                            <SelectControl
                                label={__('Object Fit', 'studio')}
                                value={objectFit}
                                options={objectFitOptions}
                                onChange={(value) => setAttributes({ objectFit: value })}
                                help={__('How the image should fit within the aspect ratio', 'studio')}
                            />
                        )}
                        <SelectControl
                            label={__('Border Radius', 'studio')}
                            value={borderRadius}
                            options={borderRadiusOptions}
                            onChange={(value) => setAttributes({ borderRadius: value })}
                        />
                    </PanelBody>

                    <PanelBody title={__('Effects', 'studio')} initialOpen={false}>
                        <SelectControl
                            label={__('Image Effect', 'studio')}
                            value={imageEffect}
                            options={imageEffectOptions}
                            onChange={(value) => setAttributes({ imageEffect: value })}
                        />
                        <SelectControl
                            label={__('Hover Effect', 'studio')}
                            value={hoverEffect}
                            options={hoverEffectOptions}
                            onChange={(value) => setAttributes({ hoverEffect: value })}
                        />
                    </PanelBody>

                    <PanelBody title={__('Caption', 'studio')} initialOpen={false}>
                        <SelectControl
                            label={__('Caption Position', 'studio')}
                            value={captionPosition}
                            options={captionPositionOptions}
                            onChange={(value) => setAttributes({ captionPosition: value })}
                        />
                        {captionPosition.includes('overlay') && (
                            <SelectControl
                                label={__('Caption Style', 'studio')}
                                value={captionStyle}
                                options={captionStyleOptions}
                                onChange={(value) => setAttributes({ captionStyle: value })}
                            />
                        )}
                    </PanelBody>

                    <PanelBody title={__('Link Settings', 'studio')} initialOpen={false}>
                        <SelectControl
                            label={__('Link To', 'studio')}
                            value={linkTo}
                            options={linkToOptions}
                            onChange={(value) => setAttributes({ linkTo: value })}
                        />
                        {linkTo === 'custom' && (
                            <>
                                <TextControl
                                    label={__('URL', 'studio')}
                                    value={href}
                                    onChange={(value) => setAttributes({ href: value })}
                                />
                                <ToggleControl
                                    label={__('Open in new tab', 'studio')}
                                    checked={linkTarget === '_blank'}
                                    onChange={(value) => {
                                        setAttributes({
                                            linkTarget: value ? '_blank' : '',
                                            rel: value ? 'noopener' : ''
                                        });
                                    }}
                                />
                            </>
                        )}
                        {linkTo === 'media' && (
                            <ToggleControl
                                label={__('Enable Lightbox', 'studio')}
                                checked={lightbox}
                                onChange={(value) => setAttributes({ lightbox: value })}
                            />
                        )}
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    {!url ? (
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={onSelectImage}
                                allowedTypes={['image']}
                                value={id}
                                render={({ open }) => (
                                    <Placeholder
                                        icon={imageIcon}
                                        label={__('Studio Image', 'studio')}
                                        instructions={__('Upload an image or pick one from your media library.', 'studio')}
                                    >
                                        <Button variant="primary" onClick={open}>
                                            {__('Media Library', 'studio')}
                                        </Button>
                                    </Placeholder>
                                )}
                            />
                        </MediaUploadCheck>
                    ) : (
                        <figure className={`studio-image__figure studio-image__caption-${captionPosition} studio-image__caption-style-${captionStyle}`}>
                            <ImageWrapper>
                                <div className="studio-image__wrapper">
                                    <img 
                                        src={url} 
                                        alt={alt}
                                        className="studio-image__img"
                                        style={{
                                            objectFit: aspectRatio !== 'original' ? objectFit : undefined
                                        }}
                                    />
                                </div>
                            </ImageWrapper>
                            <RichText
                                tagName="figcaption"
                                className="studio-image__caption"
                                value={caption}
                                onChange={(value) => setAttributes({ caption: value })}
                                placeholder={__('Write caption...', 'studio')}
                                allowedFormats={['core/bold', 'core/italic', 'core/link']}
                            />
                        </figure>
                    )}
                </div>
            </>
        );
    },

    save: function Save({ attributes }) {
        const {
            url,
            alt,
            caption,
            aspectRatio,
            objectFit,
            imageEffect,
            hoverEffect,
            borderRadius,
            captionPosition,
            captionStyle,
            linkTo,
            href,
            linkTarget,
            rel,
            lightbox
        } = attributes;

        const blockProps = useBlockProps.save({
            className: `studio-image studio-image--aspect-${aspectRatio} studio-image--radius-${borderRadius} studio-image--effect-${imageEffect} studio-image--hover-${hoverEffect}`
        });

        if (!url) {
            return null;
        }

        const image = (
            <div className="studio-image__wrapper">
                <img 
                    src={url} 
                    alt={alt}
                    className="studio-image__img"
                    style={{
                        objectFit: aspectRatio !== 'original' ? objectFit : undefined
                    }}
                />
            </div>
        );

        const ImageWrapper = ({ children }) => {
            if (linkTo === 'none') {
                return children;
            }

            const linkUrl = linkTo === 'media' ? url : href;
            const linkClass = lightbox ? 'studio-image__link studio-image__link--lightbox' : 'studio-image__link';
            
            return (
                <a 
                    href={linkUrl}
                    target={linkTarget}
                    rel={rel}
                    className={linkClass}
                    data-lightbox={lightbox ? 'true' : undefined}
                >
                    {children}
                </a>
            );
        };

        return (
            <div {...blockProps}>
                <figure className={`studio-image__figure studio-image__caption-${captionPosition} studio-image__caption-style-${captionStyle}`}>
                    <ImageWrapper>
                        {image}
                    </ImageWrapper>
                    {caption && (
                        <RichText.Content
                            tagName="figcaption"
                            className="studio-image__caption"
                            value={caption}
                        />
                    )}
                </figure>
            </div>
        );
    }
});
