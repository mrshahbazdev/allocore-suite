@extends('layouts.shell')
@section('title', 'Unternehmen anlegen — Allocore')
@section('page-title', '🏢 Neues Unternehmen')
@section('topbar-actions')
    <a href="{{ route('companies.index') }}" class="btn btn-secondary btn-sm">← Zurück</a>
@endsection
@push('styles')
<style>
    .company-form-wrap { max-width: 600px; }
    @media (max-width: 640px) {
        .company-form-wrap { max-width: 100%; }
    }
</style>
@endpush
@section('content')
<div class="company-form-wrap">
<form method="POST" action="{{ route('companies.store') }}">
@csrf
<div class="card">
    <div class="card-title">Unternehmensdaten</div>
    <div class="form-group">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" placeholder="Muster GmbH" value="{{ old('name') }}" required>
    </div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Branche</label>
            <input type="text" name="industry" class="form-control" placeholder="z.B. Software, Immobilien" value="{{ old('industry') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Währung</label>
            <select name="currency" class="form-control">
                <option value="EUR" {{ old('currency','EUR')==='EUR'?'selected':'' }}>EUR €</option>
                <option value="USD" {{ old('currency')==='USD'?'selected':'' }}>USD $</option>
                <option value="CHF" {{ old('currency')==='CHF'?'selected':'' }}>CHF</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Land</label>
        <input type="text" name="country" class="form-control" placeholder="Deutschland" value="{{ old('country') }}">
    </div>
    <div class="form-group">
        <label class="form-label">Beschreibung</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Kurze Beschreibung">{{ old('description') }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">🏢 Unternehmen speichern</button>
</div>
</form>
</div>
@endsection
