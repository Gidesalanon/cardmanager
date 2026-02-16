document.addEventListener('alpine:init', () => {
    Alpine.data('studentImport', () => ({
        students: [],
        loading: false,

        async upload() {
            try {
                const file = this.$refs.file.files[0];
                if (!file) {
                    alert('Veuillez sélectionner un fichier');
                    return;
                }

                const formData = new FormData();
                formData.append('document', file);

                const response = await fetch(
                    previewUrl,
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    }
                );

                if (!response.ok) {
                    throw new Error('Erreur serveur');
                }

                const data = await response.json();
                console.log(data);

                this.students = data.students ?? [];

            } catch (e) {
                console.error(e);
                alert('Erreur lors de l’analyse');
            }
        },

        handlePhoto(e, index) {
            this.students[index].photo = e.target.files[0];
        },

        async save() {
            alert('Save déclenché (OK)');
        }
    }));
});
