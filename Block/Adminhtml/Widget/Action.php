<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Social\Block\Adminhtml\Widget;

/**
 * Call to action widget
 */
class Action extends \Magento\Backend\Block\Widget\Button
{
    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function _prepareAttributes($title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => join(' ', $classes),
            'onclick' => $this->getOnClick(),
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled,
            'href' => $this->getHref(),
            'target' => $this->getTarget()
        ];
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }
}
