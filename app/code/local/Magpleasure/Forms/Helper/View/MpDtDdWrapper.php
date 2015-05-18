<?php

class Magpleasure_Forms_Helper_View_MpDtDdWrapper extends Zend_Form_Decorator_DtDdWrapper
{

    /**
     * Render
     *
     * Renders as the following:
     * <dt>$dtLabel</dt>
     * <dd>$content</dd>
     *
     * $dtLabel can be set via 'dtLabel' option, defaults to '\&#160;'
     * 
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $elementName = $this->getElement()->getName();
        
        $dtLabel = $this->getOption('dtLabel');
        if( null === $dtLabel ) {
            $dtLabel = '&#160;';
        }

        return '<dt id="' . $elementName . '-label">' . $dtLabel . '</dt>' .
               '<dd id="' . $elementName . '-element">' . $content . '</dd>';
    }
}
