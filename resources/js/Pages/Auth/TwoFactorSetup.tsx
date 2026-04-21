import { useState, FormEventHandler } from "react";
import { Head, useForm } from "@inertiajs/react";

interface Props {
    qrCodeSvg: string;
}

export default function TwoFactorSetup({ qrCodeSvg }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        code: "",
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route("2fa.enable"), {
            onFinish: () => reset("code"),
        });
    };

    return (
        <>
            <Head title="Setup 2FA" />

            <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
                <div className="max-w-md w-full bg-white shadow-md rounded-lg p-8">
                    <h1 className="text-2xl font-bold text-center mb-6">
                        Setup Two-Factor Authentication
                    </h1>

                    <div className="mb-6 text-center">
                        <p className="text-gray-600 mb-4">
                            Scan this QR code with your authenticator app
                            (Google Authenticator, Authy, etc.)
                        </p>
                        <div
                            className="mx-auto"
                            dangerouslySetInnerHTML={{ __html: qrCodeSvg }}
                        />
                    </div>

                    <form onSubmit={submit}>
                        <div className="mb-4">
                            <label
                                htmlFor="code"
                                className="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Authentication Code
                            </label>
                            <input
                                type="text"
                                id="code"
                                value={data.code}
                                onChange={(e) =>
                                    setData("code", e.target.value)
                                }
                                className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter 6-digit code"
                                maxLength={6}
                            />
                            {errors.code && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.code}
                                </p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {processing ? "Enabling..." : "Enable 2FA"}
                        </button>
                    </form>

                    <div className="mt-6 text-center">
                        <a
                            href={route("logout")}
                            method="post"
                            as="button"
                            className="text-sm text-gray-600 hover:text-gray-900"
                        >
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}
