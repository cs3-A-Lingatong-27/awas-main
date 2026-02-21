<flux:navlist>
    <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
    
    @if (Route::has('user-password.edit'))
        <flux:navlist.item :href="route('user-password.edit')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
    @endif

    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication() && Route::has('two-factor.show'))
        <flux:navlist.item :href="route('two-factor.show')" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navlist.item>
    @endif

    @if (Route::has('appearance.edit'))
        <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
    @endif
</flux:navlist>