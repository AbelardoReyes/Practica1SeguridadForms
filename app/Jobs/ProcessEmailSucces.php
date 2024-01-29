<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmailSucces;
use Illuminate\Support\Facades\Log;

class ProcessEmailSucces implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->user->email)->send(new SendEmailSucces($this->user));
            Log::channel('slackinfo')->info('Esta cuenta ha sido activada' . $this->user->email);
        } catch (\Throwable $th) {
            Log::channel('slackerror')->critical($th->getMessage());
        }
    }
}
