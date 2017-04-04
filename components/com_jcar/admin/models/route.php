<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Models a single route for editing.
 *
 * @package     JSpace.Component
 * @subpackage  Administrator
 */
class JCarModelRoute extends JModelAdmin
{
    /**
     * The type alias for this content type.
     *
     * @var    string
     */
    public $typeAlias = 'com_jcar.route';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) {
                return;
            }

            return JFactory::getUser()->authorise('core.delete');
        }
    }

    /**
     * Returns a Table object, always creating it
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     */
    public function getTable($type = 'Route', $prefix = 'JCarTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|boolean  A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            $this->typeAlias,
            'route',
            [
                'control'=>'jform',
                'load_data'=>$loadData
            ]);

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object)$data)) {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState($this->option.'.edit.route.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData($this->typeAlias, $data);

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   JTable  $table  The JTable object
     *
     * @return  void
     */
    protected function prepareTable($table)
    {
        $date = JFactory::getDate()->toSql();

        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);

        $table->generateAlias();

        if (empty($table->id)) {
            $table->created = $date;
        } else {
            $table->modified = $date;
        }
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $category_id  The id of the parent.
     * @param   string   $alias        The alias.
     * @param   string   $name         The title.
     *
     * @return  array  Contains the modified title and alias.
     */
    protected function generateNewTitle($category_id, $alias, $name)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias))) {
            if ($name == $table->name) {
                $name = JString::increment($name);
            }

            $alias = JString::increment($alias, 'dash');
        }

        return array($name, $alias);
    }
}
