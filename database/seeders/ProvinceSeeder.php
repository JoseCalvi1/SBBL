<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;

class ProvinceSeeder extends Seeder
{
    public function run()
    {
        $mapaEspana = [
            'Andalucía' => ['Almería', 'Cádiz', 'Córdoba', 'Granada', 'Huelva', 'Jaén', 'Málaga', 'Sevilla'],
            'Aragón' => ['Huesca', 'Teruel', 'Zaragoza'],
            'Asturias' => ['Asturias'],
            'Baleares' => ['Islas Baleares'],
            'Canarias' => ['Las Palmas', 'Santa Cruz de Tenerife'],
            'Cantabria' => ['Cantabria'],
            'Castilla y León' => ['Ávila', 'Burgos', 'León', 'Palencia', 'Salamanca', 'Segovia', 'Soria', 'Valladolid', 'Zamora'],
            'Castilla La Mancha' => ['Albacete', 'Ciudad Real', 'Cuenca', 'Guadalajara', 'Toledo'],
            'Catalunya' => ['Barcelona', 'Girona', 'Lleida', 'Tarragona'],
            'Valencia' => ['Alicante', 'Castellón', 'Valencia'],
            'Extremadura' => ['Badajoz', 'Cáceres'],
            'Galicia' => ['A Coruña', 'Lugo', 'Ourense', 'Pontevedra'],
            'Madrid' => ['Madrid'],
            'Murcia' => ['Murcia'],
            'Navarra' => ['Navarra'],
            'País Vasco' => ['Álava', 'Gipuzkoa', 'Bizkaia'],
            'La Rioja' => ['La Rioja'],
            'Ceuta' => ['Ceuta'],
            'Melilla' => ['Melilla'],
        ];

        foreach ($mapaEspana as $regionName => $provinces) {
            // Buscamos la región en tu base de datos actual (usa LIKE por si hay ligeras diferencias)
            $region = Region::where('name', 'LIKE', '%' . $regionName . '%')->first();

            if ($region) {
                foreach ($provinces as $provinceName) {
                    Province::firstOrCreate([
                        'name' => $provinceName,
                        'region_id' => $region->id
                    ]);
                }
            } else {
                $this->command->warn("No se encontró la región: {$regionName}. Revisa el nombre.");
            }
        }

        $this->command->info("¡Provincias cargadas y enlazadas correctamente!");
    }
}
