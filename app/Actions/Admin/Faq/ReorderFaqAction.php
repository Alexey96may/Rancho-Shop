<?php

namespace App\Actions\Admin\Faq;

use Illuminate\Support\Facades\DB;

class ReorderFaqAction
{
    public function handle(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $cases = [];
        $params = [];

        foreach ($ids as $index => $id) {
            $cases[] = 'WHEN id = ? THEN ?::integer';
            $params[] = $id;
            $params[] = $index;
        }

        $params = array_merge($params, $ids);
        $idsPlaceholder = implode(',', array_fill(0, count($ids), '?'));

        $query = '
            UPDATE faqs
            SET sort_order = (CASE ' . implode(' ', $cases) . " END)
            WHERE id IN ($idsPlaceholder)
        ";

        DB::transaction(fn () => DB::update($query, $params));
    }
}
