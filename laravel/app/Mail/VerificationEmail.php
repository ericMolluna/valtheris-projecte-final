<?
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $tier;
    public $paymentOption;

    public function __construct($token, $tier, $paymentOption)
    {
        $this->token = $token;
        $this->tier = $tier;
        $this->paymentOption = $paymentOption;
    }

    public function build()
    {
        return $this->subject('Verificación de Suscripción')
            ->view('emails.verification')
            ->with([
                'token' => $this->token,
                'tier' => $this->tier,
                'paymentOption' => $this->paymentOption,
            ]);
    }
}