<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BeybladeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Opciones para cada tabla
        $bladeOptions = ['Aero Pegasus', 'Bite Croc', 'Black Shell', 'Cobalt Dragoon', 'Cobalt Drake', 'Crimson Garuda', 'Darth Vader',
            'Dran Buster', 'Dran Dagger', 'Dran Sword', 'Dranzer S', 'Driger S', 'Hells Chain', 'Hells Hammer', 'Hells Scythe',
            'Impact Drake', 'Iron Man', 'Knife Shinobi', 'Knight Lance', 'Knight Mail', 'Knight Shield', 'Lightning L-Drago
            Assault', 'Lightning L-Drago Barrage', 'Leon Claw', 'Leon Crest',
            'Lightning L-Drago', 'Luke Skywalker', 'Megatron', 'Moff Gideon', 'Optimus Primal', 'Optimus Prime', 'Phoenix Feather',
            'Phoenix Rudder', 'Phoenix Wing', 'Rhino Horn', 'Roar Tyranno', 'Samurai Saber', 'Savage Bear', 'Sharke Edge', 'Shinobi Shadow',
            'Silver Wolf', 'Sphinx Cowl', 'Spider-Man', 'Starscream', 'Steel Samurai', 'Talon Ptera', 'Thanos', 'The Mandalorian',
            'Tusk Mammoth', 'Tyranno Beat', 'Unicorn Sting', 'Venom', 'Viper Tail', 'Weiss Tiger', 'Whale Wave', 'Wizard Arrow',
            'Wizard Rod', 'Wyvern Gale', 'Yell Kong'];

        $ratchetOptions = ['0-80', '1-60', '1-80', '2-60', '2-70', '2-80', '3-60', '3-70', '3-80', '3-85', '4-60', '4-70', '4-80',
            '5-60', '5-70', '5-80', '7-60', '7-70', '9-60', '9-70', '9-80'];

        $bitOptions = ['Accel', 'Ball', 'Bound Spike', 'Cyclone', 'Disc Ball', 'Dot', 'Elevate', 'Flat', 'Free Ball', 'Gear Ball',
            'Gear Flat', 'Gear Needle', 'Gear Point', 'Glide', 'Hexa', 'High Needle', 'High Taper', 'Level', 'Low Flat', 'Low Rush',
            'Metal Needle', 'Needle', 'Orb', 'Point', 'Quake', 'Rubber Accel', 'Rush', 'Spike', 'Taper', 'Trans Point', 'Unite'];

        // Poblar la tabla blades
        foreach ($bladeOptions as $blade) {
            DB::table('blades')->insert([
                'nombre_takara' => $blade,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Poblar la tabla ratchets
        foreach ($ratchetOptions as $ratchet) {
            DB::table('ratchets')->insert([
                'nombre' => $ratchet,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Poblar la tabla bits
        foreach ($bitOptions as $bit) {
            DB::table('bits')->insert([
                'nombre' => $bit,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
