<?php

namespace Drupal\workshop\Controller;

use CreativeServices\Workshop\Template\TemplateInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Url;
use Drupal\workshop\Theme\Theme;
use Drupal\workshop\Theme\ThemeInterface;
use CreativeServices\Fixtures\File\FixtureCollectionDirectory;
use CreativeServices\Fixtures\Twig\FixtureFunction;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class WorkshopController extends ControllerBase
{
    /**
     * @var TwigEnvironment
     */
    private $twig;

    public static function create(ContainerInterface $container)
    {
        /** @var TwigEnvironment $twig */
        $twig = $container->get('twig');
        return new static($twig);
    }

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function viewIndex(Request $request, $theme)
    {
        $theme = $this->getTheme($theme);
        $templates = iterator_to_array($theme->getTemplates());
        return $this->makeTemplateTable($theme, $templates);
    }

    public function viewTemplate(Request $request, $theme)
    {
        if (!$request->query->has('template')) {
            throw new \Exception("Unspecified template");
        }
        $theme = $this->getTheme($theme);
        $templates = $theme->getTemplates();
        $twig = clone $this->twig;
        $fixtures = new FixtureCollectionDirectory($theme->getFixturesPath());
        $twig->addFunction(new FixtureFunction($fixtures));
        $template = $templates->getTemplate($request->query->get('template'));
        $templateName = $this->makeTemplateName($theme, $template);
        $context = _template_preprocess_default_variables();
        $rendered = $twig->render($templateName, $context);
        return $this->makeHtmlResponse($rendered, $theme->getWorkshopLibraries());
    }

    private function formatTime($time) {
        return \Drupal::service('date.formatter')->format($time);
    }

    /**
     * @param $name
     * @return ThemeInterface
     */
    private function getTheme($name)
    {
        return new Theme($name);
    }

    private function makeHtmlResponse($content, $libraries = [])
    {
        $renderer = \Drupal::service('renderer');
        $processor = \Drupal::service('html_response.attachments_processor');

        $replaceRendered = function () use ($content) {
            return $content;
        };

        $html = [
            '#type' => 'html',
            '#cache' => ['max-age' => 0],
            'page' => [
                '#title' => 'Workshop',
                '#markup' => $content,
                '#post_render' => [$replaceRendered],
            ]
        ];
        if ($libraries) {
            $html['page']['#attached']['library'] = $libraries;
        }

        system_page_attachments($html['page']);
        $renderer->renderRoot($html);

        $response = new HtmlResponse();
        $response->setContent($html);

        $response = $processor->processAttachments($response);
        return $response;
    }

    private function makeTemplateLink(ThemeInterface $theme, TemplateInterface $template)
    {
        $params = [
            'theme' => $theme->getName(),
            'template' => $template->getName(),
        ];
        return Url::fromRoute('workshop.template', $params);
    }

    private function makeTemplateName(ThemeInterface $theme, TemplateInterface $template)
    {
        $segments = [$theme->getName(), $theme->getWorkshopDirectory(), $template->getName()];
        return '@' . implode(DIRECTORY_SEPARATOR, array_filter($segments));
    }

    /**
     * @param TemplateInterface[] $templates
     * @return array
     */
    public function makeTemplateTable(ThemeInterface $theme, array $templates)
    {
        $cmp = function (TemplateInterface $a, TemplateInterface $b)  {
            return strnatcasecmp($a->getName(), $b->getName());
        };
        usort($templates, $cmp);
        $table = [
            '#type' => 'table',
            '#header' => [
                [
                    'data' => 'Template',
                ],
                [
                    'data' => 'Modified',
                ],
            ],
            '#rows' => [],
        ];
        foreach ($templates as $template) {
            $table['#rows'][] = [
                'data' => [
                    [
                        'data' => [
                            '#type' => 'link',
                            '#title' => $template->getName(),
                            '#url' => $this->makeTemplateLink($theme, $template),
                        ],
                    ],
                    [
                        'data' => $this->formatTime($template->getTime()),
                    ],
                ],
            ];
        }
        return $table;
    }
}