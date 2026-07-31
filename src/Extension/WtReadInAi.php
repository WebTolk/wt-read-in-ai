<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.wtreadinai
 *
 * @copyright   Copyright (c) 2026 WebTolk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     __DEPLOY_VERSION__
 */

namespace Webtolk\Plugin\Content\WtReadInAi\Extension;

use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class WtReadInAi extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onContentAfterTitle'    => 'onContentAfterTitle',
            'onContentBeforeDisplay' => 'onContentBeforeDisplay',
            'onContentAfterDisplay'  => 'onContentAfterDisplay',
        ];
    }

    public function onContentAfterTitle(AfterTitleEvent $event): void
    {
        $html = $this->renderForContentEvent($event, 'onContentAfterTitle');

        if ($html !== '') {
            $event->addResult($html);
        }
    }

    public function onContentBeforeDisplay(BeforeDisplayEvent $event): void
    {
        $html = $this->renderForContentEvent($event, 'onContentBeforeDisplay');

        if ($html !== '') {
            $event->addResult($html);
        }
    }

    public function onContentAfterDisplay(AfterDisplayEvent $event): void
    {
        $html = $this->renderForContentEvent($event, 'onContentAfterDisplay');

        if ($html !== '') {
            $event->addResult($html);
        }
    }

    private function renderForContentEvent(ContentPrepareEvent $event, string $eventName): string
    {
        if ((int) $this->params->get('enabled', 1) !== 1) {
            return '';
        }

        if ((string) $this->params->get('render_event', 'onContentAfterDisplay') !== $eventName) {
            return '';
        }

        if (!$this->getApplication()->isClient('site')) {
            return '';
        }

        if ($event->getContext() !== 'com_content.article') {
            return '';
        }

        $article = $event->getItem();

        if (!isset($article->id) || (int) $article->id <= 0) {
            return '';
        }

        if (!$this->isAllowedForCategory($article)) {
            return '';
        }

        $prompt   = $this->buildPrompt($article);
        $services = $this->buildServiceLinks($prompt);

        if ($services === []) {
            return '';
        }

        return $this->renderBlock([
            'title'             => (string) $this->params->get('title', Text::_('PLG_CONTENT_WTREADINAI_DEFAULT_TITLE')),
            'introText'         => (string) $this->params->get('intro_text', Text::_('PLG_CONTENT_WTREADINAI_DEFAULT_INTRO')),
            'copyButtonLabel'   => (string) $this->params->get('copy_button_label', Text::_('PLG_CONTENT_WTREADINAI_COPY_PROMPT')),
            'copiedButtonLabel' => (string) $this->params->get('copied_button_label', Text::_('PLG_CONTENT_WTREADINAI_COPIED')),
            'prompt'            => $prompt,
            'services'          => $services,
            'article'           => $article,
        ]);
    }

    private function isAllowedForCategory(object $article): bool
    {
        $categoryId = (int) ($article->catid ?? 0);

        if ($categoryId <= 0) {
            return true;
        }

        $showCategories = $this->normaliseCategoryIds($this->params->get('show_categories', []));
        $hideCategories = $this->normaliseCategoryIds($this->params->get('hide_categories', []));

        if (in_array($categoryId, $hideCategories, true)) {
            return false;
        }

        return $showCategories === [] || in_array($categoryId, $showCategories, true);
    }

    /**
     * @param   mixed  $value  Raw category field value from plugin params.
     *
     * @return array<int, int>
     */
    private function normaliseCategoryIds(mixed $value): array
    {
        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return [];
            }

            $decoded = json_decode($value, true);
            $value   = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $value);
        }

        if ($value instanceof \stdClass) {
            $value = get_object_vars($value);
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $categoryIds = [];

        foreach ($value as $categoryId) {
            $categoryId = (int) $categoryId;

            if ($categoryId > 0) {
                $categoryIds[] = $categoryId;
            }
        }

        return array_values(array_unique($categoryIds));
    }

    private function buildPrompt(object $article): string
    {
        $starterPrompt = trim((string) $this->params->get('starter_prompt', Text::_('PLG_CONTENT_WTREADINAI_DEFAULT_PROMPT')));
        $articleTitle  = trim((string) ($article->title ?? ''));
        $articleUrl    = Uri::getInstance()->toString(['scheme', 'host', 'port', 'path', 'query', 'fragment']);

        $parts = array_filter([
            $starterPrompt,
            $articleTitle !== '' ? Text::sprintf('PLG_CONTENT_WTREADINAI_PROMPT_ARTICLE_TITLE', $articleTitle) : '',
            Text::sprintf('PLG_CONTENT_WTREADINAI_PROMPT_ARTICLE_URL', $articleUrl),
        ]);

        return implode("\n\n", $parts);
    }

    /**
     * @return array<int, array{title: string, url: string}>
     */
    private function buildServiceLinks(string $prompt): array
    {
        $links = [];

        foreach ($this->normaliseServices($this->params->get('services', [])) as $service) {
            $title      = trim((string) ($service['title'] ?? ''));
            $baseUrl    = trim((string) ($service['base_url'] ?? ''));
            $queryParam = trim((string) ($service['query_param'] ?? 'q'));
            $enabled    = (int) ($service['enabled'] ?? 1);

            if ($enabled !== 1 || $title === '' || $baseUrl === '' || $queryParam === '') {
                continue;
            }

            $url = $this->buildServiceUrl($baseUrl, $queryParam, $prompt);

            if ($url === '') {
                continue;
            }

            $links[] = [
                'title' => $title,
                'url'   => $url,
            ];
        }

        return $links;
    }

    /**
     * @param   mixed  $services  Raw subform value from plugin params.
     *
     * @return array<int, array<string, mixed>>
     */
    private function normaliseServices(mixed $services): array
    {
        if (is_string($services)) {
            $decoded = json_decode($services, true);
            $services = is_array($decoded) ? $decoded : [];
        }

        if ($services instanceof \stdClass) {
            $services = get_object_vars($services);
        }

        if (!is_array($services)) {
            return [];
        }

        $normalised = [];

        foreach ($services as $service) {
            if ($service instanceof \stdClass) {
                $service = get_object_vars($service);
            }

            if (is_array($service)) {
                $normalised[] = $service;
            }
        }

        return $normalised;
    }

    private function buildServiceUrl(string $baseUrl, string $queryParam, string $prompt): string
    {
        $parts = parse_url($baseUrl);

        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $query = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query[$queryParam] = $prompt;

        $url = $parts['scheme'] . '://' . $parts['host'];
        $url .= isset($parts['port']) ? ':' . $parts['port'] : '';
        $url .= $parts['path'] ?? '';
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $url .= isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $url;
    }

    /**
     * @param   array<string, mixed>  $displayData
     */
    private function renderBlock(array $displayData): string
    {
        $this->loadAssets();

        $layout = preg_replace('/[^A-Za-z0-9_\-:.]/', '', (string) $this->params->get('layout', 'default')) ?: 'default';
        $path   = PluginHelper::getLayoutPath('content', 'wtreadinai', $layout);

        if (!is_file($path)) {
            return '';
        }

        ob_start();
        include $path;

        return (string) ob_get_clean();
    }

    private function loadAssets(): void
    {
        $mediaBase = Uri::root(true) . '/media/plg_content_wtreadinai';
        $document  = $this->getApplication()->getDocument();

        $document->addStyleSheet($mediaBase . '/css/read-with-ai.css', ['version' => 'auto']);
        $document->addScript($mediaBase . '/js/read-with-ai.js', ['version' => 'auto'], ['defer' => true]);
    }
}
