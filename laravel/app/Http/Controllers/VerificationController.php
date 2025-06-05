<?
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use App\Models\User;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
   public function sendVerificationEmail(Request $request)
{
    \Log::info('Send verification request:', $request->all());

    // Check authentication
    $user = $request->user();
    if (!$user) {
        \Log::error('No authenticated user found');
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    // Validate input
    $validated = $request->validate([
        'email' => 'required|email',
        'tier' => 'required|string|in:Tier 1,Tier 2,Tier 3',
        'paymentOption' => 'required|string|in:monthly,yearly',
    ]);

    $email = $validated['email'];
    $tier = $validated['tier'];
    $paymentOption = $validated['paymentOption'];

    // Generate verification token
    $token = Str::random(40);
    $user->verification_token = $token;
    $user->save();

    // Send email
    try {
        Mail::to($email)->send(new VerificationEmail($token, $tier, $paymentOption));
        \Log::info('Verification email sent to: ' . $email);
    } catch (\Exception $e) {
        \Log::error('Failed to send verification email: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to send verification email', 'error' => $e->getMessage()], 500);
    }

    return response()->json(['token' => $token], 200);
}
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if ($user) {
            $user->verification_token = null;
            $user->save();
            return response()->json(['success' => true], 200);
        }

        return response()->json(['success' => false, 'message' => 'Invalid token'], 400);
    }
}