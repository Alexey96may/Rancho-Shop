<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
