<?php

/**
 * This file is part of the Texy! formatter (http://texy.info/)
 *
 * Copyright (c) 2004-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 119 $ $Date: 2007-04-13 21:04:57 +0200 (pá, 13 IV 2007) $
 * @package  Texy
 */

// security - include texy.php, not this file
if (!defined('TEXY')) die();



/**
 * Paragraph module
 */
class TexyParagraphModule extends TexyModule
{
    /** @var bool  how split paragraphs (internal usage) */
    public $mode;



    public function begin()
    {
        $this->mode = TRUE;
    }



    /**
     * Finish invocation
     *
     * @param string
     * @param TexyModifier
     * @return TexyHtml|FALSE
     */
    public function solve($content, $mod)
    {
        $tx = $this->texy;

        // find hard linebreaks
        if ($tx->mergeLines) {
            // ....
            //  ...  => \r means break line
            $content = preg_replace('#\n (?=\S)#', "\r", $content);
        } else {
            $content = preg_replace('#\n#', "\r", $content);
        }

        $el = TexyHtml::el('p');
        $el->parseLine($tx, $content);
        $content = $el->getText(); // string

        // check content type
        // block contains block tag
        if (strpos($content, Texy::CONTENT_BLOCK) !== FALSE) {
            $el->name = '';  // ignores modifier!

        // block contains text (protected)
        } elseif (strpos($content, Texy::CONTENT_TEXTUAL) !== FALSE) {
            // leave element p

        // block contains text
        } elseif (preg_match('#[^\s'.TEXY_MARK.']#u', $content)) {
            // leave element p

        // block contains only replaced element
        } elseif (strpos($content, Texy::CONTENT_REPLACED) !== FALSE) {
            $el->name = 'div';

        // block contains only markup tags or spaces or nothig
        } else {
            if ($tx->ignoreEmptyStuff) return FALSE;
            if ($mod->empty) $el->name = '';
        }

        // apply modifier
        if ($el->name && $mod) $mod->decorate($tx, $el);

        // add <br />
        if ($el->name && (strpos($content, "\r") !== FALSE)) {
            $key = $tx->protect('<br />', Texy::CONTENT_REPLACED);
            $content = str_replace("\r", $key, $content);
        };
        $content = strtr($content, "\r\n", '  ');
        $el->setText($content);

        return $el;
    }


} // TexyParagraphModule