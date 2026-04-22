import React from "react";
import AppLayout from "@/components/layouts/AppLayout";
import StatusBadge from "@/components/shared/StatusBadge";
import { Link, router } from "@inertiajs/react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import {
    IconPlus,
    IconChevronLeft,
    IconChevronRight,
    IconEye,
} from "@tabler/icons-react";

interface Submission {
    id: string;
    warehouse_name: string;
    status: string;
    budget_estimate: number;
    created_at: string;
}

interface SubmissionsData {
    data: Submission[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    mySubmissions: SubmissionsData;
    pendingApprovals?: Submission[];
}

export default function Index({ mySubmissions, pendingApprovals }: Props) {
    const handlePageChange = (page: number) => {
        router.get(
            route("submissions.index"),
            { page },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <AppLayout title="Submissions">
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h2 className="text-2xl font-bold">Submissions</h2>
                    <p className="text-muted-foreground">
                        View and manage your warehouse construction submissions
                    </p>
                </div>
                <Button asChild>
                    <Link href={route("submissions.create")} className="gap-2">
                        <IconPlus className="w-4 h-4" />
                        New Submission
                    </Link>
                </Button>
            </div>

            {/* Pending Approvals */}
            {pendingApprovals && pendingApprovals.length > 0 && (
                <Card className="mb-6 border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/20">
                    <CardHeader>
                        <CardTitle className="text-amber-900 dark:text-amber-100">
                            Needs Your Approval
                        </CardTitle>
                        <p className="text-sm text-amber-700 dark:text-amber-300">
                            {pendingApprovals.length} submission(s) awaiting
                            your review
                        </p>
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
                                {pendingApprovals.map((submission) => (
                                    <TableRow
                                        key={submission.id}
                                        className="py-3"
                                    >
                                        <TableCell className="font-medium py-3">
                                            {submission.warehouse_name}
                                        </TableCell>
                                        <TableCell className="py-3">
                                            Rp{" "}
                                            {submission.budget_estimate.toLocaleString(
                                                "id-ID",
                                            )}
                                        </TableCell>
                                        <TableCell className="py-3">
                                            <StatusBadge
                                                status={submission.status}
                                            />
                                        </TableCell>
                                        <TableCell className="text-muted-foreground py-3">
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
                                                className="inline-flex items-center justify-center w-8 h-8 rounded-md hover:bg-amber-200 dark:hover:bg-amber-800 text-amber-700 dark:text-amber-300 transition-colors"
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
                            {mySubmissions.data.length > 0 ? (
                                mySubmissions.data.map((submission) => (
                                    <TableRow
                                        key={submission.id}
                                        className="py-3"
                                    >
                                        <TableCell className="font-medium py-3">
                                            {submission.warehouse_name}
                                        </TableCell>
                                        <TableCell className="py-3">
                                            Rp{" "}
                                            {submission.budget_estimate.toLocaleString(
                                                "id-ID",
                                            )}
                                        </TableCell>
                                        <TableCell className="py-3">
                                            <StatusBadge
                                                status={submission.status}
                                            />
                                        </TableCell>
                                        <TableCell className="text-muted-foreground py-3">
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
                                        No submissions found. Create your first
                                        submission to get started.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    {/* Pagination */}
                    {mySubmissions.last_page > 1 && (
                        <div className="flex items-center justify-between mt-4">
                            <p className="text-sm text-muted-foreground">
                                Showing {mySubmissions.from} to{" "}
                                {mySubmissions.to} of {mySubmissions.total}{" "}
                                results
                            </p>
                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        handlePageChange(
                                            mySubmissions.current_page - 1,
                                        )
                                    }
                                    disabled={mySubmissions.current_page === 1}
                                >
                                    <IconChevronLeft className="w-4 h-4" />
                                </Button>
                                <span className="text-sm text-muted-foreground">
                                    Page {mySubmissions.current_page} of{" "}
                                    {mySubmissions.last_page}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        handlePageChange(
                                            mySubmissions.current_page + 1,
                                        )
                                    }
                                    disabled={
                                        mySubmissions.current_page ===
                                        mySubmissions.last_page
                                    }
                                >
                                    <IconChevronRight className="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppLayout>
    );
}
