@extends('layouts.shell')

@section('title', 'Lead bearbeiten — Allocore')
@section('page-title', 'Lead bearbeiten: ' . $lead->name)

@section('topbar-actions')
    <a href="{{ route('leads.show', $lead) }}" class="btn btn-secondary btn-sm">{{ __('Zurück') }}</a>
@endsection

@section('content')
<div class="card" style="max-width:800px;">
    <div class="card-title">{{ __('Lead bearbeiten') }}</div>

    <form method="POST" action="{{ route('leads.update', $lead) }}">
        @csrf @method('PATCH')

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Name *') }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $lead->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('E-Mail') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Telefon') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Position') }}</label>
                <input type="text" name="position" class="form-control" value="{{ old('position', $lead->position) }}">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Unternehmen (aus Datenbank)') }}</label>
                <select name="company_id" class="form-control">
                    <option value="">{{ __('— Kein Unternehmen —') }}</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $lead->company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Firmenname (manuell)') }}</label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $lead->company_name) }}">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('LinkedIn') }}</label>
                <input type="url" name="linkedin" class="form-control" value="{{ old('linkedin', $lead->linkedin) }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Website') }}</label>
                <input type="url" name="website" class="form-control" value="{{ old('website', $lead->website) }}">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Status') }}</label>
                <select name="status" class="form-control">
                    @foreach(['new'=>'Neu','contacted'=>'Kontaktiert','qualified'=>'Qualifiziert','proposal'=>'Angebot','won'=>'Gewonnen','lost'=>'Verloren'] as $k=>$v)
                        <option value="{{ $k }}" {{ old('status', $lead->status) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Priorität') }}</label>
                <select name="priority" class="form-control">
                    @foreach(['low'=>'Niedrig','medium'=>'Mittel','high'=>'Hoch','critical'=>'Kritisch'] as $k=>$v)
                        <option value="{{ $k }}" {{ old('priority', $lead->priority) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">{{ __('Branche') }}</label>
                <input type="text" name="industry" class="form-control" value="{{ old('industry', $lead->industry) }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Quelle') }}</label>
                <input type="text" name="source" class="form-control" value="{{ old('source', $lead->source) }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('Budget (EUR)') }}</label>
            <input type="number" name="budget" class="form-control" value="{{ old('budget', $lead->budget) }}" step="0.01" min="0" style="max-width:300px;">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('Notizen') }}</label>
            <textarea name="notes" class="form-control" rows="4">{{ old('notes', $lead->notes) }}</textarea>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">{{ __('Speichern') }}</button>
            <a href="{{ route('leads.show', $lead) }}" class="btn btn-secondary">{{ __('Abbrechen') }}</a>
        </div>
    </form>
</div>
@endsection
