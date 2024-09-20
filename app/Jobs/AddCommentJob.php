<?php

namespace App\Jobs;

use App\Events\AddCommentEvent;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $comment;

    /**
     * Create a new job instance.
     */
    public function __construct(array $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $comment = Comment::query()->create($this->comment);
        broadcast(new AddCommentEvent($comment));
    }
}
