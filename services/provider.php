<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.wtreadinai
 *
 * @copyright   Copyright (c) 2026 WebTolk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Webtolk\Plugin\Content\WtReadInAi\Extension\WtReadInAi;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            $container->lazy(WtReadInAi::class, function (Container $container) {
                $plugin = new WtReadInAi(
                    (array) PluginHelper::getPlugin('content', 'wtreadinai')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            })
        );
    }
};
