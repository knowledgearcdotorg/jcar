<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
?>

<section id="jcarCategory">
    <header>
        <?php if ($this->params->get('show_page_heading')) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
        <?php else : ?>
        <h1><?php echo $this->item->name; ?></h1>
        <?php endif; ?>

        <div><?php echo $this->item->description; ?></div>

        <?php echo $this->item->pagination->getResultsCounter(); ?>
    </header>

    <div>

        <?php foreach ($this->item->items as $item) : ?>

        <h2>
            <a href="<?php echo JCarHelperRoute::getItemRoute($item->id); ?>">
                <?php echo $item->name; ?></a></h2>

        <?php endforeach; ?>

    </div>

    <footer>

        <div class="pagination">
        <?php echo $this->item->pagination->getPagesLinks(); ?>
        </div>

    </footer>
</section>
