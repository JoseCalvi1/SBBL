<?php
// www/ejecutar_turno.php

// 1. Cargar el autoloader.
// Usamos '../' porque vendor está en la carpeta de arriba, fuera de www
require __DIR__ . '/../vendor/autoload.php';

// 2. Iniciar la aplicación
// Usamos '../' porque bootstrap está fuera de www
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Cargar el Kernel
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// 4. Ejecutar comando
$status = $kernel->call('game:resolve');

// 5. Salida para el log
echo "<h1>Estado: Ejecutado</h1>";
echo "<pre>" . $kernel->output() . "</pre>";
?>
