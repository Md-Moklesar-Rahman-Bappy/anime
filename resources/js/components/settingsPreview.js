export function settingsPreview(config = {}) {
    return {
        siteName: config.siteName || '',
        siteDescription: config.siteDescription || '',
        footerText: config.footerText || '',

        logoPreview: config.logoPreview || null,
        faviconPreview: config.faviconPreview || null,

        handleLogo(event) {
            const file = event.target.files?.[0];

            if (!file) return;

            if (this.logoPreview && this.logoPreview.startsWith('blob:')) {
                URL.revokeObjectURL(this.logoPreview);
            }

            this.logoPreview = URL.createObjectURL(file);
        },

        handleFavicon(event) {
            const file = event.target.files?.[0];

            if (!file) return;

            if (this.faviconPreview && this.faviconPreview.startsWith('blob:')) {
                URL.revokeObjectURL(this.faviconPreview);
            }

            this.faviconPreview = URL.createObjectURL(file);
        },
    };
}