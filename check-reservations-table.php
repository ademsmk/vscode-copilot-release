<?php
require_once 'backend/config/database.php';

try {
    $conn = Database::getInstance()->getConnection();
    echo "✓ Bağlantı başarılı\n";
    
    // Rezervasyon tablosu yapısını kontrol et
    $stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'reservations' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Rezervasyon tablosu kolonları:\n";
    foreach ($columns as $column) {
        echo "- " . $column['column_name'] . " (" . $column['data_type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>
