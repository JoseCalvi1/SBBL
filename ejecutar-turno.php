<?php
// ejecutar_turno.php

// 1. Carga las librerías de Laravel
require __DIR__ . '/vendor/autoload.php';

// 2. Inicia la aplicación
$app = require_once __DIR__ . '/bootstrap/app.php';

// 3. Carga el Kernel de la consola
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// 4. EJECUTA TU COMANDO
// El output se guarda en el buffer, así que capturamos la salida
$status = $kernel->call('game:resolve');

// 5. Mostrar resultado (para el log de OVH)
echo "<h1>Resultado de la Tarea:</h1>";
echo "<pre>" . $kernel->output() . "</pre>";
?>
