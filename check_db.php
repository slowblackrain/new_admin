<?php
try {
    echo "Checking 'password()' function...\n";
    $res = DB::select("SELECT password('1234') as p");
    echo "Result: " . $res[0]->p . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

try {
    echo "Checking 'old_password()' function...\n";
    $res = DB::select("SELECT old_password('1234') as p");
    echo "Result: " . $res[0]->p . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
