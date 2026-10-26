<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_unknown_routes_are_json_and_no_web_view_is_exposed(): void
    {
        $this->get('/')
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json');
    }
}
