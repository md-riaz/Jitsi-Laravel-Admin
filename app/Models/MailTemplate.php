<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class MailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_key',
        'name',
        'mailable_class',
        'view_name',
        'subject_template',
        'body_html',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (MailTemplate $template): void {
            $allowed = $template->allowedPlaceholders();

            $unknownSubject = array_values(array_diff(
                $template->extractPlaceholders((string) $template->subject_template),
                $allowed
            ));

            $unknownBody = array_values(array_diff(
                $template->extractPlaceholders((string) $template->body_html),
                $allowed
            ));

            if (!empty($unknownSubject) || !empty($unknownBody)) {
                throw ValidationException::withMessages([
                    'subject_template' => !empty($unknownSubject)
                        ? ['Unknown placeholder(s): ' . implode(', ', array_map(fn (string $k) => '{{' . $k . '}}', $unknownSubject))]
                        : [],
                    'body_html' => !empty($unknownBody)
                        ? ['Unknown placeholder(s): ' . implode(', ', array_map(fn (string $k) => '{{' . $k . '}}', $unknownBody))]
                        : [],
                ]);
            }
        });
    }

    public function allowedPlaceholders(): array
    {
        $templates = (array) config('mail-templates.templates', []);
        $definition = collect($templates)->firstWhere('template_key', $this->template_key);

        if (!$definition) {
            return [];
        }

        $subjectTokens = $this->extractPlaceholders((string) ($definition['subject_template'] ?? ''));
        $bodyTokens = $this->extractPlaceholders((string) ($definition['body_html'] ?? ''));

        $tokens = array_unique(array_merge($subjectTokens, $bodyTokens));
        sort($tokens);

        return $tokens;
    }

    public function extractPlaceholders(string $content): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_\.:-]+)\s*\}\}/', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $tokens = array_values(array_unique(array_map(fn (string $token) => trim($token), $matches[1])));
        sort($tokens);

        return $tokens;
    }
}
