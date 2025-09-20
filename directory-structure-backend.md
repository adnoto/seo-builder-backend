seo-builder-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── ProjectController.php
│   │   │       └── PageController.php
│   │   └── Middleware/
│   │       └── IdempotencyMiddleware.php
│   ├── Models/
│   │   ├── Project.php
│   │   └── Page.php
│   ├── Services/
│   │   └── BuilderService.php
│   └── Providers/
│       └── RouteServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2025_09_20_000000_create_projects_table.php
│   │   └── 2025_09_20_000001_create_pages_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
│       ├── ProjectControllerTest.php
│       └── PageControllerTest.php
├── .env.example
├── artisan
├── composer.json
└── README.md