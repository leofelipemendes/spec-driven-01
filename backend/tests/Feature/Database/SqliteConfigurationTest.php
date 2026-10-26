<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SqliteConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sqlite_foreign_keys_are_enabled_and_enforced(): void
    {
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);

        DB::statement('CREATE TABLE fk_test_parents (id INTEGER PRIMARY KEY)');
        DB::statement('CREATE TABLE fk_test_children (parent_id INTEGER REFERENCES fk_test_parents(id))');

        $this->expectException(QueryException::class);
        DB::table('fk_test_children')->insert(['parent_id' => 999]);
    }
}
