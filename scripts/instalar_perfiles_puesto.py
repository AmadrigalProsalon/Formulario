from pathlib import Path

root = Path.cwd()

# 1. Add routes require
web = root / 'routes' / 'web.php'
if web.exists():
    text = web.read_text()
    require_line = "require __DIR__ . '/perfiles_puesto.php';"
    if require_line not in text:
        marker = "require __DIR__ . '/auth.php';"
        if marker in text:
            text = text.replace(marker, require_line + "\n" + marker)
        else:
            text += "\n" + require_line + "\n"
        web.write_text(text)
        print('OK: ruta perfiles_puesto agregada en routes/web.php')
    else:
        print('OK: ruta perfiles_puesto ya existía')
else:
    print('WARN: no existe routes/web.php')

# 2. Add JS import
app_js = root / 'resources' / 'js' / 'app.js'
if app_js.exists():
    text = app_js.read_text()
    import_line = "import './perfiles-puesto-form';"
    if import_line not in text:
        text += "\n" + import_line + "\n"
        app_js.write_text(text)
        print('OK: JS autocomplete agregado en resources/js/app.js')
    else:
        print('OK: JS autocomplete ya existía')
else:
    print('WARN: no existe resources/js/app.js')

# 3. Add menu link in admin layout if possible
layout = root / 'resources' / 'views' / 'admin' / 'layout.blade.php'
menu_link = """
                <a href=\"{{ route('admin.perfiles-puesto.index') }}\"
                   class=\"block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.perfiles-puesto.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}\">
                    Perfiles de puesto
                </a>
"""
if layout.exists():
    text = layout.read_text()
    if "admin.perfiles-puesto.index" not in text:
        if "admin.formularios.index" in text:
            # Insert before Formularios link block approximately
            idx = text.find("<a href=\"{{ route('admin.formularios.index') }}\"")
            if idx != -1:
                text = text[:idx] + menu_link + "\n" + text[idx:]
            else:
                text = text.replace('<nav class="flex-1 p-4 space-y-2">', '<nav class="flex-1 p-4 space-y-2">\n' + menu_link)
        else:
            text = text.replace('<nav class="flex-1 p-4 space-y-2">', '<nav class="flex-1 p-4 space-y-2">\n' + menu_link)
        layout.write_text(text)
        print('OK: menú Perfiles de puesto agregado')
    else:
        print('OK: menú Perfiles de puesto ya existía')
else:
    print('WARN: no existe resources/views/admin/layout.blade.php')
