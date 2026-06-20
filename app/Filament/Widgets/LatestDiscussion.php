<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Filament\Resources\Topics\TopicResource;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\Topic;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LatestDiscussion extends Widget
{
    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.latest-discussion';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    /**
     * Newest comments across every idea and topic, shaped for the view.
     *
     * @return Collection<int, object>
     */
    public function getComments(): Collection
    {
        return Comment::query()
            ->with(['user', 'commentable'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (Comment $comment): object {
                $subject = $comment->commentable;

                [$subjectLabel, $subjectUrl] = match (true) {
                    $subject instanceof Idea => [$subject->title, IdeaResource::getUrl('edit', ['record' => $subject])],
                    $subject instanceof Topic => [$subject->name, TopicResource::getUrl('edit', ['record' => $subject])],
                    default => ['(deleted)', null],
                };

                return (object) [
                    'author' => $comment->user?->name ?? 'Unknown',
                    'body' => (string) str($comment->body)->limit(180),
                    'subjectLabel' => $subjectLabel,
                    'subjectUrl' => $subjectUrl,
                    'when' => $comment->created_at?->diffForHumans(),
                    'isReply' => filled($comment->parent_id),
                ];
            });
    }
}
