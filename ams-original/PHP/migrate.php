<?php
require __DIR__ . '/db.php';
$dir = __DIR__ . '/migrations';
$files = glob($dir . '/*.sql'); sort($files);
foreach ($files as $file) {
  $sql = file_get_contents($file);
  try { $pdo->exec($sql); echo "Applied: " . basename($file) . "\n"; }
  catch (Throwable $e) { echo "Error: " . $e->getMessage() . "\n"; http_response_code(500); exit(1); }
}
echo "Done.\n";
