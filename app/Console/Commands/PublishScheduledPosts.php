<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'blog:publish-scheduled';

    protected $description = 'Publish posts whose scheduled publish time has passed';

    public function handle(): int
    {
        $count = Post::where('is_published', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['is_published' => true]);

        $this->info("Published {$count} scheduled posts.");

        return self::SUCCESS;
    }
}
