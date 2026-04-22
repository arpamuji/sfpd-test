import React from "react";
import AppLayout from "@/components/layouts/AppLayout";
import StatusBadge from "@/components/shared/StatusBadge";
import { Link } from "@inertiajs/react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    IconFileArrowRight,
    IconClock,
    IconCheck,
    IconX,
    IconEye,
} from "@tabler/icons-react";

interface Submission {
    id: string;
    warehouse_name: string;
    status: string;
    budget_estimate: number;
    created_at: string;
}

interface Props {
    mySubmissions?: { data: Submission[] };
    pendingApprovals?: Submission[];
    stats?: {
        total: number;
        pending: number;
        approved: number;
        rejected: number;
    };
}

export default function Dashboard({
    mySubmissions,
    pendingApprovals,
    stats,
}: Props) {
    return (
        <AppLayout title="Dashboard">
            {/* Stats cards */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Total Submissions
                        </CardTitle>
                        <IconFileArrowRight className="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold">
                            {stats?.total ?? 0}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            All your submissions
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Pending Approval
                        </CardTitle>
                        <IconClock className="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold">
                            {stats?.pending ?? 0}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            Awaiting approval
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Approved
                        </CardTitle>
                        <IconCheck className="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-green-600">
                            {stats?.approved ?? 0}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            Successfully approved
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Rejected
                        </CardTitle>
                        <IconX className="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-red-600">
                            {stats?.rejected ?? 0}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            Needs revision
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Pending Approvals */}
            {pendingApprovals && pendingApprovals.length > 0 && (
                <Card className="mb-8">
                    <CardHeader>
                        <CardTitle>Pending Approvals</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Warehouse</TableHead>
                                    <TableHead>Budget</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pendingApprovals.map((submission) => (
                                    <TableRow
                                        key={submission.id}
                                        className="py-4"
                                    >
                                        <TableCell className="font-medium py-4">
                                            {submission.warehouse_name}
                                        </TableCell>
                                        <TableCell className="py-4">
                                            Rp{" "}
                                            {submission.budget_estimate.toLocaleString(
                                                "id-ID",
                                            )}
                                        </TableCell>
                                        <TableCell className="py-4">
                                            <StatusBadge
                                                status={submission.status}
                                            />
                                        </TableCell>
                                        <TableCell className="py-4">
                                            <Link
                                                href={route(
                                                    "submissions.show",
                                                    submission.id,
                                                )}
                                                className="inline-flex items-center justify-center w-8 h-8 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground transition-colors"
                                                title="Review submission"
                                            >
                                                <IconEye className="w-4 h-4" />
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            )}

            {/* My Submissions */}
            <Card>
                <CardHeader>
                    <CardTitle>My Submissions</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Warehouse</TableHead>
                                <TableHead>Budget</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {mySubmissions?.data &&
                            mySubmissions.data.length > 0 ? (
                                mySubmissions.data.map((submission) => (
                                    <TableRow key={submission.id}>
                                        <TableCell className="font-medium py-3">
                                            {submission.warehouse_name}
                                        </TableCell>
                                        <TableCell className="py-3">
                                            Rp{" "}
                                            {parseInt(
                                                submission.budget_estimate,
                                            ).toLocaleString("id-ID")}
                                        </TableCell>
                                        <TableCell className="py-3">
                                            <StatusBadge
                                                status={submission.status}
                                            />
                                        </TableCell>
                                        <TableCell className="py-3 text-muted-foreground">
                                            {new Date(
                                                submission.created_at,
                                            ).toLocaleDateString("id-ID", {
                                                day: "numeric",
                                                month: "short",
                                                year: "numeric",
                                            })}
                                        </TableCell>
                                        <TableCell className="py-3">
                                            <Link
                                                href={route(
                                                    "submissions.show",
                                                    submission.id,
                                                )}
                                                className="inline-flex items-center justify-center w-8 h-8 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground transition-colors"
                                                title="View submission"
                                            >
                                                <IconEye className="w-4 h-4" />
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="text-center text-muted-foreground py-8"
                                    >
                                        No submissions yet. Create your first
                                        submission to get started.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
