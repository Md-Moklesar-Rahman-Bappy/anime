export function adminForm(options = {}) {
    return {
        key: options.key || null,
        ajax: options.ajax ?? false,
        redirect: options.redirect || null,

        saving: false,
        progress: 0,
        dirty: false,

        init() {
            this.restoreDraft();
        },

        draftKey() {
            return this.key ? `admin_form_draft_${this.key}` : null;
        },

        saveDraft() {
            if (!this.key) return;

            const data = {};
            const formData = new FormData(this.$el);

            for (const [name, value] of formData.entries()) {
                if (!name || name === '_token' || name === '_method') continue;
                if (value instanceof File) continue;

                if (name.endsWith('[]')) {
                    if (!Array.isArray(data[name])) data[name] = [];
                    data[name].push(value);
                } else {
                    data[name] = value;
                }
            }

            try {
                localStorage.setItem(this.draftKey(), JSON.stringify(data));
                this.dirty = true;
            } catch (_) {}
        },

        restoreDraft() {
            if (!this.key) return;

            const raw = localStorage.getItem(this.draftKey());
            if (!raw) return;

            let data = {};

            try {
                data = JSON.parse(raw);
            } catch (_) {
                return;
            }

            if (!Object.keys(data).length) return;

            const shouldRestore = confirm('A saved draft was found. Restore it?');

            if (!shouldRestore) return;

            [...this.$el.elements].forEach((field) => {
                if (!field.name || data[field.name] === undefined) return;

                const value = data[field.name];

                if (field.type === 'checkbox') {
                    if (Array.isArray(value)) {
                        field.checked = value.includes(field.value);
                    } else {
                        field.checked = Boolean(value);
                    }
                    return;
                }

                if (field.type === 'radio') {
                    field.checked = value === field.value;
                    return;
                }

                if (field.type !== 'file') {
                    field.value = value;
                }
            });

            this.toast('Draft restored', 'success');
        },

        clearDraft() {
            if (!this.key) return;

            try {
                localStorage.removeItem(this.draftKey());
            } catch (_) {}
        },

        validate() {
            let valid = true;

            [...this.$el.elements].forEach((field) => {
                if (!field.checkValidity || field.type === 'hidden') return;

                const isValid = field.checkValidity();

                field.classList.toggle('field-error', !isValid);

                if (!isValid) valid = false;
            });

            if (!valid) {
                this.toast('Please fix the highlighted fields.', 'error');
            }

            return valid;
        },

        submit(event) {
            if (!this.validate()) {
                event.preventDefault();
                return false;
            }

            if (!this.ajax) {
                this.clearDraft();
                return true;
            }

            event.preventDefault();
            this.ajaxSubmit();
            return false;
        },

        ajaxSubmit() {
            const form = this.$el;
            const formData = new FormData(form);

            this.saving = true;
            this.progress = 0;

            const xhr = new XMLHttpRequest();

            xhr.open(form.method || 'POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable) return;

                this.progress = Math.round((event.loaded / event.total) * 100);

                window.dispatchEvent(
                    new CustomEvent('upload-progress', {
                        detail: {
                            progress: this.progress,
                        },
                    })
                );
            });

            xhr.onload = () => {
                this.saving = false;

                let response = {};

                try {
                    response = JSON.parse(xhr.responseText || '{}');
                } catch (_) {}

                if (xhr.status >= 200 && xhr.status < 300) {
                    this.progress = 100;
                    this.clearDraft();

                    this.toast(response.message || 'Saved successfully.', 'success');

                    if (response.redirect) {
                        window.location.href = response.redirect;
                        return;
                    }

                    if (this.redirect) {
                        window.location.href = this.redirect;
                    }

                    return;
                }

                this.toast(response.message || 'Save failed.', 'error');
            };

            xhr.onerror = () => {
                this.saving = false;
                this.toast('Network error. Please try again.', 'error');
            };

            xhr.send(formData);
        },

        toast(message, type = 'success') {
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: { message, type },
                })
            );
        },
    };
}

export function toastCenter() {
    return {
        showToast: false,
        message: '',
        type: 'success',

        init() {
            window.addEventListener('toast', (event) => {
                this.message = event.detail.message || 'Done';
                this.type = event.detail.type || 'success';
                this.showToast = true;

                setTimeout(() => {
                    this.showToast = false;
                }, 3000);
            });
        },
    };
}

export function smartDropzone(config = {}) {
    return {
        name: config.name || 'file',
        multiple: config.multiple || false,

        drag: false,
        files: [],
        dragIndex: null,
        progress: 0,

        init() {
            window.addEventListener('upload-progress', (event) => {
                this.progress = event.detail.progress || 0;

                this.files = this.files.map((file) => ({
                    ...file,
                    progress: this.progress,
                }));
            });
        },

        handleInput(event) {
            this.setFiles(event.target.files);
        },

        handleDrop(event) {
            this.drag = false;
            this.setFiles(event.dataTransfer.files);
            this.syncInput();
        },

        setFiles(fileList) {
            const incoming = Array.from(fileList || []);

            if (!this.multiple) {
                this.revokePreviews();
                this.files = incoming.slice(0, 1).map((file, index) => this.makeFile(file, index));
            } else {
                this.files = [
                    ...this.files,
                    ...incoming.map((file, index) => this.makeFile(file, this.files.length + index)),
                ];
            }

            this.syncInput();
        },

        makeFile(file, index) {
            return {
                id: `${Date.now()}_${index}_${file.name}`,
                file,
                name: file.name,
                size: this.formatSize(file.size),
                preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
                progress: 0,
            };
        },

        removeFile(index) {
            if (this.files[index]?.preview) {
                URL.revokeObjectURL(this.files[index].preview);
            }

            this.files.splice(index, 1);
            this.syncInput();
        },

        clearFiles() {
            this.revokePreviews();
            this.files = [];
            this.syncInput();
        },

        revokePreviews() {
            this.files.forEach((item) => {
                if (item.preview) URL.revokeObjectURL(item.preview);
            });
        },

        startDrag(index) {
            this.dragIndex = index;
        },

        dropOn(index) {
            if (this.dragIndex === null || this.dragIndex === index) return;

            const moved = this.files.splice(this.dragIndex, 1)[0];
            this.files.splice(index, 0, moved);

            this.dragIndex = null;
            this.syncInput();
        },

        syncInput() {
            const input = this.$refs.input;
            if (!input) return;

            const dataTransfer = new DataTransfer();

            this.files.forEach((item) => {
                dataTransfer.items.add(item.file);
            });

            input.files = dataTransfer.files;

            input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        formatSize(bytes) {
            if (!bytes) return '0 KB';

            const kb = bytes / 1024;

            if (kb < 1024) {
                return `${kb.toFixed(1)} KB`;
            }

            return `${(kb / 1024).toFixed(1)} MB`;
        },
    };
}