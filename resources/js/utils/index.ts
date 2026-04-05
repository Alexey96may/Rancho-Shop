export const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.onerror = null;
    const placeholder = '/images/no-image.png';

    if (target.src.includes(placeholder)) return;
    target.src = placeholder;
};
