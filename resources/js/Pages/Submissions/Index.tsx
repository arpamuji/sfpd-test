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
    submissions: SubmissionsData;
}

export default function Index({ submissions }: Props) {
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

            <Card>
                <CardHeader>
                    <CardTitle>All Submissions</CardTitle>
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
                            {submissions.data.length > 0 ? (
                                submissions.data.map((submission) => (
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
                    {submissions.last_page > 1 && (
                        <div className="flex items-center justify-between mt-4">
                            <p className="text-sm text-muted-foreground">
                                Showing {submissions.from} to {submissions.to}{" "}
                                of {submissions.total} results
                            </p>
                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        handlePageChange(
                                            submissions.current_page - 1,
                                        )
                                    }
                                    disabled={submissions.current_page === 1}
                                >
                                    <IconChevronLeft className="w-4 h-4" />
                                </Button>
                                <span className="text-sm text-muted-foreground">
                                    Page {submissions.current_page} of{" "}
                                    {submissions.last_page}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        handlePageChange(
                                            submissions.current_page + 1,
                                        )
                                    }
                                    disabled={
                                        submissions.current_page ===
                                        submissions.last_page
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
