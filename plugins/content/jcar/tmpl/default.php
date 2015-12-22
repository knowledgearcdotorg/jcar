<?php
/**
 * @package     Content.Plugin
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register(
    'JCarHelper',
    JPATH_ROOT.'/administrator/components/com_jcar/helpers/jcar.php');
?>

<dl>
    <?php foreach ($displayData->metadata as $key=>$values) : ?>
    <dt><?php echo $key; ?></dt>

    <?php foreach ($values as $value) : ?>
    <dd><?php echo $value; ?></dd>
    <?php endforeach; ?>

    <?php endforeach; ?>
</dl>

<dl>
    <?php foreach ($displayData->bundles as $bundle) : ?>
    <dt><?php echo $bundle->name; ?></dt>

    <dd>
        <dl>
            <?php foreach ($bundle->bitstreams as $bitstream) : ?>
            <dt>
                <a href="<?php echo JRoute::_((string)$bitstream->url); ?>">
                    <?php echo $bitstream->name; ?>
                </a>
            </dt>
            <dd><?php echo JCarHelper::formatBytes($bitstream->size, 0); ?></dd>
            <dd><?php echo $bitstream->formatDescription; ?></dd>
            <?php endforeach; ?>
        </dl>
    </dd>
<?php endforeach; ?>
</dl>
