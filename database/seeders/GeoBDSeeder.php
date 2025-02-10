<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class GeoBDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $http = Http::baseUrl('https://gist.githubusercontent.com/bdsumon4u/6bb444f7fbe788673e5c0268423825c3/raw/5c552965dd4d120c726a92d88d53b08543a25eb1');

        District::query()->delete();
        Division::query()->delete();

        $http->get('/divisions.json')
            ->collect()
            ->each(function ($division) {
                \App\Models\Division::create([
                    'id' => $division['id'],
                    'name' => $division['bn_name'],
                ]);
            });

        $http->get('/districts.json')
            ->collect()
            ->each(function ($district) {
                \App\Models\District::create([
                    'id' => $district['id'],
                    'division_id' => $district['division_id'],
                    'name' => $district['bn_name'],
                ]);
            });
    }
}
