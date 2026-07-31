<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.wtreadinai
 *
 * @copyright   Copyright (c) 2026 WebTolk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;

\defined('_JEXEC') or die;

/** @var array<string, mixed> $displayData */
$services = $displayData['services'] ?? [];

if (!is_array($services) || $services === []) {
    return;
}
?>
<section class="wt-read-in-ai" aria-labelledby="wt-read-in-ai-title-<?php echo (int) ($displayData['article']->id ?? 0); ?>">
    <div class="wt-read-in-ai__content">
        <h2 class="wt-read-in-ai__title" id="wt-read-in-ai-title-<?php echo (int) ($displayData['article']->id ?? 0); ?>">
            <?php echo htmlspecialchars(HTMLHelper::_('string.truncate', (string) $displayData['title'], 120, true, false), ENT_QUOTES, 'UTF-8'); ?>
        </h2>
        <?php if (!empty($displayData['introText'])) : ?>
            <p class="wt-read-in-ai__intro"><?php echo $displayData['introText']; ?></p>
        <?php endif; ?>
        <div class="wt-read-in-ai__actions">
            <button
                class="wt-read-in-ai__copy"
                type="button"
                data-wt-read-in-ai-copy
                data-wt-read-in-ai-prompt="<?php echo htmlspecialchars((string) $displayData['prompt'], ENT_QUOTES, 'UTF-8'); ?>"
                data-wt-read-in-ai-label="<?php echo htmlspecialchars((string) $displayData['copyButtonLabel'], ENT_QUOTES, 'UTF-8'); ?>"
                data-wt-read-in-ai-copied-label="<?php echo htmlspecialchars((string) $displayData['copiedButtonLabel'], ENT_QUOTES, 'UTF-8'); ?>"
            >
                <?php echo htmlspecialchars((string) $displayData['copyButtonLabel'], ENT_QUOTES, 'UTF-8'); ?>
            </button>
            <?php foreach ($services as $service) : ?>
                <a
                    class="wt-read-in-ai__service"
                    href="<?php echo htmlspecialchars((string) $service['url'], ENT_QUOTES, 'UTF-8'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php echo htmlspecialchars((string) $service['title'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
