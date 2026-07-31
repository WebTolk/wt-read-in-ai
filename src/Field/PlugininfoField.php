<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.wtreadinai
 *
 * @copyright   Copyright (c) 2026 WebTolk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     __DEPLOY_VERSION__
 */

declare(strict_types=1);

namespace Webtolk\Plugin\Content\WtReadInAi\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Field\NoteField;
use Joomla\CMS\Language\Text;
use Throwable;

class PlugininfoField extends NoteField
{
    protected $type = 'Plugininfo';

    protected function getInput(): string
    {
        try {
            $app = Factory::getApplication();

            if ($app instanceof AdministratorApplication) {
                $document = $app->getDocument();

                if ($document instanceof HtmlDocument) {
                    $document->getWebAssetManager()->addInlineStyle('
                        .plugin-info-img-svg:hover * {
                            cursor: pointer;
                        }
                    ');
                }
            }
        } catch (Throwable) {
        }

        return '<div class="d-flex flex-column flex-md-row shadow p-4">
            <div class="flex-shrink-0">
                <a href="https://web-tolk.ru" target="_blank" rel="noopener noreferrer">
                    <svg class="plugin-info-img-svg" width="200" height="50" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="WebTolk">
                        <g>
                            <title>Go to https://web-tolk.ru</title>
                            <text font-weight="bold" xml:space="preserve" text-anchor="start"
                                font-family="Helvetica, Arial, sans-serif" font-size="32" y="36.085949"
                                x="8.152073" stroke-width="0" stroke="#000" fill="#0fa2e6">Web</text>
                            <text font-weight="bold" xml:space="preserve" text-anchor="start"
                                font-family="Helvetica, Arial, sans-serif" font-size="32" y="36.081862"
                                x="74.239105" stroke-width="0" stroke="#000" fill="#384148">Tolk</text>
                        </g>
                    </svg>
                </a>
            </div>
            <div class="flex-grow-1 mt-3 mt-md-0 ms-md-3">
                <span class="badge bg-success text-white">v.' . htmlspecialchars($this->getPluginVersion(), ENT_QUOTES, 'UTF-8') . '</span>
                ' . Text::_('PLG_CONTENT_WTREADINAI_XML_DESCRIPTION') . '
            </div>
        </div>';
    }

    protected function getLabel(): string
    {
        return '';
    }

    protected function getTitle(): string
    {
        return $this->getLabel();
    }

    private function getPluginVersion(): string
    {
        $data    = $this->form->getData();
        $element = (string) $data->get('element', 'wtreadinai');
        $folder  = (string) $data->get('folder', 'content');
        $path    = JPATH_SITE . '/plugins/' . $folder . '/' . $element . '/' . $element . '.xml';

        if (!is_file($path)) {
            return '1.0.0';
        }

        $xml = simplexml_load_file($path);

        if ($xml === false || empty($xml->version)) {
            return '1.0.0';
        }

        return (string) $xml->version;
    }
}
