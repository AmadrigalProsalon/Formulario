from pathlib import Path

root = Path.cwd()
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
