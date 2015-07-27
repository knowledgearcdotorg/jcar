<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
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
    <?php
    foreach ($this->items as $key=>$value) :
    ?>
    <dt><?php echo $value->name; ?>&nbsp;<?php echo isset($value->count) ? '('.$value->count.')' : ''; ?></dt>
    <dd>
        <?php
        if (isset($value->children)) :
            $this->items = $value->children;
            echo $this->loadTemplate('category');
        endif;
    endforeach;
    ?>
    </dd>
</dl>