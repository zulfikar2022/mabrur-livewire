<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            'ঢাকা',
            'চট্টগ্রাম',
            'খুলনা',
            'রাজশাহী',
            'বরিশাল',
            'সিলেট',
            'রংপুর',
            'ময়মনসিংহ',
        ];

        if (Division::count() > 0) {
            return;
        }
        foreach ($divisions as $division) {
            Division::create(['name' => $division]);
        }
    }
}
