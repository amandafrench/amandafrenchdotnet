<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\App\Helpers\Helper;

class TermsAndConditions extends BaseComponent
{
    /**
     * Compile and echo the html element
     *
     * @param array     $data [element data]
     * @param \stdClass $form [Form Object]
     *
     * @return void
     */
    public function compile($data, $form)
    {
        $elementName = $data['element'];
        $data = apply_filters('fluentform_rendering_field_data_' . $elementName, $data, $form);

        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';

        $cls = trim(
            $this->getDefaultContainerClass()
            . ' ' . @$data['settings']['container_class']
            . ' ' . $hasConditions
            . ' ff-el-input--content'
        );

        $uniqueId = $this->getUniqueId($data['attributes']['name']);

        $data['attributes']['id'] = $uniqueId;
        $data['attributes']['class'] = trim(
            'ff-el-form-check-input ' .
            $data['attributes']['class']
        );

        if ($tabIndex = Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $atts = $this->buildAttributes($data['attributes']);
        $checkbox = '';
        if ($data['settings']['has_checkbox']) {
            $checkbox = "<span class='ff_tc_checkbox'><input {$atts} value='on'></span>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
        }

        $html = "<div class='" . esc_attr($cls) . "'>";
        $html .= "<div class='ff-el-form-check ff-el-tc'>";
        $html .= "<label class='ff-el-form-check-label ff_tc_label' for={$uniqueId}>";
        $html .= "{$checkbox} <div class='ff_t_c'>" . fluentform_sanitize_html($data['settings']['tnc_html']) . '</div>';
        $html .= '</label>';
        $html .= '</div>';
        $html .= '</div>';

        $this->printContent('fluentform_rendering_field_html_' . $elementName, $html, $data, $form);
    }
}
