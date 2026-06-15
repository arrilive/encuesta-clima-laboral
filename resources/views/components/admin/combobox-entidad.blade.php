@props([
    'wireModel',
    'placeholder' => 'Buscar...',
    'disabled' => false,
    'hasError' => false,
])

<div wire:key="combobox-{{ $wireModel }}-{{ $disabled ? 'd' : 'e' }}"
     x-data="{
    open: false,
    search: '',
    selectedLabel: '',
    wireValue: $wire.entangle('{{ $wireModel }}').live,
    options: [],
    activeIndex: -1,

    init() {
        this.readOptions();
        this.syncLabel();

        // MutationObserver: re-leer opciones cuando Livewire actualiza el DOM del slot
        const observer = new MutationObserver(() => {
            this.readOptions();
            this.syncLabel();
        });
        observer.observe(this.$refs.optionsSource, {
            childList: true,
            subtree: true,
            characterData: true,
        });

        // Sincronizar label cuando el valor cambia externamente (reset en cascada)
        this.$watch('wireValue', () => this.syncLabel());

        // Resetear índice activo al abrir/cerrar o buscar
        this.$watch('open', v => {
            if (!v) this.activeIndex = -1;
        });
        this.$watch('search', () => {
            this.activeIndex = -1;
        });
    },

    readOptions() {
        this.options = Array.from(this.$refs.optionsSource.querySelectorAll('option'))
            .map(opt => ({
                value: opt.value,
                label: opt.textContent.trim(),
                disabled: opt.disabled
            }));
    },

    syncLabel() {
        const found = this.options.find(o => o.value == this.wireValue);
        this.selectedLabel = found && found.value !== '' ? found.label : '';
    },

    get filteredOptions() {
        if (!this.search) return this.options;
        const q = this.search.toLowerCase();
        return this.options.filter(o => {
            // Ocultar opciones con value vacío durante la búsqueda
            if (o.value === '') return false;
            return o.label.toLowerCase().includes(q);
        });
    },

    select(option) {
        if (!option || option.disabled) return;
        this.wireValue = option.value;
        this.selectedLabel = option.value !== '' ? option.label : '';
        this.search = '';
        this.open = false;
        this.activeIndex = -1;
    },

    clear() {
        this.wireValue = '';
        this.selectedLabel = '';
        this.search = '';
    }
}" class="relative">
    {{-- Opciones ocultas — fuente de verdad del DOM --}}
    <div x-ref="optionsSource" class="hidden">
        {{ $slot }}
    </div>

    {{-- Trigger: muestra el valor seleccionado o placeholder --}}
    <button type="button"
        @click="open = !open"
        @disabled($disabled)
        class="w-full flex items-center justify-between px-4 py-2.5 border rounded-xl text-sm text-left
               focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10
               transition-all duration-200
               {{ $disabled ? 'opacity-50 cursor-not-allowed bg-slate-50 border-slate-200' : 'bg-white cursor-pointer hover:border-slate-400' }}
               {{ $hasError ? 'border-red-400 bg-red-50' : 'border-slate-300' }}">
        <span :class="selectedLabel ? 'text-slate-900' : 'text-slate-400'"
              x-text="selectedLabel || '{{ $placeholder }}'"></span>
        <div class="flex items-center gap-1 flex-shrink-0 ml-2">
            {{-- Botón limpiar --}}
            @unless($disabled)
            <span x-show="wireValue !== '' && wireValue !== null"
                  @click.stop="clear()"
                  class="text-slate-300 hover:text-slate-500 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </span>
            @endunless
            {{-- Chevron --}}
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                 :class="open ? 'rotate-180' : ''"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         @click.outside="open = false; search = ''"
         x-cloak
         class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">

        {{-- Input de búsqueda --}}
        <div class="p-2 border-b border-slate-100">
            <input x-ref="searchInput"
                   x-model="search"
                   @keydown.escape="open = false; search = ''"
                   @keydown.arrow-down.prevent="activeIndex = filteredOptions.length ? (activeIndex + 1) % filteredOptions.length : -1"
                   @keydown.arrow-up.prevent="activeIndex = filteredOptions.length ? (activeIndex - 1 + filteredOptions.length) % filteredOptions.length : -1"
                   @keydown.enter.prevent="if (activeIndex >= 0 && activeIndex < filteredOptions.length) select(filteredOptions[activeIndex])"
                   x-init="$watch('open', v => v && $nextTick(() => $refs.searchInput?.focus()))"
                   type="text"
                   placeholder="{{ $placeholder }}"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg
                          focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20
                          placeholder-slate-400 text-slate-900" />
        </div>

        {{-- Lista de opciones --}}
        <ul class="max-h-52 overflow-y-auto py-1">
            <template x-for="(option, index) in filteredOptions" :key="option.value">
                <li @click="select(option)"
                    @mouseenter="activeIndex = index"
                    :class="{
                        'opacity-50 cursor-not-allowed bg-slate-50': option.disabled,
                        'bg-blue-50 text-blue-700 font-medium': option.value == wireValue && activeIndex !== index,
                        'bg-slate-100 text-slate-900': activeIndex === index && !option.disabled,
                        'text-slate-700 hover:bg-slate-50': option.value != wireValue && activeIndex !== index && !option.disabled
                    }"
                    class="px-4 py-2.5 text-sm cursor-pointer transition-colors duration-100"
                    x-text="option.label">
                </li>
            </template>
            <li x-show="filteredOptions.length === 0"
                class="px-4 py-3 text-sm text-slate-400 text-center">
                Sin resultados
            </li>
        </ul>
    </div>
</div>
