<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');
JFactory::getDocument()->addScript(JUri::root().'media/com_jcar/js/jcar.js');

$nextPage = $this->item->pagination->getData()->next->link;
if ($nextPage) :
    $url = new JUri($nextPage);
    $url->setVar("format", "json");
    $nextPage = (string)$url;
endif;
?>

<section id="jcarCategory">
    <header>
        <h1><?php echo $this->item->name; ?></h1>
        <div><?php echo $this->item->description; ?></div>

        <?php echo $this->item->pagination->getResultsCounter(); ?>
    </header>

    <articles id="jcar-lists">

        <?php foreach ($this->item->items as $item) : ?>

        <h2>
            <a href="<?php echo JCarHelperRoute::getItemRoute($item->id); ?>">
                <?php echo $item->name; ?></a></h2>

        <?php endforeach; ?>

    </articles>
    <footer>

        <?php if ($nextPage) : ?>
        <button
            class="jcar-load-more"
            data-url="<?php echo $nextPage; ?>">
            <?php echo JText::_('COM_JCAR_LOADMORE_BUTTON'); ?>
        </button>
        <?php endif; ?>

    </footer>
</section>
