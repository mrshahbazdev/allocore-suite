<h2>{{ __('Architecture Manual') }}</h2>

<h3>{{ __('Business Context') }}</h3>
<p><strong>{{ __('What the SaaS does:') }}</strong> {{ $answer('business', 'what_does_it_do') }}</p>
<p><strong>{{ __('Target users:') }}</strong> {{ $answer('business', 'target_users') }}</p>
<p><strong>{{ __('Value created:') }}</strong> {{ $answer('business', 'value_created') }}</p>
<p><strong>{{ __('Business model:') }}</strong> {{ $answer('business', 'business_model') }}</p>

<h3>{{ __('Technology Stack') }}</h3>
<ul>
    <li><strong>{{ __('Frontend:') }}</strong> {{ $answer('technology', 'frontend') }}</li>
    <li><strong>{{ __('Backend:') }}</strong> {{ $answer('technology', 'backend') }}</li>
    <li><strong>{{ __('Database:') }}</strong> {{ $answer('technology', 'database') }}</li>
    <li><strong>{{ __('Cloud provider:') }}</strong> {{ $answer('technology', 'cloud_provider') }}</li>
    <li><strong>{{ __('Frameworks & libraries:') }}</strong> {{ $answer('technology', 'frameworks') }}</li>
</ul>

<h3>{{ __('Code Structure') }}</h3>
<p><strong>{{ __('Modules:') }}</strong> {{ $answer('code', 'modules') }}</p>
<p><strong>{{ __('APIs:') }}</strong> {{ $answer('code', 'apis') }}</p>
<p><strong>{{ __('Database tables:') }}</strong> {{ $answer('code', 'tables') }}</p>
<p><strong>{{ __('Dependencies:') }}</strong> {{ $answer('code', 'dependencies') }}</p>

<h3>{{ __('Infrastructure') }}</h3>
<ul>
    <li><strong>{{ __('Domains:') }}</strong> {{ $answer('infrastructure', 'domains') }}</li>
    <li><strong>{{ __('Hosting:') }}</strong> {{ $answer('infrastructure', 'hosting') }}</li>
    <li><strong>{{ __('CI/CD:') }}</strong> {{ $answer('infrastructure', 'cicd') }}</li>
    <li><strong>{{ __('DNS:') }}</strong> {{ $answer('infrastructure', 'dns') }}</li>
    <li><strong>{{ __('Backups:') }}</strong> {{ $answer('infrastructure', 'backups') }}</li>
</ul>
