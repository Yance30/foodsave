<script>
    window.addEventListener('theme-toggle', () => {
        const root = document.documentElement;
        root.classList.toggle('dark');
        try {
            localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
        } catch (e) {}
    });
</script>
