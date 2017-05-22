<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'jui/cms.js', false, true);

$eName = json_encode($this->eName);

$script  = <<<JS
(function($) {
    $(document).ready(function() {
        $("form").on('submit',function() {
            var vars = {};

            $(this).find(":input").each(function() {
                console.log($(this).attr("name"));
                if ($(this).attr("name") &&
                    $(this).attr("name") != "idLookupPlugin" &&
                    $(this).attr("name") != "idLookup") {
                    var matches = $(this).attr("name").match(/jform\[(.*?)\]/i);
                    var id = matches[1];

                    vars[id] = $(this).val();
                }
            });

            var tag = "{jcar ";

            tag += vars["id"];

            vars["id"] = null;

            $.each(vars, function(index, item) {
                if (item != null && item != "") {
                    tag += "|"+index+"="+item;
                }
            });

            tag += "}";

            window.parent.jInsertEditorText(tag, $eName);
            window.parent.jModalClose();
            return false;
        })
    })
})(jQuery);
JS;
JFactory::getDocument()->addScriptDeclaration($script);
?>

<form class="form-horizontal">

    <?php
    foreach ($this->form->getFieldset() as $field) :
        /*$classnames = 'control-group';
        $rel = '';
        $showon = $this->form->getFieldAttribute($field->fieldname, 'showon');

        if (!empty($showon)) :
            $id = $this->form->getFormControl();
            $showon = explode(':', $showon, 2);
            $classnames .= ' showon_'.implode(' showon_', explode(',', $showon[1]));
            $rel = ' rel="showon_'.$id.'['.$showon[0].']"';

            echo $rel;
        endif;
    ?>

    <div class="<?php echo $classnames; ?>"<?php echo $rel; ?>>

        <?php if (!isset($this->showlabel) || $this->showlabel) : ?>
            <div class="control-label"><?php echo $field->label; ?></div>
        <?php endif; ?>

        <div class="controls"><?php echo $field->input; ?></div>

    </div>

    <?php
    */
        $html = $field->renderField();

        $html = str_replace('"field":"jform[idLookupPlugin]"', '"field":"idLookupPlugin"', $html);

        echo $html;
    endforeach;
    ?>

    <button class="btn btn-primary">
        <?php echo JText::_('COM_JCAR_ITEM_INSERT_BUTTON'); ?>
    </button>

</form>
