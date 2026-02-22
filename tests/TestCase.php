<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Wibiesana\Padi\Core\Env;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load test environment
        Env::load(dirname(__DIR__) . '/.env');

        // You could run migrations here for testing
    }
}
