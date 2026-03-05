<?php
/**
 * Initialize database schema - Run this once to set up the database
 */

echo "🔧 Initializing SpotMap Database...\n\n";

try {
    // Read schema file
    $schemaFile = __DIR__ . '/init-db/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Connect to MySQL without selecting a database first
    $dsn = 'mysql:host=localhost';
    $pdo = new \PDO($dsn, 'root', '');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $i => $sql) {
        if (empty($sql)) continue;
        
        try {
            $pdo->exec($sql);
            echo "✓ Statement " . ($i + 1) . " executed\n";
        } catch (\Exception $e) {
            // Some statements might fail if tables exist, that's ok
            echo "⚠ Statement " . ($i + 1) . " skipped (already exists?)\n";
        }
    }
    
    echo "\n✅ Database initialization completed!\n";
    echo "\n📊 Verifying data...\n";
    
    // Verify
    $pdo = new \PDO('mysql:host=localhost;dbname=spotmap', 'root', '');
    
    $count = $pdo->query('SELECT COUNT(*) as total FROM spots')->fetch()['total'];
    echo "Spots in database: $count\n";
    
    $spots = $pdo->query('SELECT id, title, category FROM spots LIMIT 5')->fetchAll(\PDO::FETCH_ASSOC);
    echo "\nSample spots:\n";
    foreach ($spots as $spot) {
        echo "  - {$spot['title']} ({$spot['category']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
