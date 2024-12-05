<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessHours;
use Carbon\Carbon;

class FakeBusinessHoursSeeder extends Seeder
{
    public function run()
    {
        $business = \App\Models\Business::first(); // Pega o negócio que acabamos de criar

        // Array com os horários de funcionamento
        $weekDays = [
            'Monday' => ['09:00', '19:00', false], // [hora_abertura, hora_fechamento, is_closed]
            'Tuesday' => ['09:00', '19:00', false],
            'Wednesday' => ['09:00', '19:00', false],
            'Thursday' => ['09:00', '19:00', false],
            'Friday' => ['09:00', '19:00', false],
            'Saturday' => ['10:00', '18:00', false],
            'Sunday' => ['00:00', '00:00', true], // Fechado aos domingos
        ];

        // Criar registros para cada dia da semana
        foreach ($weekDays as $day => $hours) {
            BusinessHours::create([
                'business_id' => $business->id,
                'day_of_week' => $day,
                'opening_time' => $hours[0],
                'closing_time' => $hours[1],
                'is_closed' => $hours[2],
                'is_holiday' => false,
            ]);
        }
    }
}