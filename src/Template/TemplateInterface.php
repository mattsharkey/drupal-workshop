<?php

namespace Drupal\workshop\Template;

use Drupal\workshop\Fixture\FixtureInterface;
use Drupal\workshop\Theme\ThemeInterface;

interface TemplateInterface
{
    /**
     * @param string $name
     * @return FixtureInterface
     */
    public function getFixture($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return ThemeInterface
     */
    public function getTheme();
}