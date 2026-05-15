<x-layouts.encuesta>
    <div class="flex items-center justify-center px-4 py-14 min-h-[calc(100vh-5rem)]">
        <div class="w-full max-w-sm page-enter">

            {{-- Título --}}
            <h1 class="text-[1.75rem] font-bold text-slate-900 text-center tracking-tight leading-tight mb-3">
                Hola, queremos escucharte
            </h1>

            {{-- Subtítulo --}}
            <p class="text-sm text-slate-500 text-center leading-relaxed mb-8">
                Tu opinión es el primer paso para construir un mejor lugar de trabajo.
                Esta encuesta es <strong class="text-slate-700 font-semibold">completamente anónima</strong>,
                nadie podrá saber quién dijo qué.
            </p>

            {{-- Botón que abre el modal --}}
            <button onclick="window.dispatchEvent(new CustomEvent('abrir-modal-otp'))"
                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                       text-white font-semibold text-sm px-6 py-3 rounded-xl
                       transition-all duration-200 hover:-translate-y-px
                       hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                       active:translate-y-0 active:shadow-none">
                Comenzar
            </button>

        </div>
    </div>

    {{-- Modal OTP — patrón idéntico al modal PDF (animaciones + backdrop click + escape) --}}
    {{-- Modal OTP — patrón idéntico al modal PDF (animaciones + backdrop click + escape) --}}
    <div x-teleport="body">
        <div x-data="modalOtp" x-on:abrir-modal-otp.window="abrir()" x-on:keyup.escape.window="cerrar()" x-cloak
            x-show="abierto" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop con fade + click-fuera para cerrar --}}
            <div x-show="abierto" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
                @click="cerrar()"></div>

            {{-- Card con slide-up + scale --}}
            <div x-show="abierto" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden ring-1 ring-slate-900/5">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900" x-text="tituloEstado()"></h2>
                    <button @click="cerrar()" class="text-slate-400 hover:text-slate-500 transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                {{-- Cuerpo --}}
                <div class="p-6">

                    {{-- ── Estado: ingreso_llave ─────────────────────────────── --}}
                    <div x-show="estado === 'ingreso_llave'">
                        <p class="text-sm text-slate-500 mb-5">Ingresa la contraseña que te proporcionó tu empresa.</p>
                        <div class="space-y-4">
                            <input type="password" x-model="llave" x-on:keydown.enter="verificarLlave()"
                                placeholder="Contraseña de acceso" autocomplete="current-password"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900
                                       placeholder-slate-400 bg-white focus:outline-none focus:border-blue-500
                                       focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                            <button x-on:click="verificarLlave()" :disabled="llave.trim() === ''"
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                                       text-white font-semibold text-sm px-6 py-2.5 rounded-xl
                                       transition-all duration-200 hover:-translate-y-px
                                       hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                                       active:translate-y-0 active:shadow-none
                                       disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">
                                Continuar
                            </button>
                        </div>
                    </div>

                    {{-- ── Estado: validando_llave ──────────────────────────── --}}
                    <div x-show="estado === 'validando_llave'" class="flex flex-col items-center gap-3 py-6">
                        <svg class="animate-spin text-blue-600" width="28" height="28" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                            <path d="M12 2a10 10 0 0 1 10 10" />
                        </svg>
                        <p class="text-base text-slate-600">Verificando acceso…</p>
                    </div>

                    {{-- ── Estado: ingreso_numero ───────────────────────────── --}}
                    <div x-show="estado === 'ingreso_numero'" x-init="$watch('estado', val => { if (val === 'ingreso_numero') $nextTick(() => iniciarIti()) })">
                        <div class="flex items-center gap-2 mb-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100"
                                x-text="nombreEntidad"></span>
                        </div>
                        <p class="text-sm text-slate-500 mb-5">Te enviaremos un código de un solo uso a tu WhatsApp.</p>
                        <div class="space-y-4">
                            <input id="phone-input" type="tel" placeholder="Número de WhatsApp"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900
                                       placeholder-slate-400 bg-white focus:outline-none focus:border-blue-500
                                       focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Tu número se usa únicamente para enviarte el código de verificación. No se almacena en
                                nuestros sistemas.
                            </p>
                            <button x-on:click="solicitarOtp()"
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                                       text-white font-semibold text-sm px-6 py-2.5 rounded-xl
                                       transition-all duration-200 hover:-translate-y-px
                                       hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                                       active:translate-y-0 active:shadow-none">
                                Enviar código
                            </button>
                        </div>
                    </div>

                    {{-- ── Estado: enviando_otp ─────────────────────────────── --}}
                    <div x-show="estado === 'enviando_otp'" class="flex flex-col items-center gap-3 py-6">
                        <svg class="animate-spin text-blue-600" width="28" height="28" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                            <path d="M12 2a10 10 0 0 1 10 10" />
                        </svg>
                        <p class="text-base text-slate-600">Enviando código a tu WhatsApp…</p>
                    </div>

                    {{-- ── Estado: ingreso_otp ──────────────────────────────── --}}
                    <div x-show="estado === 'ingreso_otp'">
                        <p class="text-sm text-slate-500 mb-5">
                            Revisa tu WhatsApp. El código expira en
                            <span class="font-semibold tabular-nums text-slate-700" x-text="timerFormateado()"></span>.
                        </p>
                        <div class="flex gap-2 justify-center mb-5">
                            <template x-for="(_, i) in otp" :key="i">
                                <input type="text" inputmode="numeric" maxlength="1" :id="'otp-' + i"
                                    x-model="otp[i]" x-on:input="moverFoco($event, i)"
                                    x-on:keydown.backspace="retrocederFoco($event, i)"
                                    x-on:paste.prevent="pegarOtp($event)"
                                    class="w-11 h-12 text-center text-xl font-semibold border border-slate-300 rounded-xl
                                           text-slate-900 bg-white focus:outline-none focus:border-blue-500
                                           focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                            </template>
                        </div>
                        <button x-on:click="verificarOtp()" :disabled="otp.join('').length < 6"
                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-6 py-2.5 rounded-xl
                                   transition-all duration-200 hover:-translate-y-px
                                   hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                                   active:translate-y-0 active:shadow-none
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">
                            Verificar
                        </button>
                    </div>

                    {{-- ── Estado: verificando ──────────────────────────────── --}}
                    <div x-show="estado === 'verificando'" class="flex flex-col items-center gap-3 py-6">
                        <svg class="animate-spin text-blue-600" width="28" height="28" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                            <path d="M12 2a10 10 0 0 1 10 10" />
                        </svg>
                        <p class="text-base text-slate-600">Verificando…</p>
                    </div>

                    {{-- ── Estado: error ────────────────────────────────────── --}}
                    <div x-show="estado === 'error'">
                        <div class="flex items-start gap-3 p-4 rounded-lg bg-red-50 border border-red-200 mb-5">
                            <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <div>
                                <p class="text-base text-red-700 font-medium" x-text="errorMsg"></p>
                                <p x-show="intentosRestantes < 3 && intentosRestantes > 0"
                                    class="text-sm text-red-500 mt-0.5">
                                    Intentos restantes: <span class="font-semibold" x-text="intentosRestantes"></span>
                                </p>
                            </div>
                        </div>
                        <button x-on:click="reiniciarDesdeError()"
                            class="w-full inline-flex items-center justify-center gap-2 bg-white border border-slate-300
                                   hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700
                                   text-slate-600 font-medium text-sm px-6 py-2.5 rounded-xl
                                   transition-all duration-200 active:bg-blue-100">
                            Intentar de nuevo
                        </button>
                    </div>

                    {{-- ── Estado: bloqueado ────────────────────────────────── --}}
                    <div x-show="estado === 'bloqueado'">
                        <div class="flex items-start gap-3 p-4 rounded-lg bg-amber-50 border border-amber-200 mb-5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                            <p class="text-base text-amber-800 font-medium">Demasiados intentos. Solicita un nuevo
                                código.</p>
                        </div>
                        <button x-on:click="reiniciarFlujo()"
                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold text-sm px-6 py-2.5 rounded-xl
                                   transition-all duration-200 hover:-translate-y-px
                                   hover:shadow-[0_4px_16px_rgba(37,99,235,.25)]
                                   active:translate-y-0 active:shadow-none">
                            Solicitar nuevo código
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('modalOtp', () => ({
                abierto: false,
                estado: 'ingreso_llave',
                llave: '',
                loteId: null,
                nombreEntidad: '',
                numeroE164: '',
                otp: ['', '', '', '', '', ''],
                intentosRestantes: 3,
                errorMsg: '',
                timer: null,
                segundos: 600,
                iti: null,

                tituloEstado() {
                    const titulos = {
                        ingreso_llave: 'Acceso a la encuesta',
                        validando_llave: 'Verificando…',
                        ingreso_numero: 'Verifica tu identidad',
                        enviando_otp: 'Enviando código',
                        ingreso_otp: 'Ingresa el código',
                        verificando: 'Verificando…',
                        error: 'Algo salió mal',
                        bloqueado: 'Acceso bloqueado',
                    };
                    return titulos[this.estado] ?? 'Acceso a la encuesta';
                },

                abrir() {
                    this.abierto = true;
                    this.estado = 'ingreso_llave';
                },

                cerrar() {
                    this.abierto = false;
                    clearInterval(this.timer);
                },

                timerFormateado() {
                    const m = Math.floor(this.segundos / 60).toString().padStart(2, '0');
                    const s = (this.segundos % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                iniciarTimer() {
                    this.segundos = 600;
                    clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        this.segundos--;
                        if (this.segundos <= 0) {
                            clearInterval(this.timer);
                            this.errorMsg = 'El código expiró. Solicita uno nuevo.';
                            this.estado = 'error';
                        }
                    }, 1000);
                },

                iniciarIti() {
                    const input = document.getElementById('phone-input');
                    if (!input || typeof intlTelInput === 'undefined') return;
                    if (this.iti) {
                        this.iti.destroy();
                    }
                    this.iti = intlTelInput(input, {
                        preferredCountries: ['mx'],
                        separateDialCode: true,
                        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js',
                    });
                },

                async verificarLlave() {
                    this.estado = 'validando_llave';
                    try {
                        const res = await fetch('/encuesta/verificar-llave', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({
                                password: this.llave
                            }),
                        });
                        const data = await res.json();
                        if (data.status === 'llave_valida') {
                            this.loteId = data.lote_id;
                            this.nombreEntidad = data.nombre_entidad;
                            this.estado = 'ingreso_numero';
                        } else {
                            this.errorMsg = data.error === 'sin_lote_activo' ?
                                'No hay una encuesta activa en este momento.' :
                                'Acceso incorrecto. Verifica la contraseña.';
                            this.estado = 'error';
                        }
                    } catch {
                        this.errorMsg = 'Error de conexión. Intenta de nuevo.';
                        this.estado = 'error';
                    }
                },

                async solicitarOtp() {
                    if (this.iti) {
                        this.numeroE164 = this.iti.getNumber();
                    }
                    this.estado = 'enviando_otp';
                    try {
                        const res = await fetch('/encuesta/solicitar-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({
                                numero_e164: this.numeroE164,
                                lote_id: this.loteId
                            }),
                        });
                        const data = await res.json();
                        if (data.status === 'otp_enviado') {
                            this.otp = ['', '', '', '', '', ''];
                            this.estado = 'ingreso_otp';
                            this.iniciarTimer();
                            this.$nextTick(() => {
                                document.getElementById('otp-0')?.focus();
                            });
                        } else {
                            this.errorMsg = data.error === 'ya_participaste' ?
                                'Ya participaste en esta encuesta.' :
                                'No fue posible enviar el código. Intenta de nuevo.';
                            this.estado = 'error';
                        }
                    } catch {
                        this.errorMsg = 'Error de conexión. Intenta de nuevo.';
                        this.estado = 'error';
                    }
                },

                async verificarOtp() {
                    this.estado = 'verificando';
                    try {
                        const res = await fetch('/encuesta/verificar-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({
                                numero_e164: this.numeroE164,
                                otp: this.otp.join(''),
                                lote_id: this.loteId,
                            }),
                        });
                        const data = await res.json();
                        if (data.status === 'token_asignado') {
                            clearInterval(this.timer);
                            window.location.href = '/encuesta/' + data.token;
                        } else if (data.error === 'intentos_agotados') {
                            clearInterval(this.timer);
                            this.estado = 'bloqueado';
                        } else {
                            this.intentosRestantes = data.intentos_restantes ?? this
                                .intentosRestantes;
                            this.errorMsg = 'Código incorrecto.';
                            this.otp = ['', '', '', '', '', ''];
                            this.estado = 'error';
                        }
                    } catch {
                        this.errorMsg = 'Error de conexión. Intenta de nuevo.';
                        this.estado = 'error';
                    }
                },

                reiniciarDesdeError() {
                    this.errorMsg = '';
                    if (this.loteId && this.numeroE164) {
                        this.otp = ['', '', '', '', '', ''];
                        this.estado = 'ingreso_otp';
                        this.iniciarTimer();
                        this.$nextTick(() => {
                            document.getElementById('otp-0')?.focus();
                        });
                    } else if (this.loteId) {
                        this.estado = 'ingreso_numero';
                    } else {
                        this.llave = '';
                        this.estado = 'ingreso_llave';
                    }
                },

                reiniciarFlujo() {
                    clearInterval(this.timer);
                    this.otp = ['', '', '', '', '', ''];
                    this.intentosRestantes = 3;
                    this.errorMsg = '';
                    this.numeroE164 = '';
                    this.estado = 'ingreso_numero';
                },

                moverFoco(event, index) {
                    const val = event.target.value.replace(/\D/g, '');
                    this.otp[index] = val ? val[0] : '';
                    if (val && index < 5) {
                        this.$nextTick(() => {
                            document.getElementById('otp-' + (index + 1))?.focus();
                        });
                    }
                },

                retrocederFoco(event, index) {
                    if (!this.otp[index] && index > 0) {
                        this.otp[index - 1] = '';
                        this.$nextTick(() => {
                            document.getElementById('otp-' + (index - 1))?.focus();
                        });
                    }
                },

                pegarOtp(event) {
                    const text = (event.clipboardData || window.clipboardData).getData('text').replace(
                        /\D/g, '').slice(0, 6);
                    this.otp = text.split('').concat(['', '', '', '', '', '']).slice(0, 6);
                    const nextIndex = Math.min(text.length, 5);
                    this.$nextTick(() => {
                        document.getElementById('otp-' + nextIndex)?.focus();
                    });
                },
            }));
        });
    </script>

</x-layouts.encuesta>
