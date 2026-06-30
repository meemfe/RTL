<?php
declare(strict_types=1);

/*
 * RTL - Corrects reversed RTL text in Minecraft: Bedrock Edition
 * Copyright (C) 2026 github.com/meemfe/RTL
 * Licensed under GPL-3.0
 */

namespace rtl;

use function array_reverse;
use function count;
use function function_exists;
use function grapheme_strlen;
use function grapheme_substr;
use function implode;
use function preg_match;
use function preg_match_all;
use function strlen;
use function substr;

final class Processor {

        // Unicode ranges for RTL scripts.
        private const RTL_CHARS =
                "\x{0590}-\x{05FF}" .  // Hebrew
                "\x{0600}-\x{06FF}" .  // Arabic (incl. Persian letters & digits)
                "\x{0700}-\x{074F}" .  // Syriac
                "\x{0750}-\x{077F}" .  // Arabic Supplement
                "\x{0780}-\x{07BF}" .  // Thaana
                "\x{07C0}-\x{07FF}" .  // NKo
                "\x{0800}-\x{083F}" .  // Samaritan
                "\x{0840}-\x{085F}" .  // Mandaic
                "\x{08A0}-\x{08FF}" .  // Arabic Extended-A
                "\x{200C}-\x{200F}" .  // ZWNJ, ZWJ, LRM, RLM
                "\x{202A}-\x{202E}" .  // BiDi controls
                "\x{FB1D}-\x{FB4F}" .  // Hebrew Presentation Forms
                "\x{FB50}-\x{FDFF}" .  // Arabic Presentation Forms-A
                "\x{FE70}-\x{FEFF}";   // Arabic Presentation Forms-B

        private const CHAR_PATTERN = '/[' . self::RTL_CHARS . ']/u';
        private const SEGMENT_PATTERN = '/[' . self::RTL_CHARS . ']+(?:\s+[' . self::RTL_CHARS . ']+)*/u';

        public function hasRtl(string $text): bool {
                return $text !== "" && preg_match(self::CHAR_PATTERN, $text) === 1;
        }

        public function correct(string $text): string {
                if (!$this->hasRtl($text)) {
                        return $text;
                }

                $matches = [];
                if (preg_match_all(self::SEGMENT_PATTERN, $text, $matches, PREG_OFFSET_CAPTURE) === false) {
                        return $text;
                }
                if (empty($matches[0])) {
                        return $text;
                }

                $segments = [];
                foreach ($matches[0] as $m) {
                        $segments[] = ['text' => $m[0], 'offset' => $m[1]];
                }

                // Swap segment order and reverse chars in each.
                // Bedrock will undo both, leaving the text correct.
                $count = count($segments);
                $swapped = [];
                for ($i = 0; $i < $count; $i++) {
                        $swapped[$i] = self::reverse($segments[$count - 1 - $i]['text']);
                }

                // Stitch LTR text and the swapped RTL segments back together.
                $result = "";
                $cursor = 0;
                for ($i = 0; $i < $count; $i++) {
                        $offset = $segments[$i]['offset'];
                        $length = strlen($segments[$i]['text']);

                        if ($offset > $cursor) {
                                $result .= substr($text, $cursor, $offset - $cursor);
                        }
                        $result .= $swapped[$i];
                        $cursor = $offset + $length;
                }
                if ($cursor < strlen($text)) {
                        $result .= substr($text, $cursor);
                }

                return $result;
        }

        // Grapheme-safe so combining marks (Arabic harakat) stay attached.
        private static function reverse(string $str): string {
                if ($str === "") {
                        return "";
                }

                if (function_exists("grapheme_strlen")) {
                        $len = grapheme_strlen($str);
                        if ($len === false || $len <= 0) {
                                return $str;
                        }
                        $out = "";
                        for ($i = $len - 1; $i >= 0; $i--) {
                                $chunk = grapheme_substr($str, $i, 1);
                                if ($chunk !== false) {
                                        $out .= $chunk;
                                }
                        }
                        return $out;
                }

                // Fallback without ext-intl.
                preg_match_all('/./us', $str, $chars);
                return implode("", array_reverse($chars[0]));
        }
}
