<?php
// Script para ejecutar el comando de Laravel desde el Cron de OVH
// Ajustamos la ruta base por si acaso
chdir(__DIR__);

// Ejecutamos el comando artisan
// Usamos la ruta completa a PHP 7.4 (o la que use tu server) y al artisan
$output = shell_exec('/usr/local/php7.4/bin/php artisan game:resolve 2>&1');

// Imprimimos el resultado para que salga en el log de OVH si lo activas
echo "<pre>$output</pre>";
?>
