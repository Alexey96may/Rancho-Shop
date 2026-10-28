<?php

namespace App\Actions\Admin\Units;

use Illuminate\Support\Facades\DB;

class ReorderUnitAction
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
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = 'UPDATE units SET position = (CASE ' . implode(' ', $cases) . " END) WHERE id IN ({$placeholders})";

        DB::transaction(fn () => DB::update($sql, $params));
    }
}
