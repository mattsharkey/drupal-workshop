<?php

namespace Drupal\workshop\Template;

use Drupal\workshop\Fixture\FixtureCollection;
use Drupal\workshop\Fixture\FixtureCollectionInterface;
use Drupal\workshop\Theme\ThemeInterface;

class Template implements TemplateInterface
{
    const EXTENSION = '.twig';

    private $file;

    /**
     * @var FixtureCollectionInterface
     */
    private $fixtures;

    private $name;

    private $theme;

    public function __construct(ThemeInterface $theme, $name, \SplFileInfo $file)
    {
        $this->theme = $theme;
        $this->name = $name;
        $this->file = $file;
        $this->fixtures = FixtureCollection::forTemplate($this);
    }

    public function __debugInfo()
    {
        return [
            'name' => $this->name,
            'file' => $this->file,
            'fixtures' => $this->getFixturesPath(),
            'hasFixtures' => $this->hasFixtures(),
        ];
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFixture($name)
    {
        return $this->fixtures->get($name);
    }

    public function getFixtures()
    {
        return FixtureCollection::forTemplate($this);
    }

    public function getFixturesPath()
    {
        return $this->file->getPath() . DIRECTORY_SEPARATOR . $this->file->getBasename(static::EXTENSION) . '.yml';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function hasFixtures()
    {
        return is_file($this->getFixturesPath());
    }
}