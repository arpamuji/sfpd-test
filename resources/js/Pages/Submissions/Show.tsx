import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AppLayout from "@/components/layouts/AppLayout";
import StatusBadge from "@/components/shared/StatusBadge";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import {
    IconArrowLeft,
    IconCheck,
    IconX,
    IconPaperclip,
} from "@tabler/icons-react";
import { cn } from "@/lib/utils";

interface ApprovalLog {
    id: string;
    approver_name: string;
    approver_role: string;
    action: "approved" | "rejected";
    remarks?: string;
    created_at: string;
}

interface Submission {
    id: string;
    warehouse_name: string;
    warehouse_address: string;
    latitude: number;
    longitude: number;
    budget_estimate: number;
    description?: string;
    status: string;
    created_at: string;
    files?: { id: string; name: string; url: string }[];
    approvalLogs: ApprovalLog[];
    can_approve: boolean;
    can_reject: boolean;
}

interface Props {
    submission: Submission;
}

export default function Show({ submission }: Props) {
    const [action, setAction] = useState<"approve" | "reject" | null>(null);
    const { post, processing, errors, setData, data } = useForm({
        notes: "",
        action: "",
    });

    const handleApprove = () => {
        setAction("approve");
        setData("notes", "");
        setData("action", "approve");
    };

    const handleReject = () => {
        setAction("reject");
        setData("notes", "");
        setData("action", "reject");
    };

    const handleSubmit = () => {
        post(
            action === "approve"
                ? route("approvals.approve", submission.id)
                : route("approvals.reject", submission.id),
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const cancelAction = () => {
        setAction(null);
        setData("notes", "");
    };

    return (
        <AppLayout title={submission.warehouse_name}>
            <Head title={submission.warehouse_name} />

            <div className="mb-6">
                <Link
                    href={route("submissions.index")}
                    className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-2"
                >
                    <IconArrowLeft className="w-4 h-4" />
                    Back to Submissions
                </Link>
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-bold">
                            {submission.warehouse_name}
                        </h2>
                        <p className="text-muted-foreground">
                            Submission details and approval history
                        </p>
                    </div>
                    <StatusBadge
                        status={submission.status}
                        className="text-sm px-3 py-1"
                    />
                </div>
            </div>

            <div className="space-y-6">
                {/* Submission Details */}
                <Card>
                    <CardHeader>
                        <CardTitle>Submission Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableBody>
                                <TableRow>
                                    <TableCell className="font-medium w-48">
                                        Warehouse Name
                                    </TableCell>
                                    <TableCell>
                                        {submission.warehouse_name}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell className="font-medium">
                                        Address
                                    </TableCell>
                                    <TableCell>
                                        {submission.warehouse_address}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell className="font-medium">
                                        Coordinates
                                    </TableCell>
                                    <TableCell>
                                        {submission.latitude},{" "}
                                        {submission.longitude}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell className="font-medium">
                                        Budget Estimate
                                    </TableCell>
                                    <TableCell>
                                        Rp{" "}
                                        {submission.budget_estimate.toLocaleString(
                                            "id-ID",
                                        )}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell className="font-medium">
                                        Description
                                    </TableCell>
                                    <TableCell>
                                        {submission.description || "-"}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableCell className="font-medium">
                                        Created
                                    </TableCell>
                                    <TableCell>
                                        {new Date(
                                            submission.created_at,
                                        ).toLocaleDateString("id-ID", {
                                            year: "numeric",
                                            month: "long",
                                            day: "numeric",
                                        })}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Documents */}
                {submission.files && submission.files.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <IconPaperclip className="w-5 h-5" />
                                Documents
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="space-y-2">
                                {submission.files.map((file) => (
                                    <li key={file.id}>
                                        <a
                                            href={file.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-primary hover:underline flex items-center gap-2"
                                        >
                                            <IconPaperclip className="w-4 h-4" />
                                            {file.name}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>
                )}

                {/* Approval Actions */}
                {(submission.can_approve || submission.can_reject) && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Approval Actions</CardTitle>
                            <CardDescription>
                                Review and take action on this submission
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {action === null ? (
                                <div className="flex items-center gap-4">
                                    {submission.can_approve && (
                                        <Button
                                            onClick={handleApprove}
                                            disabled={processing}
                                            className="gap-2 bg-green-600 hover:bg-green-700"
                                        >
                                            <IconCheck className="w-4 h-4" />
                                            Approve
                                        </Button>
                                    )}
                                    {submission.can_reject && (
                                        <Button
                                            onClick={handleReject}
                                            disabled={processing}
                                            variant="destructive"
                                            className="gap-2"
                                        >
                                            <IconX className="w-4 h-4" />
                                            Reject
                                        </Button>
                                    )}
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="notes">
                                            {action === "approve"
                                                ? "Approval Notes"
                                                : "Rejection Reason"}
                                            {action === "reject" && (
                                                <span className="text-destructive">
                                                    *
                                                </span>
                                            )}
                                        </Label>
                                        <Textarea
                                            id="notes"
                                            placeholder={
                                                action === "approve"
                                                    ? "Add any comments or notes (optional)"
                                                    : "Please provide a reason for rejection (required)"
                                            }
                                            value={data.notes}
                                            onChange={(e) =>
                                                setData("notes", e.target.value)
                                            }
                                            rows={4}
                                            className={cn(
                                                errors.notes &&
                                                    "border-destructive",
                                            )}
                                        />
                                        {errors.notes && (
                                            <p className="text-sm text-destructive">
                                                {errors.notes}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Button
                                            onClick={handleSubmit}
                                            disabled={
                                                processing ||
                                                (action === "reject" &&
                                                    !data.notes.trim())
                                            }
                                            className={
                                                action === "approve"
                                                    ? "bg-green-600 hover:bg-green-700"
                                                    : "bg-destructive hover:bg-destructive/90"
                                            }
                                        >
                                            {processing
                                                ? "Submitting..."
                                                : action === "approve"
                                                  ? "Confirm Approval"
                                                  : "Confirm Rejection"}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={cancelAction}
                                            disabled={processing}
                                        >
                                            Cancel
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Approval History */}
                {submission.approvalLogs &&
                    submission.approvalLogs.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Approval History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Approver</TableHead>
                                            <TableHead>Role</TableHead>
                                            <TableHead>Action</TableHead>
                                            <TableHead>Remarks</TableHead>
                                            <TableHead>Date</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {submission.approvalLogs.map((log) => (
                                            <TableRow key={log.id}>
                                                <TableCell className="font-medium">
                                                    {log.approver_name}
                                                </TableCell>
                                                <TableCell>
                                                    {log.approver_role}
                                                </TableCell>
                                                <TableCell>
                                                    <span
                                                        className={cn(
                                                            "inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium",
                                                            log.action ===
                                                                "approved"
                                                                ? "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                                                                : "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
                                                        )}
                                                    >
                                                        {log.action ===
                                                        "approved" ? (
                                                            <IconCheck className="w-3 h-3" />
                                                        ) : (
                                                            <IconX className="w-3 h-3" />
                                                        )}
                                                        {log.action ===
                                                        "approved"
                                                            ? "Approved"
                                                            : "Rejected"}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    {log.remarks || "-"}
                                                </TableCell>
                                                <TableCell className="text-muted-foreground">
                                                    {new Date(
                                                        log.created_at,
                                                    ).toLocaleDateString(
                                                        "id-ID",
                                                        {
                                                            year: "numeric",
                                                            month: "long",
                                                            day: "numeric",
                                                        },
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    )}
            </div>
        </AppLayout>
    );
}
