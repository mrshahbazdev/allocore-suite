<h2>{{ __('Infrastructure Overview') }}</h2>

<ul>
    <li><strong>{{ __('Domains:') }}</strong> {{ $answer('infrastructure', 'domains') }}</li>
    <li><strong>{{ __('Hosting:') }}</strong> {{ $answer('infrastructure', 'hosting') }}</li>
    <li><strong>{{ __('CI/CD:') }}</strong> {{ $answer('infrastructure', 'cicd') }}</li>
    <li><strong>{{ __('DNS:') }}</strong> {{ $answer('infrastructure', 'dns') }}</li>
    <li><strong>{{ __('Backups:') }}</strong> {{ $answer('infrastructure', 'backups') }}</li>
</ul>

<h3>{{ __('Cloud provider') }}</h3>
<p>{{ $answer('technology', 'cloud_provider') }}</p>

<h3>{{ __('Database hosting') }}</h3>
<p>{{ $answer('technology', 'database') }}</p>
