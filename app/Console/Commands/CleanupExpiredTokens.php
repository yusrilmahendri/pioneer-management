<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired authentication tokens (older than 24 hours)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCount = PersonalAccessToken::where('created_at', '<', now()->subHours(24))->count();
        
        PersonalAccessToken::where('created_at', '<', now()->subHours(24))->delete();
        
        $this->info("Cleaned up {$expiredCount} expired tokens.");
        
        return 0;
    }
}
