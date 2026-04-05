export const handleImageError = (
    event: Event,
    type: 'product' | 'animal' | 'general' = 'general',
) => {
    const target = event.target as HTMLImageElement;
    target.onerror = null;

    const placeholders = {
        product: '/images/no-product.jpg',
        animal: '/images/no-animal.jpg',
        general: '/images/no-image.png',
    };

    const newSrc = placeholders[type];

    if (target.src.includes(newSrc)) return;
    target.src = newSrc;
};
