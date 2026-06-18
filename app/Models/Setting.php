<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public function typedValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->value)) return null;

                return match ($this->type) {
                    'integer' => (int) $this->value,
                    'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
                    'json'    => json_decode($this->value, true),
                    default   => $this->value, // string
                };
            }
        );
    }
}
