export function adminForm(options = {}) {
    return {
        key: options.key || null,
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

            localStorage.setItem(this.draftKey(), JSON.stringify(data));
            this.dirty = true;
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
            if (this.key) {
                localStorage.removeItem(this.draftKey());
            }
        },

        validate() {
            let valid = true;

            [...this.$el.elements].forEach((field) => {
                if (!field.checkValidity || field.type === 'hidden') return;

                const isValid = field.checkValidity();

                field.classList.toggle('field-error', !isValid);

                if (!isValid) {
                    valid = false;
                }
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

            this.clearDraft();

            return true;
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