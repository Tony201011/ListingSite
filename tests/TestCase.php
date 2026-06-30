<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Guard against a stale PDO transaction left by a previous test whose
        // RefreshDatabase cleanup callback did not fire (e.g. because the test
        // itself failed during setUp, so the callback was never registered).
        // Without this, Connection::beginTransaction() would throw
        // "There is already an active transaction" when $transactions is 0 but
        // the underlying PDO object still owns an uncommitted transaction.
        foreach (RefreshDatabaseState::$inMemoryConnections as $storedPdo) {
            if ($storedPdo instanceof \PDO && $storedPdo->inTransaction()) {
                try {
                    $storedPdo->rollBack();
                } catch (\Throwable) {
                    // Nothing we can do; at least we tried to clean up.
                }
            }
        }

        parent::setUp();
        $this->withoutVite();
        $this->registerSqliteCompatFunctions();
    }

    /**
     * Register MySQL-compatible user-defined functions on the SQLite test connection
     * so that production queries using SUBSTRING_INDEX, LEAST, GREATEST, and
     * haversine trig functions can run without skipping in the test suite.
     */
    protected function registerSqliteCompatFunctions(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        $pdo = $connection->getPdo();

        // SUBSTRING_INDEX(str, delim, count): MySQL string-splitting function.
        // Positive count returns the first N parts; negative returns the last |N| parts.
        $pdo->sqliteCreateFunction('SUBSTRING_INDEX', function ($str, $delim, $count) {
            if ($str === null || $delim === null || $count === null) {
                return null;
            }
            $parts = explode((string) $delim, (string) $str);
            if ($count > 0) {
                return implode((string) $delim, array_slice($parts, 0, (int) $count));
            }
            if ($count < 0) {
                return implode((string) $delim, array_slice($parts, (int) $count));
            }

            return '';
        }, 3);

        // LEAST / GREATEST: return the smallest / largest non-null argument.
        $pdo->sqliteCreateFunction('LEAST', function () {
            $args = array_filter(func_get_args(), fn ($v) => $v !== null);

            return empty($args) ? null : min($args);
        }, -1);

        $pdo->sqliteCreateFunction('GREATEST', function () {
            $args = array_filter(func_get_args(), fn ($v) => $v !== null);

            return empty($args) ? null : max($args);
        }, -1);

        // Trigonometric / math functions used in the haversine distance formula.
        // SQLite may not include these unless compiled with SQLITE_ENABLE_MATH_FUNCTIONS.
        if (! $this->sqliteSupportsMathFunctions($pdo)) {
            $pdo->sqliteCreateFunction('acos', 'acos', 1);
            $pdo->sqliteCreateFunction('cos', 'cos', 1);
            $pdo->sqliteCreateFunction('sin', 'sin', 1);
            $pdo->sqliteCreateFunction('radians', fn ($deg) => deg2rad((float) $deg), 1);
        }
    }

    /**
     * Detect whether the SQLite build already provides native math functions.
     */
    private function sqliteSupportsMathFunctions(\PDO $pdo): bool
    {
        try {
            $pdo->query('SELECT acos(1.0)');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
