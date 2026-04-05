<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LandingBlock;

class LandingBlockSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LandingBlock::create([
            'key' => 'values',
            'title' => 'Наши ценности',
            'subtitle' => 'Почему выбирают продукты Молочной Долины',
            'content' => [
                ['icon' => 'Leaf', 'title' => '100% Натурально', 'desc' => 'Никакой химии и консервантов.'],
                ['icon' => 'Heart', 'title' => 'С любовью', 'desc' => 'Заботимся о животных как о родных.'],
                ['icon' => 'ShieldCheck', 'title' => 'Контроль качества', 'desc' => 'Проверяем каждую партию молока.'],
            ]
        ]);

        LandingBlock::create([
            'key' => 'how_it_works',
            'title' => 'Путь молока',
            'content' => [
                ['step' => 1, 'title' => 'Утренняя дойка', 'desc' => 'В 5 утра мы уже на ферме.'],
                ['step' => 2, 'title' => 'Охлаждение', 'desc' => 'Мгновенно охлаждаем до 4°C.'],
                ['step' => 3, 'title' => 'Доставка', 'desc' => 'Через 3 часа продукт у вас на столе.'],
            ]
        ]);
    }
}
