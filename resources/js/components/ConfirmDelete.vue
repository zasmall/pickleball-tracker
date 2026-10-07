<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { RouteDefinition } from '@/wayfinder';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const props = defineProps<{
    title: string;
    description: string;
    action: RouteDefinition<'delete'>;
    label?: string;
}>();

function confirm(): void {
    router.visit(props.action, { preserveScroll: true });
}
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="destructive">{{ label ?? 'Delete' }}</Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary">Cancel</Button>
                </DialogClose>
                <DialogClose as-child>
                    <Button variant="destructive" @click="confirm">{{
                        label ?? 'Delete'
                    }}</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
