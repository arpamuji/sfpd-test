import React, { useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import {
    IconBuildingWarehouse,
    IconDashboard,
    IconFileText,
    IconPlus,
    IconLogout,
    IconMenu2,
    IconUser,
} from "@tabler/icons-react";
import { useToast } from "@/hooks/useToast";

interface User {
    id: string;
    name: string;
    email: string;
    role?: {
        id: string;
        name: string;
    } | null;
}

interface PageProps {
    auth: {
        user: User | null;
    };
}

interface Props {
    title: string;
    children: React.ReactNode;
}

export default function AppLayout({ title, children }: Props) {
    useToast();
    const { post } = useForm({});
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { auth } = usePage<PageProps>().props;
    const user = auth?.user;

    const handleLogout = () => {
        post(route("logout"));
    };

    const navigation = [
        { name: "Dashboard", href: route("dashboard"), icon: IconDashboard },
        {
            name: "Submissions",
            href: route("submissions.index"),
            icon: IconFileText,
        },
        {
            name: "New Submission",
            href: route("submissions.create"),
            icon: IconPlus,
        },
    ];

    return (
        <>
            <Head title={title} />
            <div className="min-h-screen bg-background">
                {/* Mobile sidebar backdrop */}
                {sidebarOpen && (
                    <div
                        className="fixed inset-0 z-40 bg-black/50 lg:hidden"
                        onClick={() => setSidebarOpen(false)}
                    />
                )}

                {/* Sidebar */}
                <aside
                    className={cn(
                        "fixed inset-y-0 left-0 z-50 w-64 bg-sidebar border-r border-sidebar-border transform transition-transform duration-200 ease-in-out lg:translate-x-0",
                        sidebarOpen ? "translate-x-0" : "-translate-x-full",
                    )}
                >
                    {/* Sidebar header */}
                    <div className="flex items-center gap-3 px-4 h-16 border-b border-sidebar-border">
                        <div className="w-8 h-8 rounded-lg bg-sidebar-primary flex items-center justify-center">
                            <IconBuildingWarehouse className="w-5 h-5 text-sidebar-primary-foreground" />
                        </div>
                        <span className="font-semibold text-sidebar-foreground">
                            Warehouse Approval
                        </span>
                    </div>

                    {/* Navigation */}
                    <nav className="p-4 space-y-1">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors"
                                onClick={() => setSidebarOpen(false)}
                            >
                                <item.icon className="w-5 h-5" />
                                {item.name}
                            </Link>
                        ))}
                    </nav>

                    {/* Sidebar footer */}
                    <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-sidebar-border space-y-3">
                        {user && (
                            <div className="flex items-center gap-3 px-3 py-2 rounded-md bg-sidebar-accent/50">
                                <div className="w-8 h-8 rounded-full bg-sidebar-primary flex items-center justify-center flex-shrink-0">
                                    <IconUser className="w-4 h-4 text-sidebar-primary-foreground" />
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium text-sidebar-foreground truncate">
                                        {user.name}
                                    </p>
                                    <p className="text-xs text-sidebar-foreground/70 truncate">
                                        {user.role?.name || "User"}
                                    </p>
                                </div>
                            </div>
                        )}
                        <Button
                            variant="ghost"
                            onClick={handleLogout}
                            className="w-full justify-start gap-3 text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        >
                            <IconLogout className="w-5 h-5" />
                            Logout
                        </Button>
                    </div>
                </aside>

                {/* Main content */}
                <div className="lg:pl-64">
                    {/* Top bar */}
                    <header className="sticky top-0 z-30 flex items-center gap-4 h-16 bg-background border-b px-4 lg:px-8">
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="lg:hidden p-2 -ml-2 text-muted-foreground hover:text-foreground"
                        >
                            <IconMenu2 className="w-6 h-6" />
                        </button>
                        <h1 className="text-lg font-semibold">{title}</h1>
                    </header>

                    {/* Page content */}
                    <main className="p-4 lg:p-8">{children}</main>
                </div>
            </div>
        </>
    );
}
