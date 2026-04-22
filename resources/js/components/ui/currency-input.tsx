import React from "react";
import { cn } from "@/lib/utils";

interface Props {
    id?: string;
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    disabled?: boolean;
    className?: string;
    prefix?: string;
    maxLength?: number;
}

/**
 * Formats a raw numeric string with Indonesian thousand separator (dots)
 * "5000000000" -> "5.000.000.000"
 */
export function formatCurrency(value: string): string {
    const numeric = value.replace(/[^0-9]/g, "");
    if (!numeric) return "";
    return parseInt(numeric).toLocaleString("id-ID");
}

/**
 * Parses formatted currency string to raw numeric value
 * "5.000.000.000" -> "5000000000"
 */
export function parseCurrency(formatted: string): string {
    return formatted.replace(/[^0-9]/g, "");
}

/**
 * Currency input component with Indonesian formatting (dots for thousands)
 * Stores raw numeric value but displays formatted value
 */
export default function CurrencyInput({
    id,
    value,
    onChange,
    placeholder,
    disabled,
    className,
    prefix = "Rp ",
    maxLength = 17, // 999.999.999.999.999 (999 trillion)
}: Props) {
    const formattedValue = formatCurrency(value);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const rawValue = parseCurrency(e.target.value);
        onChange(rawValue);
    };

    return (
        <div className="relative">
            {prefix && (
                <div className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm pointer-events-none">
                    {prefix}
                </div>
            )}
            <input
                id={id}
                type="text"
                inputMode="numeric"
                maxLength={maxLength}
                value={formattedValue}
                onChange={handleChange}
                placeholder={placeholder}
                disabled={disabled}
                className={cn(
                    "flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50",
                    prefix && "pl-10",
                    className,
                )}
            />
        </div>
    );
}
