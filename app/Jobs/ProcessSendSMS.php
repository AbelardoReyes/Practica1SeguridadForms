<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class ProcessSendSMS implements ShouldQueue
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
     * Envia un SMS con el código de verificación al usuario
     */
    public function handle(): void
    {
        try {
            Http::withBasicAuth('ACd8e2ad424b562a15ce13ac163a54bce7', '0f0bbb09ef682b08505f0fe4b01f4f3a')
                ->asForm()
                ->post('https://api.twilio.com/2010-04-01/Accounts/ACd8e2ad424b562a15ce13ac163a54bce7/Messages.json', [
                    'To' => "whatsapp:+521" . $this->user->phone,
                    'From' => "whatsapp:+14155238886",
                    'Body' => "Tu código de verificación es: " . $this->nRandom
                ]);
            Log::channel('slackinfo')->warning('Se envio un SMS de verificación a ' . $this->user->email);
        } catch (\Throwable $th) {
            Log::channel('slackerror')->error($th->getMessage());
        }
    }
}
