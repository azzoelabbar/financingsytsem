import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import vm from "node:vm";

const source = readFileSync(new URL("../../resources/js/app.js", import.meta.url), "utf8");

function shell(links = []) {
    let factory;
    let destination;
    const context = {
        document: { addEventListener: (_, callback) => callback() },
        window: {
            Alpine: { data: (_, value) => (factory = value) },
            location: { assign: (href) => (destination = href) },
        },
    };
    vm.runInNewContext(source, context);
    const state = factory();
    const scrolled = [];
    state.$nextTick = (callback) => callback();
    state.$root = { querySelectorAll: () => links };
    state.$refs = {
        commands: {
            open: false,
            showModal() {
                this.open = true;
            },
        },
        commandSearch: { focus() {} },
        commandResults: {
            querySelectorAll: () =>
                state.results.map((_, index) => ({
                    scrollIntoView: () => scrolled.push(index),
                })),
        },
    };
    return { state, scrolled, destination: () => destination };
}

const link = (href, label, group) => ({
    href,
    textContent: label,
    closest: () => ({ dataset: { navGroup: group } }),
});

test("navigation deduplicates routes and searches Arabic labels or English groups", () => {
    const { state } = shell([
        link("/ar/invoices", "فواتير المبيعات", "Revenue"),
        link("/ar/invoices", "Duplicate", "Revenue"),
        link("/ap/invoices", "Purchase invoices", "Payables"),
    ]);
    state.openCommands();
    assert.equal(state.results.length, 2);
    state.query = "المبيعات";
    assert.equal(state.results[0].href, "/ar/invoices");
    state.query = " PAYABLES ";
    assert.equal(state.results[0].href, "/ap/invoices");
});

test("keyboard selection wraps and scrolls the actual option, not the template", () => {
    const { state, scrolled, destination } = shell([link("/a", "A", ""), link("/b", "B", "")]);
    state.openCommands();
    state.moveSelection(-1);
    assert.equal(state.selected, 1);
    assert.deepEqual(scrolled, [1]);
    state.openSelected();
    assert.equal(destination(), "/b");
    state.moveSelection(1);
    assert.equal(state.selected, 0);
});

test("empty search has no destination and repeated shortcut preserves input", () => {
    const { state, destination } = shell([link("/a", "A", "")]);
    state.openCommands();
    state.query = "unmatched";
    state.moveSelection(1);
    state.openSelected();
    state.openCommands();
    assert.equal(destination(), undefined);
    assert.equal(state.query, "unmatched");
});
