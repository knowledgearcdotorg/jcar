<?php
defined('_JEXEC') or die('Restricted access');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
    Joomla.submitbutton = function(task) {
        if (task == "division.cancel" || document.formvalidator.isValid(document.getElementById("division-form"))) {
            Joomla.submitform(task, document.getElementById("sefRouteForm"));
        }
    };
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_jcar&layout=edit&id='.(int)$this->item->id); ?>" method="post" name="adminForm" id="sefRouteForm" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_JCAR_FIELDSET_ROUTE_DETAILS')); ?>

            <div class="row-fluid">
                <div class="span9">
                    <div class="form-vertical">
                        <?php echo $this->form->getControlGroup('item_id'); ?>
                        <?php echo $this->form->getControlGroup('description'); ?>
                    </div>
                </div>
                <div class="span3">
                    <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
                </div>
            </div>

            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span6">
                    <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        
    </div>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>
