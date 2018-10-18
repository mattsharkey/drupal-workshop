<?php

namespace Drupal\workshop\Theme;

use Drupal\Core\Extension\Extension;
use Symfony\Component\Yaml\Yaml;

class Theme implements ThemeInterface
{
    private $info;

    private $name;

    /**
     * @var Extension
     */
    private $theme;

    public function __construct($name)
    {
        $this->name = $name;
        $themes = system_list('theme');
        if (!isset($themes[$name])) {
            throw new \OutOfBoundsException("Unknown theme: $name");
        }
        $this->theme = $themes[$name];
    }

    public function getFixturesPath()
    {
        $info = $this->getWorkshopInfo();
        if (isset($info['fixtures'])) {
            return $this->makePath($info['fixtures']);
        } else {
            return $this->getWorkshopPath();
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTemplatesPath()
    {
        return $this->makePath($this->getTemplatesDirectory());
    }

    public function getWorkshopDirectory()
    {
        $info = $this->getWorkshopInfo();
        return isset($info['directory']) ? $info['directory'] : null;
    }

    public function getWorkshopLibraries()
    {
        $libraries = [];
        $info = $this->getWorkshopInfo();
        if (isset($info['libraries'])) {
            $libraries = (array)$info['libraries'];
        }
        return $libraries;
    }

    public function getWorkshopPath()
    {
        return $this->makePath($this->getTemplatesDirectory(), $this->getWorkshopDirectory());
    }

    public function makeTemplateName($name)
    {
        return '@' . $this->getName() . DIRECTORY_SEPARATOR . $name;
    }

    private function getInfo()
    {
        if (!isset($this->info)) {
            $this->info = Yaml::parse(file_get_contents($this->getInfoPath()));
        }
        return $this->info;
    }

    private function getInfoPath()
    {
        return implode(DIRECTORY_SEPARATOR, [DRUPAL_ROOT, $this->theme->getPathname()]);
    }

    private function getPath()
    {
        return $this->theme->getPath();
    }

    private function getTemplatesDirectory()
    {
        return 'templates';
    }

    private function getWorkshopInfo()
    {
        $info = $this->getInfo();
        return isset($info['workshop']) ? $info['workshop'] : [];
    }

    private function makePath()
    {
        $segments = func_get_args();
        array_unshift($segments, $this->getPath());
        array_unshift($segments, DRUPAL_ROOT);
        return implode(DIRECTORY_SEPARATOR, array_filter($segments));
    }
}