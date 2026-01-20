try {
echo "=== Login Verification ===\n";
$req = new Illuminate\Http\Request();
$req->replace(['userid' => 'dometopia', 'password' => '1234']);

$controller = new App\Http\Controllers\Front\MemberController();
// Validate manually since calling method directly bypasses route validation middleware sometimes in simple tests,
// but Controller uses validate() which needs a Request.
// However, validation might fail if session/request isn't perfectly set up in Tinker.
// Let's manually test logic:

$userid = 'dometopia';
$password = '1234';
$str_sha = hash('sha256', $password);
echo "Target Hash (SHA256 of 1234): " . $str_sha . "\n";

$member = App\Models\Member::where('userid', $userid)
->where(function ($query) use ($str_sha) {
$query->where('password', $str_sha);
})
->first();

if ($member) {
echo "Login Logic Success: Found member " . $member->userid . "\n";
Auth::login($member);
echo "Auth::check() = " . (Auth::check() ? 'True' : 'False') . "\n";
echo "Auth::user()->userid = " . Auth::user()->userid . "\n";
} else {
echo "Login Logic Failed: Member not found with hash.\n";
}

} catch (\Exception $e) {
echo "ERROR: " . $e->getMessage() . "\n";
}