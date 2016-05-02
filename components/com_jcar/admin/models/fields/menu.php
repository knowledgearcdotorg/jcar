<?php
defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  1.6
 */
JFormHelper::loadFieldClass('menu');

class JCarFormFieldMenu extends JFormFieldMenu
{
    public $type = 'JCar.Menu';
}
