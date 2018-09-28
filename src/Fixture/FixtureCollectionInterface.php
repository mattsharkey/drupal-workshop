<?php

namespace Drupal\workshop\Fixture;

interface FixtureCollectionInterface
{
    public function get($name);

    public function getFixtures();
}