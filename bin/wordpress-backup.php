<?php
// Private SQL backup for the WordPress database. Never prints content or credentials.
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || count($argv) !== 3 || !is_file(rtrim($argv[1], '/\\') . '/wp-load.php')) {
    fwrite(STDERR, "Usage: php wordpress-backup.php /wordpress/root /private/output.sql.gz\n");
    exit(2);
}
umask(0077);
require rtrim($argv[1], '/\\') . '/wp-load.php';
$db = db();
$db->exec("SET time_zone = '+00:00'");
$out = gzopen($argv[2], 'wb9');
if (!$out) {
    throw new RuntimeException('Could not create private database backup.');
}
try {
    $db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $db->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
    gzwrite($out, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSET time_zone='+00:00';\nSET FOREIGN_KEY_CHECKS=0;\n");
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new RuntimeException('Unexpected table name.');
        }
        $create = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        gzwrite($out, "DROP TABLE IF EXISTS `$table`;\n$create;\n");
        foreach ($db->query("SELECT * FROM `$table`") as $row) {
            $values = array_map(fn($value) => $value === null ? 'NULL' : $db->quote((string) $value), array_values($row));
            gzwrite($out, "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n");
        }
    }
    gzwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $error;
} finally {
    gzclose($out);
}
chmod($argv[2], 0600);
echo 'Database backup complete: ' . count($tables) . " tables.\n";
