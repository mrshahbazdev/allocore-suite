@extends('layouts.shell')
@section('title', 'Unternehmen bearbeiten')
@section('page-title', '✏ ' . $company->name . ' bearbeiten')
@section('topbar-actions')
    <a href="{{ route('companies.show', $company) }}" class="btn btn-secondary btn-sm">← Zurück</a>
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
<form method="POST" action="{{ route('companies.update', $company) }}">
@csrf @method('PATCH')
<div class="card">
    <div class="card-title">Unternehmensdaten</div>
    <div class="form-group">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
    </div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Branche</label>
            <input type="text" name="industry" class="form-control" value="{{ old('industry', $company->industry) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Währung</label>
            <select name="currency" class="form-control">
                <option value="EUR" {{ old('currency',$company->currency)==='EUR'?'selected':'' }}>EUR €</option>
                <option value="USD" {{ old('currency',$company->currency)==='USD'?'selected':'' }}>USD $</option>
                <option value="CHF" {{ old('currency',$company->currency)==='CHF'?'selected':'' }}>CHF</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Land</label>
        <input type="text" name="country" class="form-control" value="{{ old('country', $company->country) }}">
    </div>
    <div class="form-group">
        <label class="form-label">Beschreibung</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $company->description) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">💾 Speichern</button>
</div>
</form>
</div>
@endsection
