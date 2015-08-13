<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$eName = json_encode($this->eName);

$script  = <<<JS
function insertJCARPlugin() {
    var tag = "{jcar";

    tag += " id="+document.getElementById("id").value;

    tag += "}";

    window.parent.jInsertEditorText(tag, $eName);
    window.parent.jModalClose();
    return false;
}
JS;
JFactory::getDocument()->addScriptDeclaration($script);
?>

<form class="form-horizontal">

    <div class="control-group">
        <label for="title" class="control-label">
            <?php echo JText::_('COM_JCAR_ITEM_ID_TITLE'); ?>
        </label>
        <div class="controls">
            <input type="text" id="id" name="id"/>
        </div>
    </div>

    <button onclick="insertJCARPlugin();" class="btn btn-primary">
        <?php echo JText::_('COM_JCAR_ITEM_INSERT_BUTTON'); ?>
    </button>

</form>