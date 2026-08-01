<div
    x-data="{ toasts: [] }"
    x-on:toast-notify.window="
        const id = Date.now() + Math.random();
        toasts.push({ id, mensaje: $event.detail.mensaje, tipo: $event.detail.tipo || 'success' });
        setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 3000);
    "
    class="fixed top-4 right-4 z-[60] space-y-2 w-full max-w-sm"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition
            :class="toast.tipo === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'"
            class="rounded-2xl border px-4 py-3 shadow-lg text-sm font-medium flex items-center justify-between gap-3"
        >
            <span x-html="toast.mensaje"></span>
            <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
    </template>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            window.dispatchEvent(new CustomEvent('toast-notify', { detail: data }));
        });
    });
</script>
