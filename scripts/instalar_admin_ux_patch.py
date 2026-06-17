from pathlib import Path
import re

root = Path.cwd()

# 1) Agregar la ruta nueva del calendario si todavía no está incluida
web = root / 'routes' / 'web.php'
line = "require __DIR__ . '/admin_ux.php';"
if web.exists():
    text = web.read_text(encoding='utf-8')
    if line not in text:
        text = text.rstrip() + "\n\n" + line + "\n"
        web.write_text(text, encoding='utf-8')
        print('OK: agregado require de routes/admin_ux.php en routes/web.php')
    else:
        print('OK: routes/admin_ux.php ya estaba agregado')
else:
    print('AVISO: no existe routes/web.php')

# 2) Evitar ruta duplicada antigua del calendario.
#    La ruta correcta debe usar App\Http\Controllers\Permisos\CalendarioAusenciasController@index.
permisos = root / 'routes' / 'permisos.php'
if permisos.exists():
    original = permisos.read_text(encoding='utf-8')
    lines = original.splitlines()
    new_lines = []
    changed = False
    for current in lines:
        stripped = current.strip()
        if (
            'PermisosAdminController::class' in current
            and "'calendario'" in current
            and "name('permisos.calendario')" in current
            and not stripped.startswith('//')
        ):
            new_lines.append('// ' + current + ' // Desactivada: ruta duplicada, se usa routes/admin_ux.php')
            changed = True
        else:
            new_lines.append(current)
    if changed:
        permisos.write_text('\n'.join(new_lines) + '\n', encoding='utf-8')
        print('OK: se desactivó ruta antigua duplicada de calendario en routes/permisos.php')
    else:
        print('OK: no se encontró ruta antigua duplicada de calendario o ya estaba desactivada')
else:
    print('AVISO: no existe routes/permisos.php')
