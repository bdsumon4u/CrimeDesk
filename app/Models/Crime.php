<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;
use LakM\Comments\Concerns\Commentable;
use LakM\Comments\Contracts\CommentableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Crime extends Model implements CommentableContract, HasMedia
{
    use Commentable;
    use InteractsWithMedia;

    public $guestMode = false;

    protected function casts(): array
    {
        return [
            'happened_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function reacts(): HasMany
    {
        return $this->hasMany(React::class);
    }

    public function upvotes(): HasMany
    {
        return $this->reacts()->where('vote', '>', 0);
    }

    public function downvotes(): HasMany
    {
        return $this->reacts()->where('vote', '<', 0);
    }

    public function userReact(): BelongsTo
    {
        return $this->belongsTo(React::class, 'id', 'crime_id')
            ->where('user_id', Filament::auth()->id());
    }

    public function userVote(): Attribute
    {
        return new Attribute(
            get: fn () => $this->userReact?->vote,
        );
    }

    public function like(): void
    {
        if (! $this->userReact) {
            $this->reacts()->create([
                'user_id' => Filament::auth()->id(),
                'vote' => 1,
            ]);
        } elseif ($this->userReact->vote > 0) {
            $this->userReact->delete();
        } else {
            $this->userReact->update([
                'vote' => 1,
            ]);
        }
    }

    public function dislike(): void
    {
        if (! $this->userReact) {
            $this->reacts()->create([
                'user_id' => Filament::auth()->id(),
                'vote' => -1,
            ]);
        } elseif ($this->userReact->vote < 0) {
            $this->userReact->delete();
        } else {
            $this->userReact->update([
                'vote' => -1,
            ]);
        }
    }

    public function score(): Attribute
    {
        return new Attribute(
            get: function () {
                $votes = $this->upvotes_count + $this->downvotes_count;
                $comments = $this->comments->sum('score') + $this->comments->count();

                return Number::format(($votes + $comments) / ($votes + $comments) * 100);
            }
        );
    }
}
