<h2>{{ __('Onboarding Guide') }}</h2>

<h3>{{ __('Welcome') }}</h3>
<p>{{ __('This guide helps new team members get productive with') }} {{ $project->name }}.</p>

<h3>{{ __('1. Understand the Product') }}</h3>
<p>{{ $answer('business', 'what_does_it_do') }}</p>

<h3>{{ __('2. Meet the Users') }}</h3>
<p>{{ $answer('business', 'target_users') }}</p>

<h3>{{ __('3. Set up the Environment') }}</h3>
<ul>
    <li>{{ __('Tech stack:') }} {{ $answer('technology', 'frontend') }}, {{ $answer('technology', 'backend') }}, {{ $answer('technology', 'database') }}</li>
    <li>{{ __('Hosting:') }} {{ $answer('infrastructure', 'hosting') }}</li>
    <li>{{ __('CI/CD:') }} {{ $answer('infrastructure', 'cicd') }}</li>
</ul>

<h3>{{ __('4. Explore the Codebase') }}</h3>
<p>{{ __('Modules:') }} {{ $answer('code', 'modules') }}</p>
<p>{{ __('Key APIs:') }} {{ $answer('code', 'apis') }}</p>
<p>{{ __('Database tables:') }} {{ $answer('code', 'tables') }}</p>

<h3>{{ __('5. Key Dependencies & Integrations') }}</h3>
<p>{{ $answer('code', 'dependencies') }}</p>

<h3>{{ __('6. Useful Links') }}</h3>
<ul>
    @if($project->url)<li><a href="{{ $project->url }}" target="_blank">{{ __('Production URL') }}</a></li>@endif
    @foreach($assetList('module') as $module)
        @if($module->link)<li><a href="{{ $module->link }}" target="_blank">{{ $module->name }}</a></li>@endif
    @endforeach
</ul>
