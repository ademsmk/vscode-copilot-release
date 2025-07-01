<?php
// filepath: c:\wamp64\www\apphotel\database_operations.php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$username = 'postgree';
$password = '521300';
$database = 'hotelyonetim';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'execute_query':
        executeQuery($pdo);
        break;
    
    case 'get_tables':
        getTables($pdo);
        break;
    
    case 'get_table_details':
        getTableDetails($pdo);
        break;
    
    case 'get_stats':
        getDatabaseStats($pdo);
        break;
    
    case 'get_performance_stats':
        getPerformanceStats($pdo);
        break;
    
    case 'get_files':
        getSQLFiles();
        break;
    
    case 'get_file_content':
        getFileContent();
        break;
    
    case 'get_backups':
        getBackups();
        break;
    
    case 'create_backup':
        createBackup($pdo);
        break;
    
    case 'restore_backup':
        restoreBackup($pdo);
        break;
    
    case 'save_query':
        saveQuery();
        break;
    
    case 'upload_file':
        uploadFile();
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function executeQuery($pdo) {
    $query = $_POST['query'] ?? '';
    
    if(empty($query)) {
        echo json_encode(['error' => 'No query provided']);
        return;
    }
    
    try {
        $startTime = microtime(true);
        
        // Determine query type
        $queryType = strtolower(trim(explode(' ', $query)[0]));
        
        if($queryType === 'select' || strpos(strtolower($query), 'show') === 0 || strpos(strtolower($query), 'describe') === 0) {
            // SELECT, SHOW, DESCRIBE queries
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            echo json_encode([
                'type' => 'select',
                'results' => $results,
                'execution_time' => $executionTime,
                'row_count' => count($results)
            ]);
        } else {
            // INSERT, UPDATE, DELETE queries
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute();
            $affectedRows = $stmt->rowCount();
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            echo json_encode([
                'type' => 'modify',
                'success' => $result,
                'affected_rows' => $affectedRows,
                'execution_time' => $executionTime,
                'message' => "Query executed successfully. $affectedRows rows affected."
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'error' => $e->getMessage(),
            'line' => $e->getLine()
        ]);
    }
}

function getTables($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $tableInfo = [];
        foreach($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) as row_count FROM `$table`");
            $rowCount = $stmt->fetch(PDO::FETCH_ASSOC)['row_count'];
            
            $tableInfo[] = [
                'name' => $table,
                'rows' => $rowCount
            ];
        }
        
        echo json_encode($tableInfo);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getTableDetails($pdo) {
    $tableName = $_GET['table'] ?? '';
    
    if(empty($tableName)) {
        echo json_encode(['error' => 'No table specified']);
        return;
    }
    
    try {
        $stmt = $pdo->query("DESCRIBE `$tableName`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get table info
        $stmt = $pdo->query("SHOW TABLE STATUS WHERE Name = '$tableName'");
        $tableStatus = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'table_name' => $tableName,
            'columns' => $columns,
            'status' => $tableStatus
        ]);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getDatabaseStats($pdo) {
    try {
        // Get database size
        $stmt = $pdo->query("SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size_mb'
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()");
        $size = $stmt->fetch(PDO::FETCH_ASSOC)['size_mb'];
        
        // Get uptime
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name = 'Uptime'");
        $uptime = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
        $uptimeFormatted = formatUptime($uptime);
        
        // Get number of tables
        $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = DATABASE()");
        $tableCount = $stmt->fetch(PDO::FETCH_ASSOC)['table_count'];
        
        echo json_encode([
            'size' => $size . ' MB',
            'uptime' => $uptimeFormatted,
            'table_count' => $tableCount
        ]);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getPerformanceStats($pdo) {
    try {
        // Get active connections
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name = 'Threads_connected'");
        $connections = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
        
        // Get query statistics
        $stmt = $pdo->query("SHOW STATUS WHERE Variable_name IN ('Queries', 'Slow_queries')");
        $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        echo json_encode([
            'connections' => $connections,
            'total_queries' => $stats['Queries'] ?? 0,
            'slow_queries' => $stats['Slow_queries'] ?? 0,
            'cpu_usage' => rand(10, 80), // Mock data
            'memory_usage' => rand(30, 90) // Mock data
        ]);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getSQLFiles() {
    $sqlDir = __DIR__ . '/sql_files/';
    
    if(!is_dir($sqlDir)) {
        mkdir($sqlDir, 0755, true);
    }
    
    $files = [];
    $scanDir = scandir($sqlDir);
    
    foreach($scanDir as $file) {
        if(pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $files[] = [
                'name' => $file,
                'size' => formatBytes(filesize($sqlDir . $file)),
                'modified' => date('Y-m-d H:i:s', filemtime($sqlDir . $file))
            ];
        }
    }
    
    echo json_encode($files);
}

function getFileContent() {
    $fileName = $_GET['file'] ?? '';
    $sqlDir = __DIR__ . '/sql_files/';
    $filePath = $sqlDir . $fileName;
    
    if(file_exists($filePath) && pathinfo($fileName, PATHINFO_EXTENSION) === 'sql') {
        echo file_get_contents($filePath);
    } else {
        echo 'File not found or invalid file type';
    }
}

function getBackups() {
    $backupDir = __DIR__ . '/backups/';
    
    if(!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backups = [];
    $scanDir = scandir($backupDir);
    
    foreach($scanDir as $file) {
        if(pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => formatBytes(filesize($backupDir . $file)),
                'date' => date('Y-m-d H:i:s', filemtime($backupDir . $file))
            ];
        }
    }
    
    // Sort by date descending
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    echo json_encode($backups);
}

function createBackup($pdo) {
    $backupName = $_POST['name'] ?? 'backup_' . date('Y_m_d_H_i_s');
    $backupDir = __DIR__ . '/backups/';
    
    if(!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . $backupName . '.sql';
    
    try {
        // Get all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $output = "-- Database Backup Created: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Generated by Advanced Database Management System\n\n";
        
        foreach($tables as $table) {
            // Get table structure
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $output .= "-- Table structure for table `$table`\n";
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $createTable['Create Table'] . ";\n\n";
            
            // Get table data
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if($rows) {
                $output .= "-- Dumping data for table `$table`\n";
                
                foreach($rows as $row) {
                    $values = array_map(function($value) use ($pdo) {
                        return $pdo->quote($value);
                    }, array_values($row));
                    
                    $output .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }
        
        file_put_contents($backupFile, $output);
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup created successfully',
            'file' => $backupName . '.sql'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function restoreBackup($pdo) {
    if(!isset($_FILES['backup_file'])) {
        echo json_encode(['error' => 'No file uploaded']);
        return;
    }
    
    $file = $_FILES['backup_file'];
    $dropExisting = $_POST['drop_existing'] ?? false;
    
    if($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload error']);
        return;
    }
    
    if(pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
        echo json_encode(['error' => 'Invalid file type. Only SQL files are allowed.']);
        return;
    }
    
    try {
        $sql = file_get_contents($file['tmp_name']);
        
        // Split SQL into individual statements
        $statements = explode(';', $sql);
        
        $pdo->beginTransaction();
        
        foreach($statements as $statement) {
            $statement = trim($statement);
            if(!empty($statement) && !preg_match('/^--/', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Database restored successfully'
        ]);
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function saveQuery() {
    $queryName = $_POST['name'] ?? '';
    $queryContent = $_POST['query'] ?? '';
    
    if(empty($queryName) || empty($queryContent)) {
        echo json_encode(['error' => 'Name and query content are required']);
        return;
    }
    
    $sqlDir = __DIR__ . '/sql_files/';
    
    if(!is_dir($sqlDir)) {
        mkdir($sqlDir, 0755, true);
    }
    
    $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $queryName) . '.sql';
    $filePath = $sqlDir . $fileName;
    
    $content = "-- Query: $queryName\n";
    $content .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
    $content .= $queryContent;
    
    if(file_put_contents($filePath, $content)) {
        echo json_encode([
            'success' => true,
            'message' => 'Query saved successfully',
            'file' => $fileName
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save query'
        ]);
    }
}

function uploadFile() {
    if(!isset($_FILES['sql_file'])) {
        echo json_encode(['error' => 'No file uploaded']);
        return;
    }
    
    $file = $_FILES['sql_file'];
    
    if($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload error']);
        return;
    }
    
    if(pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
        echo json_encode(['error' => 'Invalid file type. Only SQL files are allowed.']);
        return;
    }
    
    $sqlDir = __DIR__ . '/sql_files/';
    
    if(!is_dir($sqlDir)) {
        mkdir($sqlDir, 0755, true);
    }
    
    $fileName = basename($file['name']);
    $targetPath = $sqlDir . $fileName;
    
    if(move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file' => $fileName
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to upload file'
        ]);
    }
}

// Helper functions
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function formatUptime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return $hours . 'h ' . $minutes . 'm';
}
?>