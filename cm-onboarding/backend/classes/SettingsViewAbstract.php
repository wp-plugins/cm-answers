<?php

abstract class CMOB_SettingsViewAbstract
{
    protected $categories = array();
    protected $subcategories = array();
    protected $currentOptions = array();

    public function render()
    {
        $result = '';
        $categories = $this->getCategories();
        foreach($categories as $category => $title)
        {
            $result .= $this->renderCategory($category);
        }
        return $result;
    }

    public function renderCategory($category)
    {
        $result = '';
        $subcategories = $this->getSubcategories();
        if( !empty($subcategories[$category]) )
        {
            foreach($subcategories[$category] as $subcategory => $title)
            {
                $result .= $this->renderSubcategory($category, $subcategory);
            }
        }
        return $result;
    }

    abstract protected function getCategories();

    abstract protected function getSubcategories();

    public function renderSubcategory($category, $subcategory)
    {
        $result = '';
        $subcategories = $this->getSubcategories();
        if( isset($subcategories[$category]) AND isset($subcategories[$category][$subcategory]) )
        {
            $options = CMOB_Settings::getOptionsConfigByCategory($category, $subcategory);
            foreach($options as $name => $option)
            {
                $result .= $this->renderOption($name, $option);
            }
        }
        return $result;
    }

    public function renderOption($name, array $option = array())
    {
        if( empty($option) )
        {
            $option = CMOB_Settings::getOptionConfig($name);
        }
        return $this->renderOptionTitle($option)
                . $this->renderOptionControls($name, $option)
                . $this->renderOptionDescription($option);
    }

    public function renderOptionTitle($option)
    {
        return $option['title'];
    }

    public function renderOptionControls($name, array $option = array())
    {
        if( empty($option) ) $option = CMOB_Settings::getOptionConfig($name);

        $this->currentOptions = $option;

        switch($option['type'])
        {
            case CMOB_Settings::TYPE_BOOL:
                return $this->renderBool($name);
            case CMOB_Settings::TYPE_INT:
                return $this->renderInputNumber($name);
            case CMOB_Settings::TYPE_TEXTAREA:
                return $this->renderTextarea($name);
            case CMOB_Settings::TYPE_RADIO:
                return $this->renderRadio($name, $option['options']);
            case CMOB_Settings::TYPE_SELECT:
                return $this->renderSelect($name, $option['options']);
            case CMOB_Settings::TYPE_MULTISELECT:
                return $this->renderMultiSelect($name, $option['options']);
            case CMOB_Settings::TYPE_CSV_LINE:
                return $this->renderCSVLine($name);
            default:
                return $this->renderInputText($name);
        }
    }

    public function renderOptionDescription($option)
    {
        return (isset($option['desc']) ? $option['desc'] : '');
    }

    protected function renderInputText($name, $value = null)
    {
        if( is_null($value) )
        {
            $value = CMOB_Settings::getOption($name);
        }
        $disabled = disabled(TRUE, isset($this->currentOptions['disabled']) && $this->currentOptions['disabled'], false);
        return sprintf('<input type="text" name="%s" value="%s" %s />', esc_attr($name), esc_attr($value), esc_attr($disabled));
    }

    protected function renderInputNumber($name)
    {
        $disabled = disabled(TRUE, isset($this->currentOptions['disabled']) && $this->currentOptions['disabled'], false);
        return sprintf('<input type="number" name="%s" value="%s" %s />', esc_attr($name), esc_attr(CMOB_Settings::getOption($name), esc_attr($disabled)));
    }

    protected function renderCSVLine($name)
    {
        $value = CMOB_Settings::getOption($name);
        if( is_array($value) ) $value = implode(',', $value);
        return $this->renderInputText($name, $value);
    }

    protected function renderTextarea($name)
    {
        return sprintf('<textarea name="%s" cols="60" rows="5">%s</textarea>', esc_attr($name), esc_html(CMOB_Settings::getOption($name)));
    }

    protected function renderBool($name)
    {
        return $this->renderRadio($name, array(0 => 'No', 1 => 'Yes'), intval(CMOB_Settings::getOption($name)));
    }

    protected function renderRadio($name, $options, $currentValue = null)
    {
        if( is_null($currentValue) )
        {
            $currentValue = CMOB_Settings::getOption($name);
        }
        $result = '';
        $fieldName = esc_attr($name);
        $disabled = disabled(TRUE, isset($this->currentOptions['disabled']) && $this->currentOptions['disabled'], false);
        $labelClass = (isset($this->currentOptions['disabled']) && $this->currentOptions['disabled']) ? 'cmob-disabled' : '';
        foreach($options as $value => $text)
        {
            $fieldId = esc_attr($name . '_' . $value);
            $result .= sprintf('<div><input type="radio" name="%s" id="%s" %s value="%s" %s />'
                    . '<label for="%s" class="%s"> %s</label></div>', $fieldName, $fieldId, esc_attr($disabled), esc_attr($value), ( $currentValue == $value ? ' checked="checked"' : ''), $fieldId, esc_attr($labelClass), esc_html($text)
            );
        }
        return $result;
    }

    protected function renderSelect($name, $options, $currentValue = null)
    {
        $disabled = disabled(TRUE, isset($this->currentOptions['disabled']) && $this->currentOptions['disabled'], false);
        return sprintf('<div><select name="%s" %s>%s</select>', esc_attr($name), esc_attr($disabled), $this->renderSelectOptions($name, $options, $currentValue));
    }

    protected function renderSelectOptions($name, $options, $currentValue = null)
    {
        if( is_null($currentValue) )
        {
            $currentValue = CMOB_Settings::getOption($name);
        }
        $result = '';
        if( is_callable($options) ) $options = call_user_func($options, $name);
        foreach($options as $value => $text)
        {
            $result .= sprintf('<option value="%s"%s>%s</option>', esc_attr($value), ( $this->isSelected($value, $currentValue) ? ' selected="selected"' : ''), esc_html($text)
            );
        }
        return $result;
    }

    protected function isSelected($option, $value)
    {
        if( is_array($value) )
        {
            return in_array($option, $value);
        }
        else
        {
            return ($option == $value);
        }
    }

    protected function renderMultiSelect($name, $options, $currentValue = null)
    {
        return sprintf('<div><select name="%s[]" multiple="multiple">%s</select>', esc_attr($name), $this->renderSelectOptions($name, $options, $currentValue));
    }

}