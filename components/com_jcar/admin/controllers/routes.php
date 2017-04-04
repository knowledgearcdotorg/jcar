<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class JCarControllerRoutes extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  The prefix for the PHP class name.
     * @param   array   $config  Array of configuration parameters.
     *
     * @return  JModelLegacy
     */
    public function getModel($name = 'Route', $prefix = 'JCarModel', $config = array('ignore_request'=>true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
