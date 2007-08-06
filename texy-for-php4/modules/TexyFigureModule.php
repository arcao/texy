<?php

/**
 * This file is part of the Texy! formatter (http://texy.info/)
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004-2007 David Grudl aka -dgx- (http://www.dgx.cz)
 * @license    GNU GENERAL PUBLIC LICENSE version 2 or 3
 * @version    $Revision$ $Date$
 * @category   Text
 * @package    Texy
 */

// security - include texy.php, not this file
if (!class_exists('Texy')) die();



/**
 * The captioned figures
 */
class TexyFigureModule extends TexyModule
{
    var $syntax = array('figure' => TRUE);

    /** @var string  non-floated box CSS class */
    var $class = 'figure';

    /** @var string  left-floated box CSS class */
    var $leftClass;

    /** @var string  right-floated box CSS class */
    var $rightClass;

    /** @var int  how calculate div's width */
    var $widthDelta = 10;


    function begin()
    {
        $this->texy->registerBlockPattern(
            array($this, 'pattern'),
            '#^'.TEXY_IMAGE.TEXY_LINK_N.'?? +\*\*\* +(.*)'.TEXY_MODIFIER_H.'?()$#mUu',
            'figure'
        );
    }



    /**
     * Callback for [*image*]:link *** .... .(title)[class]{style}>
     *
     * @param TexyBlockParser
     * @param array      regexp matches
     * @param string     pattern name
     * @return TexyHtml|string|FALSE
     */
    function pattern($parser, $matches)
    {
        list(, $mURLs, $mImgMod, $mAlign, $mLink, $mContent, $mMod) = $matches;
        //    [1] => URLs
        //    [2] => .(title)[class]{style}<>
        //    [3] => * < >
        //    [4] => url | [ref] | [*image*]
        //    [5] => ...
        //    [6] => .(title)[class]{style}<>

        $tx = $this->texy;
        $image = $tx->imageModule->factoryImage($mURLs, $mImgMod.$mAlign);
        $mod = new TexyModifier($mMod);
        $mContent = ltrim($mContent);

        if ($mLink) {
            if ($mLink === ':') {
                $link = new TexyLink($image->linkedURL === NULL ? $image->URL : $image->linkedURL);
                $link->raw = ':';
                $link->type = TexyLink_IMAGE;
            } else {
                $link = $tx->linkModule->factoryLink($mLink, NULL, NULL);
            }
        } else $link = NULL;

        // event wrapper
        if (is_callable(array($tx->handler, 'figure'))) {
            $res = $tx->handler->figure($parser, $image, $link, $mContent, $mod);
            if ($res !== TEXY_PROCEED) return $res;
        }

        return $this->solve($image, $link, $mContent, $mod);
    }



    /**
     * Finish invocation
     *
     * @param TexyImage
     * @param TexyLink
     * @param string
     * @param TexyModifier
     * @return TexyHtml|FALSE
     */
    function solve(/*TexyImage*/ $image, $link, $content, $mod)
    {
        $tx = $this->texy;

        $hAlign = $image->modifier->hAlign;
        $mod->hAlign = $image->modifier->hAlign = NULL;

        $elImg = $tx->imageModule->solve($image, $link); // returns TexyHtml or false!
        if (!$elImg) return FALSE;

        $el = TexyHtml::el('div');
        if (!empty($image->width)) $el->attrs['style']['width'] = ($image->width + $this->widthDelta) . 'px';
        $mod->decorate($tx, $el);

        $el->children['img'] = $elImg;
        $el->children['caption'] = TexyHtml::el('p');
        $el->children['caption']->parseLine($tx, ltrim($content));

        if ($hAlign === 'left') {
            if ($this->leftClass != '')
                $el->attrs['class'][] = $this->leftClass;
            else
                $el->attrs['style']['float'] = 'left';

        } elseif ($hAlign === 'right')  {

            if ($this->rightClass != '')
                $el->attrs['class'][] = $this->rightClass;
            else
                $el->attrs['style']['float'] = 'right';
        } elseif ($this->class)
            $el->attrs['class'][] = $this->class;

        return $el;
    }

}
