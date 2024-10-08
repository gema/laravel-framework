<?php

namespace GemaDigital\Helpers;

class UnityHelper
{
    public static function stripTags(mixed $item): mixed
    {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                $item[$key] = self::stripTags($value);
            }

            return $item;
        }

        if (isset($item)) {
            $item = html_entity_decode((string) $item);
            $item = str_replace('<br/>', "\n", $item);
            $item = str_replace('<br />', "\n", $item);
            $item = str_replace('</p><p>', "\n\n", $item);
            $item = str_replace('<strong>', '[B]', $item);
            $item = str_replace('</strong>', '[/B]', $item);
            $item = strip_tags($item);
            $item = str_replace('[B]', '<b>', $item);
            $item = str_replace('[/B]', '</b>', $item);
            $item = stripslashes($item);
        }

        return $item;
    }
}
