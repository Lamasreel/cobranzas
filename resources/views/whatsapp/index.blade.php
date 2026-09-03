<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Conversaciones WhatsApp</h2>
                <p class="text-sm text-slate-500">Historial de mensajes con los clientes y prueba de las plantillas de aviso de mora</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    id="btn-enviar-plantillas"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow"
                >
                    <i class="fa-solid fa-paper-plane"></i>
                    Enviar plantillas de prueba
                </button>
            </div>
        </div>
    </x-slot>

    <div
        id="whatsapp-config"
        data-clientes-url="{{ route('morosos.whatsapp.clientes') }}"
        data-conversacion-url="{{ route('morosos.whatsapp.conversacion', ['documento' => '__DNI__']) }}"
        data-csrf="{{ csrf_token() }}"
    ></div>
    <div id="plantillas-config" data-url="{{ route('whatsapp.plantillas_prueba') }}" data-csrf="{{ csrf_token() }}"></div>
    <div id="plantillas-telefono" data-value="{{ config('services.whatsapp.to') }}"></div>

    <div class="bg-white rounded-2xl shadow-2xl w-full h-[75vh] overflow-hidden border border-slate-200/80 flex flex-col">
        <div class="px-6 py-4 border-b flex items-center justify-between bg-emerald-50">
            <div>
                <h3 class="text-xl font-bold text-slate-800">
                    Conversaciones WhatsApp
                </h3>
                <p class="text-sm text-slate-500">
                    Registros individuales de clientes y conversaciones con el chatbot
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 flex-1 overflow-hidden">

            <div class="border-r overflow-y-auto bg-slate-50">
                <div class="p-3 border-b bg-white">
                    <input
                        type="text"
                        id="waBuscarCliente"
                        placeholder="Buscar por DNI o nombre..."
                        class="w-full rounded-xl border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                    >
                </div>

                <div id="waClientesLista" class="divide-y"></div>
            </div>

            <div class="md:col-span-2 flex flex-col overflow-hidden">
                <div id="waClienteHeader" class="px-5 py-4 border-b bg-white">
                    <h3 class="font-bold text-slate-800">Seleccioná un cliente</h3>
                    <p class="text-sm text-slate-500">La conversación aparecerá acá.</p>
                </div>

                <div id="waMensajesLista" class="flex-1 overflow-y-auto p-5 space-y-3 bg-slate-100">
                    <div class="text-center text-sm text-slate-500 mt-10">
                        No hay conversación seleccionada.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        function openWhatsappPage() {
            cargarClientesWhatsapp();
        }

        async function cargarClientesWhatsapp(busqueda = '') {
            const cfg = document.getElementById('whatsapp-config');
            const url = cfg.dataset.clientesUrl + '?q=' + encodeURIComponent(busqueda);

            const lista = document.getElementById('waClientesLista');

            lista.innerHTML = `
                <div class="p-4 text-sm text-slate-500">
                    Cargando clientes...
                </div>
            `;

            try {
                const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await resp.json();

                if (!resp.ok || data.ok === false) {
                    throw new Error(data.message || 'No se pudieron cargar los clientes.');
                }

                lista.innerHTML = '';

                if (!data.clientes.length) {
                    lista.innerHTML = `
                        <div class="p-4 text-sm text-slate-500">
                            No se encontraron conversaciones.
                        </div>
                    `;
                    return;
                }

                data.clientes.forEach(cliente => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full text-left p-4 hover:bg-emerald-50 transition';

                    btn.innerHTML = `
                        <div class="font-bold text-slate-800">${escapeHtml(cliente.NOMBRE || 'Sin nombre')}</div>
                        <div class="text-xs text-slate-500">
                            ${cliente.DNI ? 'DNI: ' + escapeHtml(cliente.DNI) : 'Teléfono: ' + escapeHtml(cliente.telefono || '-')}
                        </div>
                        <div class="text-xs text-slate-400 mt-1">
                            Último mensaje: ${escapeHtml(cliente.ultimo_mensaje || '-')}
                        </div>
                    `;

                    btn.addEventListener('click', () => {
                        cargarConversacionWhatsapp(cliente);
                    });

                    lista.appendChild(btn);
                });
            } catch (e) {
                lista.innerHTML = `
                    <div class="p-4 text-sm text-red-600">
                        ${escapeHtml(e.message || 'Error cargando clientes.')}
                    </div>
                `;
            }
        }

        async function cargarConversacionWhatsapp(cliente) {
            const header = document.getElementById('waClienteHeader');
            const mensajes = document.getElementById('waMensajesLista');

            header.innerHTML = `
                <h3 class="font-bold text-slate-800">${escapeHtml(cliente.NOMBRE || 'Cliente')}</h3>
                <p class="text-sm text-slate-500">${cliente.DNI ? 'DNI: ' + escapeHtml(cliente.DNI) : 'Teléfono: ' + escapeHtml(cliente.telefono || '-')}</p>
            `;

            // Conversaciones de prueba (sin DNI) no tienen un chat asociado a un cliente.
            if (!cliente.DNI) {
                mensajes.innerHTML = `
                    <div class="text-center text-sm text-slate-500 mt-10 px-6">
                        <div class="text-base font-semibold text-slate-600 mb-1">Conversación de prueba</div>
                        Mensajes registrados desde la prueba de plantillas enviada al número
                        <b>${escapeHtml(cliente.telefono || '')}</b>.
                    </div>
                `;
                return;
            }

            const cfg = document.getElementById('whatsapp-config');
            const url = cfg.dataset.conversacionUrl.replace('__DNI__', encodeURIComponent(cliente.DNI));

            mensajes.innerHTML = `
                <div class="text-center text-sm text-slate-500 mt-10">
                    Cargando conversación...
                </div>
            `;

            try {
                const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await resp.json();

                if (!resp.ok || data.ok === false) {
                    throw new Error(data.message || 'No se pudo cargar la conversación.');
                }

                mensajes.innerHTML = '';

                if (!data.mensajes.length) {
                    mensajes.innerHTML = `
                        <div class="text-center text-sm text-slate-500 mt-10">
                            Este cliente todavía no tiene mensajes.
                        </div>
                    `;
                    return;
                }

                data.mensajes.forEach(msg => {
                    const esSaliente = msg.direccion === 'saliente';

                    const div = document.createElement('div');
                    div.className = esSaliente ? 'flex justify-end' : 'flex justify-start';

                    div.innerHTML = `
                        <div class="max-w-[75%] rounded-2xl px-4 py-3 shadow text-sm ${
                            esSaliente
                                ? 'bg-emerald-600 text-white rounded-br-sm'
                                : 'bg-white text-slate-800 rounded-bl-sm'
                        }">
                            <div class="whitespace-pre-wrap">${escapeHtml(msg.mensaje || '')}</div>
                            <div class="text-[11px] mt-2 opacity-70 text-right">
                                ${escapeHtml(msg.fecha || '')}
                            </div>
                        </div>
                    `;

                    mensajes.appendChild(div);
                });

                mensajes.scrollTop = mensajes.scrollHeight;
            } catch (e) {
                mensajes.innerHTML = `
                    <div class="text-center text-sm text-red-600 mt-10">
                        ${escapeHtml(e.message || 'Error cargando conversación.')}
                    </div>
                `;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const boton = document.getElementById('btn-enviar-plantillas');
            const buscador = document.getElementById('waBuscarCliente');

            openWhatsappPage();

            if (buscador) {
                buscador.addEventListener('input', () => {
                    cargarClientesWhatsapp(buscador.value.trim());
                });
            }

            if (boton) {
                boton.addEventListener('click', enviarPlantillasPrueba);
            }
        });

        async function enviarPlantillasPrueba() {
            const cfg = document.getElementById('plantillas-config');
            const url = cfg.dataset.url;
            const csrf = cfg.dataset.csrf;
            const telDefault = document.getElementById('plantillas-telefono')?.dataset?.value || '';

            const { value: telefono } = await Swal.fire({
                title: 'Enviar plantillas de prueba',
                html: `
                    <div class="text-left">
                        <div class="text-sm text-slate-600 mb-2">
                            Se enviarán las 3 plantillas (<b>primer_aviso</b>, <b>segundo_aviso</b> y <b>prejudicial</b>) a este número. No se envía a clientes.
                        </div>
                        <input id="swal-plantillas-tel" type="text" inputmode="tel" placeholder="+54..."
                               class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                        <div class="text-xs text-slate-400 mt-2">El número se normaliza automáticamente al formato 549…</div>
                    </div>
                `,
                input: 'text',
                inputValue: telDefault,
                inputAttributes: { inputmode: 'tel' },
                showCancelButton: true,
                confirmButtonText: 'Enviar prueba',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16a34a',
                didOpen: () => document.getElementById('swal-plantillas-tel')?.focus()
            });

            if (!telefono) return;

            Swal.fire({
                title: 'Enviando plantillas…',
                text: 'Por favor esperá',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ telefono })
                });

                const data = await resp.json().catch(() => ({}));

                if (!resp.ok || data.ok === false) {
                    throw new Error(data.message || 'No se pudieron enviar las plantillas.');
                }

                const nombres = {
                    'primer_aviso_mora': 'Primer aviso de mora',
                    'segundo_aviso_mora': 'Segundo aviso de mora',
                    'aviso_prejudicial_mora': 'Aviso prejudicial'
                };

                let filas = '';
                Object.entries(data.resultados || {}).forEach(([clave, r]) => {
                    const estadoHtml = r.ok
                        ? '<span class="text-emerald-600 font-bold">✓ Enviado</span>'
                        : `<span class="text-red-600 font-bold">✗ Error (${escapeHtml(r.status || '')})</span>`;
                    filas += `
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2">
                            <span class="font-medium">${nombres[clave] || escapeHtml(clave)}</span>
                            ${estadoHtml}
                        </div>
                    `;
                });

                Swal.fire({
                    icon: data.todasOk ? 'success' : 'warning',
                    title: 'Resultado de la prueba',
                    html: `
                        <div class="text-left text-sm">
                            <div class="text-xs text-slate-500 mb-2">Destino: <b>${escapeHtml(data.telefono || '')}</b></div>
                            ${filas}
                        </div>
                    `,
                    confirmButtonText: 'Listo'
                });

                cargarClientesWhatsapp(document.getElementById('waBuscarCliente')?.value.trim() || '');
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: `<div class="text-left text-sm whitespace-pre-wrap">${escapeHtml(e.message || 'Error')}</div>`
                });
            }
        }
    </script>
</x-app-layout>