<?php
/**
 * Data upgrade
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$content = <<< END
<div class="slide active">
    <div class="text-slide">
        <div class="holder">
            <div class="frame">
                <strong>The Most beautiful <br/> Rings on earth</strong>
                <p>Celebrate your loved one with the enduring romance rings to last a life time.</p>
                <a href="#">Shop Rings</a>
            </div>
        </div>
    </div>
    <img src="/skin/frontend/enterprise/jewelersclub/images/img-17.png" />
</div>
END;


for ($i=0; $i < 3; $i++) {
    $data = array(
        'title'           => 'homeslider'.($i+1),
        'identifier'      => 'homeslider'.($i+1),
        'content'         => $content,
        'is_active'       => 1,
    );

    Mage::getModel('cms/block')->setData($data)->save();
}
?>