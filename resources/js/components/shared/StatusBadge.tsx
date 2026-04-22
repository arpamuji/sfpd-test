import React from "react";
import { cn } from "@/lib/utils";

interface Props {
    status: string;
    className?: string;
}

export default function StatusBadge({ status, className }: Props) {
    const config = {
        draft: {
            label: "Draft",
            variant: "bg-secondary text-secondary-foreground",
        },
        pending_spv: {
            label: "Pending SPV",
            variant:
                "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
        },
        pending_kepala: {
            label: "Pending Kepala",
            variant:
                "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
        },
        pending_manager_ops: {
            label: "Pending Manager",
            variant:
                "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
        },
        pending_direktur_ops: {
            label: "Pending Direktur Ops",
            variant:
                "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
        },
        pending_direktur_keuangan: {
            label: "Pending Direktur Keuangan",
            variant:
                "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
        },
        approved: {
            label: "Approved",
            variant:
                "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
        },
        rejected: {
            label: "Rejected",
            variant:
                "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
        },
    };

    const { label, variant } =
        config[status as keyof typeof config] || config.draft;

    return (
        <span
            className={cn(
                "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium",
                variant,
                className,
            )}
        >
            {label}
        </span>
    );
}
