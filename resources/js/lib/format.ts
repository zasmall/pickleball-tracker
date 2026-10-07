/**
 * Format a Y-m-d date string for display without timezone shifts.
 */
export function formatDate(date: string): string {
    const [year, month, day] = date.split('-').map(Number);

    return new Date(year, month - 1, day).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

export function formatDiff(diff: number): string {
    return diff > 0 ? `+${diff}` : String(diff);
}
