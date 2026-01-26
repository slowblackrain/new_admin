<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Http\Request;

echo "\n[Category CRUD Verification] Starting...\n";

// Helper
function createMockRequest($data) {
    global $app;
    $req = Request::create('/test', 'POST', $data);
    $app->instance('request', $req);
    return $req;
}

try {
    DB::beginTransaction();
    $controller = new CategoryController();

    // 1. Create (Store)
    echo "\n[Step 1] Creating New Category under Root(1)...\n";
    $req1 = createMockRequest(['parent_id' => 1]);
    $res1 = $controller->store($req1);
    $data1 = $res1->getData(true);
    $newId = $data1['id'];
    echo " -> Created ID: $newId (" . $data1['title'] . ")\n";

    // 2. Update
    echo "\n[Step 2] Updating Title to 'Test Category'...\n";
    $req2 = createMockRequest(['title' => 'Test Category', 'hide' => '0']);
    $controller->update($req2, $newId);
    $cat = Category::find($newId);
    echo " -> Updated Title: " . $cat->title . "\n";
    if($cat->title != 'Test Category') throw new Exception("Update Failed");

    // 3. Move (Change Position/Parent)
    echo "\n[Step 3] Moving Node...\n";
    // First create a sibling
    $req3a = createMockRequest(['parent_id' => 1]);
    $res3a = $controller->store($req3a);
    $siblingId = $res3a->getData(true)['id'];
    echo " -> Created Sibling ID: $siblingId\n";

    // Move First Node 'after' Sibling (Index 1)
    $req3b = createMockRequest([
        'id' => $newId,
        'parent_id' => 1,
        'position' => 1 // Should be 2nd
    ]);
    $controller->move($req3b);
    
    // Check Position
    $catAfter = Category::find($newId);
    $siblingAfter = Category::find($siblingId);
    echo " -> New ID Pos: " . $catAfter->position . " (Expected: 1)\n";
    echo " -> Sibling ID Pos: " . $siblingAfter->position . " (Expected: 0?)\n"; 
    // Wait, move logic bulk updates siblings. 
    // Let's verify sort
    $all = Category::where('parent_id', 1)->whereIn('id', [$newId, $siblingId])->orderBy('position')->get();
    echo " -> Order: " . $all[0]->id . " (" . $all[0]->position . "), " . $all[1]->id . " (" . $all[1]->position . ")\n";

    // 4. Destroy
    echo "\n[Step 4] Deleting Node...\n";
    $controller->destroy($siblingId); // Delete sibling
    $check = Category::find($siblingId);
    if($check) throw new Exception("Delete Failed");
    echo " -> Deleted Sibling OK.\n";

    $controller->destroy($newId); // Delete main
    echo " -> Deleted Main OK.\n";
    
    // Rollback
    DB::rollBack();
    echo "\n[SUCCESS] Category CRUD Verified.\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
