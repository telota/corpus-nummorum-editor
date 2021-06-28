<?php

namespace App\Http\Controllers\dbi\handler;


class legend_handler {

    public function createIndex ($legend, $language = 'el') {

        $split = preg_split('~~u', trim($legend), -1, PREG_SPLIT_NO_EMPTY);
        $index = [];

        foreach ($split as $key => $char) {

            if (!in_array($char, ['?', '•', '·', '·', '⠁', '*', '–', '-', '/', '(', ')', '[', ']', '{', '}', '<', '>', '[...]'])) {
                if ($language === 'el') {

                    // Replace Epsilon
                    if (in_array($char, ['Є'])) $index[] = 'Ε';

                    // Replace Sigma
                    else if (in_array($char, ['ᛈ', 'Ϲ'])) $index[] = 'Σ';

                    // Replace Ypsilon
                    else if (in_array($char, ['V'])) $index[] = 'Υ';

                    // Replace И
                    else if (in_array($char, ['И'])) $index[] = 'Ν';

                    // Replace Omega
                    else if (in_array($char, ['W'])) $index[] = 'Ω';

                    else $index[] = $char;
                }
                else $index[] = $char;
            }
        }

        $index = implode('', $index);
        $index = trim(preg_replace("/\s+/"," ",$index));

        if (substr($index, 0, 3) === '...') $index = '.'.trim(substr($index, 3));

        $index = str_replace("...", "", $index);
        $index = trim(preg_replace("/\s+/"," ", $index));

        return $index;
    }
}
