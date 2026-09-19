<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchContext
{
    public static function active(): ?object
    {
        $user = auth()->user();

        if (self::isOverall()) {
            return null;
        }

        $branches = self::availableBranches($user);

        if ($branches->isEmpty()) {
            return null;
        }

        $sessionBranchId = (int) session('active_branch_id');
        $branch = $branches->firstWhere('id', $sessionBranchId) ?: $branches->first();

        session(['active_branch_id' => $branch->id]);

        return $branch;
    }

    public static function activeId(): ?int
    {
        if (self::isOverall()) {
            return null;
        }

        return self::active()?->id;
    }

    public static function isOverall(): bool
    {
        return session('active_branch_id') === 'overall' && self::canUseOverall(auth()->user());
    }

    public static function canUseOverall(?User $user = null): bool
    {
        $user = $user ?: auth()->user();

        return $user && ((int) ($user->is_admin ?? 0) === 2 || (int) ($user->is_admin ?? 0) === 1 || in_array(($user->role ?? null), ['admin', 'superadmin'], true));
    }

    public static function availableBranches(?User $user = null): Collection
    {
        if (! Schema::hasTable('branches')) {
            return collect();
        }

        $user = $user ?: auth()->user();
        $query = Branch::query()->where('is_active', true)->orderBy('name');

        if (! $user) {
            return $query->get();
        }

        if (self::isSuperadmin($user) || self::isAdmin($user)) {
            return $query->get();
        }

        $assigned = $user->branches()
            ->where('branches.is_active', true)
            ->orderBy('branches.name')
            ->get();

        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        if (self::isAdmin($user)) {
            return $query->get();
        }

        return collect();
    }

    public static function canAccess(?User $user, int|string $branchId): bool
    {
        if (! $user) {
            return false;
        }

        if ($branchId === 'overall') {
            return self::canUseOverall($user);
        }

        if (self::isSuperadmin($user) || self::isAdmin($user)) {
            return true;
        }

        if ($user->branches()->where('branches.id', $branchId)->exists()) {
            return true;
        }

        return false;
    }

    public static function scope(Builder $query, string $column = 'branch_id'): Builder
    {
        $branchId = self::activeId();

        if ($branchId) {
            $query->where($column, $branchId);
        }

        return $query;
    }

    public static function isSuperadmin(?User $user = null): bool
    {
        $user = $user ?: auth()->user();

        return $user && ((int) ($user->is_admin ?? 0) === 2 || ($user->role ?? null) === 'superadmin');
    }

    public static function isAdmin(?User $user = null): bool
    {
        $user = $user ?: auth()->user();

        return $user && (in_array((int) ($user->is_admin ?? 0), [1, 2], true) || in_array(($user->role ?? null), ['admin', 'superadmin'], true));
    }

    public static function syncUserBranches(User $user, array $branchIds): void
    {
        if (! Schema::hasTable('branch_user')) {
            return;
        }

        $allowedIds = Branch::query()
            ->whereIn('id', collect($branchIds)->map(fn ($id) => (int) $id)->filter()->all())
            ->pluck('id')
            ->all();

        $user->branches()->sync($allowedIds);
    }

    public static function defaultBranchId(): ?int
    {
        if (! Schema::hasTable('branches')) {
            return null;
        }

        return DB::table('branches')->where('is_active', true)->orderBy('id')->value('id');
    }
}
