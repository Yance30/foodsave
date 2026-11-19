{{-- Figma: State-Error-Jaringan — banner saat browser kehilangan koneksi. --}}
<div x-data="{ online: navigator.onLine }" x-on:online.window="online = true" x-on:offline.window="online = false"
    x-show="! online" x-cloak x-transition.opacity role="alert"
    class="fixed inset-x-4 bottom-24 z-[70] mx-auto flex max-w-md items-start gap-3 rounded-card border border-danger/40 bg-dangersoft p-4 text-sm text-danger shadow-lg md:bottom-6">
    <x-icon name="wifi-off" class="mt-0.5 h-5 w-5 shrink-0" />
    <div class="flex-1">
        <p class="font-semibold">Koneksi terputus</p>
        <p class="mt-0.5 opacity-90">Periksa internetmu. Klaim dan konfirmasi baru bisa dikirim setelah tersambung kembali.</p>
    </div>
    <button type="button" x-on:click="location.reload()" class="shrink-0 rounded-btn px-2 py-1 font-semibold hover:bg-danger/10">Coba lagi</button>
</div>
