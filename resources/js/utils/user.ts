export const getInitials = (name: string): string => {
    const parts = name.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) return '';

    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }

    return parts
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase();
};

// Function to select a color based on ID or name
export const getAvatarColor = (id: number = 0): string => {
    const colors = [
        'bg-rancho-olive',
        'bg-rancho-forest',
        'bg-amber-600',
        'bg-emerald-600',
        'bg-stone-500',
    ];

    const randomColors = shuffle(colors);

    return randomColors[id % colors.length];
};

const shuffle = (array: string[]): string[] => {
    const shuffled = [...array];

    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));

        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }

    return shuffled;
};
