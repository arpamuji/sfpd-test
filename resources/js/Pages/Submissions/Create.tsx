import React, { useState } from "react";
import AppLayout from "@/components/layouts/AppLayout";
import { useForm, Head, Link } from "@inertiajs/react";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import CurrencyInput from "@/components/ui/currency-input";
import FileUpload from "@/components/shared/FileUpload";
import LocationMap from "@/components/maps/LocationMap";
import { IconArrowLeft } from "@tabler/icons-react";

export default function Create() {
    const [latitude, setLatitude] = useState<number>(-6.2088);
    const [longitude, setLongitude] = useState<number>(106.8456);
    const [fileError, setFileError] = useState<string>("");

    const { data, setData, post, processing, errors } = useForm({
        warehouse_name: "",
        warehouse_address: "",
        latitude: String(latitude),
        longitude: String(longitude),
        budget_estimate: "",
        description: "",
        files: [] as File[],
    });

    const handleBudgetChange = (rawValue: string) => {
        setData("budget_estimate", rawValue);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        console.log("Submitting - files count:", data.files.length);

        // Validate files
        const pdfCount = data.files.filter(
            (f) => f.type === "application/pdf",
        ).length;
        if (data.files.length === 0) {
            setFileError(
                "Documents are required. Please upload at least 3 PDF files.",
            );
            return;
        }
        if (pdfCount < 3) {
            setFileError(
                `Minimum 3 PDF files required. Currently: ${pdfCount} PDF(s)`,
            );
            return;
        }
        setFileError("");

        console.log("Appending files to Inertia form:", data.files.length);
        data.files.forEach((file, idx) => {
            console.log(`  File ${idx}:`, file.name, file.type);
        });

        post(route("submissions.store"), {
            preserveState: true,
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                console.log("Submission successful!");
            },
            onError: (errors) => {
                console.log("Submission errors:", errors);
                console.log("Error keys:", Object.keys(errors));
                console.log("After error - files count:", data.files.length);
            },
        });
    };

    const handleLocationChange = (lat: number, lng: number) => {
        setLatitude(lat);
        setLongitude(lng);
        setData("latitude", String(lat));
        setData("longitude", String(lng));
    };

    return (
        <AppLayout title="New Submission">
            <Head title="New Submission" />

            <div className="mb-6">
                <Link
                    href={route("submissions.index")}
                    className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-2"
                >
                    <IconArrowLeft className="w-4 h-4" />
                    Back to Submissions
                </Link>
                <h2 className="text-2xl font-bold">New Submission</h2>
                <p className="text-muted-foreground">
                    Create a new warehouse construction submission
                </p>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Enter the warehouse details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="warehouse_name">
                                    Warehouse Name
                                </Label>
                                <Input
                                    id="warehouse_name"
                                    value={data.warehouse_name}
                                    onChange={(e) =>
                                        setData(
                                            "warehouse_name",
                                            e.target.value,
                                        )
                                    }
                                    placeholder="e.g., Jakarta Central Warehouse"
                                />
                                {errors.warehouse_name && (
                                    <p className="text-sm text-destructive">
                                        {errors.warehouse_name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="warehouse_address">
                                    Address
                                </Label>
                                <Textarea
                                    id="warehouse_address"
                                    value={data.warehouse_address}
                                    onChange={(e) =>
                                        setData(
                                            "warehouse_address",
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Enter the complete warehouse address"
                                    rows={3}
                                />
                                {errors.warehouse_address && (
                                    <p className="text-sm text-destructive">
                                        {errors.warehouse_address}
                                    </p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="latitude">Latitude</Label>
                                    <Input
                                        id="latitude"
                                        type="number"
                                        step="0.000001"
                                        value={data.latitude}
                                        onChange={(e) =>
                                            setData("latitude", e.target.value)
                                        }
                                    />
                                    {errors.latitude && (
                                        <p className="text-sm text-destructive">
                                            {errors.latitude}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="longitude">Longitude</Label>
                                    <Input
                                        id="longitude"
                                        type="number"
                                        step="0.000001"
                                        value={data.longitude}
                                        onChange={(e) =>
                                            setData("longitude", e.target.value)
                                        }
                                    />
                                    {errors.longitude && (
                                        <p className="text-sm text-destructive">
                                            {errors.longitude}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <LocationMap
                                latitude={latitude}
                                longitude={longitude}
                                onLocationChange={handleLocationChange}
                            />
                        </CardContent>
                    </Card>

                    {/* Budget & Description */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Budget & Description</CardTitle>
                            <CardDescription>
                                Provide budget estimate and project details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="budget_estimate">
                                    Budget Estimate (Rp)
                                </Label>
                                <CurrencyInput
                                    id="budget_estimate"
                                    value={data.budget_estimate}
                                    onChange={handleBudgetChange}
                                    placeholder="5.000.000.000"
                                />
                                {errors.budget_estimate && (
                                    <p className="text-sm text-destructive">
                                        {errors.budget_estimate}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) =>
                                        setData("description", e.target.value)
                                    }
                                    placeholder="Describe the construction project..."
                                    rows={4}
                                />
                                {errors.description && (
                                    <p className="text-sm text-destructive">
                                        {errors.description}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Documents */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Documents</CardTitle>
                            <CardDescription>
                                Upload required documentation
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <FileUpload
                                files={data.files}
                                onFilesChange={(newFiles) => {
                                    setData("files", newFiles, {
                                        shouldCompile: false,
                                    });
                                    setFileError("");
                                }}
                                error={fileError || errors.files}
                            />
                        </CardContent>
                    </Card>

                    {/* Submit */}
                    <div className="flex items-center gap-4">
                        <Button
                            type="submit"
                            disabled={processing}
                            className="w-full sm:w-auto"
                        >
                            {processing ? "Creating..." : "Create Submission"}
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            onClick={() =>
                                (window.location.href =
                                    route("submissions.index"))
                            }
                        >
                            Cancel
                        </Button>
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}
