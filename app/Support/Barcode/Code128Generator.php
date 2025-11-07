<?php

namespace App\Support\Barcode;

class Code128Generator
{
    private const CODE_PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_CODE_B = 104;
    private const STOP_CODE = 106;
    private const QUIET_ZONE_WIDTH = 10;

    public function generate(string $value, int $height = 40, int $scale = 1): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('Cannot render barcode for empty value.');
        }

        $codes = $this->encodeCodeSetB($value);

        $patternSequences = [];
        $patternSequences[] = self::CODE_PATTERNS[self::START_CODE_B];

        $checksum = self::START_CODE_B;
        foreach ($codes as $index => $code) {
            $patternSequences[] = self::CODE_PATTERNS[$code];
            $checksum += $code * ($index + 1);
        }

        $checksum = $checksum % 103;
        $patternSequences[] = self::CODE_PATTERNS[$checksum];
        $patternSequences[] = self::CODE_PATTERNS[self::STOP_CODE];

        $totalUnits = self::QUIET_ZONE_WIDTH * 2;
        foreach ($patternSequences as $pattern) {
            $totalUnits += array_sum(str_split($pattern));
        }

        $width = $totalUnits * $scale;
        $height = max($height, 10);

        $x = self::QUIET_ZONE_WIDTH * $scale;
        $bars = [];

        foreach ($patternSequences as $pattern) {
            $digits = str_split($pattern);
            $isBar = true;

            foreach ($digits as $digit) {
                $moduleWidth = (int) $digit * $scale;

                if ($isBar) {
                    $bars[] = sprintf('<rect x="%s" y="0" width="%s" height="%s" />', $x, $moduleWidth, $height);
                }

                $x += $moduleWidth;
                $isBar = ! $isBar;
            }
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" role="img" aria-label="%s">',
            $width,
            $height,
            $width,
            $height,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        );

        $svg .= '<g fill="currentColor">' . implode('', $bars) . '</g>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * @return int[]
     */
    private function encodeCodeSetB(string $value): array
    {
        $codes = [];

        foreach (preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) as $character) {
            $ord = $this->charCode($character);

            if ($ord < 32 || $ord > 126) {
                throw new \InvalidArgumentException('CODE128 (set B) only supports ASCII 32-126 characters.');
            }

            $codes[] = $ord - 32;
        }

        return $codes;
    }

    private function charCode(string $character): int
    {
        if (strlen($character) === 1) {
            return ord($character);
        }

        $utf16 = mb_convert_encoding($character, 'UTF-16BE', 'UTF-8');
        $code = unpack('n', $utf16);

        return $code[1];
    }
}
