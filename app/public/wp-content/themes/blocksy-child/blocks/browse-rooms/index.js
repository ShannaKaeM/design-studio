import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Helper function to generate unique IDs
function generateUniqueId() {
    return 'gb-' + Math.random().toString(36).substr(2, 9);
}

registerBlockType('blocksy-child/browse-rooms', {
    edit: function(props) {
        const { attributes, setAttributes } = props;
        const {
            title = 'Browse by rooms',
            description = 'Discover our curated collection of furniture organized by room type.',
            rooms = [
                { name: 'Living Room', count: '15', image: 'https://via.placeholder.com/600x800/8FBC8F/FFFFFF?text=Living+Room', class: 'living' },
                { name: 'Bedroom', count: '12', image: 'https://via.placeholder.com/400x300/DEB887/FFFFFF?text=Bedroom', class: 'bedroom' },
                { name: 'Walk-in Closet', count: '8', image: 'https://via.placeholder.com/400x300/DEB887/FFFFFF?text=Walk-in+Closet', class: 'closet' },
                { name: 'Kitchen', count: '20', image: 'https://via.placeholder.com/400x600/8FBC8F/FFFFFF?text=Kitchen', class: 'kitchen' }
            ]
        } = attributes;

        const updateRoom = (index, field, value) => {
            const newRooms = [...rooms];
            newRooms[index] = { ...newRooms[index], [field]: value };
            setAttributes({ rooms: newRooms });
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Browse Rooms Settings', 'blocksy-child')} initialOpen={true}>
                        <TextControl
                            label={__('Title', 'blocksy-child')}
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                        <TextControl
                            label={__('Description', 'blocksy-child')}
                            value={description}
                            onChange={(value) => setAttributes({ description: value })}
                        />
                    </PanelBody>
                    
                    {rooms.map((room, index) => (
                        <PanelBody key={index} title={__(`Room ${index + 1}`, 'blocksy-child')} initialOpen={false}>
                            <TextControl
                                label={__('Room Name', 'blocksy-child')}
                                value={room.name}
                                onChange={(value) => updateRoom(index, 'name', value)}
                            />
                            <TextControl
                                label={__('Product Count', 'blocksy-child')}
                                value={room.count}
                                onChange={(value) => updateRoom(index, 'count', value)}
                            />
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={(media) => updateRoom(index, 'image', media.url)}
                                    allowedTypes={['image']}
                                    value={room.image}
                                    render={({ open }) => (
                                        <Button onClick={open} isSecondary>
                                            {room.image ? __('Change Image', 'blocksy-child') : __('Select Image', 'blocksy-child')}
                                        </Button>
                                    )}
                                />
                            </MediaUploadCheck>
                            {room.image && (
                                <img src={room.image} alt={room.name} style={{ maxWidth: '100%', height: 'auto', marginTop: '10px' }} />
                            )}
                        </PanelBody>
                    ))}
                </InspectorControls>

                <div className="browse-rooms browse-rooms--editor">
                    <div className="browse-rooms__container">
                        <div className="browse-rooms__header">
                            <h2 className="browse-rooms__title">{title}</h2>
                            <p className="browse-rooms__description">{description}</p>
                        </div>
                        <div className="browse-rooms__grid">
                            {rooms.map((room, index) => (
                                <div 
                                    key={index}
                                    className={`room-card room-card--${room.class}`}
                                    style={{ 
                                        backgroundImage: `url(${room.image})`,
                                        backgroundSize: 'cover',
                                        backgroundPosition: 'center',
                                        backgroundRepeat: 'no-repeat'
                                    }}
                                >
                                    <div className="room-card__content">
                                        <h3 className="room-card__title">{room.name}</h3>
                                        <p className="room-card__count">{room.count} products</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </>
        );
    },

    save: function(props) {
        const { attributes } = props;
        const {
            title = 'Browse by rooms',
            description = 'Discover our curated collection of furniture organized by room type.',
            rooms = [
                { name: 'Living Room', count: '15', image: 'https://via.placeholder.com/600x800/8FBC8F/FFFFFF?text=Living+Room', class: 'living' },
                { name: 'Bedroom', count: '12', image: 'https://via.placeholder.com/400x300/DEB887/FFFFFF?text=Bedroom', class: 'bedroom' },
                { name: 'Walk-in Closet', count: '8', image: 'https://via.placeholder.com/400x300/DEB887/FFFFFF?text=Walk-in+Closet', class: 'closet' },
                { name: 'Kitchen', count: '20', image: 'https://via.placeholder.com/400x600/8FBC8F/FFFFFF?text=Kitchen', class: 'kitchen' }
            ]
        } = attributes;

        // Generate unique IDs for GenerateBlocks elements
        const mainId = generateUniqueId();
        const containerId = generateUniqueId();
        const headerId = generateUniqueId();
        const titleId = generateUniqueId();
        const gridId = generateUniqueId();

        return (
            <div className={`gb-element-${mainId} browse-rooms`}>
                <div className={`gb-element-${containerId} browse-rooms__container`}>
                    <div className={`gb-element-${headerId} browse-rooms__header`}>
                        <h2 className={`gb-text gb-${titleId} browse-rooms__title`}>{title}</h2>
                        <p className="browse-rooms__description">{description}</p>
                    </div>
                    <div className={`gb-element-${gridId} browse-rooms__grid`}>
                        {rooms.map((room, index) => {
                            const cardId = generateUniqueId();
                            const contentId = generateUniqueId();
                            const roomTitleId = generateUniqueId();
                            
                            return (
                                <div 
                                    key={index}
                                    className={`gb-element-${cardId} room-card room-card--${room.class}`}
                                    style={{ '--inline-bg-image': `url(${room.image})` }}
                                >
                                    <div className={`gb-element-${contentId} room-card__content`}>
                                        <h3 className={`gb-text gb-${roomTitleId} room-card__title`}>{room.name}</h3>
                                        <p className="room-card__count">{room.count} products</p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        );
    }
});
