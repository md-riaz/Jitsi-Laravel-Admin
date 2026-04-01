<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('template_key')->unique();
            $table->string('name');
            $table->string('mailable_class')->nullable();
            $table->string('view_name')->nullable();
            $table->string('subject_template');
            $table->longText('body_html');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $templates = config('mail-templates.templates', []);

        foreach ($templates as $template) {
            DB::table('mail_templates')->insert([
                'template_key' => $template['template_key'],
                'name' => $template['name'],
                'mailable_class' => $template['mailable_class'] ?? null,
                'view_name' => $template['view_name'] ?? null,
                'subject_template' => $template['subject_template'],
                'body_html' => $template['body_html'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
