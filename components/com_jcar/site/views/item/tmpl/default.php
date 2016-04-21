<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register(
    'JCarHelper',
    JPATH_ROOT.'/administrator/components/com_jcar/helpers/jcar.php');
?>
<section id="jcarItem">
    <header>
        <?php if ($this->params->get('show_page_heading')) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
        <?php else : ?>
        <h1><?php echo reset(JArrayHelper::getValue($this->item->metadata, "dc.title")); ?></h1>
        <?php endif; ?>
    </header>

    <article>
        <dl>
            <?php foreach ($this->item->metadata as $key=>$values) : ?>
            <dt><?php echo $key; ?></dt>

            <?php foreach ($values as $value) : ?>
            <dd><?php echo $value; ?></dd>
            <?php endforeach; ?>

            <?php endforeach; ?>
        </dl>

        <dl>
            <?php foreach ($this->item->bundles as $bundle) : ?>
            <dt><?php echo $bundle->name; ?></dt>

            <dd>
                <dl>
                    <?php foreach ($bundle->bitstreams as $bitstream) : ?>
                    <dt>
                        <a href="<?php echo JRoute::_((string)$bitstream->url); ?>">
                            <?php echo $bitstream->name; ?>
                        </a>
                    </dt>

                    <?php if (($size = JCarHelper::formatBytes($bitstream->size, 0)) != 0) : ?>
                    <dd><?php echo $size; ?></dd>
                    <?php endif ; ?>

                    <?php if (!empty($bitstream->formatDescription)) : ?>
                    <dd><?php echo $bitstream->formatDescription; ?></dd>
                    <?php endif ; ?>

                    <?php endforeach; ?>
                </dl>
            </dd>
            <?php endforeach; ?>
        </dl>
    </article>
</section>
