<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'question' => 'How do I create an account?',
                'answer' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p>',
                'sort_order' => 1,
            ],
            [
                'question' => 'How can I reset my password?',
                'answer' => '<p>Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta.</p>',
                'sort_order' => 2,
            ],
            [
                'question' => 'How long does support take to respond?',
                'answer' => '<p>Mauris massa. Vestibulum lacinia arcu eget nulla. Curabitur tortor. Pellentesque nibh. Aenean quam.</p>',
                'sort_order' => 3,
            ],
        ];

        foreach ($items as $item) {
            Faq::updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}