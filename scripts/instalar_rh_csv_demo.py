#!/usr/bin/env python3
from pathlib import Path

root = Path.cwd()

# 1) Registrar rutas
web = root / 'routes' / 'web.php'
if web.exists():
    text = web.read_text(encoding='utf-8')
    line = "require __DIR__ . '/perfiles_puesto_csv.php';"
    if line not in text:
        text = text.rstrip() + "\n\n" + line + "\n"
        web.write_text(text, encoding='utf-8')
        print('OK: rutas/perfiles_puesto_csv.php registrado en routes/web.php')
    else:
        print('OK: rutas ya registradas')
else:
    print('AVISO: no existe routes/web.php')

# 2) Incluir JS de autollenado en form.blade.php
form = root / 'resources' / 'views' / 'form.blade.php'
include = "@includeIf('partials.requisicion-csv-autofill')"
if form.exists():
    text = form.read_text(encoding='utf-8')
    if include not in text:
        if '</body>' in text:
            text = text.replace('</body>', f"    {include}\n</body>")
        else:
            text = text.rstrip() + f"\n{include}\n"
        form.write_text(text, encoding='utf-8')
        print('OK: autollenado incluido en resources/views/form.blade.php')
    else:
        print('OK: autollenado ya incluido')
else:
    print('AVISO: no existe resources/views/form.blade.php')

# 3) Agregar enlace al menú admin si se puede
layout = root / 'resources' / 'views' / 'admin' / 'layout.blade.php'
link = "{{ route('admin.perfiles-puesto.csv') }}"
menu_block = """
<a href=\"{{ route('admin.perfiles-puesto.csv') }}\"
   class=\"block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.perfiles-puesto.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}\">
    Perfiles de puesto
</a>
""".strip()
if layout.exists():
    text = layout.read_text(encoding='utf-8')
    if link not in text:
        marker = "</nav>"
        if marker in text:
            text = text.replace(marker, menu_block + "\n" + marker, 1)
        else:
            text = text + "\n" + menu_block + "\n"
        layout.write_text(text, encoding='utf-8')
        print('OK: enlace Perfiles de puesto agregado al menú admin')
    else:
        print('OK: enlace de perfiles ya existe')
else:
    print('AVISO: no existe resources/views/admin/layout.blade.php')

print('Instalación del parche CSV demo completada.')
