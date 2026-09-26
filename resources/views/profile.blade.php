<x-app-layout>
  <div class="py-space-xl">
    <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin space-y-space-lg">
      <div class="flex items-center gap-space-xs mb-space-sm">
        <span class="inline-flex items-center gap-1.5 font-label-sm text-label-sm uppercase tracking-widest text-primary font-semibold px-2 py-0.5 bg-surface-container-low shadow-sm">
          <span class="material-symbols-outlined text-[14px]">account_circle</span>
          [CONFIGURAÇÕES DE CONTA]
        </span>
      </div>

      <h1 class="font-display-lg text-display-lg-mobile md:text-headline-lg text-on-surface font-bold tracking-tight mb-space-md">
        Meu Perfil
      </h1>

      <div class="p-space-lg bg-surface-container border border-surface-variant/40 shadow-xl">
        <div class="max-w-xl">
          <livewire:profile.update-profile-information-form />
        </div>
      </div>

      <div class="p-space-lg bg-surface-container border border-surface-variant/40 shadow-xl">
        <div class="max-w-xl">
          <livewire:profile.update-password-form />
        </div>
      </div>

      <div class="p-space-lg bg-surface-container border border-surface-variant/40 shadow-xl">
        <div class="max-w-xl">
          <livewire:profile.delete-user-form />
        </div>
      </div>
    </div>
  </div>
</x-app-layout>

