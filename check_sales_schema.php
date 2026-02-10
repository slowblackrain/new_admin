<?php
try {
    $dsn = "mysql:host=localhost;dbname=dometopia_godomall;charset=utf8";
    $username = "root";
    $password = "root"; // Assuming default or known password
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    $pdo = new PDO($dsn, $username, $password, $options);

    echo "Tables found:\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'fm_sales%'");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));

    echo "\nStructure of fm_sales_list:\n";
    $stmt = $pdo->query("DESCRIBE fm_sales_list");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }

    echo "\nStructure of fm_sales:\n";
    $stmt = $pdo->query("DESCRIBE fm_sales");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
