{{-- Diseño sin compilación: no requiere npm, Vite ni manifest.json. --}}
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                boxShadow: { soft: '0 18px 45px rgba(15, 23, 42, .08)' }
            }
        }
    };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    [hidden] { display: none !important; }
    html { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
    body { margin: 0; }
    input, select, textarea, button { font: inherit; }
</style>
