<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
?>

<div id="cpanel" class="span12">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2"><?php echo $this->sidebar; ?></div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    <div id="j-main-container">
    <?php endif;?>
    <?php echo JText::_("COM_JCAR_CPANEL_INTRO"); ?>
</div>
