<?php

namespace Drupal\workshop\Fixture;

use Drupal\workshop\Template\TemplateInterface;

interface FixtureInterface extends \Labcoat\Fixture\FixtureInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @return TemplateInterface
     */
    public function getTemplate();
}