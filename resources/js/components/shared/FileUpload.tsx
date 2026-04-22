import React, { useRef, useState, DragEvent } from "react";
import { IconUpload, IconFile, IconX } from "@tabler/icons-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface Props {
    files: File[];
    onFilesChange: (files: File[]) => void;
    error?: string;
    className?: string;
    minPdfCount?: number;
    maxFileSizeMB?: number;
}

export default function FileUpload({
    files,
    onFilesChange,
    error,
    className,
    minPdfCount = 3,
    maxFileSizeMB = 5,
}: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [isDragging, setIsDragging] = useState(false);

    const validateFile = (file: File): string | null => {
        const validTypes = [
            "application/pdf",
            "image/jpeg",
            "image/png",
            "image/jpg",
        ];
        const maxSize = maxFileSizeMB * 1024 * 1024;

        if (!validTypes.includes(file.type)) {
            return `File "${file.name}" has invalid type. Only PDF and images (JPG, PNG) are allowed.`;
        }

        if (file.size > maxSize) {
            return `File "${file.name}" exceeds ${maxFileSizeMB}MB limit.`;
        }

        return null;
    };

    const handleFiles = (fileList: FileList | null) => {
        const newFiles = Array.from(fileList || []);
        const errors: string[] = [];
        const validFiles: File[] = [];

        newFiles.forEach((file) => {
            // Create a new File instance to ensure unique identity
            const uniqueFile = new File([file], file.name, {
                type: file.type,
                lastModified: Date.now() + Math.random() * 10000,
            });

            const validationError = validateFile(uniqueFile);
            if (validationError) {
                errors.push(validationError);
            } else {
                validFiles.push(uniqueFile);
            }
        });

        if (errors.length > 0) {
            console.error(errors);
        }

        const updatedFiles = [...files, ...validFiles];
        onFilesChange(updatedFiles);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        handleFiles(e.target.files);
        // Reset input value to allow selecting the same file again if needed
        e.target.value = "";
    };

    const handleDragEnter = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(true);
    };

    const handleDragLeave = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
    };

    const handleDragOver = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
    };

    const handleDrop = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
        handleFiles(e.dataTransfer.files);
    };

    const removeFile = (index: number) => {
        const updatedFiles = files.filter((_, i) => i !== index);
        onFilesChange(updatedFiles);
    };

    const pdfCount = files.filter((f) => f.type === "application/pdf").length;
    const hasMinimumPdfs = pdfCount >= minPdfCount;

    return (
        <div className={className}>
            <label className="block text-sm font-medium text-foreground mb-1">
                Documents <span className="text-destructive">*</span>
            </label>
            <p className="text-sm text-muted-foreground mb-3">
                Minimum {minPdfCount} PDF files required. Images (JPG, PNG)
                optional. Max {maxFileSizeMB}MB each.
            </p>

            {/* Dropzone */}
            <div
                onDragEnter={handleDragEnter}
                onDragLeave={handleDragLeave}
                onDragOver={handleDragOver}
                onDrop={handleDrop}
                onClick={() => inputRef.current?.click()}
                className={cn(
                    "flex flex-col items-center justify-center px-6 pt-8 pb-10 border-2 border-dashed rounded-lg cursor-pointer transition-colors bg-muted/20",
                    isDragging
                        ? "border-primary bg-primary/5"
                        : "border-border hover:border-primary/50",
                )}
            >
                <IconUpload className="w-10 h-10 text-muted-foreground mb-3" />
                <span className="text-sm font-medium text-foreground hover:text-primary transition-colors">
                    {isDragging
                        ? "Drop files here"
                        : "Click or drag files to upload"}
                </span>
                <p className="text-xs text-muted-foreground mt-1">
                    PDF, JPG, PNG up to {maxFileSizeMB}MB each
                </p>
            </div>
            <input
                ref={inputRef}
                type="file"
                multiple
                accept=".pdf,.jpg,.jpeg,.png"
                onChange={handleFileChange}
                className="hidden"
            />

            {/* Selected files list */}
            {files.length > 0 && (
                <div className="mt-4 space-y-2">
                    <div className="flex items-center justify-between">
                        <p className="text-sm font-medium">
                            Selected files ({files.length})
                        </p>
                        {!hasMinimumPdfs && (
                            <p className="text-xs text-amber-600">
                                {minPdfCount - pdfCount} more PDF(s) required
                            </p>
                        )}
                    </div>
                    <ul className="space-y-2">
                        {files.map((file, index) => (
                            <li
                                key={index}
                                className="flex items-center justify-between p-3 rounded-md bg-muted/50 border border-border"
                            >
                                <div className="flex items-center gap-3">
                                    <IconFile className="w-5 h-5 text-muted-foreground" />
                                    <div>
                                        <p className="text-sm font-medium text-foreground">
                                            {file.name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {(file.size / 1024 / 1024).toFixed(
                                                2,
                                            )}{" "}
                                            MB
                                        </p>
                                    </div>
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => removeFile(index)}
                                    className="h-8 w-8 p-0 hover:bg-destructive/10 hover:text-destructive"
                                >
                                    <IconX className="w-4 h-4" />
                                </Button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {/* Validation errors */}
            {error && <p className="mt-2 text-sm text-destructive">{error}</p>}
            {!hasMinimumPdfs && files.length > 0 && (
                <p className="mt-2 text-sm text-amber-600">
                    Please upload at least {minPdfCount} PDF files. Currently:{" "}
                    {pdfCount} PDF(s)
                </p>
            )}
        </div>
    );
}
