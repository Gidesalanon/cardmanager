document.addEventListener('alpine:init', () => {
    Alpine.data('studentImport', () => ({
        file: null,
        students: [],
        loading: false,

        async upload() {
            if (!this.file) {
                alert('Veuillez sélectionner un fichier');
                return;
            }

            this.loading = true;

            const fd = new FormData();
            fd.append('document', this.file);

            try {
                const r = await fetch(PREVIEW_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: fd
                });

                const d = await r.json();
                console.log('PREVIEW RESPONSE', d);

                this.students = d.students ?? [];

            } catch (e) {
                console.error(e);
                alert('Erreur lors du preview');
            } finally {
                this.loading = false;
            }
        },

        handlePhoto(e, i) {
            this.students[i].photo = e.target.files[0];
        },

        async save() {
            alert('Save OK (à venir)');
        }
    }));
});
