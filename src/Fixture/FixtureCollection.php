<?php

namespace Drupal\workshop\Fixture;

use Drupal\workshop\Template\Template;
use Labcoat\Fixture\Definition;
use Symfony\Component\Yaml\Yaml;

class FixtureCollection implements FixtureCollectionInterface, \Countable, \IteratorAggregate
{
    private $fixtures = [];

    public static function forTemplate(Template $template)
    {
        if ($template->hasFixtures()) {
            return static::fromFile($template, $template->getFixturesPath());
        } else {
            return new static();
        }
    }

    public static function fromFile(Template $template, $path)
    {
        $collection = new static();
        foreach (Definition::fromFile($path) as $name => $fixture) {
            $collection->fixtures[$name] = new Fixture($template, $fixture);
        }
        return $collection;
    }

    public function count()
    {
        return count($this->getFixtures());
    }

    public function get($name)
    {
        if (!isset($this->fixtures[$name])) {
            throw new \Exception("Unknown fixture: $name");
        }
        return $this->fixtures[$name];
    }

    public function getFixtures()
    {
        return $this->fixtures;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getFixtures());
    }
}