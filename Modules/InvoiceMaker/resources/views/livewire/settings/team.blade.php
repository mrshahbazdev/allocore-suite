@php $title = __('Team Settings'); @endphp

<div>
 <div class="mb-8 items-center justify-between">
 <h2 class="text-2xl font-bold text-txmain">{{ __('Team Management') }}</h2>
 <p class="text-txmain">{{ __('Invite and manage collaborators for') }} {{ $profile->name }}
 </p>
 </div>

 @if (session()->has('message'))
 <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 text-green-700 rounded shadow-sm">
 <div class="flex items-center">
 <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd"
 d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
 clip-rule="evenodd"></path>
 </svg>
 {{ session('message') }}
 </div>
 </div>
 @endif

 @if (session()->has('error'))
 <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 text-red-700 rounded shadow-sm">
 <div class="flex items-center">
 <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd"
 d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
 clip-rule="evenodd"></path>
 </svg>
 {{ session('error') }}
 </div>
 </div>
 @endif

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
 <!-- Invite New Member -->
 <div class="lg:col-span-1">
 <div class="bg-card rounded-lg shadow p-6 h-fit border border-gray-100">
 <h3 class="text-lg font-semibold text-txmain mb-4 flex items-center">
 <svg class="w-5 h-5 mr-2 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
 </path>
 </svg>
 {{ __('Invite Team Member') }}
 </h3>
 <form wire:submit="invite" class="space-y-4">
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Email Address') }}</label>
 <input type="email" wire:model="email"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
 placeholder="{{ __('colleague@example.com') }}">
 @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-sm font-medium text-txmain mb-1">{{ __('Role') }}</label>
 <select wire:model="role"
 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
 <option value="viewer">{{ __('Viewer (Read Only)') }}</option>
 <option value="admin">{{ __('Admin (Full Access)') }}</option>
 </select>
 <p class="mt-2 text-xs text-gray-500">
 <strong>{{ __('Note') }}:</strong>
 {{ __('Admins can create invoices, manage clients, and invite others.') }}
 {{ __('Viewers can only see records.') }}
 </p>
 </div>
 <button type="submit"
 class="w-full py-2 px-4 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition shadow-md hover:shadow-lg flex items-center justify-center">
 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
 </svg>
 {{ __('Send Invitation') }}
 </button>
 </form>
 </div>
 </div>

 <!-- Team Members List -->
 <div class="lg:col-span-2 space-y-8">
 <div class="bg-card rounded-lg shadow overflow-hidden border border-gray-100">
 <div class="px-6 py-4 border-b border-gray-100 bg-page">
 <h3 class="text-lg font-semibold text-txmain">{{ __('Current Members') }}</h3>
 </div>
 <div class="divide-y divide-gray-100">
 @foreach($members as $member)
 <div class="p-6 flex items-center justify-between hover:bg-page transition">
 <div class="flex items-center space-x-4">
 <div
 class="h-10 w-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold">
 {{ substr($member->name, 0, 2) }}
 </div>
 <div>
 <div class="font-medium text-txmain flex items-center">
 {{ $member->name }}
 @if($member->id === auth()->id())
 <span
 class="ml-2 px-2 py-0.5 text-[10px] bg-page text-txmain rounded-full">{{ __('You') }}</span>
 @endif
 </div>
 <div class="text-sm text-gray-500">{{ $member->email }}</div>
 </div>
 </div>
 <div class="flex items-center space-x-4">
 <span
 class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $member->isOwner() ? 'bg-purple-100 text-purple-700' : ($member->isAdmin() ? 'bg-brand-100 text-brand-700' : 'bg-green-100 text-green-700') }}">
 {{ ucfirst($member->role) }}
 </span>
 @if(auth()->user()->isOwner() && $member->id !== auth()->id() && !$member->isOwner())
 <button wire:click="removeMember({{ $member->id }})"
 wire:confirm="{{ __('Are you sure you want to remove this member?') }}"
 class="p-2 text-gray-400 hover:text-red-600 transition">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
 </path>
 </svg>
 </button>
 @endif
 </div>
 </div>
 @endforeach
 </div>
 </div>

 <!-- Pending Invitations -->
 @if($invitations->count() > 0)
 <div class="bg-card rounded-lg shadow overflow-hidden border border-gray-100">
 <div class="px-6 py-4 border-b border-gray-100 bg-page flex items-center">
 <h3 class="text-lg font-semibold text-txmain">{{ __('Pending Invitations') }}</h3>
 <span
 class="ml-2 px-2 py-0.5 text-xs bg-brand-100 text-brand-700 rounded-full font-bold">{{ $invitations->count() }}</span>
 </div>
 <div class="divide-y divide-gray-100 text-sm">
 @foreach($invitations as $invite)
 <div class="p-6 flex items-center justify-between bg-brand-50/30 hover:bg-brand-50/50 transition">
 <div>
 <div class="font-medium text-txmain">{{ $invite->email }}</div>
 <div class="text-xs text-gray-500 mt-1 flex items-center">
 <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
 </svg>
 {{ __('Expires') }} {{ $invite->expires_at->diffForHumans() }}
 </div>
 </div>
 <div class="flex items-center space-x-3">
 <span
 class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-card border border-gray-200 text-txmain rounded shadow-sm">
 {{ $invite->role }}
 </span>
 <button wire:click="cancelInvitation({{ $invite->id }})"
 class="text-xs font-semibold text-red-600 hover:text-red-800 transition">
 {{ __('Cancel') }}
 </button>
 </div>
 </div>
 @endforeach
 </div>
 <div class="px-6 py-4 bg-page text-xs text-gray-500 italic">
 {{ __('Invited users will receive a link to join your business. Links are valid for 7 days.') }}
 </div>
 </div>
 @endif
 </div>
 </div>
</div>