<h2>{{ __('Developer Handbook') }}</h2>

<h3>{{ __('Getting Started') }}</h3>
<p>{{ __('Project URL:') }} {{ $project->url ?: '—' }}</p>
<p>{{ __('Stage:') }} {{ $project->stage ?: '—' }}</p>
<p>{{ __('Industry:') }} {{ $project->industry ?: '—' }}</p>

<h3>{{ __('Tech Stack') }}</h3>
<ul>
    <li>{{ __('Frontend:') }} {{ $answer('technology', 'frontend') }}</li>
    <li>{{ __('Backend:') }} {{ $answer('technology', 'backend') }}</li>
    <li>{{ __('Database:') }} {{ $answer('technology', 'database') }}</li>
    <li>{{ __('Cloud provider:') }} {{ $answer('technology', 'cloud_provider') }}</li>
    <li>{{ __('Frameworks & libraries:') }} {{ $answer('technology', 'frameworks') }}</li>
</ul>

<h3>{{ __('Modules & Boundaries') }}</h3>
<p>{{ $answer('code', 'modules') }}</p>

<h3>{{ __('APIs & Integrations') }}</h3>
<p>{{ $answer('code', 'apis') }}</p>

<h3>{{ __('Database Model') }}</h3>
<p>{{ $answer('code', 'tables') }}</p>

<h3>{{ __('Dependencies') }}</h3>
<p>{{ $answer('code', 'dependencies') }}</p>

<h3>{{ __('Local Development') }}</h3>
<p><strong>{{ __('Setup:') }}</strong> {{ $answer('infrastructure', 'hosting') }}</p>
<p><strong>{{ __('CI/CD:') }}</strong> {{ $answer('infrastructure', 'cicd') }}</p>
