<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            FakeBusinessSeeder::class,
            BusinessAnalyticsSeeder::class
        ]);
    }
}

        // Depois, configure os competidores
        $businesses = Business::all();

        foreach ($businesses as $business) {
            // Pegar outros negócios do mesmo segmento
            $competitors = Business::where('id', '!=', $business->id)
                ->where('segment', $business->segment)
                ->inRandomOrder()
                ->take(3)
                ->get();

            $business->competitors()->attach($competitors->pluck('id'));
        }
    }
}