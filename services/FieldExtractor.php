<?php
/**
 * services/FieldExtractor.php
 * Turns raw OCR text into an editable list of rows - NO configuration
 * needed beforehand. Every document is different, so instead of matching
 * against a fixed list of fields, this looks at the text itself:
 *
 *   "Address: 25 Jalan ABC"   -> label "Address",         value "25 Jalan ABC"
 *   "Financial Year - 2026"   -> label "Financial Year",  value "2026"
 *   "ABC Sdn Bhd"              -> label "Line 1",          value "ABC Sdn Bhd" (no separator found)
 *
 * Staff can rename a label, edit a value, add a field OCR missed,
 * or remove a line that isn't useful (e.g. a page header).
 */
class FieldExtractor
{
    /** Returns an ordered list of ['key' => string, 'label' => string, 'value' => string]. */
    public static function autoDetect(string $rawText): array
    {
        $lines  = preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $rows   = [];
        $lineNo = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $lineNo++;

            // "Label: Value" or "Label - Value" - split on the FIRST separator only
            if (preg_match('/^(.{1,60}?)\s*[:\-]\s*(.+)$/', $line, $m) && trim($m[1]) !== '') {
                $label = trim($m[1]);
                $value = trim($m[2]);
            } else {
                // No obvious label: keep the whole line as the value
                $label = 'Line ' . $lineNo;
                $value = $line;
            }

            $rows[] = ['key' => self::slug($label, $lineNo), 'label' => $label, 'value' => $value];
        }

        return $rows;
    }

    /** "Financial Year" -> "financial_year_3" - stays under 50 chars, unique enough per row. */
    public static function slug(string $label, int $fallbackNumber = 0): string
    {
        $key = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($label)), '_');
        if ($key === '') {
            $key = 'field';
        }
        return mb_substr($key . '_' . $fallbackNumber, 0, 50);
    }
}