<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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
    }
}
