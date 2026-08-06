<?php

return [
    'name' => 'KnowledgeManager',

    'sections' => [
        'business' => [
            'label' => 'Business',
            'questions' => [
                ['key' => 'what_does_it_do', 'label' => 'What does the SaaS do?', 'placeholder' => 'Describe the product and the problem it solves.'],
                ['key' => 'target_users', 'label' => 'Who are the target users?', 'placeholder' => 'Primary personas and their jobs-to-be-done.'],
                ['key' => 'value_created', 'label' => 'What value will be created?', 'placeholder' => 'Measurable business and user value.'],
                ['key' => 'business_model', 'label' => 'What is the business model?', 'placeholder' => 'Pricing, revenue streams, go-to-market.'],
            ],
        ],
        'technology' => [
            'label' => 'Technology',
            'questions' => [
                ['key' => 'frontend', 'label' => 'Which frontend?', 'placeholder' => 'e.g. React, Vue, Livewire + Alpine.js'],
                ['key' => 'backend', 'label' => 'Which backend?', 'placeholder' => 'e.g. Laravel, Node.js, Python'],
                ['key' => 'database', 'label' => 'Which database?', 'placeholder' => 'e.g. MySQL, PostgreSQL, SQLite'],
                ['key' => 'cloud_provider', 'label' => 'Which cloud provider?', 'placeholder' => 'e.g. AWS, Azure, Hetzner, DigitalOcean'],
                ['key' => 'frameworks', 'label' => 'Key frameworks & libraries', 'placeholder' => 'Major packages and frameworks used.'],
            ],
        ],
        'infrastructure' => [
            'label' => 'Infrastructure',
            'questions' => [
                ['key' => 'domains', 'label' => 'Domains', 'placeholder' => 'Production, staging and sandbox domains.'],
                ['key' => 'hosting', 'label' => 'Hosting', 'placeholder' => 'Server, container platform or managed service.'],
                ['key' => 'cicd', 'label' => 'CI/CD', 'placeholder' => 'Build, test and deployment pipelines.'],
                ['key' => 'dns', 'label' => 'DNS', 'placeholder' => 'DNS providers and key records.'],
                ['key' => 'backups', 'label' => 'Backups', 'placeholder' => 'Backup strategy, retention and restore process.'],
            ],
        ],
        'code' => [
            'label' => 'Code',
            'questions' => [
                ['key' => 'modules', 'label' => 'Which modules exist?', 'placeholder' => 'High-level modules, packages or bounded contexts.'],
                ['key' => 'apis', 'label' => 'Which APIs exist?', 'placeholder' => 'Internal and external APIs, protocols, auth.'],
                ['key' => 'tables', 'label' => 'Which database tables exist?', 'placeholder' => 'Core tables and their responsibilities.'],
                ['key' => 'dependencies', 'label' => 'Which dependencies exist?', 'placeholder' => 'Critical third-party services and integrations.'],
            ],
        ],
    ],

    'documents' => [
        'architecture' => [
            'label' => 'Architecture Manual',
            'description' => 'High-level system overview, tech stack and design decisions.',
            'partial' => 'architecture',
        ],
        'developer-handbook' => [
            'label' => 'Developer Handbook',
            'description' => 'Everything a new developer needs to start contributing.',
            'partial' => 'developer-handbook',
        ],
        'api' => [
            'label' => 'API Documentation',
            'description' => 'API surface, endpoints, authentication and examples.',
            'partial' => 'api',
        ],
        'infrastructure' => [
            'label' => 'Infrastructure Overview',
            'description' => 'Domains, hosting, CI/CD, DNS and backups.',
            'partial' => 'infrastructure',
        ],
        'investor-readiness' => [
            'label' => 'Investor Readiness Report',
            'description' => 'Business summary, tech maturity and risk profile.',
            'partial' => 'investor-readiness',
        ],
        'onboarding' => [
            'label' => 'Onboarding Guide',
            'description' => 'Step-by-step onboarding for new team members.',
            'partial' => 'onboarding',
        ],
    ],
];
