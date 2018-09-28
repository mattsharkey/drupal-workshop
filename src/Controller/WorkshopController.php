<?php

namespace Drupal\workshop\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Url;
use Drupal\workshop\Fixture\FixtureInterface;
use Drupal\workshop\Template\TemplateInterface;
use Drupal\workshop\Theme\Theme;
use Drupal\workshop\Theme\ThemeInterface;
use Labcoat\Environment\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class WorkshopController extends ControllerBase
{
    /**
     * @var Environment
     */
    private $labcoat;

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
        $this->labcoat = new Environment($twig);
    }

    public function index(Request $request, $theme)
    {
        $sortField = $request->query->get('order');
        $sortOrder = $request->query->get('sort');
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
                'Fixtures',
            ],
            '#rows' => [],
        ];
        $rows = $this->getComponentRows($theme, $sortField, $sortOrder);
        foreach ($rows as $row) {
            $table['#rows'][] = [
                'data' => [
                    [
                        'data' => $row['name'],
                    ],
                    [
                        'data' => $this->formatInterval($row['time']) . ' ago',
                    ],
                    [
                        'data' => $row['fixtures'],
                    ],
                ],
            ];
        }
        return $table;
    }

    public function component(Request $request, $theme)
    {
        if (!$request->query->has('template')) {
            throw new \Exception("Unspecified template");
        }
        if (!$request->query->has('fixture')) {
            throw new \Exception("Unspecified fixture");
        }
        $theme = $this->getTheme($theme);
        $template = $theme->getTemplate($request->query->get('template'));
        $fixture = $template->getFixture($request->query->get('fixture'));
        $rendered = $this->labcoat->render($fixture);

        $html = [
            '#type' => 'html',
            'page' => [
                '#type' => 'page',
                '#title' => 'Workshop',
            ],
        ];

        foreach ($theme->getRegions() as $name => $desc) {
            $html['page'][$name] = [
                '#theme_wrappers' => ['region'],
                '#region' => $name,
            ];
            if ($name === $fixture->getRegion()) {
                $html['page'][$name]['#markup'] = $rendered;
            } else {
                $html['page'][$name]['#children'] = [
                    '#theme' => 'workshop_region_placeholder',
                    '#region' => $name,
                    '#label' => $desc,
                    '#attached' => [
                        'library' => ['workshop/workshop']
                    ]
                ];
            }
        }

        $renderer = \Drupal::service('renderer');
        $processor = \Drupal::service('html_response.attachments_processor');

        system_page_attachments($html['page']);
        $renderer->renderRoot($html);
        $response = new HtmlResponse($html);
        $response = $processor->processAttachments($response);

        return $response;
    }

    private function formatInterval($time) {
        $interval = time() - $time;
        return \Drupal::service('date.formatter')->formatInterval($interval);
    }

    private function getComponentRows($theme, $sortField = null, $sortOrder = null)
    {
        $theme = $this->getTheme($theme);
        $rows = [];
        foreach ($theme->getTemplates() as $template) {
            if (!$template->hasFixtures()) {
                continue;
            }
            $rows[] = [
                'name' => $template->getName(),
                'time' => $template->getFile()->getMTime(),
                'fixtures' => $this->makeFixtureLinks($template),
            ];
        }
        // Sort rows
        return $rows;
    }

    /**
     * @param $name
     * @return ThemeInterface
     */
    private function getTheme($name)
    {
        return new Theme($name);
    }

    private function makeComponentLink(FixtureInterface $fixture)
    {
        $params = [
            'theme' => $fixture->getTheme()->getName(),
            'template' => $fixture->getTemplate()->getName(),
            'fixture' => $fixture->getName(),
        ];
        return Url::fromRoute('workshop.component', $params);
    }

    private function makeFixtureLinks(TemplateInterface $template)
    {
        $list = [
            '#theme' => 'item_list',
            '#items' => [],
        ];
        foreach ($template->getFixtures() as $fixture) {
            $link = [
                '#type' => 'link',
                '#title' => $fixture->getName(),
                '#url' => $this->makeComponentLink($fixture),
            ];
            $list['#items'][] = $link;
        }
        return $list;
    }
}