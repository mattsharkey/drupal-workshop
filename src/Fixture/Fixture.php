<?php

namespace Drupal\workshop\Fixture;

use Drupal\workshop\Template\TemplateInterface;

class Fixture implements FixtureInterface
{
    private $config = [];

    private $context;

    private $name;

    private $region;

    private $template;

    public function __construct(TemplateInterface $template, array $config = [])
    {
        $this->template = $template;
        if (isset($config['name'])) {
            $this->name = $config['name'];
            unset($config['name']);
        }
        if (isset($config['region'])) {
            $this->region = $config['region'];
            unset($config['region']);
        } else {
            $this->region = $template->getTheme()->getDefaultRegion();
        }
        if (isset($config['context'])) {
            $this->context = $config['context'];
            unset($config['context']);
        }
        $this->config = $config;
    }

    public function getContext()
    {
        return (array)$this->context + _template_preprocess_default_variables();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @return TemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function getTemplateName()
    {
        return '@' . $this->getThemeName() . DIRECTORY_SEPARATOR . $this->getTemplate()->getName();
    }

    public function getTheme()
    {
        return $this->getTemplate()->getTheme();
    }

    public function getThemeName()
    {
        return $this->getTheme()->getName();
    }
}