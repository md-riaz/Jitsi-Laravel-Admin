<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the dashboard routes prefix and middleware.
    |
    */
    'routes' => [
        'prefix' => env('TYRO_DASHBOARD_PREFIX', 'dashboard'),
        'middleware' => ['web', 'auth'],
        'name_prefix' => 'tyro-dashboard.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Roles
    |--------------------------------------------------------------------------
    |
    | Users with these roles will have full access to admin features
    | (user management, role management, privilege management, settings).
    |
    */
    'admin_roles' => ['super-admin', 'org-admin'],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model to use throughout the dashboard.
    |
    */
    'user_model' => env('TYRO_DASHBOARD_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for lists.
    |
    */
    'pagination' => [
        'users' => 15,
        'roles' => 15,
        'privileges' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | Customize the dashboard appearance.
    |
    */
    'branding' => [
        'app_name' => env('TYRO_DASHBOARD_APP_NAME', env('APP_NAME', 'Laravel')),
        'logo' => env('TYRO_DASHBOARD_LOGO', null),
        'logo_height' => env('TYRO_DASHBOARD_LOGO_HEIGHT', '32px'),
        'favicon' => env('TYRO_DASHBOARD_FAVICON', null),
        
        // Sidebar colors (supports any CSS color value: hex, rgb, hsl, etc.)
        'sidebar_bg' =>  env('TYRO_DASHBOARD_SIDEBAR_BG',null), // Custom background color for sidebar
        'sidebar_text' => env('TYRO_DASHBOARD_SIDEBAR_TEXT',null), // Custom text color for sidebar
    ],

    /*
    |--------------------------------------------------------------------------
    | Collapsible Sidebar
    |--------------------------------------------------------------------------
    |
    | Enable or disable the collapsible sidebar feature.
    |
    */
    'collapsible_sidebar' => env('TYRO_DASHBOARD_COLLAPSIBLE_SIDEBAR', true),

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific dashboard features.
    |
    */
    'features' => [
        'user_management' => true,
        'role_management' => true,
        'privilege_management' => true,
        'settings_management' => true,
        'profile_management' => true,
        'invitation_system' => env('TYRO_DASHBOARD_ENABLE_INVITATION', true),
        'activity_log' => false, // Future feature
        'profile_photo_upload' => env('TYRO_DASHBOARD_ENABLE_PROFILE_PHOTO', false),
        'gravatar' => env('TYRO_DASHBOARD_ENABLE_GRAVATAR', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Resources
    |--------------------------------------------------------------------------
    |
    | Resources that cannot be deleted through the dashboard.
    |
    */
    'protected' => [
        'roles' => ['admin', 'super-admin', 'user'],
        'users' => [], // Add user IDs that cannot be deleted
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widgets
    |--------------------------------------------------------------------------
    |
    | Configure which widgets appear on the dashboard home.
    |
    */
    'widgets' => [
        'stats' => true,
        'recent_users' => true,
        'role_distribution' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure dashboard notifications behavior.
    |
    */
    'notifications' => [
        'show_flash_messages' => true,
        'auto_dismiss_seconds' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default settings for file uploads in resources.
    |
    */
    'uploads' => [
        'disk' => env('TYRO_DASHBOARD_UPLOAD_DISK', 'public'),
        'directory' => env('TYRO_DASHBOARD_UPLOAD_DIRECTORY', 'uploads'),
        'auto_delete_on_resource_delete' => env('TYRO_DASHBOARD_AUTO_DELETE_UPLOADS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Photo Configuration
    |--------------------------------------------------------------------------
    |
    | Configure settings for user profile photos and gravatar support.
    |
    */
    'profile_photo' => [
        'disk' => env('TYRO_DASHBOARD_PROFILE_PHOTO_DISK', 'public'),
        'directory' => env('TYRO_DASHBOARD_PROFILE_PHOTO_DIRECTORY', 'profile_images'),
        'max_size' => env('TYRO_DASHBOARD_PROFILE_PHOTO_MAX_SIZE', 10240), // in KB (default 10MB)
        'width' => env('TYRO_DASHBOARD_PROFILE_PHOTO_WIDTH', 400),
        'height' => env('TYRO_DASHBOARD_PROFILE_PHOTO_HEIGHT', 400),
        'quality' => env('TYRO_DASHBOARD_PROFILE_PHOTO_QUALITY', 90),
        'crop_position' => env('TYRO_DASHBOARD_PROFILE_PHOTO_CROP', 'center'), // top, center, bottom
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'auto_delete_on_user_delete' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic Resources (CRUD)
    |--------------------------------------------------------------------------
    |
    | Define your resources here to automatically generate CRUD interfaces.
    |
    */
    // 'resources' => [
    //     // Example:
    //     // 'posts' => [
    //     //     'model' => 'App\Models\Post',
    //     //     'title' => 'Posts',
    //     //     'icon' => '<svg>...</svg>', // Optional SVG icon
    //     //     'fields' => [
    //     //         'title' => ['type' => 'text', 'label' => 'Title', 'rules' => 'required'],
    //     //         'content' => ['type' => 'textarea', 'label' => 'Content'],
    //     //     ],
    //     // ],
    // ],
    'resources' => [
        'meetings' => [
            'model' => 'App\\Models\\Meeting',
            'title' => 'Meetings',
            'roles' => ['super-admin', 'org-admin', 'host'],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title', 'rules' => 'required|string|max:255', 'searchable' => true],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'rules' => 'nullable|string', 'searchable' => false],
                'start_at' => ['type' => 'datetime-local', 'label' => 'Start Time', 'rules' => 'nullable|date', 'searchable' => true],
                'end_at' => ['type' => 'datetime-local', 'label' => 'End Time', 'rules' => 'nullable|date|after_or_equal:start_at'],
                'timezone' => ['type' => 'select', 'label' => 'Timezone', 'options' => ['UTC' => 'UTC', 'America/New_York' => 'Eastern Time', 'America/Chicago' => 'Central Time', 'America/Denver' => 'Mountain Time', 'America/Los_Angeles' => 'Pacific Time', 'Europe/London' => 'London', 'Europe/Paris' => 'Paris', 'Asia/Tokyo' => 'Tokyo', 'Asia/Shanghai' => 'Shanghai', 'Australia/Sydney' => 'Sydney'], 'rules' => 'required|string'],
                'status' => ['type' => 'select', 'label' => 'Status', 'options' => ['scheduled' => 'Scheduled', 'live' => 'Live', 'ended' => 'Ended', 'canceled' => 'Canceled'], 'searchable' => true],
                'visibility' => ['type' => 'select', 'label' => 'Visibility', 'options' => ['invite_only' => 'Invite Only', 'link_anyone' => 'Anyone with Link', 'org_only' => 'Organization Only']],
                'organization_id' => ['type' => 'select', 'label' => 'Organization', 'relationship' => 'organization', 'rules' => 'required_if:visibility,org_only|nullable|exists:organizations,id', 'searchable' => true],
                'join_early_minutes' => ['type' => 'number', 'label' => 'Join Early (minutes)', 'rules' => 'nullable|integer|min:0|max:120', 'default' => 10],
                'join_late_minutes' => ['type' => 'number', 'label' => 'Join Late (minutes)', 'rules' => 'nullable|integer|min:0|max:240', 'default' => 60],
            ],
        ],
        'meeting_participants' => [
            'model' => 'App\\Models\\MeetingParticipant',
            'title' => 'Participants',
            'roles' => ['super-admin', 'org-admin', 'host'],
            'fields' => [
                'display_name' => ['type' => 'text', 'label' => 'Display Name', 'searchable' => true],
                'email' => ['type' => 'text', 'label' => 'Email', 'searchable' => true],
                'role' => ['type' => 'select', 'label' => 'Role', 'options' => ['host' => 'Host', 'cohost' => 'Co-host', 'participant' => 'Participant']],
                'joined_at' => [
                    'type' => 'datetime-local',
                    'label' => 'Joined At',
                    'rules' => 'nullable|date',
                    'help_text' => 'Participant joined সময় (date + time). সাধারণত system auto-set করে, manual edit only correction এর জন্য।',
                    'attributes' => ['title' => 'Joined At: participant meeting এ যেই সময় join করেছে'],
                    'hide_in_create' => true,
                ],
            ],
        ],
        'organizations' => [
            'model' => 'App\\Models\\Organization',
            'title' => 'Organizations',
            'roles' => ['super-admin', 'org-admin'],
            'fields' => [
                'name' => ['type' => 'text', 'label' => 'Name', 'searchable' => true],
                'slug' => ['type' => 'text', 'label' => 'Slug', 'searchable' => true],
            ],
        ],
        'meeting_invites' => [
            'model' => 'App\\Models\\MeetingInvite',
            'title' => 'Invites',
            'roles' => ['super-admin', 'org-admin', 'host'],
            'fields' => [
                'email' => ['type' => 'text', 'label' => 'Email', 'searchable' => true],
                'expires_at' => ['type' => 'datetime-local', 'label' => 'Expires At', 'rules' => 'nullable|date', 'help_text' => 'Invite expire হওয়ার date+time দিন'],
                'revoked_at' => ['type' => 'datetime-local', 'label' => 'Revoked At', 'rules' => 'nullable|date', 'hide_in_create' => true, 'help_text' => 'Invite revoke হলে date+time (optional)'],
            ],
        ],
        'meeting_events' => [
            'model' => 'App\\Models\\MeetingEvent',
            'title' => 'Audit Events',
            'roles' => ['super-admin', 'org-admin'],
            'fields' => [
                'type' => ['type' => 'text', 'label' => 'Type', 'searchable' => true],
                'meeting_id' => ['type' => 'text', 'label' => 'Meeting', 'searchable' => true],
                'payload_preview' => ['type' => 'text', 'label' => 'Payload', 'hide_in_form' => true],
                'payload' => ['type' => 'json', 'label' => 'Payload (Raw)', 'hide_in_index' => true, 'hide_in_single_view' => true, 'hide_in_form' => true],
                'created_at' => ['type' => 'datetime', 'label' => 'Created At'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource UI Settings
    |--------------------------------------------------------------------------
    |
    | Configure the appearance and behavior of resource forms and lists.
    |
    */
    'resource_ui' => [
        'show_global_errors' => env('TYRO_SHOW_GLOBAL_ERRORS', true),
        'show_field_errors' => env('TYRO_SHOW_FIELD_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Disable Examples
    |--------------------------------------------------------------------------
    |
    | If this is true, the "Examples" section in the sidebar will be hidden
    | and the example routes will be disabled.
    |
    */
    'disable_examples' => env('TYRO_DASHBOARD_DISABLE_EXAMPLES', false),
];
