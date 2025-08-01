<?php
// Update permission untuk SPK agar user bisa melakukan otorisasi
// Menu: 08103 (SPK), Permission: IsOtorisasi1

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_bobajetbrain";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== UPDATE PERMISSION SPK OTORISASI ===\n";
    
    // Cek current permission untuk SPK
    $stmt = $pdo->prepare("SELECT USERID, L1, IsOtorisasi1, ISKOREKSI, ISCETAK FROM DBFLMENU WHERE L1 = '08103'");
    $stmt->execute();
    $current = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current SPK permissions:\n";
    foreach ($current as $row) {
        echo "User: {$row['USERID']}, Menu: {$row['L1']}, IsOtorisasi1: {$row['IsOtorisasi1']}, ISKOREKSI: {$row['ISKOREKSI']}, ISCETAK: {$row['ISCETAK']}\n";
    }
    
    // Update permission IsOtorisasi1 untuk semua user yang punya akses SPK
    $updateSql = "UPDATE DBFLMENU SET IsOtorisasi1 = 1 WHERE L1 = '08103' AND HASACCESS = 1";
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute();
    
    if ($result) {
        $rowCount = $stmt->rowCount();
        echo "\nSUCCESS: Updated IsOtorisasi1 permission for $rowCount users in SPK menu (08103)\n";
        
        // Verify update
        $stmt = $pdo->prepare("SELECT USERID, L1, IsOtorisasi1, ISKOREKSI, ISCETAK FROM DBFLMENU WHERE L1 = '08103'");
        $stmt->execute();
        $updated = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nUpdated SPK permissions:\n";
        foreach ($updated as $row) {
            echo "User: {$row['USERID']}, Menu: {$row['L1']}, IsOtorisasi1: {$row['IsOtorisasi1']}, ISKOREKSI: {$row['ISKOREKSI']}, ISCETAK: {$row['ISCETAK']}\n";
        }
    } else {
        echo "ERROR: Failed to update permissions\n";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>
