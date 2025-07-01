<?php
echo "PostgreSQL Bağlantı Diagnostik\n";
echo "=============================\n";

// Farklı bağlantı yöntemlerini dene
$configs = [
    [
        'name' => 'Şifre ile bağlantı',
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'hotelyonetim',
        'user' => 'postgres',
        'password' => '521300'
    ],
    [
        'name' => 'Şifresiz peer auth',
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'hotelyonetim',
        'user' => 'postgres',
        'password' => ''
    ],
    [
        'name' => 'Unix socket bağlantısı',
        'host' => '/var/run/postgresql',
        'port' => '5432',
        'dbname' => 'hotelyonetim',
        'user' => 'postgres',
        'password' => '521300'
    ]
];

foreach ($configs as $config) {
    echo "\n" . $config['name'] . ":\n";
    try {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
        echo "DSN: $dsn\n";
        
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        echo "✓ Bağlantı başarılı!\n";
        
        // Version bilgisi al
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "PostgreSQL Version: " . substr($version, 0, 50) . "...\n";
        
        break; // İlk başarılı bağlantıda dur
        
    } catch (PDOException $e) {
        echo "✗ Hata: " . $e->getMessage() . "\n";
    }
}

// Sistem bilgileri
echo "\n\nSistem Bilgileri:\n";
echo "===============\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'Yüklü' : 'Yüklü değil') . "\n";

// Environment variables kontrol et
echo "\nEnvironment Variables:\n";
$pgvars = ['PGHOST', 'PGPORT', 'PGDATABASE', 'PGUSER', 'PGPASSWORD'];
foreach ($pgvars as $var) {
    $value = getenv($var);
    echo "$var: " . ($value ? $value : 'Ayarlanmamış') . "\n";
}
?>
