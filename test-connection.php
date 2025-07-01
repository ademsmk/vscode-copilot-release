<?php
// PostgreSQL bağlantı testi
require_once 'backend/config/database.php';

echo "PostgreSQL Bağlantı Testi\n";
echo "========================\n";

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✓ Veritabanına başarıyla bağlandı!\n";
    
    // Tabloları listele
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nMevcut Tablolar:\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
    }
    
    // Rezervasyon sayısını kontrol et
    if (in_array('reservations', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
        $count = $stmt->fetchColumn();
        echo "\nRezervasyon Sayısı: " . $count . "\n";
    }
    
    // Personel sayısını kontrol et
    if (in_array('staff', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM staff");
        $count = $stmt->fetchColumn();
        echo "Personel Sayısı: " . $count . "\n";
    }
    
    // Oda sayısını kontrol et
    if (in_array('rooms', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM rooms");
        $count = $stmt->fetchColumn();
        echo "Oda Sayısı: " . $count . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Bağlantı hatası: " . $e->getMessage() . "\n";
    echo "Hata detayı: " . $e->getTraceAsString() . "\n";
}
?>
