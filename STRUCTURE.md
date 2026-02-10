# STRUCTURE.md
## Project Folder Structure

app/
├── Domain/
│   └── Meetings/
│       ├── Models/
│       ├── Services/
│       ├── Policies/
│       └── Events/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   └── Web/
│   └── Middleware/
├── Jobs/
├── Notifications/
├── Providers/
│
resources/
├── views/
│   ├── dashboard/        # Tyro Dashboard generated
│   └── meeting/          # Public meeting pages
│
routes/
├── web.php
└── api.php

Rules:
Dashboard views ONLY under resources/views/dashboard
Meeting join pages are NOT dashboard pages
