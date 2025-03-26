import React from 'react';
import { Button } from "@/components/ui/button"
import { 
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow 
} from "@/components/ui/table"
import { 
    Card,
    CardContent,
    CardHeader,
    CardTitle 
} from "@/components/ui/card"
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
} from "@/components/ui/sheet"
import { Badge } from "@/components/ui/badge"
import { useState, useEffect } from "react"
import { FiEdit3, FiTrash2, FiPlus, FiShield } from 'react-icons/fi';
import { RoleForm } from './RoleForm';

import { rolesService, type Role, type RoleModulePermissions } from '@/services/roles.service';
import { LoadingBar } from "../ui/loading-bar";

export default function Roles() {
    const [roles, setRoles] = useState<Role[]>([]);
    const [permissions, setPermissions] = useState<RoleModulePermissions>({});
    const [loading, setLoading] = useState(false);
    const [pageError, setPageError] = useState<string | null>(null);
    const [formError, setFormError] = useState<string | null>(null);
    const [selectedRole, setSelectedRole] = useState<Role | null>(null);
    const [isAddSheetOpen, setIsAddSheetOpen] = useState(false);
    const [isEditSheetOpen, setIsEditSheetOpen] = useState(false);
    const [isViewSheetOpen, setIsViewSheetOpen] = useState(false);
    const [viewRole, setViewRole] = useState<Role | null>(null);

    // Form states
    const [formName, setFormName] = useState('');
    const [formDescription, setFormDescription] = useState('');
    const [formPermissions, setFormPermissions] = useState<number[]>([]);
    const [permissionFilter, setPermissionFilter] = useState('');

    useEffect(() => {
        fetchRolesAndPermissions();
    }, []);

    const fetchRolesAndPermissions = async () => {
        setLoading(true);
        try {
            const data = await rolesService.getRolesAndPermissions();
            setRoles(data.roles);
            setPermissions(data.permissions);
            setPageError(null);
        } catch (err) {
            setPageError('Failed to fetch roles data');
            console.error('Error fetching roles:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleAddRole = async (name: string, description: string) => {
        try {
            const newRole = await rolesService.createRole({
                name,
                description,
                permissions: formPermissions
            });

            // Refresh roles to get the latest data
            await fetchRolesAndPermissions();
            
            setIsAddSheetOpen(false);
            resetForm();
        } catch (err) {
            setFormError('Failed to create role');
            console.error('Error creating role:', err);
        }
    };

    const handleEditRole = async (name: string, description: string) => {
        if (!selectedRole) return;

        try {
            const updatedRole = await rolesService.updateRole(selectedRole.id, {
                name,
                description,
                permissions: formPermissions
            });
            
            // Refresh roles to get the latest data
            await fetchRolesAndPermissions();
            
            setIsEditSheetOpen(false);
            resetForm();
        } catch (err) {
            setFormError('Failed to update role');
            console.error('Error updating role:', err);
        }
    };

    const handleDeleteRole = async (roleId: number) => {
        if (!confirm('Are you sure you want to delete this role?')) return;

        try {
            await rolesService.deleteRole(roleId);
            setRoles(roles.filter(role => role.id !== roleId));
        } catch (err) {
            setPageError('Failed to delete role');
            console.error('Error deleting role:', err);
        }
    };

    const resetForm = () => {
        setFormName('');
        setFormDescription('');
        setFormPermissions([]);
        setSelectedRole(null);
        setPermissionFilter('');
        setFormError(null);
    };

    const handleOpenAddRole = () => {
        resetForm();
        setIsAddSheetOpen(true);
    };

    const openEditSheet = (role: Role) => {
        setSelectedRole(role);
        setFormName(role.name);
        setFormDescription(role.description);
        setFormPermissions(role.permissions.map(p => p.id));
        setIsEditSheetOpen(true);
    };

    return (
        <div className="container mx-auto p-6 space-y-6">
            <Card className="border-0 shadow-md">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4 border-b">
                        <div className="space-y-1">
                            <div className="flex items-center gap-2">
                                <FiShield className="h-6 w-6 text-muted-foreground" />
                                <CardTitle className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Role Management</CardTitle>
                            </div>
                            <p className="text-sm text-muted-foreground">Manage user roles and their permissions</p>
                        </div>
                        <Button 
                            onClick={handleOpenAddRole}
                            className="bg-primary hover:bg-primary/90 items-center justify-center gap-2 rounded-lg bg-purple-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-purple-700 hover:shadow-md hover:shadow-purple-200/50 active:bg-purple-800 group-hover:scale-[1.02]"
                        >
                            <FiPlus className="mr-2 h-4 w-4" />
                            Add New Role
                        </Button>
                    </CardHeader>
                    <CardContent className="pt-6">
                        {loading ? (
                            <LoadingBar isLoading={loading} />
                        ) : pageError ? (
                            <div className="flex items-center justify-center p-6 text-destructive">
                                <span className="text-sm font-medium">{pageError}</span>
                            </div>
                        ) : (
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/50">
                                            <TableHead className="font-semibold">Role Name</TableHead>
                                            <TableHead className="font-semibold">Description</TableHead>
                                            <TableHead className="font-semibold">Permissions</TableHead>
                                            <TableHead className="text-right font-semibold">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {roles.map((role) => (
                                            <TableRow key={role.id} className="hover:bg-muted/50">
                                                <TableCell className="font-medium">{role.name}</TableCell>
                                                <TableCell className="text-muted-foreground">{role.description}</TableCell>
                                                <TableCell>
                                                    <div className="flex flex-wrap gap-1.5">
                                                        {role.permissions.slice(0, 3).map((permission) => (
                                                            <Badge
                                                                key={permission.name}
                                                                variant="secondary"
                                                                className="rounded-full text-xs font-normal"
                                                            >
                                                                {permission.name}
                                                            </Badge>
                                                        ))}
                                                        {role.permissions.length > 3 && (
                                                            <Badge 
                                                                variant="outline" 
                                                                className="rounded-full text-xs font-normal cursor-pointer hover:bg-primary/10"
                                                                onClick={() => {
                                                                    setViewRole(role);
                                                                    setIsViewSheetOpen(true);
                                                                }}
                                                            >
                                                                +{role.permissions.length - 3} more
                                                            </Badge>
                                                        )}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => openEditSheet(role)}
                                                            disabled={role.name === 'admin'}
                                                            className="hover:bg-primary/10 hover:text-primary"
                                                        >
                                                            <FiEdit3 className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleDeleteRole(role.id)}
                                                            disabled={role.name === 'admin'}
                                                            className="hover:bg-destructive/10 hover:text-destructive"
                                                        >
                                                            <FiTrash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Add Role Sheet */}
                <Sheet 
                    open={isAddSheetOpen} 
                    onOpenChange={(open) => {
                        if (!open) resetForm();
                        setIsAddSheetOpen(open);
                    }}
                >
                    <SheetContent side="right" className="w-full max-w-[400px] sm:max-w-[540px] flex flex-col h-full p-6">
                        <SheetHeader className="space-y-2">
                            <SheetTitle className="flex items-center gap-2 text-xl">
                                <FiPlus className="h-5 w-5 text-primary" />
                                Add New Role
                            </SheetTitle>
                            <SheetDescription>
                                Create a new role and assign permissions.
                            </SheetDescription>
                        </SheetHeader>
                        <RoleForm
                            initialName={formName}
                            initialDescription={formDescription}
                            formPermissions={formPermissions}
                            permissions={permissions}
                            permissionFilter={permissionFilter}
                            error={formError}
                            onPermissionChange={(permissionId, checked) => {
                                if (checked) {
                                    setFormPermissions([...formPermissions, permissionId]);
                                } else {
                                    setFormPermissions(formPermissions.filter(id => id !== permissionId));
                                }
                            }}
                            onPermissionFilterChange={setPermissionFilter}
                            onCancel={() => {
                                setIsAddSheetOpen(false);
                                resetForm();
                            }}
                            onSubmit={handleAddRole}
                        />
                    </SheetContent>
                </Sheet>

                {/* Edit Role Sheet */}
                <Sheet open={isEditSheetOpen} onOpenChange={setIsEditSheetOpen}>
                    <SheetContent side="right" className="w-full max-w-[400px] sm:max-w-[540px] flex flex-col h-full p-6">
                        <SheetHeader className="space-y-2">
                            <SheetTitle className="flex items-center gap-2 text-xl">
                                <FiShield className="h-5 w-5 text-primary" />
                                Edit Role
                            </SheetTitle>
                            <SheetDescription>
                                Make changes to role permissions and settings.
                            </SheetDescription>
                        </SheetHeader>
                        <RoleForm
                            initialName={formName}
                            initialDescription={formDescription}
                            formPermissions={formPermissions}
                            permissions={permissions}
                            permissionFilter={permissionFilter}
                            error={formError}
                            isEdit={true}
                            selectedRole={selectedRole}
                            onPermissionChange={(permissionId, checked) => {
                                if (checked) {
                                    setFormPermissions([...formPermissions, permissionId]);
                                } else {
                                    setFormPermissions(formPermissions.filter(id => id !== permissionId));
                                }
                            }}
                            onPermissionFilterChange={setPermissionFilter}
                            onCancel={() => {
                                setIsEditSheetOpen(false);
                                resetForm();
                            }}
                            onSubmit={handleEditRole}
                        />
                    </SheetContent>
                </Sheet>

            {/* View Permissions Sheet */}
            <Sheet open={isViewSheetOpen} onOpenChange={setIsViewSheetOpen}>
                <SheetContent side="right" className="w-full max-w-[400px] sm:max-w-[540px] flex flex-col h-full p-6">
                    <SheetHeader className="space-y-2">
                        <SheetTitle className="flex items-center gap-2 text-xl">
                            <FiShield className="h-5 w-5 text-primary" />
                            {viewRole?.name} Permissions
                        </SheetTitle>
                        <SheetDescription>
                            View all permissions assigned to this role
                        </SheetDescription>
                    </SheetHeader>
                    <div className="flex-1 space-y-2 py-6">
                        {viewRole?.permissions.map((permission) => (
                            <Badge 
                                key={permission.id}
                                variant="outline" 
                                className="rounded-full text-xs font-normal cursor-pointer hover:bg-primary/10"
                            >
                                {permission.name}
                            </Badge>
                        ))}
                    </div>
                    <SheetFooter>
                        <Button 
                            variant="outline" 
                            onClick={() => {
                                setIsViewSheetOpen(false);
                                setViewRole(null);
                            }}
                        >
                            <div className="mr-2 h-4 w-4" />
                            Close
                        </Button>
                    </SheetFooter>
                </SheetContent>
            </Sheet>
        </div>
    );
}
