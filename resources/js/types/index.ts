//В ts мы описываем created_at и updated_at. Может их не будем передавать на фронт,
// только в админку. Как лучше организовать логику. Дублировать лучше для админки, или создать надстройку full для админки,
// где добавим в админку.

//Объединить Админки

//У нас есть в типах, админочные варианты, их можно объединить в общий

export * from './Animal';
export * from './Product';
export * from './Comment';
export * from './Cart';
export * from './Settings';
export * from './Order';
export * from './PromoCode';
export * from './Seo';
