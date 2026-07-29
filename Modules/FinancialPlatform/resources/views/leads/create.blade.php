@extends('layouts.shell')

@section('title', 'Neuer Lead — Allocore')
@section('page-title', 'Neuen Lead erstellen')

@section('topbar-actions')
    <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-sm">{{ __('Zurück') }}</a>
@endsection

@section('content')
<div class="card" style="max-width:800px;">
    <div class="card-title">{{ __('Lead-Informationen') }}</div>

    <form method="POST" action="{{ route('leads.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Name *') }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('E-Mail') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Telefon') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Position') }}</label>
                <input type="text" name="position" class="form-control" value="{{ old('position') }}" placeholder="{{ __('z.B. CEO, CFO, Manager') }}">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Unternehmen (aus Datenbank)') }}</label>
                <select name="company_id" class="form-control">
                    <option value="">{{ __('— Kein Unternehmen —') }}</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Firmenname (manuell)') }}</label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="{{ __('Falls nicht in Datenbank') }}">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('LinkedIn') }}</label>
                <input type="url" name="linkedin" class="form-control" value="{{ old('linkedin') }}" placeholder="{{ __('https://linkedin.com/in/...') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Website') }}</label>
                <input type="url" name="website" class="form-control" value="{{ old('website') }}" placeholder="{{ __('https://...') }}">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Status') }}</label>
                <select name="status" class="form-control">
                    @foreach(['new'=>'Neu','contacted'=>'Kontaktiert','qualified'=>'Qualifiziert','proposal'=>'Angebot','won'=>'Gewonnen','lost'=>'Verloren'] as $k=>$v)
                        <option value="{{ $k }}" {{ old('status', 'new') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Priorität') }}</label>
                <select name="priority" class="form-control">
                    @foreach(['low'=>'Niedrig','medium'=>'Mittel','high'=>'Hoch','critical'=>'Kritisch'] as $k=>$v)
                        <option value="{{ $k }}" {{ old('priority', 'medium') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Branche') }}</label>
                <input type="text" name="industry" class="form-control" value="{{ old('industry') }}" placeholder="{{ __('z.B. Technologie, Finanzen') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Quelle') }}</label>
                <input type="text" name="source" class="form-control" value="{{ old('source', 'manual') }}" placeholder="{{ __('z.B. Website, Messe, Empfehlung') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('Budget (EUR)') }}</label>
            <input type="number" name="budget" class="form-control" value="{{ old('budget') }}" step="0.01" min="0" style="max-width:300px;">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('Notizen') }}</label>
            <textarea name="notes" class="form-control" rows="4" placeholder="Zusätzliche Informationen...">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">{{ __('Lead erstellen') }}</button>
            <a href="{{ route('leads.index') }}" class="btn btn-secondary">{{ __('Abbrechen') }}</a>
        </div>
    </form>
</div>
@endsection
