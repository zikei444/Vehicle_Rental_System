<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class VehiclesTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $rows = [
            ['car','cars/vios.jpg','Toyota','Vios',2022,'ABC123',120.00,'available',null,null],
            ['car','cars/civic.jpg','Honda','Civic',2021,'ABC777',150.00,'available',null,null],
            ['car','cars/corolla.jpg','Toyota','Corolla',2020,'CAR456',140.00,'available',null,null],
            ['car','cars/accord.jpg','Honda','Accord',2019,'CAR789',180.00,'available',null,null],
            ['truck','trucks/dmax.jpg','Isuzu','D-Max',2021,'TRK999',300.00,'available',null,null],
            ['truck','trucks/ranger.jpg','Ford','Ranger',2022,'TRK456',320.00,'available',null,null],
            ['truck','trucks/hilux.jpg','Toyota','Hilux',2020,'TRK123',310.00,'available',null,null],
            ['van','vans/urvan.jpg','Nissan','Urvan',2018,'VAN999',260.00,'available',null,null],
            ['van','vans/hiace.jpg','Toyota','Hiace',2019,'VAN321',270.00,'available',null,null],
            ['van','vans/transit.jpg','Ford','Transit',2021,'VAN654',290.00,'available',null,null],
            ['car','cars/model3.jpg','Tesla','Model 3',2023,'EV333',350.00,'available',null,null],
            ['car','cars/altis.jpg','Toyota','Altis',2017,'ALT777',110.00,'available',null,null],
        ];

        foreach ($rows as [$type,$image,$brand,$model,$year,$reg,$price,$status,$ins,$road]) {
            DB::table('vehicles')->insert([
                'type' => $type,
                'image' => $image,
                'brand' => $brand,
                'model' => $model,
                'year_of_manufacture' => $year,
                'registration_number' => $reg,
                'rental_price' => $price,
                'availability_status' => $status,
                'insurance_doc' => $ins,
                'roadtax_doc' => $road,
                'average_rating' => 0.00,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}