export interface Media {
    id: number;
    url: string;
    thumbnails: {
        original: string | null;
        webp: string | null;
        avif: string | null;
    };
    previews: {
        original: string | null;
        webp: string | null;
        avif: string | null;
    };
    responsive: string[] | null;
    name: string;
    mime_type: string;
    order_column: number;
}
