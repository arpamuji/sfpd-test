"use client";

import { useEffect } from "react";
import { toast } from "sonner";
import { usePage, router } from "@inertiajs/react";

export function useToast() {
    const { props } = usePage();

    useEffect(() => {
        const flash = props.flash as Record<string, string> | undefined;
        const errors = props.errors as Record<string, string> | undefined;

        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
        if (flash?.warning) toast.warning(flash.warning);
        if (flash?.info) toast.info(flash.info);

        if (errors) {
            Object.values(errors).forEach((message) => toast.error(message));
        }
    }, [props]);

    return { toast };
}
