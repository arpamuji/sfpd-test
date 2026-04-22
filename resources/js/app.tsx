import "./bootstrap";
import React from "react";
import ReactDOM from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { route } from "ziggy-js";
import { ToastProvider } from "@/components/ui/toast-provider";

window.route = route;

createInertiaApp({
    title: (title) =>
        title ? `${title} - Warehouse Approval` : "Warehouse Approval",
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.tsx", {
            eager: true,
        }) as Record<string, { default: React.ComponentType }>;
        return pages[`./Pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        ReactDOM.createRoot(el).render(
            <ToastProvider>
                <App {...props} />
            </ToastProvider>,
        );
    },
});
