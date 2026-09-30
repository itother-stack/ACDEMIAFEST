<?php
// =====================================================================
//  includes/schema.php — Database schema + automatic, non-destructive upgrade
//
//  - Creates any missing table
//  - Adds any missing column
//  - Fixes incompatible column types (e.g. DATE / ENUM -> VARCHAR)
//  - Makes NOT NULL columns without a default safe for inserts
//  Existing data is never deleted.
// =====================================================================

const SCHEMA_VERSION = 6;

/**
 * Column format: name => [category, full definition]
 * Categories are used to decide whether an existing column is compatible.
 */
function schema_tables() {
    return [
        'app_meta' => [
            'columns' => [
                'meta_key'   => ['string', "VARCHAR(100) NOT NULL"],
                'meta_value' => ['text',   "TEXT NULL"],
            ],
            'primary' => 'meta_key',
            'keys'    => [],
        ],
        'admins' => [
            'columns' => [
                'id'            => ['int',    "INT UNSIGNED NOT NULL AUTO_INCREMENT"],
                'username'      => ['string', "VARCHAR(100) NOT NULL"],
                'password_hash' => ['string', "VARCHAR(255) NOT NULL"],
                'created_at'    => ['time',   "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"],
            ],
            'primary' => 'id',
            'keys'    => ["UNIQUE KEY `uniq_username` (`username`)"],
        ],
        'events' => [
            'columns' => [
                'id'             => ['int',    "INT UNSIGNED NOT NULL AUTO_INCREMENT"],
                'event_name'     => ['string', "VARCHAR(255) NOT NULL"],
                'event_date'     => ['string', "VARCHAR(100) NULL DEFAULT NULL"],
                'event_venue'    => ['string', "VARCHAR(500) NULL DEFAULT NULL"],
                'contact_phone'  => ['string', "VARCHAR(50) NULL DEFAULT NULL"],
                'offer_zone'     => ['text',   "TEXT NULL"],
                'expert_details' => ['text',   "TEXT NULL"],
                'logo_path'      => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'photo_path'     => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'event_type'     => ['string', "VARCHAR(100) NULL DEFAULT NULL"],
                'city'           => ['string', "VARCHAR(150) NULL DEFAULT NULL"],
                'event_time'     => ['string', "VARCHAR(100) NULL DEFAULT NULL"],
                'description'    => ['text',   "TEXT NULL"],
                'seat_limit'     => ['string', "VARCHAR(50) NULL DEFAULT NULL"],
                'sort_order'     => ['int',    "INT NOT NULL DEFAULT 0"],
                'is_active'      => ['int',    "TINYINT(1) NOT NULL DEFAULT 1"],
                // Custom Entry Pass template (per event) — background image + where the
                // student's name, registration number and QR code are placed on it (% of width/height)
                'entry_pass_bg'      => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'entry_pass_name_x'  => ['int',    "TINYINT UNSIGNED NULL DEFAULT NULL"],
                'entry_pass_name_y'  => ['int',    "TINYINT UNSIGNED NULL DEFAULT NULL"],
                'entry_pass_reg_x'   => ['int',    "TINYINT UNSIGNED NULL DEFAULT NULL"],
                'entry_pass_reg_y'   => ['int',    "TINYINT UNSIGNED NULL DEFAULT NULL"],
                'entry_pass_qr_x'    => ['int',    "TINYINT UNSIGNED NULL DEFAULT NULL"],
                'entry_pass_qr_y'    => ['int',    "TINYINT UNSIGNED NULL DEFAULT NULL"],
                'entry_pass_qr_size' => ['int',    "TINYINT UNSIGNED NULL DEFAULT NULL"],
                'created_at'     => ['time',   "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"],
            ],
            'primary' => 'id',
            'keys'    => ["KEY `idx_active` (`is_active`)"],
        ],
        'agents' => [
            'columns' => [
                'id'         => ['int',    "INT UNSIGNED NOT NULL AUTO_INCREMENT"],
                'event_id'   => ['int',    "INT UNSIGNED NULL DEFAULT NULL"],
                'agent_name' => ['string', "VARCHAR(255) NOT NULL DEFAULT ''"],
            ],
            'primary' => 'id',
            'keys'    => ["KEY `idx_event` (`event_id`)"],
        ],
        'student_registrations' => [
            'columns' => [
                'id'              => ['int',    "INT UNSIGNED NOT NULL AUTO_INCREMENT"],
                'event_id'        => ['int',    "INT UNSIGNED NULL DEFAULT NULL"],
                'interested_in'   => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'student_name'    => ['string', "VARCHAR(255) NOT NULL DEFAULT ''"],
                'gender'          => ['string', "VARCHAR(20) NULL DEFAULT NULL"],
                'mobile_number'   => ['string', "VARCHAR(20) NOT NULL DEFAULT ''"],
                'whatsapp_number' => ['string', "VARCHAR(20) NULL DEFAULT NULL"],
                'parent_mobile'   => ['string', "VARCHAR(20) NULL DEFAULT NULL"],
                'email_id'        => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'city'            => ['string', "VARCHAR(150) NULL DEFAULT NULL"],
                'courses'         => ['text',   "TEXT NULL"],
                'ref1_name'       => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'ref1_mobile'     => ['string', "VARCHAR(20) NULL DEFAULT NULL"],
                'ref2_name'       => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'ref2_mobile'     => ['string', "VARCHAR(20) NULL DEFAULT NULL"],
                'source'          => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'status'          => ['string', "VARCHAR(20) NOT NULL DEFAULT 'Completed'"],
                'wa_status'       => ['string', "VARCHAR(20) NOT NULL DEFAULT 'Pending'"],
                'wa_sent_at'      => ['time',   "DATETIME NULL DEFAULT NULL"],
                'created_at'      => ['time',   "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"],
                'last_updated'    => ['time',   "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"],
            ],
            'primary' => 'id',
            'keys'    => ["KEY `idx_event` (`event_id`)", "KEY `idx_mobile` (`mobile_number`)", "KEY `idx_created` (`created_at`)"],
        ],
        'gallery_photos' => [
            'columns' => [
                'id'         => ['int',    "INT UNSIGNED NOT NULL AUTO_INCREMENT"],
                'image_path' => ['string', "VARCHAR(255) NOT NULL DEFAULT ''"],
                'title'      => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'caption'    => ['string', "VARCHAR(255) NULL DEFAULT NULL"],
                'tag'        => ['string', "VARCHAR(100) NULL DEFAULT NULL"],
                'sort_order' => ['int',    "INT NOT NULL DEFAULT 0"],
                'is_active'  => ['int',    "TINYINT(1) NOT NULL DEFAULT 1"],
                'created_at' => ['time',   "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"],
            ],
            'primary' => 'id',
            'keys'    => ["KEY `idx_active_sort` (`is_active`, `sort_order`)"],
        ],
        'hero_banners' => [
            'columns' => [
                'id'         => ['int',    "INT UNSIGNED NOT NULL AUTO_INCREMENT"],
                'image_path' => ['string', "VARCHAR(255) NOT NULL DEFAULT ''"],
                'sort_order' => ['int',    "INT NOT NULL DEFAULT 0"],
                'is_active'  => ['int',    "TINYINT(1) NOT NULL DEFAULT 1"],
                'created_at' => ['time',   "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"],
            ],
            'primary' => 'id',
            'keys'    => ["KEY `idx_active_sort` (`is_active`, `sort_order`)"],
        ],
    ];
}

/** CREATE TABLE statement for a table definition */
function schema_create_sql($table, array $def) {
    $lines = [];
    foreach ($def['columns'] as $col => $c) $lines[] = "  `$col` {$c[1]}";
    $lines[] = "  PRIMARY KEY (`{$def['primary']}`)";
    foreach ($def['keys'] as $k) $lines[] = "  $k";
    return "CREATE TABLE IF NOT EXISTS `$table` (\n" . implode(",\n", $lines) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
}

/** Map a MySQL DATA_TYPE to our category */
function schema_category($dataType) {
    $t = strtolower($dataType);
    if (in_array($t, ['varchar', 'char'], true)) return 'string';
    if (in_array($t, ['text', 'tinytext', 'mediumtext', 'longtext'], true)) return 'text';
    if (in_array($t, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'], true)) return 'int';
    if (in_array($t, ['datetime', 'timestamp'], true)) return 'time';
    return $t; // date, enum, set, decimal, ... -> treated as incompatible
}

/** Length of VARCHAR(n) in a definition, or null */
function schema_varchar_len($typeOrDef) {
    return preg_match('/varchar\((\d+)\)/i', $typeOrDef, $m) ? (int)$m[1] : null;
}

/** Part of our definition after the type, e.g. "NOT NULL DEFAULT CURRENT_TIMESTAMP" */
function schema_def_suffix($def) {
    return trim(preg_replace('/^\s*[A-Z]+(\(\d+(,\d+)?\))?(\s+UNSIGNED)?/i', '', $def));
}

/**
 * Run all upgrades. Returns a log: [ [type, message], ... ] where type is ok|warn|err
 */
function run_migrations(PDO $pdo) {
    $log = [];
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();

    foreach (schema_tables() as $table => $def) {
        // 1. Create table
        try {
            $exists = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $pdo->quote($dbName) . " AND TABLE_NAME = " . $pdo->quote($table))->fetchColumn();
            if (!$exists) {
                $pdo->exec(schema_create_sql($table, $def));
                $log[] = ['ok', "Created table `$table`"];
                continue;
            }
        } catch (PDOException $e) {
            $log[] = ['err', "Could not create table `$table`: " . $e->getMessage()];
            continue;
        }

        // 2. Inspect existing columns
        $st = $pdo->prepare("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
                             FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $st->execute([$dbName, $table]);
        $existing = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) $existing[strtolower($r['COLUMN_NAME'])] = $r;

        foreach ($def['columns'] as $col => [$cat, $ourDef]) {
            if ($col === $def['primary']) continue;
            try {
                // 2a. Missing column -> add
                if (!isset($existing[$col])) {
                    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $ourDef");
                    $log[] = ['ok', "Added column `$table`.`$col`"];
                    continue;
                }
                $ex = $existing[$col];
                $exCat = schema_category($ex['DATA_TYPE']);
                $noDefault = $ex['IS_NULLABLE'] === 'NO' && $ex['COLUMN_DEFAULT'] === null && stripos($ex['EXTRA'], 'auto_increment') === false;
                $newDef = null;

                if ($exCat !== $cat && !($cat === 'text' && $exCat === 'string') && !($cat === 'string' && $exCat === 'text')) {
                    // Incompatible type (e.g. DATE, ENUM) -> convert to our type
                    $newDef = $ourDef;
                } elseif ($cat === 'string' && $exCat === 'string'
                          && ($ourLen = schema_varchar_len($ourDef)) && ($exLen = schema_varchar_len($ex['COLUMN_TYPE'])) && $exLen < $ourLen) {
                    // Too short -> enlarge (never shrink)
                    $newDef = $ourDef;
                } elseif ($noDefault && $cat === 'time') {
                    // Date column without default -> add CURRENT_TIMESTAMP default, keep its type
                    $newDef = $ex['COLUMN_TYPE'] . ' ' . schema_def_suffix($ourDef);
                } elseif ($noDefault && $cat === 'text') {
                    // TEXT NOT NULL cannot have a default -> allow NULL
                    $newDef = $ex['COLUMN_TYPE'] . ' NULL';
                } elseif ($noDefault && stripos($ourDef, 'DEFAULT') !== false) {
                    // NOT NULL without default -> give it our default/NULL, keep its type (safe with foreign keys)
                    $newDef = $ex['COLUMN_TYPE'] . ' ' . schema_def_suffix($ourDef);
                }

                if ($newDef !== null) {
                    $pdo->exec("ALTER TABLE `$table` MODIFY COLUMN `$col` $newDef");
                    $log[] = ['ok', "Updated column `$table`.`$col` ({$ex['COLUMN_TYPE']} -> " . strtok($newDef, ' ') . ")"];
                }
            } catch (PDOException $e) {
                $log[] = ['err', "Column `$table`.`$col`: " . $e->getMessage()];
            }
        }

        // 3. Extra columns (from older code) that are NOT NULL without default would break inserts -> allow NULL
        foreach ($existing as $name => $ex) {
            if (isset($def['columns'][$name])) continue;
            $noDefault = $ex['IS_NULLABLE'] === 'NO' && $ex['COLUMN_DEFAULT'] === null
                         && stripos($ex['EXTRA'], 'auto_increment') === false && stripos($ex['EXTRA'], 'generated') === false;
            if (!$noDefault) continue;
            try {
                $pdo->exec("ALTER TABLE `$table` MODIFY COLUMN `{$ex['COLUMN_NAME']}` {$ex['COLUMN_TYPE']} NULL DEFAULT NULL");
                $log[] = ['ok', "Made old column `$table`.`{$ex['COLUMN_NAME']}` optional (it is not used by this version)"];
            } catch (PDOException $e) {
                $log[] = ['warn', "Old column `$table`.`{$ex['COLUMN_NAME']}` is required and could not be changed: " . $e->getMessage()];
            }
        }

        // 4. Indexes (best effort)
        foreach ($def['keys'] as $k) {
            if (!preg_match('/KEY `([^`]+)`/', $k, $m)) continue;
            try {
                $has = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = " . $pdo->quote($dbName)
                    . " AND TABLE_NAME = " . $pdo->quote($table) . " AND INDEX_NAME = " . $pdo->quote($m[1]))->fetchColumn();
                if (!$has) $pdo->exec("ALTER TABLE `$table` ADD $k");
            } catch (PDOException $e) {
                // An index is an optimisation only; ignore failures (e.g. duplicate data on a UNIQUE key)
            }
        }
    }

    $hasErrors = (bool)array_filter($log, function ($l) { return $l[0] === 'err'; });
    if (!$hasErrors) {
        try {
            $pdo->prepare("REPLACE INTO app_meta (meta_key, meta_value) VALUES ('schema_version', ?)")->execute([(string)SCHEMA_VERSION]);
        } catch (PDOException $e) {
            $log[] = ['err', 'Could not save schema version: ' . $e->getMessage()];
        }
    }
    return $log;
}

/**
 * Called on every request (one cheap query). Upgrades the database automatically
 * the first time a new version of the code runs.
 */
function ensure_schema(PDO $pdo) {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $v = (int)$pdo->query("SELECT meta_value FROM app_meta WHERE meta_key = 'schema_version'")->fetchColumn();
        if ($v >= SCHEMA_VERSION) return;
    } catch (PDOException $e) {
        // app_meta table does not exist yet -> run migrations
    }
    try {
        $gotLock = (int)$pdo->query("SELECT GET_LOCK('event_site_schema', 10)")->fetchColumn();
        if (!$gotLock) return;
        foreach (run_migrations($pdo) as [$type, $msg]) {
            if ($type !== 'ok') error_log('[SCHEMA] ' . $type . ': ' . $msg);
        }
        $pdo->query("SELECT RELEASE_LOCK('event_site_schema')");
    } catch (PDOException $e) {
        error_log('[SCHEMA] upgrade failed: ' . $e->getMessage());
    }
}
