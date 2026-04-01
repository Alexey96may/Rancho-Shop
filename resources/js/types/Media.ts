export interface Media {
    id: number;
    url: string; // Прямая ссылка на оригинал
    thumbnail?: string; // Ссылка на сконвертированное превью (напр. 200x200)
    preview?: string; // Ссылка на средний размер (напр. 800x600)
    name: string; // Имя файла (для альта)
    file_name: string; // Физическое имя
    mime_type: string; // 'image/jpeg', 'audio/mpeg'
    size: number; // Размер в байтах
    order_column: number; // Для сортировки в галерее
}
