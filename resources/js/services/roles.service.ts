import { api } from '@/lib/auth';

export interface Permission {
    id: number;
    name: string;
    description: string;
    action: string;
}

export interface Role {
    id: number;
    name: string;
    description: string;
    permissions: Permission[];
}

export interface RoleModulePermissions {
    [module: string]: Permission[];
}

export interface RolesResponse {
    roles: Role[];
    permissions: RoleModulePermissions;
}

export const rolesService = {
    async getRolesAndPermissions(): Promise<RolesResponse> {
        console.log('fetching roles and permissions');

        const response = await api.get('/roles');
        const result = {
            roles: response.data.roles.map((role: Role) => ({
                ...role,
                permissions: role.permissions.map((p: Permission) => ({
                    ...p,
                    id: p.id
                }))
            })),
            permissions: response.data.permissions
        };
        console.log(result);
        return result;
    },

    async createRole(data: { name: string; description: string; permissions: number[] }): Promise<Role> {
        const response = await api.post('/roles', data);
        return {
            ...response.data.role,
            permissions: response.data.role.permissions.map((p: any) => p.id)
        };
    },

    async updateRole(id: number, data: { name: string; description: string; permissions: number[] }): Promise<Role> {
        const response = await api.post(`/roles/update/${id}`, data);
        return {
            ...response.data.role,
            permissions: response.data.role.permissions
        };
    },

    async deleteRole(id: number): Promise<void> {
        await api.post(`/roles/delete/${id}`);
    }
};
