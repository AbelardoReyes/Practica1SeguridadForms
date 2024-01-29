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
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $SMS = Http::withBasicAuth('ACd8e2ad424b562a15ce13ac163a54bce7', '8f59cf71ab57da1742feb7179f55247b')
                ->asForm()
                ->post('https://api.twilio.com/2010-04-01/Accounts/ACd8e2ad424b562a15ce13ac163a54bce7/Messages.json', [
                    'To' => "whatsapp:+5218714733996",
                    'From' => "whatsapp:+14155238886",
                    'Body' => "Tu código de verificación es: " . $this->nRandom
                ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
