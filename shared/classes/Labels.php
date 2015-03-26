<?php

class CMOB_Labels
{
    const FILENAME = 'labels.tsv';
    const OPTION_LABEL_PREFIX = 'cmob_label_';

    protected static $labels = array();
    protected static $labelsByCategories = array();

    public static function getLabel($labelKey)
    {
        $optionName = self::OPTION_LABEL_PREFIX . $labelKey;
        $default = self::getDefaultLabel($labelKey);
        return get_option($optionName, (empty($default) ? $labelKey : $default));
    }

    public static function setLabel($labelKey, $value)
    {
        $optionName = self::OPTION_LABEL_PREFIX . $labelKey;
        update_option($optionName, $value);
    }

    public static function getLocalized($labelKey)
    {
        return CMOnBoarding::__(self::getLabel($labelKey));
    }

    public static function getDefaultLabel($key)
    {
        if( $label = self::getLabelDefinition($key) )
        {
            return $label['default'];
        }
    }

    public static function getDescription($key)
    {
        if( $label = self::getLabelDefinition($key) )
        {
            return $label['desc'];
        }
    }

    public static function getLabelDefinition($key)
    {
        $labels = self::getLabels();
        return (isset($labels[$key]) ? $labels[$key] : NULL);
    }

    public static function getLabels()
    {
        if( empty(self::$labels) )
        {
            self::loadLabels();
        }
        return self::$labels;
    }

    public static function getLabelsByCategories()
    {
        if( empty(self::$labelsByCategories) )
        {
            self::loadLabels();
        }
        return self::$labelsByCategories;
    }

    protected static function loadLabels()
    {
        $file = explode("\n", file_get_contents(dirname(__FILE__) . '/' . self::FILENAME));
        foreach($file as $row)
        {
            $row = explode("\t", trim($row));
            if( count($row) >= 2 )
            {
                $label = array(
                    'default'  => $row[1],
                    'desc'     => (isset($row[2]) ? $row[2] : null),
                    'category' => (isset($row[3]) ? $row[3] : null),
                );
                self::$labels[$row[0]] = $label;
                self::$labelsByCategories[$label['category']][] = $row[0];
            }
        }
    }

}