// UI-only navigation. Alpine and the application lifecycle are supplied by Livewire.
document.addEventListener("alpine:init", () => {
    window.Alpine.data("mizanShell", () => ({
        sidebarOpen: false,
        userMenuOpen: false,
        query: "",
        selected: 0,
        destinations: [],

        get results() {
            const query = this.query.trim().toLocaleLowerCase();
            return this.destinations.filter((item) =>
                `${item.label} ${item.group}`.toLocaleLowerCase().includes(query),
            );
        },

        openCommands() {
            if (this.$refs.commands.open) return;
            const seen = new Set();
            this.destinations = [...this.$root.querySelectorAll("[data-command-source] a[href]")]
                .filter((link) => {
                    if (seen.has(link.href)) return false;
                    seen.add(link.href);
                    return true;
                })
                .map((link) => ({
                    href: link.href,
                    label: link.textContent.trim(),
                    group: link.closest("[data-nav-group]")?.dataset.navGroup || "",
                }));
            this.query = "";
            this.selected = 0;
            this.$refs.commands.showModal();
            this.$nextTick(() => this.$refs.commandSearch.focus());
        },

        moveSelection(step) {
            if (!this.results.length) return;
            this.selected = (this.selected + step + this.results.length) % this.results.length;
            this.$nextTick(() =>
                this.$refs.commandResults
                    .querySelectorAll('[role="option"]')
                    [this.selected]?.scrollIntoView({ block: "nearest" }),
            );
        },

        openSelected() {
            const destination = this.results[this.selected];
            if (destination) window.location.assign(destination.href);
        },
    }));

    // Searchable dropdown. Wraps a real <select> (kept in the DOM as the source of
    // truth for wire:model) with a filterable listbox, so long lists such as the
    // customer or account pickers can be typed into instead of scrolled.
    window.Alpine.data("mizanPicker", (searchThreshold = 8) => ({
        open: false,
        query: "",
        highlighted: -1,
        options: [],
        value: "",
        observer: null,

        init() {
            this.sync();
            this.observer = new MutationObserver(() => this.sync());
            this.observer.observe(this.$refs.native, { childList: true, subtree: true, attributes: true });
            this.$refs.native.addEventListener("change", () => this.sync());
            this.$watch("query", () => {
                this.highlighted = this.filtered.length ? 0 : -1;
            });
        },

        destroy() {
            this.observer?.disconnect();
        },

        sync() {
            this.options = [...this.$refs.native.options].map((option) => ({
                value: option.value,
                text: option.textContent.trim(),
                disabled: option.disabled,
            }));
            this.value = this.$refs.native.value;
        },

        // Fold Arabic orthography variants so "احمد" also finds "أحمد".
        normalize(text) {
            return text
                .toLocaleLowerCase()
                .replace(/[ً-ْـ]/g, "")
                .replace(/[أإآٱ]/g, "ا")
                .replace(/ى/g, "ي")
                .replace(/ة/g, "ه")
                .replace(/\s+/g, " ")
                .trim();
        },

        get placeholderOption() {
            return this.options.find((option) => option.value === "");
        },

        get selectedLabel() {
            const match = this.options.find((option) => option.value === this.value);
            return match && match.value !== "" ? match.text : "";
        },

        get showSearch() {
            return this.options.length > searchThreshold;
        },

        get filtered() {
            const needle = this.normalize(this.query);
            return this.options.filter((option) => {
                if (option.value === "") return needle === "";
                return needle === "" || this.normalize(option.text).includes(needle);
            });
        },

        toggle() {
            this.open ? this.close() : this.show();
        },

        show() {
            if (this.$refs.native.disabled) return;
            this.sync();
            this.open = true;
            this.query = "";
            this.highlighted = this.filtered.findIndex((option) => option.value === this.value);
            this.$nextTick(() => {
                if (this.showSearch) this.$refs.search?.focus();
                this.scrollToHighlighted();
            });
        },

        close() {
            this.open = false;
            this.query = "";
            this.highlighted = -1;
        },

        move(step) {
            const count = this.filtered.length;
            if (!count) return;
            this.highlighted = (this.highlighted + step + count) % count;
            this.$nextTick(() => this.scrollToHighlighted());
        },

        scrollToHighlighted() {
            this.$refs.list
                ?.querySelectorAll('[role="option"]')
                [this.highlighted]?.scrollIntoView({ block: "nearest" });
        },

        chooseHighlighted() {
            const option = this.filtered[this.highlighted];
            if (option) this.choose(option.value);
        },

        // Write through the native select so wire:model (and plain form posts) see it.
        choose(value) {
            const native = this.$refs.native;
            if (native.value !== value) {
                native.value = value;
                native.dispatchEvent(new Event("input", { bubbles: true }));
                native.dispatchEvent(new Event("change", { bubbles: true }));
            }
            this.value = value;
            this.close();
            this.$refs.trigger?.focus();
        },
    }));
});
