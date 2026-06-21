/* =========================================================
   resources/js/components/adminForm.js
   - adminForm   : autosave + AJAX submit + validation
   - toastCenter : queue-based global toasts
   - smartDropzone : drag/drop + reorder + validation
   ========================================================= */


/* ──────────────────────────────────────────────
   1. adminForm
   ────────────────────────────────────────────── */
export function adminForm(options = {}) {
    return {
        key:      options.key      || null,
        ajax:     options.ajax     ?? false,
        redirect: options.redirect || null,
        confirmRestore: options.confirmRestore ?? true,

        saving: false,
        progress: 0,
        dirty: false,
        _draftTimer: null,
        _initialSnapshot: '',

        init() {
            // Wait until Alpine renders, then snapshot initial form state
            this.$nextTick(() => {
                this._initialSnapshot = this.snapshot();
                this.restoreDraft();
                this.setupUnloadGuard();
            });
        },

        // ── DRAFT ──
        draftKey() {
            return this.key ? `admin_form_draft_${this.key}` : null;
        },

        snapshot() {
            const data = {};
            const fd = new FormData(this.$el);

            for (const [name, value] of fd.entries()) {
                if (!name || name === '_token' || name === '_method') continue;
                if (value instanceof File) continue;

                if (name.endsWith('[]')) {
                    if (!Array.isArray(data[name])) data[name] = [];
                    data[name].push(value);
                } else {
                    data[name] = value;
                }
            }
            return JSON.stringify(data);
        },

        saveDraft() {
            if (!this.key) return;

            // debounce
            clearTimeout(this._draftTimer);
            this._draftTimer = setTimeout(() => {
                const snap = this.snapshot();
                this.dirty = snap !== this._initialSnapshot;

                try {
                    localStorage.setItem(this.draftKey(), snap);
                } catch (_) {}
            }, 400);
        },

        restoreDraft() {
            if (!this.key) return;

            const raw = localStorage.getItem(this.draftKey());
            if (!raw) return;

            let data = {};
            try { data = JSON.parse(raw); } catch (_) { return; }
            if (!Object.keys(data).length) return;

            // Use toast with action instead of blocking confirm()
            if (this.confirmRestore) {
                this.toast('A saved draft was found. Click to restore.', 'info', {
                    actionLabel: 'Restore',
                    action: () => this.applyDraft(data),
                    dismissLabel: 'Discard',
                    onDismiss: () => this.clearDraft(),
                    duration: 8000,
                });
            } else {
                this.applyDraft(data);
            }
        },

        applyDraft(data) {
            [...this.$el.elements].forEach((field) => {
                if (!field.name || data[field.name] === undefined) return;
                const value = data[field.name];

                if (field.type === 'checkbox') {
                    field.checked = Array.isArray(value)
                        ? value.includes(field.value)
                        : Boolean(value);
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

            this.dirty = true;
            this.toast('Draft restored', 'success');
        },

        clearDraft() {
            if (!this.key) return;
            try { localStorage.removeItem(this.draftKey()); } catch (_) {}
            this.dirty = false;
        },

        // ── UNSAVED CHANGES GUARD ──
        setupUnloadGuard() {
            window.addEventListener('beforeunload', (e) => {
                if (!this.dirty || this.saving) return;
                e.preventDefault();
                e.returnValue = '';
            });
        },

        // ── VALIDATION ──
        validate() {
            let valid = true;
            let firstInvalid = null;

            [...this.$el.elements].forEach((field) => {
                if (!field.checkValidity || field.type === 'hidden') return;

                const isValid = field.checkValidity();
                field.classList.toggle('field-error', !isValid);

                if (!isValid) {
                    valid = false;
                    if (!firstInvalid) firstInvalid = field;
                }
            });

            if (!valid) {
                this.toast('Please fix the highlighted fields.', 'error');
                firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid?.focus({ preventScroll: true });
            }

            return valid;
        },

        // ── SUBMIT ──
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
            if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable) return;
                this.progress = Math.round((event.loaded / event.total) * 100);

                window.dispatchEvent(new CustomEvent('upload-progress', {
                    detail: { progress: this.progress }
                }));
            });

            xhr.onload = () => {
                this.saving = false;
                let response = {};
                try { response = JSON.parse(xhr.responseText || '{}'); } catch (_) {}

                if (xhr.status >= 200 && xhr.status < 300) {
                    this.progress = 100;
                    this.clearDraft();
                    this.dirty = false;

                    this.toast(response.message || 'Saved successfully.', 'success');

                    // Dispatch global event (other components can react)
                    window.dispatchEvent(new CustomEvent('form-saved', { detail: response }));

                    const redirect = response.redirect || this.redirect;
                    if (redirect) window.location.href = redirect;
                    return;
                }

                // Laravel validation errors (422)
                if (xhr.status === 422 && response.errors) {
                    this.applyServerErrors(response.errors);
                    this.toast('Please fix the errors below.', 'error');
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

        applyServerErrors(errors) {
            // Clear old errors
            this.$el.querySelectorAll('.field-error').forEach(el => {
                el.classList.remove('field-error');
            });

            let firstInvalid = null;

            Object.keys(errors).forEach(name => {
                const field = this.$el.querySelector(`[name="${name}"], [name="${name}[]"]`);
                if (!field) return;

                field.classList.add('field-error');
                if (!firstInvalid) firstInvalid = field;
            });

            firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid?.focus({ preventScroll: true });
        },

        // ── TOAST HELPER ──
        toast(message, type = 'success', options = {}) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message, type, ...options }
            }));
        },
    };
}


/* ──────────────────────────────────────────────
   2. toastCenter (with queue + types)
   ────────────────────────────────────────────── */
export function toastCenter() {
    return {
        toasts: [],
        _id: 0,

        init() {
            window.addEventListener('toast', (event) => this.push(event.detail));
        },

        push(detail = {}) {
            const id = ++this._id;
            const duration = detail.duration ?? 3500;

            const toast = {
                id,
                message: detail.message || 'Done',
                type:    detail.type    || 'success',  // success | error | info | warning
                actionLabel:  detail.actionLabel  || null,
                action:       detail.action       || null,
                dismissLabel: detail.dismissLabel || null,
                onDismiss:    detail.onDismiss    || null,
                show: true,
            };

            this.toasts.push(toast);

            if (duration > 0) {
                setTimeout(() => this.dismiss(id, true), duration);
            }
        },

        runAction(id) {
            const t = this.toasts.find(x => x.id === id);
            if (t?.action) t.action();
            this.dismiss(id);
        },

        dismiss(id, auto = false) {
            const idx = this.toasts.findIndex(x => x.id === id);
            if (idx === -1) return;

            const t = this.toasts[idx];
            if (auto && t.onDismiss) t.onDismiss();
            if (!auto && t.onDismiss) t.onDismiss();

            t.show = false;
            // small delay for transition
            setTimeout(() => this.toasts.splice(idx, 1), 200);
        },

        iconFor(type) {
            return {
                success: '✓',
                error:   '✗',
                info:    'ℹ',
                warning: '⚠',
            }[type] || '✓';
        },

        colorFor(type) {
            return {
                success: 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
                error:   'border-red-500/40 bg-red-500/10 text-red-300',
                info:    'border-indigo-500/40 bg-indigo-500/10 text-indigo-300',
                warning: 'border-amber-500/40 bg-amber-500/10 text-amber-300',
            }[type] || 'border-gray-700 bg-gray-800 text-gray-200';
        },
    };
}


/* ──────────────────────────────────────────────
   3. smartDropzone (with validation + types)
   ────────────────────────────────────────────── */
export function smartDropzone(config = {}) {
    return {
        // ── CONFIG ──
        name:     config.name     || 'file',
        multiple: !!config.multiple,
        accept:   config.accept   || '*',
        maxSize:  config.maxSize  || 5,        // MB
        existing: config.existing || null,

        // ── STATE ──
        drag: false,
        files: [],
        errors: [],
        dragIndex: null,
        progress: 0,
        _errorTimer: null,

        init() {
            // Preload existing image (edit forms)
            if (this.existing) {
                this.files.push({
                    id: 'existing',
                    file: null,
                    name: 'Current file',
                    size: '',
                    preview: this.existing,
                    progress: 100,
                    isExisting: true,
                });
            }

            // Listen for global upload progress
            window.addEventListener('upload-progress', (event) => {
                this.progress = event.detail.progress || 0;
                this.files = this.files.map(f =>
                    f.isExisting ? f : { ...f, progress: this.progress }
                );
            });
        },

        // ── INPUT HANDLERS ──
        handleInput(event) {
            this.setFiles(event.target.files);
        },

        handleDrop(event) {
            this.drag = false;
            this.setFiles(event.dataTransfer.files);
        },

        setFiles(fileList) {
            const incoming = Array.from(fileList || []);
            const accepted = [];

            this.errors = [];

            for (const file of incoming) {
                if (file.size > this.maxSize * 1024 * 1024) {
                    this.errors.push(`${file.name} is too large (max ${this.maxSize}MB).`);
                    continue;
                }

                if (this.accept !== '*' && !this.matchesAccept(file)) {
                    this.errors.push(`${file.name} is not a supported file type.`);
                    continue;
                }

                accepted.push(file);
            }

            if (!this.multiple) {
                this.revokePreviews();
                this.files = accepted.slice(0, 1).map((file, i) => this.makeFile(file, i));
            } else {
                this.files = [
                    ...this.files,
                    ...accepted.map((file, i) => this.makeFile(file, this.files.length + i)),
                ];
            }

            this.syncInput();
            this.queueErrorClear();
        },

        queueErrorClear() {
            clearTimeout(this._errorTimer);
            if (this.errors.length) {
                this._errorTimer = setTimeout(() => this.errors = [], 5000);
            }
        },

        matchesAccept(file) {
            return this.accept.split(',').map(t => t.trim()).some(t => {
                if (t === '*') return true;
                if (t.endsWith('/*')) return file.type.startsWith(t.replace('/*', ''));
                if (t.startsWith('.')) return file.name.toLowerCase().endsWith(t.toLowerCase());
                return file.type === t;
            });
        },

        makeFile(file, index) {
            return {
                id: (crypto?.randomUUID?.() ?? `${Date.now()}_${index}_${file.name}`),
                file,
                name: file.name,
                size: this.formatSize(file.size),
                preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
                progress: 0,
                isExisting: false,
            };
        },

        // ── REMOVE / CLEAR ──
        removeFile(index) {
            const item = this.files[index];
            if (item?.preview && item.preview.startsWith('blob:')) {
                URL.revokeObjectURL(item.preview);
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
            this.files.forEach(item => {
                if (item.preview && item.preview.startsWith('blob:')) {
                    URL.revokeObjectURL(item.preview);
                }
            });
        },

        // ── REORDER ──
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

        // ── SYNC TO HIDDEN <input> ──
        syncInput() {
            const input = this.$refs.input;
            if (!input) return;

            const dt = new DataTransfer();
            this.files.forEach(item => {
                if (item.file) dt.items.add(item.file);
            });
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        // ── HELPERS ──
        formatSize(bytes) {
            if (!bytes) return '0 KB';
            const kb = bytes / 1024;
            if (kb < 1024) return `${kb.toFixed(1)} KB`;
            return `${(kb / 1024).toFixed(1)} MB`;
        },

        destroy() {
            this.revokePreviews();
            clearTimeout(this._errorTimer);
        },
    };
}