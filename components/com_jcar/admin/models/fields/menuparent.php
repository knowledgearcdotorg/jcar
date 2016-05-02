<?php
defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  1.6
 */
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.core');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFormHelper::addFieldPath(JPATH_ROOT.'/administrator/components/com_menus/models/fields');
JFormHelper::loadFieldClass('menuparent');

class JCarFormFieldMenuParent extends JFormFieldMenuParent
{
    public $type = 'JCar.MenuParent';

    protected function getInput()
    {
        JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function($) {
    $('#jform_menutype').change(function() {
        var menutype = $(this).val();
        $.ajax({
            url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
            dataType: 'json'
        }).done(function(data) {
            $('#jform_parent_id option').each(function() {
                if ($(this).val() != '1') {
                    $(this).remove();
                }
            });

            $.each(data, function (i, val) {
                var option = $('<option>');
                option.text(val.title).val(val.id);
                $('#jform_parent_id').append(option);
            });

            $('#jform_parent_id').trigger('liszt:updated');
        });
    });
});
JS
);

        if (!$this->form->getValue('menutype')) {
            $this->form->setValue('menutype', null, 'mainmenu');
        }

        return parent::getInput();
    }
}
