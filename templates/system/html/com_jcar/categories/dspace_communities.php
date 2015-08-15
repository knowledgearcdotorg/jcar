<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$url = new JUri('index.php');
$url->setQuery(
    array(
        'option'=>'com_jcar',
        'view'=>'category',
        'layout'=>JFactory::getApplication()->input->getString('layout'),
        'Itemid'=>JFactory::getApplication()->input->getInt('Itemid')));
?>
<ul>
    <?php
    foreach ($this->items as $item) :
        $url->setVar('id', $item->id);
    ?>

    <li>
        <a href="<?php echo (string)$url; ?>">
            <?php echo $item->name; ?></a>

        <?php
        if (isset($item->subCommunities)) :
            if (count($item->subCommunities)) :
                $this->items = $item->subCommunities;
                echo $this->loadTemplate('communities');
            endif;
        endif;
        ?>

        <?php
        if (isset($item->collections)) :
            if (count($item->collections)) :
                $this->collections = $item->collections;
                echo $this->loadTemplate('collections');
            endif;
        endif;
        ?>
    </li>

    <?php
    endforeach;
    ?>
</ul>