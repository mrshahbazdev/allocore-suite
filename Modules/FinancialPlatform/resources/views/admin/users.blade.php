@extends('layouts.admin')
@section('title', 'Benutzer — Allocore Admin')
@section('page-title', '👥 Benutzerverwaltung')

@section('content')
<div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <div class="card-title" style="margin:0;">{{ $users->total() }} Benutzer registriert</div>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>Name</th><th>E-Mail</th><th>Aktuelle Rolle</th><th>Analysen</th><th>Registriert</th><th>Rolle ändern</th></tr>
        </thead>
        <tbody>
        @foreach($users as $u)
        <tr>
            <td style="font-weight:500; color:#e2e8f0;">{{ $u->name }}</td>
            <td style="color:#64748b; font-size:11px;">{{ $u->email }}</td>
            <td>
                @php $role = $u->getRoleNames()->first() ?? 'none'; @endphp
                <span class="badge badge-{{ strtolower($role) }}">{{ $role }}</span>
            </td>
            <td style="color:#94a3b8;">{{ $u->analyses_count }}</td>
            <td style="font-size:11px; color:#475569;">{{ $u->created_at->format('d.m.Y') }}</td>
            <td>
                @if($u->id !== Auth::id())
                <form method="POST" action="{{ route('admin.users.role', $u) }}" style="display:flex; gap:6px;">
                    @csrf @method('PATCH')
                    <select name="role" class="form-control" style="width:110px; padding:4px 8px; font-size:11px;">
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ $u->hasRole($r->name) ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">✓</button>
                </form>
                @else
                    <span style="font-size:11px; color:#475569;">Du selbst</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div style="margin-top:14px;">
        {{ $users->links() }}
    </div>
</div>
@endsection
