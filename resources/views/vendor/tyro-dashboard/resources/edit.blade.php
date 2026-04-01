@extends('tyro-dashboard::layouts.app')

@section('title', 'Edit ' . Str::singular($config['title']))

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('tyro-dashboard.resources.index', $resource) }}">{{ $config['title'] }}</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @foreach($config['fields'] as $key => $field)
        @if (($field['type'] ?? '') === 'richtext')
            (function () {
                var key = '{{ $key }}';
                var textarea = document.getElementById(key);
                if (textarea) {
                    ClassicEditor.create(textarea).catch(function (error) {
                        console.error('CKEditor init failed for field: ' + key, error);
                    });
                }
            })();
        @endif
        @endforeach

        // Markdown editors
        @foreach($config['fields'] as $key => $field)
        @if (($field['type'] ?? '') === 'markdown')
            (function () {
                var key = '{{ $key }}';
                var textarea = document.getElementById(key);

                if (textarea) {
                    new EasyMDE({
                        element: textarea,
                        spellChecker: false,
                        status: false,
                        toolbar: [
                            "bold",
                            "italic",
                            "heading",
                            "|",
                            "quote",
                            "unordered-list",
                            "ordered-list",
                            "|",
                            "link",
                            "image",
                            "|",
                            "preview",
                        ]
                    });
                }
            })();
        @endif
        @endforeach
    });
</script>
@endpush

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="{{ route('tyro-dashboard.resources.index', $resource) }}" class="btn btn-ghost" title="Back to {{ $config['title'] }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="page-title">Edit {{ Str::singular($config['title']) }}</h1>
    </div>
</div>

@if($resource === 'mail_templates')
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <h3 style="margin: 0 0 0.75rem 0;">Available placeholders</h3>
        @php
            $availablePlaceholders = method_exists($item, 'allowedPlaceholders') ? $item->allowedPlaceholders() : [];
            $previewDefaults = [
                'meeting_title' => 'Weekly Product Sync',
                'meeting_date' => now()->addDay()->format('M d, Y'),
                'meeting_time' => now()->addDay()->format('g:i A'),
                'meeting_datetime' => now()->addDay()->format('M d, Y g:i A'),
                'meeting_duration_minutes' => '45',
                'organizer_name' => 'Alex Morgan',
                'invite_url' => url('/invite/demo-token'),
                'meeting_url' => url('/meet/demo-meeting'),
                'join_early_minutes' => '10',
                'minutes_until_start' => '15',
                'cancellation_reason' => 'Schedule conflict',
                'changes_html' => '<ul><li>Time changed from 2:00 PM to 2:30 PM</li></ul>',
            ];
            $previewVariables = [];
            foreach ($availablePlaceholders as $token) {
                $previewVariables[$token] = $previewDefaults[$token] ?? '[sample_' . $token . ']';
            }
            $previewSubject = app(\App\Services\MailTemplateService::class)->interpolateRaw((string) ($item->subject_template ?? ''), $previewVariables);
            $previewBodyHtml = app(\App\Services\MailTemplateService::class)->interpolateRaw((string) ($item->body_html ?? ''), $previewVariables);
        @endphp

        @if(empty($availablePlaceholders))
            <p style="margin: 0; color: var(--text-secondary);">No placeholders defined for this template.</p>
        @else
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                @foreach($availablePlaceholders as $placeholder)
                    <code style="background: var(--muted); padding: 4px 8px; border-radius: 6px;">{{ '{' . '{' . $placeholder . '}' . '}' }}</code>
                @endforeach
            </div>
        @endif

        <h3 style="margin: 0 0 0.75rem 0;">Server preview (current saved content)</h3>
        <div style="margin-bottom: 0.75rem;">
            <strong>Subject:</strong>
            <div style="margin-top: 0.25rem; padding: 0.625rem; border: 1px solid var(--border); border-radius: 8px; background: var(--muted);">
                {{ $previewSubject }}
            </div>
        </div>
        <div>
            <strong>Body:</strong>
            <div style="margin-top: 0.25rem; padding: 0.875rem; border: 1px solid var(--border); border-radius: 8px; background: #fff;">
                {!! $previewBodyHtml !!}
            </div>
        </div>
        <p style="margin: 0.75rem 0 0 0; color: var(--text-secondary); font-size: 0.875rem;">Preview updates after save in this first pass.</p>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('tyro-dashboard.resources.update', [$resource, $item->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @foreach($config['fields'] as $key => $field)
            @if(($field['hide_in_form'] ?? false) || ($field['hide_in_edit'] ?? false))
            @continue
            @endif

            @if($field['type'] === 'hidden')
            <input type="hidden" name="{{ $key }}" value="{{ old($key, $item->$key) }}">
            @continue
            @endif

            @if($field['type'] === 'password')
            {{-- For password, don't show value, and maybe handle updating differently --}}
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="{{ $key }}" class="form-label">{{ $field['label'] }} <small>(Leave blank to keep current)</small></label>
                <input type="password" name="{{ $key }}" id="{{ $key }}" class="form-input @error($key) is-invalid @enderror">
                @error($key)
                @if(config('tyro-dashboard.resource_ui.show_field_errors', true))
                <div class="form-error" style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @endif
                @enderror
            </div>
            @continue
            @endif

            <div class="form-group" style="margin-bottom: 1rem;">
                @if(!(($field['type'] ?? '') === 'checkbox' && !isset($options[$key]) && !isset($field['options'])))
                <label for="{{ $key }}" class="form-label">{{ $field['label'] }}</label>
                @endif

                @if($field['type'] === 'textarea')
                <textarea name="{{ $key }}" id="{{ $key }}" class="form-input @error($key) is-invalid @enderror" rows="5" placeholder="{{ $field['placeholder'] ?? '' }}" {{ ($field['readonly'] ?? false) ? 'readonly' : '' }} @if(isset($field['attributes'])) @foreach($field['attributes'] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach @endif>{{ old($key, $item->$key) }}</textarea>

                @elseif($field['type'] === 'richtext')
                <textarea name="{{ $key }}" id="{{ $key }}" class="form-input @error($key) is-invalid @enderror" rows="10" placeholder="{{ $field['placeholder'] ?? '' }}" {{ ($field['readonly'] ?? false) ? 'readonly' : '' }} @if(isset($field['attributes'])) @foreach($field['attributes'] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach @endif>{{ old($key, $item->$key) }}</textarea>

                @elseif($field['type'] === 'markdown')
                <textarea name="{{ $key }}" id="{{ $key }}" class="@error($key) is-invalid @enderror" placeholder="{{ $field['placeholder'] ?? '' }}">{{ old($key, $item->$key) }}</textarea>

                @elseif($field['type'] === 'select')
                @if($field['multiple'] ?? false)
                <select name="{{ $key }}[]" id="{{ $key }}" class="form-select @error($key) is-invalid @enderror" multiple>
                    @if(isset($options[$key]))
                    @foreach($options[$key] as $option)
                    <option value="{{ $option->id }}" {{ in_array($option->id, old($key, $selectedValues[$key] ?? [])) ? 'selected' : '' }}>
                        {{ $option->{$field['option_label'] ?? 'name'} }}
                    </option>
                    @endforeach
                    @elseif(isset($field['options']))
                    @foreach($field['options'] as $value => $label)
                    @php
                    $optionValue = is_int($value) ? $label : $value;
                    $optionLabel = $label;
                    @endphp
                    <option value="{{ $optionValue }}" {{ in_array($optionValue, old($key, $selectedValues[$key] ?? [])) ? 'selected' : '' }}>
                        {{ $optionLabel }}
                    </option>
                    @endforeach
                    @endif
                </select>
                @else
                <select name="{{ $key }}" id="{{ $key }}" class="form-select @error($key) is-invalid @enderror">
                    <option value="">Select {{ $field['label'] }}</option>
                    @if(isset($options[$key]))
                    @foreach($options[$key] as $option)
                    <option value="{{ $option->id }}" {{ old($key, $item->$key) == $option->id ? 'selected' : '' }}>
                        {{ $option->{$field['option_label'] ?? 'name'} }}
                    </option>
                    @endforeach
                    @elseif(isset($field['options']))
                    @foreach($field['options'] as $value => $label)
                    @php
                    $optionValue = is_int($value) ? $label : $value;
                    $optionLabel = $label;
                    @endphp
                    <option value="{{ $optionValue }}" {{ old($key, $item->$key) == $optionValue ? 'selected' : '' }}>
                        {{ $optionLabel }}
                    </option>
                    @endforeach
                    @endif
                </select>
                @endif

                @elseif($field['type'] === 'multiselect')
                <select name="{{ $key }}[]" id="{{ $key }}" class="form-select @error($key) is-invalid @enderror" multiple>
                    @if(isset($options[$key]))
                    @foreach($options[$key] as $option)
                    <option value="{{ $option->id }}" {{ in_array($option->id, old($key, $selectedValues[$key] ?? ($item->$key ?? []))) ? 'selected' : '' }}>
                        {{ $option->{$field['option_label'] ?? 'name'} }}
                    </option>
                    @endforeach
                    @elseif(isset($field['options']))
                    @foreach($field['options'] as $value => $label)
                    @php
                    $optionValue = is_int($value) ? $label : $value;
                    $optionLabel = $label;
                    @endphp
                    <option value="{{ $optionValue }}" {{ in_array($optionValue, old($key, $item->$key ?? [])) ? 'selected' : '' }}>
                        {{ $optionLabel }}
                    </option>
                    @endforeach
                    @endif
                </select>

                @elseif($field['type'] === 'radio')
                <div class="radio-group">
                    @if(isset($options[$key]))
                    @foreach($options[$key] as $option)
                    <div class="form-check">
                        <input type="radio" name="{{ $key }}" id="{{ $key }}_{{ $option->id }}" value="{{ $option->id }}" {{ old($key, $item->$key) == $option->id ? 'checked' : '' }}>
                        <label for="{{ $key }}_{{ $option->id }}">{{ $option->{$field['option_label'] ?? 'name'} }}</label>
                    </div>
                    @endforeach
                    @elseif(isset($field['options']))
                    @foreach($field['options'] as $value => $label)
                    @php
                    $optionValue = is_int($value) ? $label : $value;
                    $optionLabel = $label;
                    @endphp
                    <div class="form-check">
                        <input type="radio" name="{{ $key }}" id="{{ $key }}_{{ $optionValue }}" value="{{ $optionValue }}" {{ old($key, $item->$key) == $optionValue ? 'checked' : '' }}>
                        <label for="{{ $key }}_{{ $optionValue }}">{{ $optionLabel }}</label>
                    </div>
                    @endforeach
                    @endif
                </div>

                @elseif($field['type'] === 'checkbox' && !isset($options[$key]) && !isset($field['options']))
                <div class="form-check" style="display:flex; align-items:center; gap:0.5rem;">
                    <input type="hidden" name="{{ $key }}" value="0">
                    <input type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1" style="margin:0; width:16px; height:16px;" {{ old($key, $item->$key) ? 'checked' : '' }}>
                    <label for="{{ $key }}" style="margin:0;">{{ $field['label'] }}</label>
                </div>

                @elseif($field['type'] === 'checkbox' && (isset($options[$key]) || isset($field['options'])))
                <div class="checkbox-group">
                    @if(isset($options[$key]))
                    @foreach($options[$key] as $option)
                    <div class="form-check">
                        <input type="checkbox" name="{{ $key }}[]" id="{{ $key }}_{{ $option->id }}" value="{{ $option->id }}" {{ in_array($option->id, old($key, $selectedValues[$key] ?? ($item->$key ?? []))) ? 'checked' : '' }}>
                        <label for="{{ $key }}_{{ $option->id }}">{{ $option->{$field['option_label'] ?? 'name'} }}</label>
                    </div>
                    @endforeach
                    @elseif(isset($field['options']))
                    @foreach($field['options'] as $value => $label)
                    @php
                    $optionValue = is_int($value) ? $label : $value;
                    $optionLabel = $label;
                    @endphp
                    <div class="form-check">
                        <input type="checkbox" name="{{ $key }}[]" id="{{ $key }}_{{ $optionValue }}" value="{{ $optionValue }}" {{ in_array($optionValue, old($key, $item->$key ?? [])) ? 'checked' : '' }}>
                        <label for="{{ $key }}_{{ $optionValue }}">{{ $optionLabel }}</label>
                    </div>
                    @endforeach
                    @endif
                </div>

                @elseif($field['type'] === 'file')
                @php
                    $displayImage = $field['display_image'] ?? false;
                    $displayImagePosition = $field['display_image_position'] ?? 'top';
                    $isImage = !empty($item->$key) && $displayImage && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $item->$key);
                @endphp
                
                @if($isImage && $displayImagePosition === 'top')
                <div style="margin-bottom: 0.5rem;">
                    <img src="{{ Storage::url($item->$key) }}" alt="Current image" style="width: 200px; height: auto; border: 1px solid var(--border); border-radius: 4px;">
                </div>
                @endif
                
                <input type="file" name="{{ $key }}" id="{{ $key }}" class="form-input @error($key) is-invalid @enderror" {{ ($field['readonly'] ?? false) ? 'readonly' : '' }} @if(isset($field['attributes'])) @foreach($field['attributes'] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach @endif>
                @if(!empty($item->$key))
                <div style="margin-top: 0.5rem;">
                    <small>Current file: <a href="{{ Storage::url($item->$key) }}" target="_blank">{{ basename($item->$key) }}</a></small>
                </div>
                @endif
                
                @if($isImage && $displayImagePosition === 'bottom')
                <div style="margin-top: 0.5rem;">
                    <img src="{{ Storage::url($item->$key) }}" alt="Current image" style="width: 200px; height: auto; border: 1px solid var(--border); border-radius: 4px;">
                </div>
                @endif

                @elseif($field['type'] === 'boolean')
                <div class="form-check" style="display:flex; align-items:center; gap:0.5rem;">
                    <input type="hidden" name="{{ $key }}" value="0">
                    <input type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1" style="margin:0; width:16px; height:16px;" {{ old($key, $item->$key) ? 'checked' : '' }}>
                    <label for="{{ $key }}" style="margin:0;">{{ $field['label'] }}</label>
                </div>

                @else
                <input type="{{ $field['type'] }}" name="{{ $key }}" id="{{ $key }}" class="form-input @error($key) is-invalid @enderror" value="{{ old($key, $item->$key) }}" placeholder="{{ $field['placeholder'] ?? '' }}" {{ ($field['readonly'] ?? false) ? 'readonly' : '' }} @if(isset($field['attributes'])) @foreach($field['attributes'] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach @endif>
                @endif

                @if(isset($field['help_text']))
                <div class="form-help-text" style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">{{ $field['help_text'] }}</div>
                @endif

                @error($key)
                @if(config('tyro-dashboard.resource_ui.show_field_errors', true))
                <div class="form-error" style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @endif
                @enderror
            </div>
            @endforeach

            <div class="form-actions" style="margin-top: 1.5rem;">
                <a href="{{ route('tyro-dashboard.resources.index', $resource) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update {{ Str::singular($config['title']) }}</button>
            </div>
        </form>
    </div>
</div>
@endsection