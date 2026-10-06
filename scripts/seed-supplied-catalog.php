<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/catalog-seed.php';

$created = mthSeedSuppliedCatalog($pdo);
echo $created . " supplied catalogue products added; existing records were left unchanged.\n";
