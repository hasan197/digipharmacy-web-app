import React from 'react';
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { ScrollArea } from "@/components/ui/scroll-area"
import { Checkbox } from "@/components/ui/checkbox"
import { SheetFooter } from "@/components/ui/sheet"
import { FiCheck, FiX } from 'react-icons/fi';
import { type Role, type RoleModulePermissions } from '@/services/roles.service';

interface RoleFormProps {
    initialName?: string;
    initialDescription?: string;
    formPermissions: number[];
    permissions: RoleModulePermissions;
    permissionFilter: string;
    error: string | null;
    isEdit?: boolean;
    selectedRole?: Role | null;
    onPermissionChange: (permissionId: number, checked: boolean) => void;
    onPermissionFilterChange: (value: string) => void;
    onCancel: () => void;
    onSubmit: (name: string, description: string) => void;
}

export function RoleForm({
    initialName = '',
    initialDescription = '',
    formPermissions,
    permissions,
    permissionFilter,
    error,
    isEdit = false,
    selectedRole,
    onPermissionChange,
    onPermissionFilterChange,
    onCancel,
    onSubmit
}: RoleFormProps) {
    const [name, setName] = React.useState(initialName);
    const [description, setDescription] = React.useState(initialDescription);
    const idPrefix = isEdit ? 'edit' : 'add';

    React.useEffect(() => {
        setName(initialName);
        setDescription(initialDescription);
    }, [initialName, initialDescription]);

    return (
        <div className="flex flex-col h-full">
            <div className="flex-1 overflow-hidden px-1">
                <div className="space-y-6 h-full flex flex-col">
                    {error && (
                        <div className="p-3 text-sm text-destructive bg-destructive/10 rounded-md flex-shrink-0">
                            {error}
                        </div>
                    )}
                    <div className="space-y-4 flex-shrink-0">
                        <div className="space-y-2">
                            <Label htmlFor={`${idPrefix}-name`}>Role Name</Label>
                            <Input
                                id={`${idPrefix}-name`}
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                placeholder="Enter role name"
                                className="w-full focus-visible:ring-1 focus-visible:ring-offset-0"
                                disabled={selectedRole?.name === 'admin'}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor={`${idPrefix}-description`}>Description</Label>
                            <Input
                                id={`${idPrefix}-description`}
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                placeholder="Enter role description"
                                className="w-full focus-visible:ring-1 focus-visible:ring-offset-0"
                                disabled={selectedRole?.name === 'admin'}
                            />
                        </div>
                    </div>
                    <div className="flex-1 min-h-0 space-y-2">
                        <div className="flex items-center justify-between">
                            <Label>Permissions</Label>
                            <Input
                                type="search"
                                placeholder="Filter permissions..."
                                value={permissionFilter}
                                onChange={(e) => onPermissionFilterChange(e.target.value)}
                                className="w-[200px] focus-visible:ring-1 focus-visible:ring-offset-0"
                            />
                        </div>
                        <ScrollArea className="h-[350px] rounded-md border p-4">
                            <div className="grid gap-6">
                                {Object.entries(permissions).map(([module, modulePermissions]) => {
                                    const filteredPermissions = modulePermissions.filter(permission =>
                                        permission.name.toLowerCase().includes(permissionFilter.toLowerCase()) ||
                                        permission.description.toLowerCase().includes(permissionFilter.toLowerCase())
                                    );
                                    
                                    if (filteredPermissions.length === 0) return null;
                                    
                                    return (
                                        <div key={module} className="space-y-3">
                                            <h4 className="font-medium text-sm text-muted-foreground uppercase tracking-wide">
                                                {module}
                                            </h4>
                                            <div className="grid gap-2">
                                                {filteredPermissions.map((permission) => (
                                                    <div key={permission.id} className="flex items-center space-x-2">
                                                        <Checkbox
                                                            id={`${idPrefix}-permission-${permission.id}`}
                                                            checked={formPermissions.includes(permission.id)}
                                                            onChange={(e) => onPermissionChange(permission.id, e.target.checked)}
                                                            disabled={selectedRole?.name === 'admin'}
                                                        />
                                                        <Label
                                                            htmlFor={`${idPrefix}-permission-${permission.id}`}
                                                            className="text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                                        >
                                                            {permission.name}
                                                        </Label>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </ScrollArea>
                    </div>
                </div>
            </div>
            <div className="flex-shrink-0 mt-6">
                <SheetFooter className="flex justify-end gap-3 pt-4 border-t">
                    <Button
                        variant="outline"
                        onClick={onCancel}
                    >
                        <FiX className="mr-2 h-4 w-4" />
                        Cancel
                    </Button>
                    <Button
                        onClick={() => onSubmit(name, description)}
                        className="hover:bg-destructive/10 hover:text-destructive"
                        disabled={selectedRole?.name === 'admin'}
                    >
                        <FiCheck className="mr-2 h-4 w-4" />
                        {isEdit ? 'Save Changes' : 'Save Role'}
                    </Button>
                </SheetFooter>
            </div>
        </div>
    );
}
