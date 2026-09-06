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
});
