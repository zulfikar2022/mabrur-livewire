<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pairs = [
                'ঢাকা' => ['ঢাকা', 'গাজীপুর', 'কিশোরগঞ্জ', 'মানিকগঞ্জ', 'মুন্সিগঞ্জ', 'নারায়ণগঞ্জ', 'নরসিংদী', 'রাজবাড়ী', 'টাঙ্গাইল', 'ফরিদপুর', 'গোপালগঞ্জ', 'মাদারীপুর', 'শরীয়তপুর'],
                'চট্টগ্রাম' => ['চট্টগ্রাম', 'কক্সবাজার', 'ফেনী', 'ব্রাহ্মণবাড়িয়া', 'রাঙ্গামাটি', 'খাগড়াছড়ি', 'লক্ষ্মীপুর', 'কুমিল্লা', 'চাঁদপুর', 'নোয়াখালী', 'বান্দরবান'],
                'খুলনা' => ['খুলনা', 'যশোর', 'সাতক্ষীরা', 'মাগুরা', 'ঝিনাইদহ', 'চুয়াডাঙ্গা', 'বাগেরহাট', 'কুষ্টিয়া', 'নড়াইল', 'মেহেরপুর'],
                'রাজশাহী' => ['রাজশাহী', 'নাটোর', 'নওগাঁ', 'পাবনা', 'চাঁপাইনবাবগঞ্জ', 'বগুড়া', 'জয়পুরহাট', 'সিরাজগঞ্জ'],
                'বরিশাল' => ['বরিশাল', 'ভোলা', 'পটুয়াখালী', 'ঝালকাঠি', 'পিরোজপুর', 'বরগুনা'],
                'সিলেট' => ['সিলেট', 'মৌলভীবাজার', 'হবিগঞ্জ', 'সুনামগঞ্জ'],
                'রংপুর' => ['রংপুর', 'দিনাজপুর', 'গাইবান্ধা', 'ঠাকুরগাঁও', 'কুড়িগ্রাম', 'লালমনিরহাট', 'নীলফামারী', 'পঞ্চগড়'],
                'ময়মনসিংহ' => ['ময়মনসিংহ', 'জামালপুর', 'নেত্রকোনা', 'শেরপুর'],
            ];

        if (District::count() > 0) {
            return;
        }
        foreach ($pairs as $divisionName => $districtNames) {
            $division = Division::where('name', $divisionName)->first();
            if ($division) {
                foreach ($districtNames as $districtName) {
                    District::create([
                        'division_id' => $division->id,
                        'name' => $districtName
                    ]);
                }
            }
        }
    }
}
