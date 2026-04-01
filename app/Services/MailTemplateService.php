<?php

namespace App\Services;

use App\Models\MailTemplate;

class MailTemplateService
{
    public function renderSubject(string $templateKey, array $variables, string $fallback): string
    {
        $template = MailTemplate::query()
            ->where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (!$template || empty($template->subject_template)) {
            return $this->interpolate($fallback, $variables);
        }

        return $this->interpolate($template->subject_template, $variables);
    }

    public function renderBodyHtml(string $templateKey, array $variables, string $fallbackHtml): string
    {
        $template = MailTemplate::query()
            ->where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (!$template || empty($template->body_html)) {
            return $this->interpolate($fallbackHtml, $variables);
        }

        return $this->interpolate($template->body_html, $variables);
    }

    public function interpolateRaw(string $template, array $variables): string
    {
        return $this->interpolate($template, $variables);
    }

    private function interpolate(string $template, array $variables): string
    {
        $replacements = [];

        foreach ($variables as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) ($value ?? '');
        }

        return strtr($template, $replacements);
    }
}
