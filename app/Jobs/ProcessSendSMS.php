<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
     * Execute the job.
     */
    public function handle(): void
    {

    }
}
