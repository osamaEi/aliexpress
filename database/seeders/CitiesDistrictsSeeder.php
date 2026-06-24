<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Seeder;

class CitiesDistrictsSeeder extends Seeder
{
    /**
     * Seed major cities and a starter set of districts for AE, SA, QA, BH.
     * Idempotent: re-running won't create duplicates.
     */
    public function run(): void
    {
        $data = [
            'AE' => [
                'Dubai'        => ['Deira', 'Bur Dubai', 'Jumeirah', 'Al Barsha', 'Business Bay', 'Dubai Marina', 'Al Qusais', 'International City', 'Downtown Dubai', 'Mirdif'],
                'Abu Dhabi'    => ['Al Khalidiyah', 'Al Mushrif', 'Khalifa City', 'Al Reem Island', 'Mussafah', 'Al Bateen', 'Al Maryah Island'],
                'Sharjah'      => ['Al Majaz', 'Al Nahda', 'Al Qasimia', 'Muweilah', 'Al Taawun', 'Al Khan'],
                'Ajman'        => ['Al Nuaimiya', 'Al Rashidiya', 'Al Jurf', 'Al Mowaihat'],
                'Ras Al Khaimah' => ['Al Nakheel', 'Al Hamra', 'Khuzam', 'Al Dhait'],
                'Fujairah'     => ['Al Faseel', 'Sakamkam', 'Merashid'],
                'Umm Al Quwain' => ['Al Salama', 'Al Raas', 'Al Maidan'],
            ],
            'SA' => [
                'Riyadh'   => ['Al Olaya', 'Al Malaz', 'Al Naseem', 'Al Murabba', 'King Fahd District', 'Al Wurud', 'Al Sahafa', 'Al Yasmin', 'Al Narjis', 'Al Aqiq'],
                'Jeddah'   => ['Al Rawdah', 'Al Hamra', 'Al Salamah', 'Al Andalus', 'Al Faisaliyah', 'Al Naeem', 'Al Shati', 'Al Bawadi'],
                'Mecca'    => ['Al Aziziyah', 'Al Shawqiyah', 'Al Naseem', 'Al Hindawiyah', 'Al Awali'],
                'Medina'   => ['Quba', 'Al Aqiq', 'Al Aziziyah', 'Al Haram', 'Al Khalidiyah'],
                'Dammam'   => ['Al Faisaliyah', 'Al Shati', 'Al Aziziyah', 'Al Mazruiyah', 'Al Adamah'],
                'Khobar'   => ['Al Olaya', 'Al Aqrabiyah', 'Al Rakah', 'Al Thuqbah', 'Al Hizam'],
                'Tabuk'    => ['Al Wadi', 'Al Faisaliyah', 'Al Rawdah'],
            ],
            'QA' => [
                'Doha'        => ['West Bay', 'Al Sadd', 'Al Dafna', 'Msheireb', 'Najma', 'Al Mansoura', 'Old Airport', 'Bin Mahmoud'],
                'Al Rayyan'   => ['Al Gharrafa', 'Muaither', 'Al Wajba', 'New Al Rayyan'],
                'Al Wakrah'   => ['Al Wukair', 'Mesaieed', 'Barwa City'],
                'Al Khor'     => ['Al Khor City', 'Al Thakhira'],
                'Lusail'      => ['Marina District', 'Fox Hills', 'Energy City'],
            ],
            'BH' => [
                'Manama'       => ['Juffair', 'Adliya', 'Seef', 'Gudaibiya', 'Hoora', 'Diplomatic Area', 'Salmaniya'],
                'Muharraq'     => ['Arad', 'Hidd', 'Busaiteen', 'Galali'],
                'Riffa'        => ['East Riffa', 'West Riffa', 'Riffa Views', 'Hajiyat'],
                'Isa Town'     => ['Block 801', 'Block 802', 'Block 803'],
                'Hamad Town'   => ['Roundabout 1', 'Roundabout 9', 'Roundabout 17'],
                'Sitra'        => ['Wadyan', 'Mahazza', 'Sufala'],
            ],
        ];

        foreach ($data as $countryCode => $cities) {
            $citySort = 0;
            foreach ($cities as $cityName => $districts) {
                $city = City::firstOrCreate(
                    ['country_code' => $countryCode, 'name' => $cityName],
                    ['is_active' => true, 'sort_order' => $citySort++]
                );

                $districtSort = 0;
                foreach ($districts as $districtName) {
                    District::firstOrCreate(
                        ['city_id' => $city->id, 'name' => $districtName],
                        ['is_active' => true, 'sort_order' => $districtSort++]
                    );
                }
            }
        }

        $this->command->info('Cities & districts seeded for AE, SA, QA, BH.');
    }
}
