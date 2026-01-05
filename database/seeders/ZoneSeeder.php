<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('zones')->truncate(); // Borrar zonas antiguas
        Schema::enableForeignKeyConstraints();

        $provinces = [
            ['name' => 'Álava', 'slug' => 'alava'],
            ['name' => 'Albacete', 'slug' => 'albacete'],
            ['name' => 'Alicante', 'slug' => 'alicante'],
            ['name' => 'Almería', 'slug' => 'almeria'],
            ['name' => 'Asturias', 'slug' => 'asturias'],
            ['name' => 'Ávila', 'slug' => 'avila'],
            ['name' => 'Badajoz', 'slug' => 'badajoz'],
            ['name' => 'Barcelona', 'slug' => 'barcelona'],
            ['name' => 'Burgos', 'slug' => 'burgos'],
            ['name' => 'Cáceres', 'slug' => 'caceres'],
            ['name' => 'Cádiz', 'slug' => 'cadiz'],
            ['name' => 'Cantabria', 'slug' => 'cantabria'],
            ['name' => 'Castellón', 'slug' => 'castellon'],
            ['name' => 'Ceuta', 'slug' => 'ceuta'],
            ['name' => 'Ciudad Real', 'slug' => 'ciudad-real'],
            ['name' => 'Córdoba', 'slug' => 'cordoba'],
            ['name' => 'Cuenca', 'slug' => 'cuenca'],
            ['name' => 'Girona', 'slug' => 'girona'],
            ['name' => 'Granada', 'slug' => 'granada'],
            ['name' => 'Guadalajara', 'slug' => 'guadalajara'],
            ['name' => 'Guipúzcoa', 'slug' => 'guipuzcoa'],
            ['name' => 'Huelva', 'slug' => 'huelva'],
            ['name' => 'Huesca', 'slug' => 'huesca'],
            ['name' => 'Illes Balears', 'slug' => 'baleares'],
            ['name' => 'Jaén', 'slug' => 'jaen'],
            ['name' => 'La Coruña', 'slug' => 'coruna'],
            ['name' => 'La Rioja', 'slug' => 'rioja'],
            ['name' => 'Las Palmas', 'slug' => 'las-palmas'],
            ['name' => 'León', 'slug' => 'leon'],
            ['name' => 'Lleida', 'slug' => 'lleida'],
            ['name' => 'Lugo', 'slug' => 'lugo'],
            ['name' => 'Madrid', 'slug' => 'madrid'],
            ['name' => 'Málaga', 'slug' => 'malaga'],
            ['name' => 'Melilla', 'slug' => 'melilla'],
            ['name' => 'Murcia', 'slug' => 'murcia'],
            ['name' => 'Navarra', 'slug' => 'navarra'],
            ['name' => 'Ourense', 'slug' => 'ourense'],
            ['name' => 'Palencia', 'slug' => 'palencia'],
            ['name' => 'Pontevedra', 'slug' => 'pontevedra'],
            ['name' => 'Salamanca', 'slug' => 'salamanca'],
            ['name' => 'Santa Cruz de Tenerife', 'slug' => 'tenerife'],
            ['name' => 'Segovia', 'slug' => 'segovia'],
            ['name' => 'Sevilla', 'slug' => 'sevilla'],
            ['name' => 'Soria', 'slug' => 'soria'],
            ['name' => 'Tarragona', 'slug' => 'tarragona'],
            ['name' => 'Teruel', 'slug' => 'teruel'],
            ['name' => 'Toledo', 'slug' => 'toledo'],
            ['name' => 'Valencia', 'slug' => 'valencia'],
            ['name' => 'Valladolid', 'slug' => 'valladolid'],
            ['name' => 'Vizcaya', 'slug' => 'vizcaya'],
            ['name' => 'Zamora', 'slug' => 'zamora'],
            ['name' => 'Zaragoza', 'slug' => 'zaragoza'],
        ];

        $zonesToInsert = [];
        foreach ($provinces as $prov) {
            $zonesToInsert[] = [
                'name' => $prov['name'],
                'slug' => $prov['slug'],
                'team_id' => null,
                'defense_bonus' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('zones')->insert($zonesToInsert);
    }
}
