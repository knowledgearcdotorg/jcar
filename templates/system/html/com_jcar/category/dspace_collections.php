<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
?>
<ul>
    <?php
    foreach ($this->collections as $collection) :
        $url = JCarHelperRoute::getCategoryRoute($collection->id);
        
        $count = isset($collection->count) ? ' ('.$collection->count.')' : '';
    ?>

    <li>
        <a href="<?php echo $url; ?>">
            <?php echo $collection->name; ?></a>
        <?php echo $count; ?>
    </li>

    <?php
    endforeach;
    ?>
</ul>