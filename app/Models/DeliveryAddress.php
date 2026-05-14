<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
class DeliveryAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'lat',
        'lng',
        'is_default',
        'label',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
