<?php

namespace Drupal\workshop\Template;

use Drupal\workshop\Theme\ThemeInterface;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Eloquent\Pathogen\PathInterface;

class TemplateCollection implements TemplateCollectionInterface, \IteratorAggregate
{
    private $templates;

    private $theme;

    public function __construct(ThemeInterface $theme)
    {
        $this->theme = $theme;
        $this->findTemplates();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->templates);
    }

    public function sort($field, $order)
    {
        $cmp = function (TemplateInterface $a, TemplateInterface $b) use ($field, $order) {
            switch ($field) {
                case 'Modified':
                    $aField = $a->getTime();
                    $bField = $b->getTime();
                    break;
                case 'Template':
                default:
                    $aField = $a->getName();
                    $bField = $b->getName();
            }
            $ret = strnatcasecmp($aField, $bField);
            return $order == 'desc' ? (0 - $ret) : $ret;
        };
        return usort($this->templates, $cmp);
    }

    private function findTemplates()
    {
        $this->templates = [];
        $dirPath = $this->getTemplatesDirectory();
        if (is_dir($dirPath)) {
            $dir = FileSystemPath::fromString(realpath($dirPath));
            foreach ($this->makeTemplatesIterator($dir) as $file) {
                $path = FileSystemPath::fromString($file->getRealpath());
                $this->templates[] = new Template($path->relativeTo($dir)->string(), $file);
            }
        }
    }

    private function getTemplatesDirectory()
    {
        return $this->theme->getWorkshopPath();
    }

    /**
     * @param \Iterator $files
     * @return \RegexIterator
     */
    private function makeTemplateFilter(\Iterator $files)
    {
        return new \RegexIterator($files, '|\.twig$|');
    }

    /**
     * @var PathInterface $path
     * @return \RegexIterator
     */
    private function makeTemplatesIterator(PathInterface $path)
    {
        $dir = new \RecursiveDirectoryIterator($path->string(), \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::LEAVES_ONLY);
        return $this->makeTemplateFilter($files);
    }
}