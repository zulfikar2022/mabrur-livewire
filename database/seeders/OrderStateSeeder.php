<?php

namespace Database\Seeders;

use App\Models\OrderState;
use Illuminate\Database\Seeder;

class OrderStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'pending'],
            ['name' => 'approved'],
            ['name' => 'cancelled'],
            ['name' => 'shipped'],
            ['name' => 'delivered'],
            ['name' => 'deliver_failed'],
            ['name' => 'returned'],
        ];

        if (OrderState::count() > 0) {
            return;
        }
        foreach ($states as $state) {
            OrderState::firstOrCreate(['name' => $state['name']]);
        }
    }
}
