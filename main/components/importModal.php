<div id="importModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[100] p-4 transition-all duration-300">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden animate-slideUp relative z-[110]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Importer des étudiants</h2>
            <button type="button" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100" onclick="toggleModal('importModal', false)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6">
            <form method="post" enctype="multipart/form-data" class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">Fichier CSV</label>
                    <input accept=".csv" name="csv_file" type="file" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">Photos (Sélection multiple)</label>
                    <input name="photos[]" accept="image/*" type="file" multiple required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-200 rounded-xl p-1">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 shadow-lg transition-all">Lancer l'import</button>
                    <button type="button" class="px-6 py-3 bg-white text-slate-600 border border-slate-200 font-bold rounded-xl hover:bg-slate-50" onclick="toggleModal('importModal', false)">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModal(id, show) {
    const modal = document.getElementById(id);
    if (show) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
}
</script>