<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $category_id
 * @property int|null $parent_id
 * @property string $name
 * @property string $type
 * @property string $slug
 * @property bool $is_active
 * @property string $status
 * @property string|null $bio
 * @property array<array-key, mixed>|null $features
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Animal> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Animal|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\Seo|null $seo
 * @property-read mixed $voice_url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal cows()
 * @method static \Database\Factories\AnimalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal withoutTrashed()
 * @mixin \Eloquent
 */
	class Animal extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $description
 * @property string|null $icon
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Animal> $animals
 * @property-read int|null $animals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category foAnimals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category forProducts()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category hasActiveAnimals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category hasActiveProducts()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $guest_name
 * @property string $content
 * @property int|null $rating
 * @property CommentStatus $status
 * @property string|null $commentable_type
 * @property int|null $commentable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $author_name
 * @property-read Model|\Eloquent|null $commentable
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CommentFactory factory($count = null, $state = [])
 * @method static Builder<static>|Comment newModelQuery()
 * @method static Builder<static>|Comment newQuery()
 * @method static Builder<static>|Comment published()
 * @method static Builder<static>|Comment query()
 * @method static Builder<static>|Comment whereCommentableId($value)
 * @method static Builder<static>|Comment whereCommentableType($value)
 * @method static Builder<static>|Comment whereContent($value)
 * @method static Builder<static>|Comment whereCreatedAt($value)
 * @method static Builder<static>|Comment whereDeletedAt($value)
 * @method static Builder<static>|Comment whereGuestName($value)
 * @method static Builder<static>|Comment whereId($value)
 * @method static Builder<static>|Comment whereRating($value)
 * @method static Builder<static>|Comment whereStatus($value)
 * @method static Builder<static>|Comment whereUpdatedAt($value)
 * @method static Builder<static>|Comment whereUserId($value)
 * @mixin \Eloquent
 */
	class Comment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property int $total_revenue Revenue in cents
 * @property int $orders_count
 * @property int $items_count Number of sold units
 * @property int $avg_order_value Average Check
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereAvgOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereItemsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereOrdersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereTotalRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailySalesStat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class DailySalesStat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $address
 * @property float $lat
 * @property float $lng
 * @property bool $is_default
 * @property string|null $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\DeliveryAddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryAddress whereUserId($value)
 * @mixin \Eloquent
 */
	class DeliveryAddress extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property bool $is_published
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faq whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Faq extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property LandingBlockKey $key
 * @property string $title
 * @property string|null $subtitle
 * @property array<array-key, mixed> $content
 * @property bool $is_visible
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereIsVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class LandingBlock extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $promo_code_id
 * @property string $customer_name
 * @property string $customer_phone
 * @property string|null $customer_comment
 * @property bool $is_pickup
 * @property string|null $delivery_address
 * @property numeric|null $delivery_lat
 * @property numeric|null $delivery_lng
 * @property bool $delivery_validated
 * @property array<array-key, mixed>|null $delivery_meta
 * @property int $discount_total
 * @property int $total_price
 * @property int $delivery_price
 * @property OrderStatus $status
 * @property string|null $admin_note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PromoCode|null $promoCode
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereAdminNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIsPickup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePromoCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @mixin \Eloquent
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $product_variant_id
 * @property string $product_name
 * @property int $unit_price
 * @property int|null $old_unit_price
 * @property int $quantity
 * @property string|null $unit_name
 * @property string|null $unit_code
 * @property string|null $unit_short
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $product_id
 * @property-read int $subtotal
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProductVariant $variant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOldUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnitCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnitName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUnitShort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $content
 * @property PageType $type
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $reviews
 * @property-read int|null $reviews_count
 * @property-read \App\Models\Seo|null $seo
 * @property-read mixed $url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Page extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $category_id
 * @property int|null $animal_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property AvailabilityType $availability_type
 * @property array<array-key, mixed>|null $schedule
 * @property array<array-key, mixed>|null $attributes
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Animal> $animals
 * @property-read int|null $animals_count
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\ProductVariant|null $defaultVariant
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Seo|null $seo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $variants
 * @property-read int|null $variants_count
 * @method static Builder<static>|Product active()
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product filter(array $filters)
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product onlyTrashed()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product whereAnimalId($value)
 * @method static Builder<static>|Product whereAttributes($value)
 * @method static Builder<static>|Product whereAvailabilityType($value)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereDeletedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereIsActive($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product whereSchedule($value)
 * @method static Builder<static>|Product whereSlug($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Product withoutTrashed()
 * @mixin \Eloquent
 */
	class Product extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property int $unit_id
 * @property string $name
 * @property int $price
 * @property int|null $old_price
 * @property int $stock
 * @property bool $is_default
 * @property int $position
 * @property array<array-key, mixed>|null $attributes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $price_formatted
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Unit $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereOldPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ProductVariant extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property PromoCodeType $type
 * @property int $value
 * @property int $min_order_amount
 * @property int|null $max_discount
 * @property int|null $usage_limit
 * @property int $used_count
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $status
 * @method static Builder<static>|PromoCode active()
 * @method static \Database\Factories\PromoCodeFactory factory($count = null, $state = [])
 * @method static Builder<static>|PromoCode newModelQuery()
 * @method static Builder<static>|PromoCode newQuery()
 * @method static Builder<static>|PromoCode query()
 * @method static Builder<static>|PromoCode valid()
 * @method static Builder<static>|PromoCode whereCode($value)
 * @method static Builder<static>|PromoCode whereCreatedAt($value)
 * @method static Builder<static>|PromoCode whereDescription($value)
 * @method static Builder<static>|PromoCode whereExpiresAt($value)
 * @method static Builder<static>|PromoCode whereId($value)
 * @method static Builder<static>|PromoCode whereIsActive($value)
 * @method static Builder<static>|PromoCode whereMaxDiscount($value)
 * @method static Builder<static>|PromoCode whereMinOrderAmount($value)
 * @method static Builder<static>|PromoCode whereType($value)
 * @method static Builder<static>|PromoCode whereUpdatedAt($value)
 * @method static Builder<static>|PromoCode whereUsageLimit($value)
 * @method static Builder<static>|PromoCode whereUsedCount($value)
 * @method static Builder<static>|PromoCode whereValue($value)
 * @mixin \Eloquent
 */
	class PromoCode extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $seoable_type
 * @property int $seoable_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $keywords
 * @property string|null $canonical
 * @property array<array-key, mixed>|null $og_data
 * @property bool $is_noindex
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $seoable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereCanonical($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereIsNoindex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereOgData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereSeoableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereSeoableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Seo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $typed_value
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 * @mixin \Eloquent
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $short
 * @property string $slug
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereShort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Unit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $google_id
 * @property string|null $vkontakte_id
 * @property string|null $avatar_url
 * @property string|null $phone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\DeliveryAddress|null $defaultDeliveryAddress
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DeliveryAddress> $deliveryAddresses
 * @property-read int|null $delivery_addresses_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereVkontakteId($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

