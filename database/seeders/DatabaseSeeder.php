<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessCompetitorSeeder extends Seeder
{
    public function run()
    {
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