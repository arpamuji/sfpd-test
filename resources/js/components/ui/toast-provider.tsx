import { Toaster } from "sonner";

interface Props {
    children?: React.ReactNode;
}

export function ToastProvider({ children }: Props) {
    return (
        <>
            <Toaster
                position="bottom-right"
                expand={false}
                richColors
                closeButton
                duration={4000}
                toastOptions={{
                    classNames: {
                        error: "bg-destructive text-destructive-foreground",
                        success: "bg-green-600 text-white",
                        warning: "bg-yellow-500 text-white",
                        info: "bg-blue-500 text-white",
                    },
                }}
            />
            {children}
        </>
    );
}
