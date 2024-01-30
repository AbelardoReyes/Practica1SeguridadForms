<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProcessFactorAuthSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    protected $nRandom;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $nRandom)
    {
        $this->user = $user;
        $this->nRandom = $nRandom;
    }
    /**
     * Envia un SMS con el código de verificación al usuario como segundo factor de autenticación
     */
    public function handle(): void
    {
        try {
            Http::withBasicAuth(env('SID'), env('TWILIO_AUTH_TOKEN'))
                ->asForm()
                ->post(env('URL_TWILIO'), [
                    'To' => "whatsapp:+521" . $this->user->phone,
                    'From' => "whatsapp:+14155238886",
                    'Body' => "Tu código de verificación es: " . $this->nRandom
                ]);
            Log::info('Se envio un SMS de verificación a ' . $this->user->email);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
