<?php

namespace App\Models;

use App\Models\Concerns\HasKeywords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasKeywords;

    /** Short label per MIME type, for the file-format badge. */
    public const TYPE_LABELS = [
        'application/pdf' => 'PDF',
        'image/jpeg' => 'JPG',
        'image/png' => 'PNG',
        'application/msword' => 'Word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
        'application/vnd.ms-excel' => 'Excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
        'application/vnd.ms-powerpoint' => 'PowerPoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint',
        'text/csv' => 'CSV',
        'text/plain' => 'Text',
        'application/zip' => 'ZIP',
    ];

    /** Accepted upload MIME types for the library. */
    public const ACCEPTED_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/csv',
        'text/plain',
        'application/zip',
    ];

    /** Knowledge Library category, mapped to its human label. */
    public const KINDS = [
        'method' => 'Method',
        'guide' => 'Interview guide',
        'instrument' => 'Research instrument',
        'framework' => 'Framework',
        'case_study' => 'Case study',
        'literature' => 'Literature',
        'handbook' => 'Handbook',
        'template' => 'Template',
    ];

    protected $fillable = [
        'title', 'kind', 'description', 'path', 'original_name', 'mime', 'size',
        'topic_id', 'region_id', 'user_id',
    ];

    protected static function booted(): void
    {
        // Keep file metadata in step with the stored file, and clean the file
        // up when the record (or its file) goes away.
        static::saving(function (Document $document): void {
            if ($document->isDirty('path') && filled($document->path)
                && Storage::disk('public')->exists($document->path)) {
                $document->mime = Storage::disk('public')->mimeType($document->path) ?: $document->mime;
                $document->size = Storage::disk('public')->size($document->path);

                if (blank($document->title)) {
                    $document->title = $document->original_name ?: basename($document->path);
                }
            }

            // A replaced file leaves the old one orphaned — remove it.
            if ($document->isDirty('path') && filled($document->getOriginal('path'))) {
                Storage::disk('public')->delete($document->getOriginal('path'));
            }
        });

        static::deleted(function (Document $document): void {
            if (filled($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
        });

        // Detach shared keywords (polymorphic pivot has no DB cascade here).
        static::deleting(fn (Document $document) => $document->keywords()->detach());
    }

    /** Short, human label for the file format (PDF, Word, Excel, …). */
    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->mime] ?? ($this->mime ?? '—');
    }

    /** Public URL to the stored file. */
    public function url(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }

    /** True when the file is an image we can preview inline. */
    public function isImage(): bool
    {
        return in_array($this->mime, ['image/jpeg', 'image/png'], true);
    }

    /** Human-readable file size, e.g. "2.4 MB". */
    public function sizeForHumans(): ?string
    {
        if (! $this->size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1) . ' ' . $units[$i];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
