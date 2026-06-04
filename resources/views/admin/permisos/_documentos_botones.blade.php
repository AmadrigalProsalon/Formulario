<div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.permisos.documentos.inicial', $solicitud) }}"
       class="rounded-xl bg-slate-800 text-white px-3 py-2 hover:bg-slate-700 text-sm">
        Descargar formato
    </a>

    <form method="POST" action="{{ route('admin.permisos.documentos.reenviar_inicial', $solicitud) }}">
        @csrf
        <button type="submit" class="rounded-xl bg-blue-600 text-white px-3 py-2 hover:bg-blue-700 text-sm">
            Reenviar formato
        </button>
    </form>

    <a href="{{ route('admin.permisos.documentos.firmado', $solicitud) }}"
       class="rounded-xl bg-green-600 text-white px-3 py-2 hover:bg-green-700 text-sm">
        Descargar firmado
    </a>

    <form method="POST" action="{{ route('admin.permisos.documentos.reenviar_firmado_rh', $solicitud) }}">
        @csrf
        <button type="submit" class="rounded-xl bg-emerald-700 text-white px-3 py-2 hover:bg-emerald-800 text-sm">
            Enviar firmado a RH
        </button>
    </form>
</div>
