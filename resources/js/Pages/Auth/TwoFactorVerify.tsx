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
import { Button } from "@/components/ui/button";
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSeparator,
    InputOTPSlot,
} from "@/components/ui/input-otp";
import { IconShieldCheck, IconArrowLeft } from "@tabler/icons-react";
import { useEffect, useState } from "react";
import { useToast } from "@/hooks/useToast";

interface Props {
    throttleUntil?: number | null;
}

export default function TwoFactorVerify({ throttleUntil }: Props) {
    useToast();
    const [countdown, setCountdown] = useState(30);
    const [throttleCountdown, setThrottleCountdown] = useState<number | null>(
        null,
    );
    const { data, setData, post, processing, errors, reset } = useForm({
        code: "",
    });

    // Handle throttle countdown from backend
    useEffect(() => {
        if (throttleUntil) {
            const updateThrottleCountdown = () => {
                const now = Math.floor(Date.now() / 1000);
                const remaining = throttleUntil - now;
                if (remaining <= 0) {
                    setThrottleCountdown(null);
                } else {
                    setThrottleCountdown(remaining);
                }
            };
            updateThrottleCountdown();
            const timer = setInterval(updateThrottleCountdown, 1000);
            return () => clearInterval(timer);
        }
    }, [throttleUntil]);

    // Code expiry countdown (only when not throttled)
    useEffect(() => {
        if (throttleCountdown !== null) return;

        const timer = setInterval(() => {
            setCountdown((prev) => {
                if (prev <= 1) {
                    reset();
                    return 30;
                }
                return prev - 1;
            });
        }, 1000);

        return () => clearInterval(timer);
    }, [throttleCountdown]);

    // Focus first input on mount
    useEffect(() => {
        const firstInput = document.querySelector(
            '[data-slot="input-otp-slot-0"]',
        ) as HTMLElement;
        firstInput?.focus();
    }, []);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (data.code.length === 6 && throttleCountdown === null) {
            post(route("2fa.verify"));
        }
    }

    return (
        <>
            <Head title="2FA Verification" />
            <div className="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center space-y-2">
                        <div className="mx-auto w-12 h-12 rounded-lg bg-primary flex items-center justify-center">
                            <IconShieldCheck className="w-6 h-6 text-primary-foreground" />
                        </div>
                        <CardTitle className="text-2xl font-semibold">
                            Two-Factor Authentication
                        </CardTitle>
                        <CardDescription>
                            Enter the 6-digit code from your authenticator app
                        </CardDescription>
                    </CardHeader>
                    <form onSubmit={handleSubmit}>
                        <CardContent className="space-y-6">
                            <div className="space-y-3">
                                <Label className="text-center block">
                                    Authentication Code
                                </Label>
                                <InputOTP
                                    containerClassName="flex flex-row justify-center gap-x-4"
                                    maxLength={6}
                                    value={data.code}
                                    onChange={(value) => setData("code", value)}
                                    disabled={throttleCountdown !== null}
                                    className={
                                        errors.code
                                            ? "ring-destructive/50 ring-2"
                                            : ""
                                    }
                                >
                                    <InputOTPGroup>
                                        <InputOTPSlot index={0} />
                                        <InputOTPSlot index={1} />
                                        <InputOTPSlot index={2} />
                                    </InputOTPGroup>
                                    <InputOTPSeparator />
                                    <InputOTPGroup>
                                        <InputOTPSlot index={3} />
                                        <InputOTPSlot index={4} />
                                        <InputOTPSlot index={5} />
                                    </InputOTPGroup>
                                </InputOTP>
                                {errors.code && (
                                    <p className="text-sm text-destructive text-center">
                                        {errors.code}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                        <CardFooter className="flex flex-col gap-3 mt-4">
                            <div className="flex gap-2 w-full">
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="flex-1"
                                    onClick={() => router.post(route("logout"))}
                                >
                                    <IconArrowLeft className="w-4 h-4 mr-2" />
                                    Back to Login
                                </Button>
                                <Button
                                    type="submit"
                                    className="flex-1"
                                    disabled={
                                        processing ||
                                        data.code.length !== 6 ||
                                        throttleCountdown !== null
                                    }
                                >
                                    {throttleCountdown !== null
                                        ? `Blocked (${throttleCountdown}s)`
                                        : processing
                                          ? "Verifying..."
                                          : "Verify"}
                                </Button>
                            </div>
                            <p className="text-xs text-muted-foreground text-center">
                                {throttleCountdown !== null
                                    ? `Too many attempts. Try again in ${throttleCountdown} seconds.`
                                    : `Code expires in ${countdown} seconds`}
                            </p>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </>
    );
}
