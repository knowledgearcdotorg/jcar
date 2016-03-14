<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class JCarController extends JControllerLegacy
{
    public function __construct($config = array())
    {
        $config['default_view'] = 'cpanel';
        parent::__construct($config);
    }
}
