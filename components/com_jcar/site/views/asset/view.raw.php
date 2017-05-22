<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
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
        $this->state = $this->get('State');
        $this->params = $this->state->get('params');

        $handle = fopen((string)$this->item->url, 'rb');

        if ($handle === false) {
            return false;
        }

        $name = htmlentities($this->item->name);

        $document = JFactory::getDocument();

        $document->setMimeEncoding($this->item->mimeType);
        $document->setTitle($name);

        if ($this->params->get('inline_max_length', 0) >= $this->item->size &&
            array_search($this->item->mimeType, explode(",", $this->params->get('inline_mimetypes'))) !== false) {
            $disposition = 'inline';
        } else {
            $disposition = 'attachment';
        }

        JFactory::getApplication()
            ->setHeader(
                    'Content-disposition',
                    $disposition.'; filename="'.$name.'"',
                    true)
            ->setHeader('Content-Length', $this->item->size, true)
            ->setHeader('Content-type', $this->item->mimeType, true);

        fpassthru($handle);
    }
}
