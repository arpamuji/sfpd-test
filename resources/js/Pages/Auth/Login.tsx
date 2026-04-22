import { Head, useForm, router } from "@inertiajs/react";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { IconBuildingWarehouse } from "@tabler/icons-react";
import { useToast } from "@/hooks/useToast";
import { useEffect, useState } from "react";

interface Props {
    throttleUntil?: number | null;
}

export default function Login({ throttleUntil }: Props) {
    useToast();
    const [throttleCountdown, setThrottleCountdown] = useState<number | null>(
        null,
    );
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
    });

    // Handle throttle countdown from backend
    useEffect(() => {
        if (throttleUntil) {
            const updateThrottleCountdown = () => {
                const now = Math.floor(Date.now() / 1000);
                const remaining = throttleUntil - now;
                if (remaining <= 0) {
                    setThrottleCountdown(null);
                    reset();
                } else {
                    setThrottleCountdown(remaining);
                }
            };
            updateThrottleCountdown();
            const timer = setInterval(updateThrottleCountdown, 1000);
            return () => clearInterval(timer);
        }
    }, [throttleUntil]);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (throttleCountdown === null) {
            post(route("login"));
        }
    }

    return (
        <>
            <Head title="Login" />
            <div className="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center space-y-2">
                        <div className="mx-auto w-12 h-12 rounded-lg bg-primary flex items-center justify-center">
                            <IconBuildingWarehouse className="w-6 h-6 text-primary-foreground" />
                        </div>
                        <CardTitle className="text-2xl font-semibold">
                            Warehouse Approval System
                        </CardTitle>
                        <CardDescription>
                            Sign in to your account
                        </CardDescription>
                    </CardHeader>
                    <form onSubmit={handleSubmit}>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="you@example.com"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                    disabled={throttleCountdown !== null}
                                    className={cn(
                                        errors.email && "border-destructive",
                                    )}
                                />
                                {errors.email && (
                                    <p className="text-sm text-destructive">
                                        {errors.email}
                                    </p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password">Password</Label>
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    placeholder="•••••••••"
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    disabled={throttleCountdown !== null}
                                    className={cn(
                                        errors.password && "border-destructive",
                                    )}
                                />
                                {errors.password && (
                                    <p className="text-sm text-destructive">
                                        {errors.password}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                        <CardFooter className="flex flex-col gap-3 mt-8">
                            <Button
                                type="submit"
                                className="w-full"
                                disabled={
                                    processing || throttleCountdown !== null
                                }
                            >
                                {throttleCountdown !== null
                                    ? `Blocked (${throttleCountdown}s)`
                                    : processing
                                      ? "Signing in..."
                                      : "Sign in"}
                            </Button>
                            {throttleCountdown !== null && (
                                <p className="text-xs text-muted-foreground text-center">
                                    Too many attempts. Try again in{" "}
                                    {throttleCountdown} seconds.
                                </p>
                            )}
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </>
    );
}
