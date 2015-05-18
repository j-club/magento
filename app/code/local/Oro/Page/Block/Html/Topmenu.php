<?php
/**
 * Custom top menu
 *
 * @category   MageCore
 * @package    Oro_Page
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Page_Block_Html_Topmenu extends Mage_Page_Block_Html_Topmenu
{
    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string
     */
    public function getJcTopMenu()
    {
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
            'block' => $this
        ));

        $html = $this->_getMenuHtml($this->_menu);

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }

    /**
     * @param Varien_Data_Tree_Node $menuTree
     * @return string
     */
    protected function _getMenuHtml(Varien_Data_Tree_Node $menuTree)
    {
        $html = '';
        $children = $menuTree->getChildren();
        foreach ($children as $child) {
            $nestedLevel = 1;
            foreach ($child->getChildren() as $subChild) {
                if ($subChild->hasChildren()) {
                    $nestedLevel = 2;
                    break;
                }
            }
            if ($nestedLevel == 1) {
                $html .= $this->_drawOneLevelMenu($child);
            } else {
                $html .= $this->_drawTwoLevelMenu($child);
            }
        }
        return $html;
    }

    /**
     * @param Varien_Data_Tree_Node $menuTree
     * @return string
     */
    protected function _drawOneLevelMenu(Varien_Data_Tree_Node $menuTree)
    {
        $children = $menuTree->getChildren();
        $html = '<li>';
        $html .= '<a href="' . $menuTree->getUrl() . '"><span>'
            . $this->escapeHtml($menuTree->getName()) . '</span></a>';
        if ($children->count()) {
            $html .= '<div class="drop"><div class="drop-holder"><div class="col"><ul>';
            foreach ($children as $child) {
                $html .= '<li><a href="' . $child->getUrl() . '">'
                    . $this->escapeHtml($child->getName()) . '</a></li>';
            }
            $html .= '</ul></div></div></div>';
        }
        $html .= '</li>';
        return $html;
    }

    /**
     * @param Varien_Data_Tree_Node $menuTree
     * @return string
     */
    protected function _drawTwoLevelMenu(Varien_Data_Tree_Node $menuTree)
    {
        $children = $menuTree->getChildren();
        $html = '<li>';
        $html .= '<a href="' . $menuTree->getUrl() . '"><span>'
            . $this->escapeHtml($menuTree->getName()) . '</span></a>';
        $html .= '<div class="drop"><div class="drop-holder"><div class="col-frame"><div class="col-holder">';
        $index = 1;
        foreach ($children as $child) {
            $html .= '<div class="col">';
            $html .= '<strong class="title-drop">'
                . $this->escapeHtml($child->getName()) . '</strong><ul>';
            $secondLevelChildren = $child->getChildren();
            $childrenCount = $secondLevelChildren->count();
            $key =0;
            foreach ($secondLevelChildren as $ch) {
                if ($key == 5) {
                    break;
                }
                $html .= '<li><a href="' . $ch->getUrl() . '">'
                    . $this->escapeHtml($ch->getName()) . '</a></li>';
                $key++;
            }
            $html .= '</ul>';
            if ($childrenCount > 5 || $childrenCount == 0) {
                $html .= '<a class="all-links" href="' . $child->getUrl()
                    . '">All ' . $this->escapeHtml($child->getName()) . '</a>';
            }
            $html .= '</div>';
            if ($index%3 == 0 && $index > 0) {
                $html .= '</div><div class="col-holder">';
            }
            $index++;
        }
        $html .= '</div></div></div></div>';
        $html .= '</li>';
        return $html;
    }
}
