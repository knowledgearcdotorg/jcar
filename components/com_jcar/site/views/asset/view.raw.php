<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

\JLoader::import('joomla.filesystem.file');

/**
 * Provides raw output of file contents.
 */
class JCarViewAsset extends JViewLegacy
{
    protected static $chunksize = 4096;

    protected $item;

    /**
     * Diplay a category and all its sub-categories.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a Error object.
     */
    public function display($tpl = null)
    {
        $this->item = $this->get('Item');

        $handle = fopen((string)$this->item->url, 'rb');

        if ($handle === false) {
            return false;
        }

        $name = htmlentities($this->item->name);

        $document = JFactory::getDocument();
        $document->setType($this->item->mimeType);
        $document->setTitle($name);

        header("Content-Disposition: attachment; filename=\"".$name."\";");
        header("Content-Length: ".$this->item->size);

        while (!feof($handle)) {
            $buffer = fread($handle, static::$chunksize);

            echo $buffer;

            ob_flush();
            flush();
        }

        $status = fclose($handle);
    }
}