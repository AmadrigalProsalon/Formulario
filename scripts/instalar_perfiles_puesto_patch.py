#!/usr/bin/env python3
from pathlib import Path

root = Path.cwd()

# 1) Agregar require de rutas si no existe
web = root / 'routes' / 'web.php'
if web.exists():
    text = web.read_text(encoding='utf-8')
    line = "require __DIR__ . '/perfiles_puesto.php';"
    if line not in text:
        if "require __DIR__ . '/auth.php';" in text:
            text = text.replace("require __DIR__ . '/auth.php';", line + "\n\nrequire __DIR__ . '/auth.php';")
        else:
            text = text.rstrip() + "\n\n" + line + "\n"
        web.write_text(text, encoding='utf-8')
        print('OK: rutas/perfiles_puesto.php agregado a routes/web.php')
    else:
        print('OK: routes/web.php ya tenía perfiles_puesto.php')
else:
    print('AVISO: no existe routes/web.php')

# 2) Agregar include del autollenado al formulario público
form = root / 'resources' / 'views' / 'form.blade.php'
include = "@include('vendor.perfiles.requisicion-autofill')"
if form.exists():
    text = form.read_text(encoding='utf-8')
    if include not in text:
        if '</body>' in text:
            text = text.replace('</body>', '    ' + include + '\n</body>')
        else:
            text = text.rstrip() + "\n" + include + "\n"
        form.write_text(text, encoding='utf-8')
        print('OK: autollenado de requisición agregado a resources/views/form.blade.php')
    else:
        print('OK: form.blade.php ya tenía autollenado de requisición')
else:
    print('AVISO: no existe resources/views/form.blade.php')

# 3) Intentar agregar link de menú admin
layout = root / 'resources' / 'views' / 'admin' / 'layout.blade.php'
menu_link = """\n                <a href=\"{{ route('admin.perfiles-puesto.index') }}\"\n                   class=\"block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.perfiles-puesto.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}\">\n                    Perfiles de Puesto\n                </a>\n"""
if layout.exists():
    text = layout.read_text(encoding='utf-8')
    if "admin.perfiles-puesto.index" not in text:
        marker = "{{ route('admin.formularios.index') }}"
        pos = text.find(marker)
        if pos != -1:
            end = text.find('</a>', pos)
            if end != -1:
                end += len('</a>')
                text = text[:end] + "\n" + menu_link + text[end:]
            else:
                text += menu_link
        else:
            text += menu_link
        layout.write_text(text, encoding='utf-8')
        print('OK: menú Perfiles de Puesto agregado al layout admin')
    else:
        print('OK: layout admin ya tenía Perfiles de Puesto')
else:
    print('AVISO: no existe resources/views/admin/layout.blade.php')
