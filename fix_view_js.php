<?php
$file = 'c:/dometopia/new_admin/resources/views/front/goods/view.blade.php';
$content = file_get_contents($file);

// Find the broken spot. It looks like: "        }\n            const formData = new FormData(form);"
// The '}' belongs to updateTotal().

// We want to insert "function processCart() {" and also define "const form" inside it.
// The code says: `const formData = new FormData(form);`
// So we need to put:
// function processCart() {
//    // form selection logic reuse
//    let form = document.forms['goodsForm'];
//    if (!form) form = document.getElementById('goodsForm');
//    if (!validateForm()) return; // Should we validate? Usually yes.
// 
//    const formData = new FormData(form);
//    ...
// }

// Let's create a pattern to match the broken part.
// The broken part starts with: `            const formData = new FormData(form);`
// And ends with `        }` (the end of the existing broken block which has no opening brace)

$brokenCodeStart = 'const formData = new FormData(form);';
$fixCode = <<<'EOT'
        function processCart() {
            if (!validateForm()) return;
            
            let form = document.forms['goodsForm'];
            if (!form) form = document.getElementById('goodsForm');
            if (!form) {
                alert('주문 폼을 찾을 수 없습니다.');
                return;
            }

            const formData = new FormData(form);
EOT;

if (strpos($content, $brokenCodeStart) !== false) {
    if (strpos($content, 'function processCart()') === false) {
        $content = str_replace($brokenCodeStart, $fixCode, $content);
        file_put_contents($file, $content);
        echo "Fixed processCart definition.\n";
    } else {
        echo "function processCart already seems to exist, but maybe broken?\n";
    }
} else {
    echo "Could not find broken code fragment.\n";
}
?>
