<?php

namespace App\Models;

use App\Models\Concerns\UserCanComment;
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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Crime extends Model implements CommentableContract, HasMedia
{
    use Commentable;
    use InteractsWithMedia;
    use UserCanComment;

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
                $comments = $this->comments->sum('score') + $this->comments->count();

                if ($this->upvotes_count - $this->downvotes_count + $comments === 0) {
                    return 50;
                }

                return Number::format(max(0, $this->upvotes_count - $this->downvotes_count + $comments) / ($this->upvotes_count + $this->downvotes_count + $comments) * 100);
            }
        );
    }
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('compressed')
            ->quality(60) // Reduce quality to 70%
            ->format('webp') // Convert to JPG (reduces file size)
            ->sharpen(10) // Sharpen to maintain clarity
            ->nonQueued(); // Process conversion immediately
    }
}
