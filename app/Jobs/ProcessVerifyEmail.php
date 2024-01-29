<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyMail;
use Illuminate\Support\Facades\Log;

class ProcessVerifyEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    protected $url;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Envia un correo de verificación al usuario
     */
    public function handle(): void
    {
        try {
            Mail::to($this->user->email)->send(new VerifyMail($this->user, $this->url));
        } catch (\Throwable $th) {
            Log::channel('slackerror')->error($th->getMessage());
        }
    }
}
