<div id="announcement-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 hidden" aria-labelledby="announcement-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm dark:bg-black/50" onclick="closeAnnouncementModal()"></div>
    
    <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-5 pt-6 sm:p-6 sm:pt-8">
            <div class="text-center mb-5 sm:mb-6">
                <div class="inline-flex items-center justify-center w-11 h-11 sm:w-14 sm:h-14 rounded-full bg-slate-100 dark:bg-slate-700 mb-3 sm:mb-4">
                    <svg class="w-5 h-5 sm:w-7 sm:h-7 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <h2 id="announcement-title" class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white mb-1 sm:mb-2">{{ $title ?? 'Announcement' }}</h2>
                <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $slot }}
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeAnnouncementModal()" class="flex-1 px-4 py-2.5 bg-slate-900 dark:bg-slate-200 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 text-sm font-semibold rounded-xl transition-colors">
                    Got it
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('announcement-modal');
    
    function showModal() {
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
    
    window.closeAnnouncementModal = function() {
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };
    
    setTimeout(showModal, 500);
})();
</script>
