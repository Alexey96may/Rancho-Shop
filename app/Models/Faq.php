<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property bool $is_published
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
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
 * @method static Builder<static>|Faq filter(array $filters)
 *
 * @mixin \Eloquent
 */
class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopePublished(Builder $query)
    {
        return $query
            ->where('is_published', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Scope for case-insensitive question filtering
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = mb_strtolower($search, 'UTF-8');
            $query->whereRaw('LOWER(question) LIKE ?', ["%{$search}%"]);
        });
    }
}
