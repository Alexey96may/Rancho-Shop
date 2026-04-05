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
            'title' => 'Почему выбирают продукты Молочной Долины',
            'subtitle' => 'Забота о качестве на каждом этапе — от пастбища до вашего стола.',
            'content' => [
                ['icon' => 'Leaf', 'title' => 'Утренний сбор', 'desc' => 'Продукты попадают в доставку спустя 3-4 часа после сбора или удоя. Свежее не бывает.'],
                ['icon' => 'ShieldCheck', 'title' => 'Контроль качества', 'desc' => 'Никакого сахара, антибиотиков или ГМО. Только то, что дала природа и наши пастбища.'],
                ['icon' => 'Heart', 'title' => 'Знаем каждого в лицо', 'desc' => 'Вы можете прослушать голос коровы или прочитать биографию курицы, создавшей ваш завтрак.'],
                ['icon' => 'Truck', 'title' => 'Бережная доставка', 'desc' => 'Специальные термо-боксы сохраняют идеальную температуру молока и хрупкость яиц до вашей двери.'],
            ]
        ]);

        LandingBlock::create([
            'key' => 'about',
            'title' => 'Больше, чем просто <span class="italic text-rancho-buttercup">ферма</span>',
            'content' => [
                ['desc' => 'Мы верим, что вкус продукта напрямую зависит от счастья того, кто его дал. На «Ранчо» коровы не просто «единицы производства», а личности со своими именами и даже отпусками.'],
                ['desc' => 'Наш путь — это Socrates without Plato в мире сельского хозяйства: мы ищем истину в чистоте продукта, не приукрашивая его химией или маркетинговыми уловками. Только честный удой и свежий сбор.'],
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
