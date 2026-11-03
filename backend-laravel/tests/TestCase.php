<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function prepareUrlForRequest($uri)
    {
        if (is_string($uri) && str_starts_with($uri, '/')) {
            return $uri;
        }

        return parent::prepareUrlForRequest($uri);
    }
}
