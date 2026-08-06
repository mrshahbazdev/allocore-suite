<h2>{{ __('API Documentation') }}</h2>

<h3>{{ __('API Surface') }}</h3>
<p>{{ $answer('code', 'apis') }}</p>

<h3>{{ __('Modules exposing APIs') }}</h3>
<ul>
    @foreach($assetList('api') as $api)
        <li>
            <strong>{{ $api->name }}</strong>
            @if($api->link)<a href="{{ $api->link }}" target="_blank">{{ __('Docs') }}</a>@endif
            <p>{{ $api->description }}</p>
        </li>
    @endforeach
</ul>

<h3>{{ __('Authentication & protocols') }}</h3>
<p>{{ $answer('technology', 'backend') }} {{ $answer('technology', 'frameworks') }}</p>

<h3>{{ __('External integrations') }}</h3>
<ul>
    @foreach($assetList('dependency') as $dep)
        <li>{{ $dep->name }} — {{ $dep->description }}</li>
    @endforeach
</ul>
