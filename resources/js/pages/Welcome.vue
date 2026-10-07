<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChartLine, ClipboardList, Trophy, Users } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

const features = [
    {
        icon: ClipboardList,
        title: 'Log every game',
        description:
            'Singles or doubles, with the date, location, and final score.',
    },
    {
        icon: ChartLine,
        title: 'Track the standings',
        description:
            'A live leaderboard with win rates, point differentials, and streaks.',
    },
    {
        icon: Users,
        title: 'Know your matchups',
        description:
            'See how you do with each partner and against each opponent.',
    },
];
</script>

<template>
    <Head title="Welcome" />

    <div
        class="flex min-h-screen flex-col bg-background px-4 py-6 text-foreground sm:px-6"
    >
        <header
            class="mx-auto flex w-full max-w-5xl items-center justify-between"
        >
            <div class="flex items-center gap-2 font-semibold">
                <span
                    class="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground"
                >
                    <Trophy class="size-4" />
                </span>
                Pickleball Tracker
            </div>

            <nav class="flex items-center gap-2">
                <Button v-if="$page.props.auth.user" as-child>
                    <Link :href="dashboard()">Dashboard</Link>
                </Button>
                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="register()">Register</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main
            class="mx-auto flex w-full max-w-5xl flex-1 flex-col justify-center py-16"
        >
            <section class="max-w-2xl">
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">
                    Keep score of every game you play.
                </h1>
                <p class="mt-4 text-lg text-muted-foreground">
                    Pickleball Tracker logs your games and turns them into
                    stats, so you always know who's on top.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <Button v-if="$page.props.auth.user" size="lg" as-child>
                        <Link :href="dashboard()">Go to dashboard</Link>
                    </Button>
                    <template v-else>
                        <Button size="lg" as-child>
                            <Link :href="register()">Get started</Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link :href="login()">Log in</Link>
                        </Button>
                    </template>
                </div>
            </section>

            <section class="mt-16 grid gap-4 sm:grid-cols-3">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border"
                >
                    <component
                        :is="feature.icon"
                        class="size-5 text-muted-foreground"
                    />
                    <h2 class="mt-3 font-medium">{{ feature.title }}</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ feature.description }}
                    </p>
                </div>
            </section>
        </main>

        <footer class="mx-auto w-full max-w-5xl text-sm text-muted-foreground">
            Built with Laravel, Vue, and Inertia.
        </footer>
    </div>
</template>
