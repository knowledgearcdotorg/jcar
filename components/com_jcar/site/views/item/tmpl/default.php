<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;

$metadata = $this->item->metadata;

$title = ArrayHelper::getValue($metadata, "dc.title", array());
$author = ArrayHelper::getValue($metadata, "dc.contributor.author", array());
$issued = ArrayHelper::getValue($metadata, "dc.date.issued", array());
$publisher = ArrayHelper::getValue($metadata, "dc.publisher", array());

$title = reset($title);
$author = reset($author);
$issued = reset($issued);
$publisher = reset($publisher);

JFactory::getDocument()
    ->setMetaData("DC.title", $title)
    ->setMetaData("DC.author", $author)
    ->setMetaData("DC.issued", $issued)
    ->setMetaData("DC.publisher", $publisher);

JLoader::register(
    'JCarHelper',
    JPATH_ROOT.'/administrator/components/com_jcar/helpers/jcar.php');

    echo JRoute::_("index.php?option=com_jcar&view=item&id=dspace:21");
?>
<section id="jcarItem">
    <header>
        <?php if ($this->params->get('show_page_heading')) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
        <?php else : ?>
        <h1><?php echo $title; ?></h1>
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
                        <?php if ($bitstream->url) : ?>
                        <a href="<?php echo JRoute::_((string)$bitstream->url); ?>">
                            <?php echo $bitstream->name; ?>
                        </a>
                        <?php else : ?>
                        <?php echo $bitstream->name; ?>
                        <?php endif; ?>
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
