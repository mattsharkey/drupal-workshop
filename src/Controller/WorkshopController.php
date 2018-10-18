<?php

namespace Drupal\workshop\Controller;

use CreativeServices\Fixtures\File\FixtureCollectionDirectory;
use CreativeServices\Fixtures\Twig\FixtureFunction;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Url;
use Drupal\workshop\Template\TemplateCollection;
use Drupal\workshop\Template\TemplateInterface;
use Drupal\workshop\Theme\Theme;
use Drupal\workshop\Theme\ThemeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $templates = new TemplateCollection($theme);
        $templates->sort($request->query->get('order'), $request->query->get('sort'));

        $table = [
            '#type' => 'table',
            '#header' => [
                [
                    'data' => 'Template',
                    'field' => 'template',
                ],
                [
                    'data' => 'Modified',
                    'field' => 'time',
                    'sort' => 'desc',
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
                            '#title' => $template,
                            '#url' => $this->makeTemplateLink($theme, $template),
                        ],
                    ],
                    [
                        'data' => $this->formatInterval($template->getTime()) . ' ago',
                    ],
                ],
            ];
        }
        return $table;
    }

    /**
     * @param Request $request
     * @param string $theme
     * @return HtmlResponse|Response
     * @throws \Exception
     */
    public function viewTemplate(Request $request, $theme)
    {
        if (!$request->query->has('template')) {
            throw new \Exception("Unspecified template");
        }
        $theme = $this->getTheme($theme);
        $twig = clone $this->twig;
        $fixtures = new FixtureCollectionDirectory($theme->getFixturesPath());
        $twig->addFunction(new FixtureFunction($fixtures));
        $template = $this->makeTemplateName($theme, $request->query->get('template'));
        $context = _template_preprocess_default_variables();
        try {
            $rendered = $twig->render($template, $context);
            return $this->makeHtmlResponse($rendered, $theme->getWorkshopLibraries());
        } catch (\Twig_Error_Syntax $e) {
            return $this->makeSyntaxErrorResponse($e);
        } catch (\Twig_Error $e) {
            return $this->makeErrorResponse($e);
        }
    }

    private function formatInterval($time) {
        $interval = time() - $time;
        return \Drupal::service('date.formatter')->formatInterval($interval);
    }

    /**
     * @param $name
     * @return ThemeInterface
     */
    private function getTheme($name)
    {
        return new Theme($name);
    }

    private function makeErrorResponse(\Twig_Error $error) {
        $context = $error->getSourceContext();
        $html = <<<HTML
<!DOCTYPE html>
<html>

<title>Error</title>

<h1>{$error->getMessage()}</h1>

<dl>
    <dt>Template</dt>
    <dd>{$context->getName()}</dd>
    <dt>File</dt>
    <dd>{$context->getPath()}</dd>
</dl>

<pre style="background-color:#f0f0f0">{$context->getCode()}</pre>

<p>{$error->getFile()}, line {$error->getLine()}</p>

</html>
HTML;
        return new Response($html, 500);
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

    private function makeTemplateName(ThemeInterface $theme, $template)
    {
        $segments = [$theme->getName(), $theme->getWorkshopDirectory(), $template];
        return '@' . implode(DIRECTORY_SEPARATOR, array_filter($segments));
    }

    private function makeSyntaxErrorResponse(\Twig_Error_Syntax $error) {
        $context = $error->getSourceContext();
        $html = <<<HTML
<!DOCTYPE html>
<html>

<title>Syntax error</title>

<h1>Syntax error</h1>

<p>{$error->getMessage()}</p>

<dl>
    <dt>Template</dt>
    <dd>{$context->getName()}</dd>
    <dt>File</dt>
    <dd>{$context->getPath()}</dd>
</dl>

<pre style="background-color:#f0f0f0">{$context->getCode()}</pre>

<p>{$error->getFile()}, line {$error->getLine()}</p>

</html>
HTML;
        return new Response($html, 500);
    }
}