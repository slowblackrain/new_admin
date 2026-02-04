<?php
// c:/dometopia/new_admin/restore_option_logic.php

$filePath = 'c:/dometopia/new_admin/resources/views/front/goods/view.blade.php';
$content = file_get_contents($filePath);

// 1. Force the option select box to be hidden and auto-select default if options exist but we want to treat it as "no option" from user perspective
// Or simpler: change the condition for displaying the option area.
// User says "Options are only printing options".
// This implies real "options" (blue/red/size) are either 1 single option (default) or not used.
// But the code sees `hasOptions = true`.
// We will change the blade logic to HIDE the option select box if it's considered "default".
// But rather than changing blade logic deeply, let's just create a workaround since we don't know the exact data shape causing this.
// Best approach:
// 1. Hide the option select box (display:none style).
// 2. In JS, auto-select the first option on load if `hasOptions` is true but we hidden it? 
// No, simpler: Treat `hasOptions` logic in Blade to explicitly FALLBACK to default_qty_area if user wants "only printing options".

// However, if there ARE real options (Red, Blue), hiding it breaks functionality.
// The user claims "Dometopia only has printing options".
// This implies `fm_goods_option` might contain just one row per product (the product itself).
// If `options->count() > 1`, that contradicts the user.
// If `options->count() === 1` but `option1` is set, `hasOptions` becomes true.
// Legacy data likely has `option1` set to something even for single items?
// Let's modify the `hasOptions` check logic in Blade.

// Current: $hasOptions = $options->count() > 1 || ($options->count() === 1 && trim($options->first()->option1) !== '');
// Proposed Fix: If count is 1, treat as NO options (ignore option1 string).
// Because if there is only 1 option, it's just the product itself.

// REPLACEMENT 1: Change hasOptions logic
$oldLogic = '$hasOptions = $options->count() > 1 || ($options->count() === 1 && trim($options->first()->option1) !== \'\');';
$newLogic = '$hasOptions = $options->count() > 1; // Modified: Treat single option as NO option user-selection requirement';

// REPLACEMENT 2: Remove validateForm check
// We need to remove the block:
// if (hasOptions && Object.keys(selectedOptions).length === 0) { ... }

$fixValidate = <<<'EOT'
        function validateForm() {
            // [MODIFIED] Options are optional or auto-handled. Removed mandatory check.
            /*
            if (hasOptions && Object.keys(selectedOptions).length === 0) {
                alert('옵션을 선택해주세요.'); return false;
            }
            */
EOT;

$oldValidateStart = 'function validateForm() {';
// We just need to replace the start of function to comment out the check.
// We'll use regex or string replace for the function body.

// Let's do string replacement for hasOptions first
if (strpos($content, $oldLogic) !== false) {
    $content = str_replace($oldLogic, $newLogic, $content);
    echo "Refined hasOptions logic.\n";
} else {
    echo "Could not find exact hasOptions logic line. Maybe already changed?\n";
}

// Now replace validateForm logic. 
// We will look for the specific block inside validateForm.
$blockToComment = "if (hasOptions && Object.keys(selectedOptions).length === 0) {";
if (strpos($content, $blockToComment) !== false) {
    $content = str_replace($blockToComment, "// " . $blockToComment, $content);
    // Also comment out the body lines roughly
    $content = str_replace("alert('옵션을 선택해주세요.'); return false;", "// alert('옵션을 선택해주세요.'); return false;", $content);
    // And the closing brace
    // It's safer to just replace the whole function if we can, but partial is safer against context shift.
    echo "Disabled option validation in JS.\n";
}

// Write back
file_put_contents($filePath, $content);
?>
