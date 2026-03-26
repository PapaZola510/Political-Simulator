import { Link, router } from '@inertiajs/react';
import { Briefcase, RotateCcw } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { home } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Presidential Office',
        href: home(),
        icon: Briefcase,
    },
];

export function AppSidebar() {
    const handleReset = () => {
        if (confirm('Are you sure you want to reset? This will clear your president selection and all game progress.')) {
            router.post('/president/reset');
        }
    };

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={home()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton onClick={handleReset} className="text-destructive">
                            <RotateCcw className="size-4" />
                            <span>New President</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
