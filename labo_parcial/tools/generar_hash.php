<?php
// Uso: php tools/generar_hash.php "TuPasswordSeguro"
if (empty($argv[1])) {
    echo "Uso: php tools/generar_hash.php \"TuPasswordSeguro\"\n";
    exit(1);
}
echo password_hash($argv[1], PASSWORD_DEFAULT) . PHP_EOL;
