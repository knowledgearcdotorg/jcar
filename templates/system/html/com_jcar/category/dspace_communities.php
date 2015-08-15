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
        'layout'=>'dspace',
        'Itemid'=>JFactory::getApplication()->input->getInt('Itemid')));
?>
<ul>
    <?php
    foreach ($this->communities as $community) :
        $url->setVar('id', $community->id);
    ?>

    <li>
        <a href="<?php echo (string)$url; ?>">
            <?php echo $community->name; ?></a>

        <?php
        if (count($community->subCommunities)) :
            $this->communities = $community->subCommunities;
            echo $this->loadTemplate('communities');
        endif;
        ?>

        <?php
        if (count($community->collections)) :
            $this->collections = $community->collections;
            echo $this->loadTemplate('collections');
        endif;
        ?>
    </li>

    <?php
    endforeach;
    ?>
</ul>