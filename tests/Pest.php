<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

$feature = pest()->extend(TestCase::class)->in('Feature');

if (in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    $feature->use(RefreshDatabase::class);
} else {
    $feature->beforeEach(function (): void {
        test()->markTestSkipped('Los tests de Feature requieren la extensión pdo_sqlite.');
    });
}

pest()->extend(TestCase::class)
    ->in('Unit');
