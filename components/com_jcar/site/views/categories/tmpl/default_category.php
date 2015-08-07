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
    foreach ($this->items as $key=>$value) :
        $count = isset($value->count) ? ' ('.$value->count.')' : '';
    ?>

    <li>
        <a href="<?php echo JCarHelperRoute::getCategoryRoute($value->id); ?>">
            <?php echo $value->name; ?>
        </a><?php echo $count; ?>

        <?php
        if (isset($value->children)) :
            $this->items = $value->children;
            echo $this->loadTemplate('category');
        endif;
        ?>
    </li>

    <?php
    endforeach;
    ?>
</ul>