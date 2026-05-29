@extends('admin.layout')

@section('title', 'Usuarios')
@section('page_title', 'Usuarios del sistema')
@section('page_description', 'Crea y administra usuarios que pueden acceder al panel RH.')

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-bold mb-4">Crear nuevo usuario</h2>

        <form method="POST" action="{{ route('admin.usuarios.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-1">Nombre</label>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full rounded-xl border-slate-300"
                       required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Correo</label>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="w-full rounded-xl border-slate-300"
                       required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Contraseña</label>
                <input type="password"
                       name="password"
                       class="w-full rounded-xl border-slate-300"
                       required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Confirmar contraseña</label>
                <input type="password"
                       name="password_confirmation"
                       class="w-full rounded-xl border-slate-300"
                       required>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox"
                       name="is_admin"
                       value="1"
                       class="rounded"
                       checked>
                <span>Administrador</span>
            </div>

            <div class="md:col-span-3 flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-slate-950 text-white px-5 py-2.5 hover:bg-slate-800">
                    Crear usuario
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-lg font-bold">Usuarios registrados</h2>
            <p class="text-sm text-slate-500">
                Desde aquí puedes actualizar nombre, correo, contraseña y permisos.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left p-4">Usuario</th>
                        <th class="text-left p-4">Correo</th>
                        <th class="text-left p-4">Permisos</th>
                        <th class="text-left p-4">Cambiar contraseña</th>
                        <th class="text-right p-4">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4">
                                <form id="usuario-{{ $usuario->id }}"
                                      method="POST"
                                      action="{{ route('admin.usuarios.update', $usuario) }}"
                                      class="space-y-2">
                                    @csrf
                                    @method('PUT')

                                    <input type="text"
                                           name="name"
                                           value="{{ $usuario->name }}"
                                           class="w-full rounded-xl border-slate-300">
                            </td>

                            <td class="p-4">
                                    <input type="email"
                                           name="email"
                                           value="{{ $usuario->email }}"
                                           class="w-full rounded-xl border-slate-300">
                            </td>

                            <td class="p-4">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox"
                                               name="is_admin"
                                               value="1"
                                               class="rounded"
                                               @checked($usuario->is_admin)>
                                        <span>Administrador</span>
                                    </label>
                            </td>

                            <td class="p-4">
                                    <input type="password"
                                           name="password"
                                           placeholder="Nueva contraseña"
                                           class="w-full rounded-xl border-slate-300 mb-2">

                                    <input type="password"
                                           name="password_confirmation"
                                           placeholder="Confirmar contraseña"
                                           class="w-full rounded-xl border-slate-300">
                                </form>
                            </td>

                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="submit"
                                            form="usuario-{{ $usuario->id }}"
                                            class="rounded-xl bg-slate-950 text-white px-3 py-2 hover:bg-slate-800">
                                        Guardar
                                    </button>

                                    @if(auth()->id() !== $usuario->id)
                                        <form method="POST"
                                              action="{{ route('admin.usuarios.destroy', $usuario) }}"
                                              onsubmit="return confirm('¿Eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="rounded-xl bg-red-600 text-white px-3 py-2 hover:bg-red-700">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-200">
            {{ $usuarios->links() }}
        </div>
    </div>
@endsection
