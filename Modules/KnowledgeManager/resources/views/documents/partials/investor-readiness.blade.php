<h2>{{ __('Investor Readiness Report') }}</h2>

<h3>{{ __('Business Summary') }}</h3>
<p><strong>{{ __('What it does:') }}</strong> {{ $answer('business', 'what_does_it_do') }}</p>
<p><strong>{{ __('Target users:') }}</strong> {{ $answer('business', 'target_users') }}</p>
<p><strong>{{ __('Value created:') }}</strong> {{ $answer('business', 'value_created') }}</p>
<p><strong>{{ __('Business model:') }}</strong> {{ $answer('business', 'business_model') }}</p>

<h3>{{ __('Tech Maturity') }}</h3>
<ul>
    <li>{{ __('Frontend:') }} {{ $answer('technology', 'frontend') }}</li>
    <li>{{ __('Backend:') }} {{ $answer('technology', 'backend') }}</li>
    <li>{{ __('Database:') }} {{ $answer('technology', 'database') }}</li>
    <li>{{ __('Cloud:') }} {{ $answer('technology', 'cloud_provider') }}</li>
    <li>{{ __('CI/CD:') }} {{ $answer('infrastructure', 'cicd') }}</li>
    <li>{{ __('Backups:') }} {{ $answer('infrastructure', 'backups') }}</li>
</ul>

<h3>{{ __('Risk Profile') }}</h3>
<p>{{ __('The product is at the') }} <strong>{{ $project->stage ?: '—' }}</strong> {{ __('stage in the') }} <strong>{{ $project->industry ?: '—' }}</strong> {{ __('industry.') }}</p>
<p>{{ __('Key dependencies:') }} {{ $answer('code', 'dependencies') }}</p>
